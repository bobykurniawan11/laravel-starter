<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Silber\Bouncer\Database\Role;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RoleService
{
    public function all(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        $query = Role::query()->orderBy('name');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param array{name:string, title?:string|null} $data
     */
    public function create(array $data): Role
    {
        $this->validate($data);
        return Role::firstOrCreate(['name' => $data['name']], [
            'title' => $data['title'] ?? null,
        ]);
    }

    public function find(int $id): Role
    {
        $role = Role::find($id);
        if (! $role) {
            throw new NotFoundHttpException('Role not found');
        }
        return $role;
    }

    /**
     * @param array{name?:string,title?:string|null} $data
     */
    public function update(int $id, array $data): Role
    {
        $role = $this->find($id);
        $this->validate($data, $role->id);
        $role->fill($data);
        $role->save();
        return $role;
    }

    public function delete(int $id): bool
    {
        $role = $this->find($id);
        return (bool) $role->delete();
    }

    private function validate(array $data, ?int $ignoreId = null): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name' . ($ignoreId ? ",{$ignoreId}" : '')],
            'title' => ['nullable', 'string', 'max:255'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->errors()->first());
        }
    }
} 