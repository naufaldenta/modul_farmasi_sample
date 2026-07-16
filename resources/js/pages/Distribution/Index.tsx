import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';
import { create, show } from '@/routes/distributions';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import Pagination from '@/components/Pagination';

interface Distribution {
    id: number;
    distribution_number: string;
    unit_name: string;
    status: 'pending' | 'approved' | 'rejected' | 'completed';
    requested_by: { name: string };
    approved_by: { name: string } | null;
    created_at: string;
}

interface Props {
    distributions: {
        data: Distribution[];
        links: any[];
    };
}

export default function Index({ distributions }: Props) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'approved': return 'bg-green-100 text-green-800';
            case 'rejected': return 'bg-red-100 text-red-800';
            case 'completed': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout
            title="Daftar Permintaan Distribusi"
        >
            <Head title="Distribusi" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-2xl font-semibold text-gray-800 leading-tight">Daftar Permintaan Distribusi</h2>
                        <Link
                            href={create.url()}
                            className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            Buat Permintaan
                        </Link>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>No Distribusi</TableHead>
                                        <TableHead>Unit Peminta</TableHead>
                                        <TableHead>Pemohon</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Tanggal</TableHead>
                                        <TableHead>Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {distributions.data.map((dist) => (
                                        <TableRow key={dist.id}>
                                            <TableCell className="font-medium">
                                                {dist.distribution_number}
                                            </TableCell>
                                            <TableCell>
                                                {dist.unit_name}
                                            </TableCell>
                                            <TableCell>
                                                {dist.requested_by?.name}
                                            </TableCell>
                                            <TableCell>
                                                <Badge className={getStatusColor(dist.status)}>
                                                    {dist.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {new Date(dist.created_at).toLocaleDateString('id-ID')}
                                            </TableCell>
                                            <TableCell>
                                                <Link href={show.url({ distribution: dist.id })} className="text-indigo-600 hover:text-indigo-900">
                                                    Detail
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            <div className="p-4 border-t">
                                <Pagination links={distributions.links} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
