<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserFilterRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Models\Tenant;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(UserFilterRequest $request): UserCollection
    {
        $search = $request->getSearch();
        $tenantFilter = $request->getTenantFilter();
        $perPage = $request->getPerPage();
        
        $users = $this->service->paginate($perPage, $search, $request->user(), $tenantFilter);
        
        $tenants = $request->user()->can('read-all-tenants') 
            ? Tenant::select('id', 'name')->orderBy('name')->get() 
            : [];

        $collection = new UserCollection($users);
        
        return $collection->additional([
            'filters' => [
                'search' => $search,
                'tenant_filter' => $tenantFilter,
            ],
            'options' => [
                'tenants' => $tenants,
                'roles' => $this->getAvailableRoles($request->user()),
                'is_developer' => $request->user()->can('read-all-tenants'),
            ],
            'permissions' => [
                'can_create' => $request->user()->can('create-tenant-users'),
                'can_update' => $request->user()->can('update-tenant-users'),
                'can_delete' => $request->user()->can('delete-tenant-users'),
            ]
        ]);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('create-tenant-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = $this->service->create($request->validated(), $auth);
        $user->load('roles', 'tenant');
        
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('read-tenant-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = $this->service->find($id, $auth);
        $user->load('roles', 'tenant');
        
        return response()->json([
            'success' => true,
            'data' => new UserResource($user)
        ]);
    }

    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('update-tenant-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = $this->service->update($id, $request->validated(), $auth);
        $user->load('roles', 'tenant');
        
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('delete-tenant-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $this->service->delete($id, $auth);
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
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
} 