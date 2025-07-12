<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantStoreRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    private TenantService $service;

    public function __construct(TenantService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('read-all-tenants') || $auth->can('read-tenant-data'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $search = $request->string('q')->toString();
        $tenants = $this->service->paginate(15, $search);
        return response()->json([
            'success' => true,
            'data' => $tenants
        ]);
    }

    public function store(TenantStoreRequest $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('create-tenants') || $auth->can('create-tenant-data'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $tenant = $this->service->create($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => $tenant
        ], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('read-all-tenants') || $auth->can('read-tenant-data'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $tenant = $this->service->find($id);
        return response()->json([
            'success' => true,
            'data' => $tenant
        ]);
    }

    public function update(TenantUpdateRequest $request, int $id): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('update-all-tenants') || $auth->can('update-tenant-data'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $tenant = $this->service->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('delete-all-tenants') || $auth->can('delete-tenant-data'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }
}
