<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AvatarUploadRequest",
 *     required={"avatar"},
 *     @OA\Property(property="avatar", type="string", format="binary", description="Avatar image file (jpeg, png, jpg, gif)")
 * )
 */
class AvatarUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
