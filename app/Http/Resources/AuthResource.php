<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AuthResource",
 *     type="object",
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/UserResource"
 *     ),
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         example="1|xxxxxxxxxxxxxxxxxxxxxxxxxxxx"
 *     )
 * )
 */
class AuthResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource['user']),
            'token' => $this->resource['token'],
        ];
    }
}
