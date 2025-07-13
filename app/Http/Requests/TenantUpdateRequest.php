<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="TenantUpdateRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="Updated Tenant Inc.")
 * )
 */
class TenantUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update-all-tenants') || $this->user()?->can('update-tenant-data') ?? false;
    }

    public function rules(): array
    {
        $tenantId = $this->route('id') ?? $this->route('tenant');
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tenants,name,' . $tenantId],
        ];
    }
}
