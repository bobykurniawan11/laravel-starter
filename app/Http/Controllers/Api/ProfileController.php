<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\DeactivateAccountRequest;
use App\Http\Requests\AvatarUploadRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ProfileResource;

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="API Endpoints for managing user profile"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ProfileController extends Controller
{
    /**
     * Generate MinIO URL for avatar
     */
    private function getAvatarUrl(?string $avatarPath): ?string
    {
        if (!$avatarPath) {
            return null;
        }

        return env('MINIO_ENDPOINT') . env('MINIO_BUCKET') . '/' . $avatarPath;
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Get current user profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProfileResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => new ProfileResource($user),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Update user profile (biodata)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProfileUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProfileResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new ProfileResource($user),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     tags={"Profile"},
     *     summary="Update user password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PasswordUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/profile/deactivate",
     *     tags={"Profile"},
     *     summary="Deactivate user account (soft delete)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DeactivateAccountRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account deactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Account deactivated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function deactivate(DeactivateAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->delete();
        \Illuminate\Support\Facades\Auth::guard('api')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Account deactivated successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/profile/avatar",
     *     tags={"Profile"},
     *     summary="Upload user avatar",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/AvatarUploadRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avatar uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avatar uploaded successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="avatar_url", type="string", example="https://minio.example.com/bucket/avatars/avatar.jpg"),
     *                 @OA\Property(property="avatar_path", type="string", example="avatars/avatar.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function uploadAvatar(AvatarUploadRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('minio')->exists($user->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('minio')->delete($user->avatar);
        }
        $avatarPath = $request->file('avatar')->store('avatars', 'minio');
        $user->update([
            'avatar' => $avatarPath,
        ]);
        $avatarUrl = env('MINIO_ENDPOINT') . env('MINIO_BUCKET') . '/' . $avatarPath;
        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => $avatarUrl,
                'avatar_path' => $avatarPath,
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/profile/avatar",
     *     tags={"Profile"},
     *     summary="Delete user avatar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Avatar deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avatar deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('minio')->exists($user->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('minio')->delete($user->avatar);
        }
        $user->update([
            'avatar' => null,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Avatar deleted successfully'
        ]);
    }
}
