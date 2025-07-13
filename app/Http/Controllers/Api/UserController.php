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

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for managing users"
 * )
 */
class UserController extends Controller
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get list of users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="q", in="query", description="Search query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="tenant_id", in="query", description="Filter by tenant ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserResource")),
     *              @OA\Property(property="links", type="object"),
     *              @OA\Property(property="meta", type="object"),
     *              @OA\Property(property="filters", type="object",
     *                  @OA\Property(property="search", type="string", nullable=true),
     *                  @OA\Property(property="tenant_filter", type="integer", nullable=true)
     *              ),
     *              @OA\Property(property="options", type="object",
     *                  @OA\Property(property="tenants", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                  @OA\Property(property="is_developer", type="boolean")
     *              ),
     *              @OA\Property(property="permissions", type="object",
     *                  @OA\Property(property="can_create", type="boolean"),
     *                  @OA\Property(property="can_update", type="boolean"),
     *                  @OA\Property(property="can_delete", type="boolean")
     *              )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Get a user by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
