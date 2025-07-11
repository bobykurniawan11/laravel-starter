<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('read-roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $search = $request->string('q')->toString();
        
        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $this->service->paginate(15, $search),
                'can' => [
                    'create' => $auth->can('create-roles'),
                    'update' => $auth->can('update-roles'),
                    'delete' => $auth->can('delete-roles'),
                ],
            ],
        ]);
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('create-roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $role = $this->service->create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('read-roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->service->find($id)
        ]);
    }

    public function update(RoleUpdateRequest $request, int $id): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('update-roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $role = $this->service->update($id, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('delete-roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $this->service->delete($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }
} 