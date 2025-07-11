<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    private PermissionService $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('read-permissions')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $search = $request->string('q')->toString();
        
        return response()->json([
            'success' => true,
            'data' => [
                'permissions' => $this->service->paginate(15, $search),
                'can' => [
                    'create' => $auth->can('create-permissions'),
                    'update' => $auth->can('update-permissions'),
                    'delete' => $auth->can('delete-permissions'),
                ],
            ],
        ]);
    }

    public function store(PermissionStoreRequest $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('create-permissions')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $permission = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'data' => $permission,
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('read-permissions')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $permission = $this->service->find($id);

        return response()->json([
            'success' => true,
            'data' => $permission,
        ]);
    }

    public function update(PermissionUpdateRequest $request, int $id): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('update-permissions')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $permission = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'data' => $permission,
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        
        // Check permission
        if (!$auth->can('delete-permissions')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully',
        ]);
    }
} 