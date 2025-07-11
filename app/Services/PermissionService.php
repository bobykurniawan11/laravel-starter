<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Silber\Bouncer\Database\Ability;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PermissionService
{
    /**
     * Get all permissions.
     */
    public function all(): Collection
    {
        return Ability::query()->orderBy('name')->get();
    }

    /**
     * Create a new permission.
     *
     * @param  array{ name: string, title?: string|null }  $data
     */
    public function create(array $data): Ability
    {
        $this->validate($data);

        return Ability::query()->firstOrCreate([
            'name' => $data['name'],
        ], [
            'title' => $data['title'] ?? null,
        ]);
    }

    /**
     * Get a single permission by id.
     */
    public function find(int $id): Ability
    {
        $ability = Ability::find($id);

        if (! $ability) {
            throw new NotFoundHttpException('Permission not found');
        }

        return $ability;
    }

    /**
     * Update an existing permission.
     *
     * @param  array{ name?: string, title?: string|null }  $data
     */
    public function update(int $id, array $data): Ability
    {
        $ability = $this->find($id);

        $this->validate($data, $ability->id);

        $ability->fill($data);
        $ability->save();

        return $ability;
    }

    /**
     * Delete a permission.
     */
    public function delete(int $id): bool
    {
        $ability = $this->find($id);

        return (bool) $ability->delete();
    }

    /**
     * Validate incoming data.
     *
     * @param  array<string, mixed>  $data
     */
    private function validate(array $data, ?int $ignoreId = null): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:abilities,name' . ($ignoreId ? ',' . $ignoreId : '')],
            'title' => ['nullable', 'string', 'max:255'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->errors()->first());
        }
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        $query = Ability::query()->orderBy('name');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        return $query->paginate($perPage)->withQueryString();
    }
} 