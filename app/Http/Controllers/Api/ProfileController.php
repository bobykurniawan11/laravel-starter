<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

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
        
        return env('MINIO_ENDPOINT') .env('MINIO_BUCKET') . '/' . $avatarPath;
    }

    /**
     * Get current user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $this->getAvatarUrl($user->avatar),
                'tenant_id' => $user->tenant_id,
                'roles' => $user->getRoles()->pluck('name'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update user profile (biodata)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $this->getAvatarUrl($user->avatar),
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Deactivate user account (soft delete)
     */
    public function deactivate(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();
        $user->delete(); // Soft delete

        // Revoke all tokens for this user
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Account deactivated successfully'
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('minio')->exists($user->avatar)) {
            Storage::disk('minio')->delete($user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'minio');

        $user->update([
            'avatar' => $avatarPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => $this->getAvatarUrl($avatarPath),
                'avatar_path' => $avatarPath,
            ]
        ]);
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('minio')->exists($user->avatar)) {
            Storage::disk('minio')->delete($user->avatar);
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