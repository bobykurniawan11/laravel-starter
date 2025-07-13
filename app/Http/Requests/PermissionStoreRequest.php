<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="PermissionStoreRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="manage-users"),
 *     @OA\Property(property="title", type="string", maxLength=255, example="Manage Users")
 * )
 */
class PermissionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:abilities,name'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required.',
            'name.string' => 'Permission name must be a string.',
            'name.max' => 'Permission name may not be greater than 255 characters.',
            'name.unique' => 'Permission name already exists.',
            'title.string' => 'Permission title must be a string.',
            'title.max' => 'Permission title may not be greater than 255 characters.',
        ];
    }
}
