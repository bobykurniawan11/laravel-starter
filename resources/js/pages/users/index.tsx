import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useAuth } from '@/hooks/use-auth';
import AppLayout from '@/layouts/app-layout';
import type { PageProps } from '@/types/global';
import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { toast } from 'sonner';

interface UserItem {
    id: number;
    name: string;
    email: string;
    tenant_id?: number | null;
    tenant?: { id: number; name: string } | null;
    roles?: { name: string }[];
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Paginated {
    data: UserItem[];
    links: PaginationLink[];
}
interface Props extends PageProps {
    users: Paginated;
    search: string;
    tenantFilter?: number | null;
    isDeveloper: boolean;
    tenants: { id: number; name: string }[];
    roles: string[];
}

export default function UsersPage({ users, search: initialSearch, tenantFilter: initialTenantFilter, isDeveloper, tenants, roles }: Props) {
    const { hasPermission } = useAuth();
    const canCreate = hasPermission('create-tenant-users');
    const canUpdate = hasPermission('update-tenant-users');
    const canDelete = hasPermission('delete-tenant-users');
    const [search, setSearch] = useState(initialSearch ?? '');
    const [tenantFilter, setTenantFilter] = useState<number | null>(initialTenantFilter || null);
    const filtered = useMemo(() => {
        if (!search.trim()) return users.data;
        const t = search.toLowerCase();
        return users.data.filter((u) => u.name.toLowerCase().includes(t) || u.email.toLowerCase().includes(t));
    }, [users.data, search]);
    
    // Calculate total columns for table
    const totalColumns = useMemo(() => {
        let count = 4; // ID, Name, Email, Role
        if (isDeveloper) count++; // Tenant column
        if (canUpdate || canDelete) count++; // Actions column
        return count;
    }, [isDeveloper, canUpdate, canDelete]);
    const [createOpen, setCreateOpen] = useState(false);
    const { data: createForm, setData: setCreateForm, post, errors: createErrors, processing: createProcessing, reset: resetCreateForm } = useForm({
        name: '',
        email: '',
        password: '',
        tenant_id: isDeveloper ? tenants[0]?.id : undefined,
        role: isDeveloper ? 'admin' : 'staff',
    });
    const [editUser, setEditUser] = useState<UserItem | null>(null);
    const { data: editForm, setData: setEditForm, put, errors: editErrors, processing: editProcessing, reset: resetEditForm } = useForm({ 
        name: '', 
        email: '', 
        password: '', 
        role: 'staff' 
    });
    const [editDialogs, setEditDialogs] = useState<Record<number, boolean>>({});
    const submitCreate = (e: FormEvent) => {
        e.preventDefault();
        post('/users', {
            onSuccess: () => {
                setCreateOpen(false);
                resetCreateForm();
                toast.success('User created');
            },
        });
    };
    const openEdit = (u: UserItem) => {
        const existingRole = u.roles?.[0]?.name ?? 'staff';
        setEditUser(u);
        setEditForm('name', u.name);
        setEditForm('email', u.email);
        setEditForm('password', '');
        setEditForm('role', existingRole);
        setEditDialogs({ ...editDialogs, [u.id]: true });
    };
    const submitEdit = (e: FormEvent) => {
        e.preventDefault();
        if (!editUser) return;
        put(`/users/${editUser.id}`, {
            onSuccess: () => {
                setEditDialogs({ ...editDialogs, [editUser.id]: false });
                resetEditForm();
                toast.success('User updated');
            },
        });
    };
    const del = (id: number) => {
        if (confirm('Delete user?')) router.delete(`/users/${id}`, { onSuccess: () => toast.success('User deleted') });
    };
    return (
        <AppLayout>
            <Head title="Users" />
            <div className="space-y-6 p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold">Users</h1>
                    <div className="flex w-full items-center gap-2 sm:w-auto">
                        <div className="relative w-full sm:w-64">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Search user..."
                                className="pl-9"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        const params: Record<string, string | number> = { q: e.currentTarget.value };
                                        if (tenantFilter) params.tenant_id = tenantFilter;
                                        router.get('/users', params);
                                    }
                                }}
                            />
                        </div>
                        {isDeveloper && tenants.length > 0 && (
                            <Select
                                value={tenantFilter?.toString() || 'all'}
                                onValueChange={(value) => {
                                    const newTenantFilter = value === 'all' ? null : parseInt(value);
                                    setTenantFilter(newTenantFilter);
                                    const params: Record<string, string | number> = {};
                                    if (search) params.q = search;
                                    if (newTenantFilter) params.tenant_id = newTenantFilter;
                                    router.get('/users', params);
                                }}
                            >
                                <SelectTrigger className="w-48">
                                    <SelectValue placeholder="Filter by tenant" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Tenants</SelectItem>
                                    {tenants.map((tenant) => (
                                        <SelectItem key={tenant.id} value={tenant.id.toString()}>
                                            {tenant.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        )}
                        {canCreate && (
                            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                                <DialogTrigger asChild>
                                    <Button size="sm" onClick={() => setCreateOpen(true)}>
                                        <Plus className="size-4" />
                                        <span>Create</span>
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Create User</DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={submitCreate} className="space-y-4 py-2">
                                        <Input
                                            placeholder="Name"
                                            value={createForm.name}
                                            onChange={(e) => setCreateForm('name', e.target.value)}
                                            required
                                        />
                                        {createErrors.name && <InputError message={createErrors.name} />}
                                        <Input
                                            placeholder="Email"
                                            value={createForm.email}
                                            onChange={(e) => setCreateForm('email', e.target.value)}
                                            required
                                        />
                                        {createErrors.email && <InputError message={createErrors.email} />}
                                        <Input
                                            placeholder="Password"
                                            type="password"
                                            value={createForm.password}
                                            onChange={(e) => setCreateForm('password', e.target.value)}
                                            required
                                        />
                                        {createErrors.password && <InputError message={createErrors.password} />}
                                        {isDeveloper && (
                                            <select
                                                className="w-full rounded border px-3 py-2"
                                                value={createForm.tenant_id}
                                                onChange={(e) => setCreateForm('tenant_id', Number(e.target.value))}
                                            >
                                                {tenants.map((t) => (
                                                    <option key={t.id} value={t.id}>
                                                        {t.name}
                                                    </option>
                                                ))}
                                            </select>
                                        )}
                                        {createErrors.tenant_id && <InputError message={createErrors.tenant_id} />}
                                        {/* Role select */}
                                        <div>
                                            <label className="mb-1 block text-sm font-medium">Role</label>
                                            <Select value={createForm.role} onValueChange={(val) => setCreateForm('role', val)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select role" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {roles.map((r) => (
                                                        <SelectItem key={r} value={r}>
                                                            {r}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {createErrors.role && <InputError message={createErrors.role} />}
                                        </div>
                                        <DialogFooter className="pt-4">
                                            <DialogClose asChild>
                                                <Button variant="secondary" type="button">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button type="submit" disabled={createProcessing}>
                                                {createProcessing ? 'Creating...' : 'Save'}
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </div>
                <div className="overflow-x-auto rounded-lg border shadow-sm">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-3 font-semibold">ID</th>
                                <th className="px-4 py-3 font-semibold">Name</th>
                                <th className="px-4 py-3 font-semibold">Email</th>
                                {isDeveloper && <th className="px-4 py-3 font-semibold">Tenant</th>}
                                <th className="px-4 py-3 font-semibold">Role</th>
                                {(canUpdate || canDelete) && <th className="px-4 py-3"></th>}
                            </tr>
                        </thead>
                        <tbody>
                            {filtered.map((u) => (
                                <tr key={u.id} className="border-t hover:bg-accent/20">
                                    <td className="px-4 py-2">{u.id}</td>
                                    <td className="px-4 py-2">{u.name}</td>
                                    <td className="px-4 py-2 font-mono">{u.email}</td>
                                    {isDeveloper && (
                                        <td className="px-4 py-2">
                                            <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                {u.tenant?.name ?? 'No tenant'}
                                            </span>
                                        </td>
                                    )}
                                    <td className="px-4 py-2">
                                        <span className="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                            {u.roles?.[0]?.name ?? 'No role'}
                                        </span>
                                    </td>
                                    {(canUpdate || canDelete) && (
                                        <td className="flex justify-end gap-2 px-4 py-2 text-right">
                                            {canUpdate && (
                                                <Dialog open={editDialogs[u.id] || false} onOpenChange={(open) => setEditDialogs({ ...editDialogs, [u.id]: open })}>
                                                    <DialogTrigger asChild>
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            onClick={() => {
                                                                openEdit(u);
                                                            }}
                                                        >
                                                            <Pencil className="size-4" />
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>Edit User</DialogTitle>
                                                        </DialogHeader>
                                                        <form onSubmit={submitEdit} className="space-y-4 py-2">
                                                            <Input
                                                                placeholder="Name"
                                                                value={editForm.name}
                                                                onChange={(e) => setEditForm('name', e.target.value)}
                                                                required
                                                            />
                                                            {editErrors.name && <InputError message={editErrors.name} />}
                                                            <Input
                                                                placeholder="Email"
                                                                value={editForm.email}
                                                                onChange={(e) => setEditForm('email', e.target.value)}
                                                                required
                                                            />
                                                            {editErrors.email && <InputError message={editErrors.email} />}
                                                            <Input
                                                                placeholder="Password (leave blank)"
                                                                type="password"
                                                                value={editForm.password}
                                                                onChange={(e) => setEditForm('password', e.target.value)}
                                                            />
                                                            {editErrors.password && <InputError message={editErrors.password} />}
                                                            {/* Role select */}
                                                            <div>
                                                                <label className="mb-1 block text-sm font-medium">Role</label>
                                                                <Select
                                                                    value={editForm.role}
                                                                    onValueChange={(val) => setEditForm('role', val)}
                                                                >
                                                                    <SelectTrigger>
                                                                        <SelectValue placeholder="Select role" />
                                                                    </SelectTrigger>
                                                                    <SelectContent>
                                                                        {roles.map((r) => (
                                                                            <SelectItem key={r} value={r}>
                                                                                {r}
                                                                            </SelectItem>
                                                                        ))}
                                                                    </SelectContent>
                                                                </Select>
                                                                {editErrors.role && <InputError message={editErrors.role} />}
                                                            </div>
                                                            <DialogFooter className="pt-4">
                                                                <DialogClose asChild>
                                                                    <Button variant="secondary" type="button">
                                                                        Cancel
                                                                    </Button>
                                                                </DialogClose>
                                                                <Button type="submit" disabled={editProcessing}>
                                                                    {editProcessing ? 'Updating...' : 'Update'}
                                                                </Button>
                                                            </DialogFooter>
                                                        </form>
                                                    </DialogContent>
                                                </Dialog>
                                            )}
                                            {canDelete && (
                                                <Button size="icon" variant="destructive" onClick={() => del(u.id)}>
                                                    <Trash className="size-4" />
                                                </Button>
                                            )}
                                        </td>
                                    )}
                                </tr>
                            ))}
                            {filtered.length === 0 && (
                                <tr>
                                    <td colSpan={totalColumns} className="px-4 py-8 text-center text-muted-foreground">
                                        No users found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {users.links.length > 1 && (
                    <div className="flex justify-end gap-2">
                        {users.links.map((lnk, idx) => {
                            const label = lnk.label.replace(/&laquo;|&raquo;/g, '').trim();
                            if (!lnk.url) {
                                return (
                                    <Button key={idx} variant="ghost" size="sm" disabled>
                                        {label || '…'}
                                    </Button>
                                );
                            }
                            return (
                                <Button key={idx} variant={lnk.active ? 'secondary' : 'ghost'} size="sm" onClick={() => router.get(lnk.url!)}>
                                    {label || '…'}
                                </Button>
                            );
                        })}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
 