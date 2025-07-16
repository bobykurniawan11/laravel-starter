<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SocialAccount;
use App\Models\Tenant;
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
    public function redirect(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider and handle login/registration.
     */
    public function callback(string $provider): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $socialUser */
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            Log::error('Socialite error', ['provider' => $provider, 'message' => $e->getMessage()]);
            return redirect()->route('login')->withErrors(['social' => 'Authentication failed, please try again.']);
        }

        // Find if the social account already exists
        $account = SocialAccount::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($account) {
            // If the account exists, log in the associated user.
            Auth::login($account->user, remember: true);
            return redirect()->intended('/');
        }

        // The social account is new. We need to find or create the local user.
        $userToLink = null;
        $email = $socialUser->getEmail();

        if (Auth::check()) {
            // Case 1: User is already logged in. Link the new social account to their existing user account.
            $userToLink = Auth::user();

            // Optional: Check for email conflicts if the social account's email is different
            // and already belongs to another user.
            if ($email) {
                $existingUserWithEmail = User::where('email', $email)->first();
                if ($existingUserWithEmail && $existingUserWithEmail->id !== $userToLink->id) {
                    return redirect()->route('profile.edit')
                        ->withErrors(['social' => "The email from this {$provider} account is already in use by another user."]);
                }
            }
        } else {
            // Case 2: User is not logged in. Find the user by email or create a new one.
            if (empty($email)) {
                return redirect()->route('login')->withErrors(['social' => "Your {$provider} account does not provide a public email address."]);
            }

            $userToLink = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: ucfirst($provider) . ' User',
                    'password' => bcrypt(Str::random(32)),
                    'email_verified_at' => now(), // Assume email is verified by the provider
                ]
            );
            if ($userToLink->wasRecentlyCreated) {
                $tenant = Tenant::create([
                    'name' => $userToLink->name . "'s Company",
                ]);
        
                $userToLink->assign('admin');
                $userToLink->tenant_id = $tenant->id;
                $userToLink->save();
            }
        }

        // Now, create the social account and link it to the user.
        $userToLink->socialAccounts()->create([
            'provider_name' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);

        // If the user's primary avatar is not set, use the one from the social provider.
        if (empty($userToLink->avatar)) {
            $userToLink->avatar = $socialUser->getAvatar();
            $userToLink->save();
        }

        // Log the user in.
        Auth::login($userToLink, remember: true);

        return redirect()->intended('/');
    }

    /**
     * Unlink a social account from the user.
     */
    public function unlink(string $provider): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->socialAccounts()->where('provider_name', $provider)->delete();

        return back()->with('status', ucfirst($provider) . ' account disconnected.');
    }
}
