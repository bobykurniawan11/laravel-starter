<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        
        // Prepare auth data with roles and permissions
        $authData = [
            'user' => $user,
        ];

        if ($user) {
            $user->load('socialAccounts');
            $authData['roles'] = $user->getRoles()->pluck('name')->toArray();
            $authData['permissions'] = $user->getAbilities()->pluck('name')->toArray();
            $authData['tenant'] = $user->tenant;
            $authData['tenant_id'] = $user->tenant_id;
            $authData['social_accounts'] = $user->socialAccounts;
            
            // Helper methods for frontend
            $authData['can'] = [
                'manage_all_tenants' => $user->can('read-all-tenants'),
                'manage_own_tenant' => $user->can('read-own-tenant'),
                'update_own_tenant' => $user->can('update-own-tenant'),
                'manage_tenant_users' => $user->can('read-tenant-users'),
                'create_tenant_users' => $user->can('create-tenant-users'),
                'update_tenant_users' => $user->can('update-tenant-users'),
                'delete_tenant_users' => $user->can('delete-tenant-users'),
                'manage_tenant_data' => $user->can('read-tenant-data'),
                'create_tenant_data' => $user->can('create-tenant-data'),
                'update_tenant_data' => $user->can('update-tenant-data'),
                'delete_tenant_data' => $user->can('delete-tenant-data'),
            ];
            
            // Role checks
            $authData['is'] = [
                'developer' => $user->isA('developer'),
                'admin' => $user->isA('admin'),
                'staff' => $user->isA('staff'),
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => $authData,
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
