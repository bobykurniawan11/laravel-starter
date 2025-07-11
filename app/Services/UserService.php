<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserService
{
    /**
     * Paginate users for the current context.
     */
    public function paginate(int $perPage, ?string $search, User $auth, ?int $tenantFilter = null): LengthAwarePaginator
    {
        $query = User::query()->with('roles:name', 'tenant:id,name');
        $query->orderBy('name');
        
        if (! $auth->can('read-all-tenants')) {
            // Non-developer users can only see users from their own tenant
            $query->where('tenant_id', $auth->tenant_id);
        } else {
            // Developer can see all users, but can filter by specific tenant
            if ($tenantFilter !== 0) {
                $query->where('tenant_id', $tenantFilter);
            }
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        return $query->paginate($perPage)->withQueryString();
    }

    public function find(int $id, User $auth): User
    {
        $user = User::with('roles:name')->find($id);
        if (! $user) {
            throw new NotFoundHttpException('User not found');
        }
        if (! $auth->can('read-all-tenants') && $user->tenant_id !== $auth->tenant_id) {
            throw new NotFoundHttpException();
        }
        return $user;
    }

    /**
     * @param array{name:string,email:string,password:string,tenant_id?:int} $data
     */
    public function create(array $data, User $auth): User
    {
        if (! $auth->can('create-tenant-users')) {
            abort(403);
        }
        $isDeveloper = $auth->can('read-all-tenants');

        if (! $isDeveloper) {
            // Force tenant_id to current user's tenant and ignore any provided value
            $data['tenant_id'] = $auth->tenant_id;
        }

        $roleName = $data['role'] ?? 'staff';
        // Role assignment rules same as update:
        // - Developer can assign any role
        // - Admin can assign admin/staff (not developer)
        // - Staff can only assign staff
        if(!$auth->can('read-all-tenants')) {
            if($auth->isA('admin') && in_array($roleName, ['admin', 'staff'])) {
                // Admin can assign admin or staff roles
                $roleName = $roleName;
            } else {
                // Staff can only assign staff role
                $roleName = 'staff';
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tenant_id' => $data['tenant_id'] ?? null,
        ]);

        Bouncer::assign($roleName)->to($user);
        return $user;
    }

    /**
     * @param array{name?:string,email?:string,password?:string|null} $data
     */
    public function update(int $id, array $data, User $auth): User
    {
        $user=$this->find($id,$auth);
        if (! $auth->can('update-tenant-users') && $auth->id !== $user->id) {
            abort(403);
        }
        // Remove empty password to avoid validation fail when password not changed
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }
        $user->fill(Arr::only($data,['name','email']));
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if(isset($data['role'])){
            $roleName=$data['role'];
            // Role assignment rules:
            // - Developer can assign any role
            // - Admin can assign admin/staff (not developer)
            // - Staff can only assign staff
            if(!$auth->can('read-all-tenants')) {
                if($auth->isA('admin') && in_array($roleName, ['admin', 'staff'])) {
                    // Admin can assign admin or staff roles
                    $roleName = $roleName;
                } else {
                    // Staff can only assign staff role
                    $roleName = 'staff';
                }
            }
            
            // Remove all existing roles first
            foreach(['developer', 'admin', 'staff'] as $role) {
                if($user->isA($role)) {
                    Bouncer::retract($role)->from($user);
                }
            }
            
            // Assign new role
            Bouncer::assign($roleName)->to($user);
            
            // Refresh user to get updated roles
            $user->refresh();
            $user->load('roles');
        }
        return $user;
    }

    public function delete(int $id, User $auth): bool
    {
        $user=$this->find($id,$auth);
        if (! $auth->can('delete-tenant-users')) {
            abort(403);
        }
        return (bool)$user->delete();
    }
} 