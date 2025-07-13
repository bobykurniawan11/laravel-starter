<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RoleStoreRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="admin"),
 *     @OA\Property(property="title", type="string", maxLength=255, example="Administrator")
 * )
 */
class RoleStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
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
            'name.required' => 'Role name is required.',
            'name.string' => 'Role name must be a string.',
            'name.max' => 'Role name may not be greater than 255 characters.',
            'name.unique' => 'Role name already exists.',
            'title.string' => 'Role title must be a string.',
            'title.max' => 'Role title may not be greater than 255 characters.',
        ];
    }
}
