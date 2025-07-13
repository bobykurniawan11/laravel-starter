<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="API Endpoints for managing roles"
 * )
 */
class RoleController extends Controller
{
    private RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     tags={"Roles"},
     *     summary="Get list of roles",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="q", in="query", description="Search query", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="roles", type="object",
     *                     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RoleResource")),
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
                'roles' => new RoleCollection($this->service->paginate(15, $search)),
                'can' => [
                    'create' => $auth->can('create-roles'),
                    'update' => $auth->can('update-roles'),
                    'delete' => $auth->can('delete-roles'),
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     tags={"Roles"},
     *     summary="Create a new role",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RoleStoreRequest")),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/RoleResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
            'data' => new RoleResource($role)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     tags={"Roles"},
     *     summary="Get a role by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="data", ref="#/components/schemas/RoleResource"))),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
            'data' => new RoleResource($this->service->find($id))
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     tags={"Roles"},
     *     summary="Update a role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RoleUpdateRequest")),
     *     @OA\Response(response=200, description="Role updated successfully", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Role updated successfully"), @OA\Property(property="data", ref="#/components/schemas/RoleResource"))),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
            'data' => new RoleResource($role)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     tags={"Roles"},
     *     summary="Delete a role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Role deleted successfully", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Role deleted successfully"))),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
