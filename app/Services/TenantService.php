<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantService
{
    /**
     * Get all tenants.
     */
    public function all(): Collection
    {
        return Tenant::query()->orderBy('name')->get();
    }

    /**
     * Create a new tenant.
     *
     * @param  array{ name: string }  $data
     */
    public function create(array $data): Tenant
    {
        $this->validate($data);
        return Tenant::query()->create([
            'name' => $data['name'],
        ]);
    }

    /**
     * Get a single tenant by id.
     */
    public function find(int $id): Tenant
    {
        $tenant = Tenant::find($id);
        if (! $tenant) {
            throw new NotFoundHttpException('Tenant not found');
        }
        return $tenant;
    }

    /**
     * Update an existing tenant.
     *
     * @param  array{ name?: string }  $data
     */
    public function update(int $id, array $data): Tenant
    {
        $tenant = $this->find($id);
        $this->validate($data, $tenant->id);
        $tenant->fill($data);
        $tenant->save();
        return $tenant;
    }

    /**
     * Delete a tenant.
     */
    public function delete(int $id): bool
    {
        $tenant = $this->find($id);
        return (bool) $tenant->delete();
    }

    /**
     * Validate incoming data.
     *
     * @param  array<string, mixed>  $data
     */
    private function validate(array $data, ?int $ignoreId = null): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:tenants,name' . ($ignoreId ? ',' . $ignoreId : '')],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->errors()->first());
        }
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?int $tenantId = null)
    {
        $query = Tenant::query()->orderBy('name');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($tenantId) {
            $query->where('id', $tenantId);
        }
        return $query->paginate($perPage)->withQueryString();
    }
}
