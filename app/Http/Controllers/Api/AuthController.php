<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Tenant;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. Create a new tenant for the user
        $tenant = Tenant::create([
            'name' => $request->name . "'s Company",
        ]);

        // 2. Create the user and assign them to the new tenant
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenant->id,
        ]);

        // 3. Assign the 'admin' role to the user for their new tenant
        $user->assign('admin');

        // 4. Generate JWT Token
        $token = JWTAuth::fromUser($user);

        // 5. Return a response consistent with the login method
        return response()->json([
            'success' => true,
            'message' => 'User registered and tenant created successfully',
            'user' => $user->load('tenant', 'roles'),
            'roles' => $user->getRoles(),
            'abilities' => $user->getAbilities(),
            'tenant' => $user->tenant,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }

        $user = User::where('email', $request->email)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user->load('tenant', 'roles'),
            'roles' => $user->getRoles(),
            'abilities' => $user->getAbilities(),
            'tenant' => $user->tenant,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'user' => $user->load('tenant', 'roles'),
            'roles' => $user->getRoles(),
            'abilities' => $user->getAbilities(),
            'tenant' => $user->tenant,
            'can_access_all_tenants' => $user->can('read-all-tenants'),
        ]);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh a token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = auth('api')->refresh();
            $user = auth('api')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'user' => $user->load('tenant', 'roles'),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token'
            ], 500);
        }
    }

    /**
     * Get user permissions
     *
     * @return JsonResponse
     */
    public function permissions(): JsonResponse
    {
        $user = auth('api')->user();
        
        return response()->json([
            'success' => true,
            'roles' => $user->getRoles(),
            'abilities' => $user->getAbilities(),
            'all_abilities' => $user->getAbilities()->pluck('name')
        ]);
    }
}
