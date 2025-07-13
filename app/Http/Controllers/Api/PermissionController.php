<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use App\Http\Resources\PermissionCollection;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\PermissionResource;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="API Endpoints for managing permissions"
 * )
 */
class PermissionController extends Controller
{
    private PermissionService $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/permissions",
     *     tags={"Permissions"},
     *     summary="Get list of permissions",
     *     description="Returns paginated list of permissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="permissions", type="object",
     *                     @OA\Property(property="data", type="array",
     *                         @OA\Items(ref="#/components/schemas/PermissionResource")
     *                     ),
     *                     @OA\Property(property="meta", type="object",
     *                         @OA\Property(property="total", type="integer", example=100),
     *                         @OA\Property(property="count", type="integer", example=15),
     *                         @OA\Property(property="per_page", type="integer", example=15),
     *                         @OA\Property(property="current_page", type="integer", example=1),
     *                         @OA\Property(property="total_pages", type="integer", example=7)
     *                     )
     *                 ),
     *                 @OA\Property(property="can", type="object",
     *                     @OA\Property(property="create", type="boolean"),
     *                     @OA\Property(property="update", type="boolean"),
     *                     @OA\Property(property="delete", type="boolean")
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
                'permissions' => new PermissionCollection($this->service->paginate(15, $search)),
                'can' => [
                    'create' => $auth->can('create-permissions'),
                    'update' => $auth->can('update-permissions'),
                    'delete' => $auth->can('delete-permissions'),
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/permissions",
     *     tags={"Permissions"},
     *     summary="Create a new permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PermissionStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/PermissionResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/permissions/{id}",
     *     tags={"Permissions"},
     *     summary="Get a permission by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PermissionResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
            'data' => new PermissionResource($permission),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/permissions/{id}",
     *     tags={"Permissions"},
     *     summary="Update a permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PermissionUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/PermissionResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
            'data' => new PermissionResource($permission),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/permissions/{id}",
     *     tags={"Permissions"},
     *     summary="Delete a permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
