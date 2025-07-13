<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class SocialController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(string $provider = 'github'): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider and handle login/registration.
     */
    public function callback(string $provider = 'github')
    {
        $isLinking = Auth::check();
        try {
            $social = Socialite::driver($provider)->stateless()->user();
        } catch (Throwable $e) {
            Log::error('Socialite error', ['provider' => $provider, 'message' => $e->getMessage()]);
            return redirect()->route($isLinking ? 'profile.edit' : 'login')->withErrors(['social' => 'Authentication failed, please try again.']);
        }

        if ($isLinking) {
            $current = Auth::user();
            // if another user already has this provider_id, abort
            $existing = User::where('provider_name', $provider)->where('provider_id', $social->getId())->first();
            if ($existing && $existing->id !== $current->id) {
                return redirect()->route('profile.edit')->withErrors(['social' => 'This ' . $provider . ' account is linked to another user.']);
            }

            $current->forceFill([
                'provider_name' => $provider,
                'provider_id' => $social->getId(),
                'avatar' => $social->getAvatar() ?: $current->avatar,
            ])->save();

            return redirect()->route('profile.edit')->with('status', '' . $provider . ' account linked successfully.');
        }

        // 1) Already linked
        $user = User::query()
            ->where('provider_name', $provider)
            ->where('provider_id', $social->getId())
            ->first();

        // 2) Existing email, link it
        if (! $user && $social->getEmail()) {
            $user = User::where('email', $social->getEmail())->first();

            if ($user) {
                $user->forceFill([
                    'provider_name' => $provider,
                    'provider_id'   => $social->getId(),
                    'avatar'        => $social->getAvatar(),
                ])->save();
            }
        }

        // 3) Brand-new user
        if (! $user) {
            $email = $social->getEmail() ?: $social->getId() . '@' . $provider . '.local';
            $user = User::create([
                'name'            => $social->getName() ?: $social->getNickname() ?: ucfirst($provider) . ' User',
                'email'           => $email,
                'email_verified_at' => now(),
                'password'        => bcrypt(Str::random(32)),
                'provider_name'   => $provider,
                'provider_id'     => $social->getId(),
                'avatar'          => $social->getAvatar(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended('/');
    }

    // Add unlink method
    public function unlink(string $provider = 'github')
    {
        $user = Auth::user();
        $user->forceFill([
            'provider_name' => null,
            'provider_id' => null,
        ])->save();

        return back()->with('status', ucfirst($provider) . ' account disconnected.');
    }
}
