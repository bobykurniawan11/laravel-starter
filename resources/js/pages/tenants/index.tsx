import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { useAuth } from '@/hooks/use-auth';
import AppLayout from '@/layouts/app-layout';
import type { PageProps } from '@/types/global';
import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { toast } from 'sonner';

interface TenantItem {
    id: number;
    name: string;
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Paginated {
    data: TenantItem[];
    links: PaginationLink[];
}
interface Props extends PageProps {
    tenants: Paginated;
    search: string;
}

export default function TenantsPage({ tenants, search: initialSearch }: Props) {
    const { hasPermission } = useAuth();
    const canCreate = hasPermission('create-tenants');
    const canUpdate = hasPermission('update-all-tenants');
    const canDelete = hasPermission('delete-all-tenants');
    const [search, setSearch] = useState(initialSearch ?? '');
    const filtered = useMemo(() => {
        if (!search.trim()) return tenants.data;
        const t = search.toLowerCase();
        return tenants.data.filter((u) => u.name.toLowerCase().includes(t));
    }, [tenants.data, search]);
    const [createOpen, setCreateOpen] = useState(false);
    const { data: createForm, setData: setCreateForm, post, errors: createErrors, processing: createProcessing, reset: resetCreateForm } = useForm({
        name: '',
    });
    const [editTenant, setEditTenant] = useState<TenantItem | null>(null);
    const { data: editForm, setData: setEditForm, put, errors: editErrors, processing: editProcessing, reset: resetEditForm } = useForm({
        name: ''
    });
    const [editDialogs, setEditDialogs] = useState<Record<number, boolean>>({});
    const submitCreate = (e: FormEvent) => {
        e.preventDefault();
        post('/tenants', {
            onSuccess: () => {
                setCreateOpen(false);
                resetCreateForm();
                toast.success('Tenant created');
            },
        });
    };
    const openEdit = (u: TenantItem) => {
        setEditTenant(u);
        setEditForm('name', u.name);
        setEditDialogs({ ...editDialogs, [u.id]: true });
    };
    const submitEdit = (e: FormEvent) => {
        e.preventDefault();
        if (!editTenant) return;
        put(`/tenants/${editTenant.id}`, {
            onSuccess: () => {
                setEditDialogs({ ...editDialogs, [editTenant.id]: false });
                resetEditForm();
                toast.success('Tenant updated');
            },
        });
    };
    const del = (id: number) => {
        if (confirm('Delete tenant?')) router.delete(`/tenants/${id}`, { onSuccess: () => toast.success('Tenant deleted') });
    };
    return (
        <AppLayout>
            <Head title="Tenants" />
            <div className="space-y-6 p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold">Tenants</h1>
                    <div className="flex w-full items-center gap-2 sm:w-auto">
                        <div className="relative w-full sm:w-64">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Search tenant..."
                                className="pl-9"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        router.get('/tenants', { q: e.currentTarget.value });
                                    }
                                }}
                            />
                        </div>
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
                                        <DialogTitle>Create Tenant</DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={submitCreate} className="space-y-4 py-2">
                                        <Input
                                            placeholder="Name"
                                            value={createForm.name}
                                            onChange={(e) => setCreateForm('name', e.target.value)}
                                            required
                                        />
                                        {createErrors.name && <InputError message={createErrors.name} />}
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
                                {(canUpdate || canDelete) && <th className="px-4 py-3"></th>}
                            </tr>
                        </thead>
                        <tbody>
                            {filtered.map((u) => (
                                <tr key={u.id} className="border-t hover:bg-accent/20">
                                    <td className="px-4 py-2 text-center">{u.id}</td>
                                    <td className="px-4 py-2 text-center">{u.name}</td>
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
                                                        <DialogTitle>Edit Tenant</DialogTitle>
                                                    </DialogHeader>
                                                    <form onSubmit={submitEdit} className="space-y-4 py-2">
                                                        <Input
                                                            placeholder="Name"
                                                            value={editForm.name}
                                                            onChange={(e) => setEditForm('name', e.target.value)}
                                                            required
                                                        />
                                                        {editErrors.name && <InputError message={editErrors.name} />}
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
                                </tr>
                            ))}
                            {filtered.length === 0 && (
                                <tr>
                                    <td colSpan={3} className="px-4 py-8 text-center text-muted-foreground">
                                        No tenants found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {tenants.links.length > 1 && (
                    <div className="flex justify-end gap-2">
                        {tenants.links.map((lnk, idx) => {
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