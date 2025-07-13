<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="TenantStoreRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="New Tenant Inc.")
 * )
 */
class TenantStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-tenants') || $this->user()?->can('create-tenant-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tenants,name'],
        ];
    }
}
