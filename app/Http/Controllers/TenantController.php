<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TenantStoreRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    private TenantService $service;

    public function __construct(TenantService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): Response
    {
        if (!$request->user()->can('read-all-tenants') && !$request->user()->can('read-tenant-data')) {
            abort(403, 'Unauthorized');
        }
        $search = $request->string('q')->toString();

        if ($request->user()->can('read-all-tenants')) {
            $tenantId = null;
        } else {
            $tenantId = $request->user()->tenant_id;
            if ($tenantId == null) {
                abort(403, 'Unauthorized');
            }
        }



        return Inertia::render('tenants/index', [
            'tenants' => $this->service->paginate(15, $search, $tenantId),
            'search' => $search,
            // 'can' => [
            //     'create' => $request->user()->can('create-tenants') || $request->user()->can('create-tenant-data'),
            //     'update' => $request->user()->can('update-all-tenants') || $request->user()->can('update-tenant-data'),
            //     'delete' => $request->user()->can('delete-all-tenants') || $request->user()->can('delete-tenant-data'),
            // ],
        ]);
    }

    // public function create(Request $request): Response
    // {
    //     if (!$request->user()->can('create-tenants')) {
    //         abort(403, 'Unauthorized');
    //     }
    //     return Inertia::render('tenants/create');
    // }

    public function store(TenantStoreRequest $request): RedirectResponse
    {
        if (!$request->user()->can('create-tenants') && !$request->user()->can('create-tenant-data')) {
            abort(403, 'Unauthorized');
        }
        $this->service->create($request->validated());
        return redirect()->route('tenants.index')->with('success', 'Tenant created');
    }

    // public function edit(int $id, Request $request): Response
    // {
    //     if (!$request->user()->can('update-all-tenants')) {
    //         abort(403, 'Unauthorized');
    //     }
    //     $tenant = $this->service->find($id);
    //     return Inertia::render('tenants/edit', [
    //         'tenant' => $tenant,
    //     ]);
    // }

    public function update(TenantUpdateRequest $request, int $id): RedirectResponse
    {
        if (!$request->user()->can('update-all-tenants') && !$request->user()->can('update-tenant-data')) {
            abort(403, 'Unauthorized');
        }

        if (!$request->user()->can('update-all-tenants')) {
            $tenantId = $request->user()->tenant_id;
            if ($tenantId != $id) {
                dd($tenantId, $id);
                abort(403, 'Unauthorized');
            }
        }



        $this->service->update($id, $request->validated());
        return redirect()->route('tenants.index')->with('success', 'Tenant updated');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        if (!$request->user()->can('delete-all-tenants') && !$request->user()->can('delete-tenant-data')) {
            abort(403, 'Unauthorized');
        }

        if (!$request->user()->can('delete-all-tenants')) {
            $tenantId = $request->user()->tenant_id;
            if ($tenantId != $id) {
                abort(403, 'Unauthorized');
            }
        }

        $this->service->delete($id);
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted');
    }
}
