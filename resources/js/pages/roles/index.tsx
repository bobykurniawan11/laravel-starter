import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useAuth } from '@/hooks/use-auth';
import AppLayout from '@/layouts/app-layout';
import type { PageProps } from '@/types/global';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { toast } from 'sonner';

interface Role {
    id: number;
    name: string;
    title?: string | null;
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface PaginatedRoles {
    data: Role[];
    links: PaginationLink[];
}
interface Props extends PageProps {
    roles: PaginatedRoles;
    search: string;
}

export default function RolesIndex({ roles, search: initialSearch }: Props) {
    const { hasPermission } = useAuth();
    const canCreate = hasPermission('create-roles');
    const canUpdate = hasPermission('update-roles');
    const canDelete = hasPermission('delete-roles');
    const [search, setSearch] = useState(initialSearch ?? '');
    const filtered = useMemo(() => {
        if (!search.trim()) return roles.data;
        const t = search.toLowerCase();
        return roles.data.filter((r) => r.name.toLowerCase().includes(t));
    }, [roles.data, search]);
    const [createName, setCreateName] = useState('');
    const [editRole, setEditRole] = useState<Role | null>(null);
    const [editName, setEditName] = useState('');
    const [createOpen, setCreateOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);
    const submitCreate = (e: FormEvent) => {
        e.preventDefault();
        router.post(
            '/roles',
            { name: createName },
            {
                onSuccess: () => {
                    setCreateName('');
                    setCreateOpen(false);
                    toast.success('Role created');
                },
            },
        );
    };
    const openEdit = (r: Role) => {
        setEditRole(r);
        setEditName(r.name);
    };
    const submitEdit = (e: FormEvent) => {
        e.preventDefault();
        if (!editRole) return;
        router.put(
            `/roles/${editRole.id}`,
            { name: editName },
            {
                onSuccess: () => {
                    setEditOpen(false);
                    toast.success('Role updated');
                },
            },
        );
    };
    const del = (id: number) => {
        if (confirm('Delete role?')) router.delete(`/roles/${id}`, { onSuccess: () => toast.success('Role deleted') });
    };
    return (
        <AppLayout>
            <Head title="Roles" />
            <div className="space-y-6 p-8">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold">Roles</h1>
                    <div className="flex w-full items-center gap-2 sm:w-auto">
                        <div className="relative w-full sm:w-64">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Search role..."
                                className="pl-9"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        router.get('/roles', { q: e.currentTarget.value });
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
                                        <DialogTitle>Create Role</DialogTitle>
                                    </DialogHeader>
                                    <form onSubmit={submitCreate} className="space-y-4 py-2">
                                        <Input value={createName} onChange={(e) => setCreateName(e.target.value)} required placeholder="Role name" />
                                        <DialogFooter className="pt-4">
                                            <DialogClose asChild>
                                                <Button variant="secondary" type="button">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button type="submit">Save</Button>
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
                            {filtered.map((r) => (
                                <tr key={r.id} className="border-t hover:bg-accent/20">
                                    <td className="px-4 py-2">{r.id}</td>
                                    <td className="px-4 py-2 font-mono">{r.name}</td>
                                    {(canUpdate || canDelete) && (
                                        <td className="flex justify-end gap-2 px-4 py-2 text-right">
                                            {canUpdate && (
                                                <Dialog open={editOpen} onOpenChange={setEditOpen}>
                                                    <DialogTrigger asChild>
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            onClick={() => {
                                                                openEdit(r);
                                                                setEditOpen(true);
                                                            }}
                                                        >
                                                            <Pencil className="size-4" />
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>Edit Role</DialogTitle>
                                                        </DialogHeader>
                                                        <form onSubmit={submitEdit} className="space-y-4 py-2">
                                                            <Input value={editName} onChange={(e) => setEditName(e.target.value)} required />
                                                            <DialogFooter className="pt-4">
                                                                <DialogClose asChild>
                                                                    <Button variant="secondary" type="button">
                                                                        Cancel
                                                                    </Button>
                                                                </DialogClose>
                                                                <Button type="submit">Update</Button>
                                                            </DialogFooter>
                                                        </form>
                                                    </DialogContent>
                                                </Dialog>
                                            )}
                                            {canDelete && (
                                                <Button size="icon" variant="destructive" onClick={() => del(r.id)}>
                                                    <Trash className="size-4" />
                                                </Button>
                                            )}
                                        </td>
                                    )}
                                </tr>
                            ))}
                            {filtered.length === 0 && (
                                <tr>
                                    <td colSpan={3} className="px-4 py-8 text-center text-muted-foreground">
                                        No roles found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {roles.links.length > 1 && (
                    <div className="flex justify-end gap-2">
                        {roles.links.map((lnk, idx) => {
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
