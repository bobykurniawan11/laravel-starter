<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'tenant_id' => $this->tenant_id,
            'tenant' => $this->whenLoaded('tenant', function () {
                return [
                    'id' => $this->tenant->id,
                    'name' => $this->tenant->name,
                ];
            }),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'name' => $role->name,
                        'title' => $role->title ?? ucfirst($role->name),
                    ];
                });
            }),
            'primary_role' => $this->whenLoaded('roles', function () {
                return $this->roles->first()?->name;
            }),
            'avatar_url' => $this->when($this->avatar, function () {
                return env('MINIO_ENDPOINT') . env('MINIO_BUCKET') . '/' . $this->avatar;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'can_update' => $request->user()?->can('update-tenant-users'),
                'can_delete' => $request->user()?->can('delete-tenant-users'),
                'is_current_user' => $request->user()?->id === $this->id,
            ],
        ];
    }
} 