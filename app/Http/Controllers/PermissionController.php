<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Silber\Bouncer\Database\Ability;

class PermissionController extends Controller
{
    private PermissionService $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): Response
    {
        if (!$request->user()->can('read-permissions')) {
            abort(403, 'Unauthorized');
        }

        $search = $request->string('q')->toString();

        return Inertia::render('permissions/index', [
            'permissions' => $this->service->paginate(15, $search),
            'search' => $search,
            'can' => [
                'create' => $request->user()->can('create-permissions'),
                'update' => $request->user()->can('update-permissions'),
                'delete' => $request->user()->can('delete-permissions'),
            ],
        ]);
    }

    public function create(): Response
    {
        if (!request()->user()->can('create-permissions')) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('permissions/create');
    }

    public function store(PermissionStoreRequest $request): RedirectResponse
    {
        if (!$request->user()->can('create-permissions')) {
            abort(403, 'Unauthorized');
        }

        $permission = $this->service->create($request->validated());

        return redirect()->route('permissions.index')->with('success', 'Permission created');
    }

    public function edit(int $id): Response
    {
        if (!request()->user()->can('update-permissions')) {
            abort(403, 'Unauthorized');
        }

        /** @var Ability $permission */
        $permission = $this->service->find($id);

        return Inertia::render('permissions/edit', [
            'permission' => $permission,
        ]);
    }

    public function update(PermissionUpdateRequest $request, int $id): RedirectResponse
    {
        if (!$request->user()->can('update-permissions')) {
            abort(403, 'Unauthorized');
        }

        $this->service->update($id, $request->validated());

        return redirect()->route('permissions.index')->with('success', 'Permission updated');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        if (!$request->user()->can('delete-permissions')) {
            abort(403, 'Unauthorized');
        }

        $this->service->delete($id);

        return redirect()->route('permissions.index')->with('success', 'Permission deleted');
    }
} 