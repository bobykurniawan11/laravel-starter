<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserFilterRequest extends FormRequest
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
            'q' => 'nullable|string|max:255',
            'tenant_id' => 'nullable|integer|exists:tenants,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'q' => 'search query',
            'tenant_id' => 'tenant filter',
            'page' => 'page number',
            'per_page' => 'items per page',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tenant_id.exists' => 'The selected tenant does not exist.',
            'per_page.max' => 'Maximum 100 items per page allowed.',
        ];
    }

    /**
     * Get the search query
     */
    public function getSearch(): ?string
    {
        return $this->validated('q');
    }

    /**
     * Get the tenant filter
     */
    public function getTenantFilter(): ?int
    {
        return $this->validated('tenant_id');
    }

    /**
     * Get items per page
     */
    public function getPerPage(): int
    {
        return $this->validated('per_page') ?? 15;
    }

    /**
     * Check if user can filter by tenant
     */
    public function canFilterByTenant(): bool
    {
        return $this->user() && $this->user()->can('read-all-tenants');
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If user cannot filter by tenant, remove tenant_id parameter
        if (!$this->canFilterByTenant() && $this->has('tenant_id')) {
            $this->request->remove('tenant_id');
        }

        // Clean up empty search query
        if ($this->has('q') && trim((string) $this->input('q')) === '') {
            $this->request->remove('q');
        }
    }
} 