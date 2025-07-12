<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
