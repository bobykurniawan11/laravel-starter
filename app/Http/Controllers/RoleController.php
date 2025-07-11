<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Silber\Bouncer\Database\Role as BouncerRole;

class RoleController extends Controller
{
    private RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): Response
    {
        if (!$request->user()->can('read-roles')) {
            abort(403, 'Unauthorized');
        }

        $search = $request->string('q')->toString();
        
        return Inertia::render('roles/index', [
            'roles' => $this->service->paginate(15, $search),
            'search' => $search,
            'can' => [
                'create' => $request->user()->can('create-roles'),
                'update' => $request->user()->can('update-roles'),
                'delete' => $request->user()->can('delete-roles'),
            ],
        ]);
    }

    public function store(RoleStoreRequest $request): RedirectResponse
    {
        if (!$request->user()->can('create-roles')) {
            abort(403, 'Unauthorized');
        }

        $this->service->create($request->validated());
        return back()->with('success', 'Role created');
    }

    public function update(RoleUpdateRequest $request, int $id): RedirectResponse
    {
        if (!$request->user()->can('update-roles')) {
            abort(403, 'Unauthorized');
        }

        $this->service->update($id, $request->validated());
        return back()->with('success', 'Role updated');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        if (!$request->user()->can('delete-roles')) {
            abort(403, 'Unauthorized');
        }

        $this->service->delete($id);
        return back()->with('success', 'Role deleted');
    }
} 