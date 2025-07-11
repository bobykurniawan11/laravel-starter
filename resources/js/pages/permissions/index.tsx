import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { useAuth } from '@/hooks/use-auth';
import { useMemo, useState, FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogFooter,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
    DialogClose,
} from '@/components/ui/dialog';
import { Plus, Search, Pencil, Trash } from 'lucide-react';
import { router } from '@inertiajs/react';
import type { PageProps } from '@/types/global';
import { toast } from 'sonner';

interface Permission {
    id: number;
    name: string;
    title?: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPermissions {
    data: Permission[];
    links: PaginationLink[];
}

interface Props extends PageProps {
    permissions: PaginatedPermissions;
    search: string;
}

export default function PermissionsIndex({ permissions, search: initialSearch }: Props) {
    const { hasPermission } = useAuth();

    const canCreate = hasPermission('create-permissions');
    const canUpdate = hasPermission('update-permissions');
    const canDelete = hasPermission('delete-permissions');

    const [search, setSearch] = useState(initialSearch ?? '');

    const filtered = useMemo(() => {
        if (!search.trim()) return permissions.data;
        const term = search.toLowerCase();
        return permissions.data.filter((p) => p.name.toLowerCase().includes(term));
    }, [permissions.data, search]);

    // Create form state
    const [createName, setCreateName] = useState('');
    const [createTitle, setCreateTitle] = useState('');
    const [createOpen,setCreateOpen]=useState(false);

    // Edit form state
    const [editPermission, setEditPermission] = useState<Permission | null>(null);
    const [editName, setEditName] = useState('');
    const [editTitle, setEditTitle] = useState('');
    const [editOpen,setEditOpen]=useState(false);

    const submitCreate = (e: FormEvent) => {
        e.preventDefault();
        router.post(
            '/permissions',
            { name: createName, title: createTitle },
            {
                onSuccess: () => {
                    setCreateName('');
                    setCreateTitle('');
                    setCreateOpen(false);
                    toast.success('Permission created');
                },
            }
        );
    };

    const openEdit = (perm: Permission) => {
        setEditPermission(perm);
        setEditName(perm.name);
        setEditTitle(perm.title ?? '');
    };

    const submitEdit = (e: FormEvent) => {
        e.preventDefault();
        if (!editPermission) return;
        router.put(`/permissions/${editPermission.id}`, { name: editName, title: editTitle }, {onSuccess:()=>{setEditOpen(false); toast.success('Permission updated');}});
    };

    const deletePermission = (id: number) => {
        if (confirm('Delete permission?')) {
            router.delete(`/permissions/${id}`,{onSuccess:()=>toast.success('Permission deleted')});
        }
    };

    return (
        <AppLayout>
            <Head title="Permissions" />
            <div className="p-8 space-y-6">
                <div className="flex items-center justify-between gap-4 flex-wrap">
                    <h1 className="text-2xl font-bold text-foreground">Permissions</h1>

                    <div className="flex items-center gap-2 w-full sm:w-auto">
                        <div className="relative w-full sm:w-64">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                            <Input
                                placeholder="Search permission..."
                                className="pl-9 dark:bg-background"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e)=>{ if(e.key==='Enter'){ e.preventDefault(); router.get('/permissions',{ q: e.currentTarget.value }); }} }
                            />
                        </div>
                        {canCreate && (
                            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                                <DialogTrigger asChild>
                                    <Button size="sm" className="shrink-0" onClick={()=>setCreateOpen(true)}>
                                        <Plus className="size-4" />
                                        <span>Create</span>
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Create Permission</DialogTitle>
                                        <DialogDescription>Add a new permission ability.</DialogDescription>
                                    </DialogHeader>
                                    <form onSubmit={submitCreate} className="space-y-4 py-2">
                                        <div className="space-y-2">
                                            <label className="text-sm font-medium">Name</label>
                                            <Input value={createName} onChange={(e) => setCreateName(e.target.value)} required />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-sm font-medium">Title (optional)</label>
                                            <Input value={createTitle} onChange={(e) => setCreateTitle(e.target.value)} />
                                        </div>
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

                <div className="overflow-x-auto rounded-lg border bg-card text-card-foreground shadow-sm dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted dark:bg-muted/50">
                            <tr className="text-left">
                                <th className="px-4 py-3 font-semibold">ID</th>
                                <th className="px-4 py-3 font-semibold">Name</th>
                                <th className="px-4 py-3 font-semibold">Title</th>
                                {(canUpdate || canDelete) && <th className="px-4 py-3"></th>}
                            </tr>
                        </thead>
                        <tbody>
                            {filtered.map((perm) => (
                                <tr key={perm.id} className="border-t hover:bg-accent/20">
                                    <td className="px-4 py-2">{perm.id}</td>
                                    <td className="px-4 py-2 font-mono">{perm.name}</td>
                                    <td className="px-4 py-2">{perm.title ?? '-'}</td>
                                    {(canUpdate || canDelete) && (
                                        <td className="px-4 py-2 text-right flex gap-2 justify-end">
                                            {canUpdate && (
                                                <Dialog open={editOpen} onOpenChange={setEditOpen}>
                                                    <DialogTrigger asChild>
                                                        <Button size="icon" variant="ghost" onClick={() => {openEdit(perm);setEditOpen(true);}}>
                                                            <Pencil className="size-4" />
                                                        </Button>
                                                    </DialogTrigger>
                                                    <DialogContent>
                                                        <DialogHeader>
                                                            <DialogTitle>Edit Permission</DialogTitle>
                                                        </DialogHeader>
                                                        <form onSubmit={submitEdit} className="space-y-4 py-2">
                                                            <div className="space-y-2">
                                                                <label className="text-sm font-medium">Name</label>
                                                                <Input value={editName} onChange={(e) => setEditName(e.target.value)} required />
                                                            </div>
                                                            <div className="space-y-2">
                                                                <label className="text-sm font-medium">Title (optional)</label>
                                                                <Input value={editTitle} onChange={(e) => setEditTitle(e.target.value)} />
                                                            </div>
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
                                                <Button
                                                    size="icon"
                                                    variant="destructive"
                                                    onClick={() => deletePermission(perm.id)}
                                                >
                                                    <Trash className="size-4" />
                                                </Button>
                                            )}
                                        </td>
                                    )}
                                </tr>
                            ))}
                            {filtered.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-8 text-center text-muted-foreground">
                                        No permissions found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {permissions.links.length > 1 && (
                    <div className="flex justify-end gap-2">
                        {permissions.links.map((lnk, idx) => {
                            // Remove HTML entities from label
                            const label = lnk.label.replace(/&laquo;|&raquo;/g, '').trim();
                            if (!lnk.url) {
                                return (
                                    <Button key={idx} variant="ghost" size="sm" disabled>
                                        {label || '…'}
                                    </Button>
                                );
                            }
                            return (
                                <Button
                                    key={idx}
                                    variant={lnk.active ? 'secondary' : 'ghost'}
                                    size="sm"
                                    onClick={() => router.get(lnk.url!)}
                                >
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