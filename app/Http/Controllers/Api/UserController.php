<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
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

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('q')->toString();
        $tenantFilter = $request->integer('tenant_id', null);
        
        $users = $this->service->paginate(15, $search, $request->user(), $tenantFilter);
        
        $tenants = $request->user()->can('read-all-tenants') 
            ? Tenant::select('id', 'name')->orderBy('name')->get() 
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'tenants' => $tenants,
                'search' => $search,
                'tenantFilter' => $tenantFilter,
                'isDeveloper' => $request->user()->can('read-all-tenants'),
                'roles' => $this->getAvailableRoles($request->user()),
            ]
        ]);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('create-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = $this->service->create($request->validated(), $auth);
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('read-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = $this->service->find($id, $auth);
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('update-users')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = $this->service->update($id, $request->validated(), $auth);
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('delete-users')) {
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