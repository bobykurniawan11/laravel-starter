<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::get('permissions', [AuthController::class, 'permissions']);
    });

    // Profile routes
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::patch('/biodata', [ProfileController::class, 'updateProfile']);
        Route::patch('/password', [ProfileController::class, 'updatePassword']);
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
        Route::delete('/deactivate', [ProfileController::class, 'deactivate']);
    });
});

// Multi-tenant routes with role-based access control
Route::group(['middleware' => ['auth:api']], function () {
    
    // Developer routes - CRUD access to ALL tenants
    Route::group(['middleware' => 'can:read-all-tenants'], function () {
        Route::get('/tenants', function () {
            return response()->json([
                'success' => true,
                'message' => 'All tenants list (Developer access)',
                'data' => Tenant::with('users')->get()
            ]);
        });
        
        Route::get('/tenants/{tenant}', function (Tenant $tenant) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant details (Developer access)',
                'data' => $tenant->load('users.roles')
            ]);
        });
        
        Route::get('/tenants/{tenant}/users', function (Tenant $tenant) {
            return response()->json([
                'success' => true,
                'message' => 'All users in tenant (Developer access)',
                'data' => $tenant->users()->with('roles', 'abilities')->get()
            ]);
        });
    });
    
    Route::group(['middleware' => 'can:create-tenants'], function () {
        Route::post('/tenants', function (Request $request) {
            $request->validate(['name' => 'required|string|max:255']);
            
            $tenant = Tenant::create($request->only('name'));
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => $tenant
            ], 201);
        });
    });
    
    Route::group(['middleware' => 'can:update-all-tenants'], function () {
        Route::put('/tenants/{tenant}', function (Request $request, Tenant $tenant) {
            $request->validate(['name' => 'required|string|max:255']);
            
            $tenant->update($request->only('name'));
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant updated successfully',
                'data' => $tenant
            ]);
        });
    });
    
    Route::group(['middleware' => 'can:delete-all-tenants'], function () {
        Route::delete('/tenants/{tenant}', function (Tenant $tenant) {
            $tenant->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant deleted successfully'
            ]);
        });
    });

    // Admin/Staff routes - Read/Update their OWN tenant only
    Route::group(['middleware' => 'can:read-own-tenant'], function () {
        Route::get('/my-tenant', function (Request $request) {
            $user = $request->user();
            
            if (!$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not assigned to any tenant'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Your tenant information',
                'data' => $user->tenant()->with('users.roles')->first()
            ]);
        });
        
        Route::get('/my-tenant/users', function (Request $request) {
            $user = $request->user();
            
            if (!$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not assigned to any tenant'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Users in your tenant',
                'data' => User::where('tenant_id', $user->tenant_id)
                    ->with('roles', 'abilities')
                    ->get()
            ]);
        });
    });
    
    Route::group(['middleware' => 'can:update-own-tenant'], function () {
        Route::put('/my-tenant', function (Request $request) {
            $user = $request->user();
            $request->validate(['name' => 'required|string|max:255']);
            
            if (!$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not assigned to any tenant'
                ], 404);
            }
            
            $user->tenant()->update($request->only('name'));
            
            return response()->json([
                'success' => true,
                'message' => 'Your tenant updated successfully',
                'data' => $user->tenant
            ]);
        });
    });
    
    // Tenant data management examples
    Route::group(['middleware' => 'can:read-tenant-data'], function () {
        Route::get('/tenant-data', function (Request $request) {
            $user = $request->user();
            
            // Developer can see all tenant data, others only their own
            if ($user->can('read-all-tenants')) {
                $data = "All tenant data (Developer access)";
            } else {
                $data = "Tenant {$user->tenant_id} data only";
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant data access',
                'data' => $data,
                'tenant_id' => $user->tenant_id,
                'role' => $user->getRoles()->pluck('name')
            ]);
        });
    });

    // Developer-only: CRUD permissions
    Route::group(['middleware' => ['can:read-permissions']], function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    });

    Route::group(['middleware' => ['can:create-permissions']], function () {
        Route::post('/permissions', [PermissionController::class, 'store']);
    });

    Route::group(['middleware' => ['can:update-permissions']], function () {
        Route::put('/permissions/{id}', [PermissionController::class, 'update']);
        Route::patch('/permissions/{id}', [PermissionController::class, 'update']);
    });

    Route::group(['middleware' => ['can:delete-permissions']], function () {
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    });

    // Developer-only roles CRUD
    Route::group(['middleware'=>['can:read-roles']],function(){
        Route::get('/roles',[RoleController::class,'index']);
        Route::get('/roles/{id}',[RoleController::class,'show']);
    });
    Route::group(['middleware'=>['can:create-roles']],function(){
        Route::post('/roles',[RoleController::class,'store']);
    });
    Route::group(['middleware'=>['can:update-roles']],function(){
        Route::put('/roles/{id}',[RoleController::class,'update']);
        Route::patch('/roles/{id}',[RoleController::class,'update']);
    });
    Route::group(['middleware'=>['can:delete-roles']],function(){
        Route::delete('/roles/{id}',[RoleController::class,'destroy']);
    });

    // User CRUD routes with role-based access control
    Route::group(['middleware' => ['can:read-users']], function () {
        Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::get('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'show']);
    });

    Route::group(['middleware' => ['can:create-users']], function () {
        Route::post('/users', [\App\Http\Controllers\Api\UserController::class, 'store']);
    });

    Route::group(['middleware' => ['can:update-users']], function () {
        Route::put('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'update']);
        Route::patch('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'update']);
    });

    Route::group(['middleware' => ['can:delete-users']], function () {
        Route::delete('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'destroy']);
    });
});

// Example protected routes with role/permission checking
Route::group(['middleware' => ['auth:api']], function () {
    
    // Admin only routes
    Route::group(['middleware' => 'can:manage-users'], function () {
        Route::get('/admin/users', function () {
            return response()->json([
                'success' => true,
                'message' => 'Admin users list',
                'data' => \App\Models\User::with('roles', 'abilities')->get()
            ]);
        });
    });
    
    // Editor routes
    Route::group(['middleware' => 'can:create-posts'], function () {
        Route::get('/editor/posts', function () {
            return response()->json([
                'success' => true,
                'message' => 'Editor posts management'
            ]);
        });
    });
    
    // Basic user routes
    Route::group(['middleware' => 'can:view-posts'], function () {
        Route::get('/posts', function () {
            return response()->json([
                'success' => true,
                'message' => 'Public posts list'
            ]);
        });
    });
}); 