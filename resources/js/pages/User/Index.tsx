import { Link } from '@inertiajs/react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Pagination from '@/components/Pagination';

const breadcrumbs = [
    {
        title: 'Manajemen User',
        href: '/users',
    },
];

export default function Index({ users }: { users: { data: any[], links: any[] } }) {
    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus user ini?')) {
            router.delete(`/users/${id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manajemen User" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Daftar User</h1>
                    <Link href="/users/create">
                        <Button>Tambah User</Button>
                    </Link>
                </div>
                
                <div className="rounded-md border bg-white p-4">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nama</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {users.data.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell>{user.name}</TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell>
                                        <span className="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            {user.roles?.[0]?.name || '-'}
                                        </span>
                                    </TableCell>
                                    <TableCell className="space-x-2">
                                        <Link href={`/users/${user.id}/edit`}>
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button 
                                            variant="destructive" 
                                            size="sm" 
                                            onClick={() => handleDelete(user.id)}
                                        >
                                            Hapus
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    <div className="p-4 border-t">
                        <Pagination links={users.links} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
