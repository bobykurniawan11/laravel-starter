<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
