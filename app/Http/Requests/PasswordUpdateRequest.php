<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="PasswordUpdateRequest",
 *     required={"current_password", "password", "password_confirmation"},
 *     @OA\Property(property="current_password", type="string", example="oldpassword123"),
 *     @OA\Property(property="password", type="string", example="newpassword123"),
 *     @OA\Property(property="password_confirmation", type="string", example="newpassword123")
 * )
 */
class PasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }
}
