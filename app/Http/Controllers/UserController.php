<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Models\Tenant;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(UserFilterRequest $request): Response
    {
        $auth = $request->user();
        $search = $request->getSearch();
        $tenantFilter = $request->getTenantFilter();
        $perPage = $request->getPerPage();



        $users = $this->service->paginate($perPage, $search, $auth, $tenantFilter);

        $tenants = $auth->can('read-all-tenants') ? Tenant::select('id','name')->orderBy('name')->get() : [];

        return Inertia::render('users/index', [
            'users' => $users,
            'search' => $search,
            'tenantFilter' => $tenantFilter,
            'tenants' => $tenants,
            'isDeveloper' => $auth->can('read-all-tenants'),
            'roles' => $this->getAvailableRoles($auth),
        ]);
    }

    private function getAvailableRoles($auth): array
    {
        if ($auth->can('read-all-tenants')) {
            // Developer can assign all roles
            return ['developer', 'admin', 'staff'];
        } elseif ($auth->isA('admin')) {
            // Admin can assign admin and staff
            return ['admin', 'staff'];
        } else {
            // Staff can only assign staff
            return ['staff'];
        }
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $auth = $request->user();
        $this->service->create($request->validated(), $auth);
        return back()->with('success','User created');
    }

    public function update(UserUpdateRequest $request, int $id): RedirectResponse
    {
        $auth=$request->user();
        $this->service->update($id, $request->validated(), $auth);
        return back()->with('success','User updated');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->service->delete($id, $request->user());
        return back()->with('success','User deleted');
    }
} 