<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantStoreRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Http\Resources\TenantCollection;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Tenants",
 *     description="API Endpoints for managing tenants"
 * )
 */
class TenantController extends Controller
{
    private TenantService $service;

    public function __construct(TenantService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/tenants",
     *     tags={"Tenants"},
     *     summary="Get list of tenants",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="q", in="query", description="Search query", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TenantResource")),
     *                 @OA\Property(property="meta", type="object",
     *                     @OA\Property(property="total", type="integer"),
     *                     @OA\Property(property="count", type="integer"),
     *                     @OA\Property(property="per_page", type="integer"),
     *                     @OA\Property(property="current_page", type="integer"),
     *                     @OA\Property(property="total_pages", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('read-all-tenants') || $auth->can('read-tenant-data'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $search = $request->string('q')->toString();
        $tenants = $this->service->paginate(15, $search);
        return response()->json([
            'success' => true,
            'data' => new TenantCollection($tenants)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tenants",
     *     tags={"Tenants"},
     *     summary="Create a new tenant",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TenantStoreRequest")),
     *     @OA\Response(
     *         response=201,
     *         description="Tenant created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tenant created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TenantResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(TenantStoreRequest $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('create-tenants') || $auth->can('create-tenant-data'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $tenant = $this->service->create($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => new TenantResource($tenant)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tenants/{id}",
     *     tags={"Tenants"},
     *     summary="Get a tenant by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/TenantResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('read-all-tenants') || $auth->can('read-tenant-data'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $tenant = $this->service->find($id);
        return response()->json([
            'success' => true,
            'data' => new TenantResource($tenant)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/tenants/{id}",
     *     tags={"Tenants"},
     *     summary="Update a tenant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TenantUpdateRequest")),
     *     @OA\Response(
     *         response=200,
     *         description="Tenant updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tenant updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TenantResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function update(TenantUpdateRequest $request, int $id): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('update-all-tenants') || $auth->can('update-tenant-data'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $tenant = $this->service->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => new TenantResource($tenant)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/tenants/{id}",
     *     tags={"Tenants"},
     *     summary="Delete a tenant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Tenant deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tenant deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $auth = $request->user();
        if (!($auth->can('delete-all-tenants') || $auth->can('delete-tenant-data'))) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $this->service->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }
}
