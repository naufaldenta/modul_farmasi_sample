import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import Pagination from '@/components/Pagination';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Purchase Orders',
        href: '/purchase-orders',
    },
];

export default function Index({ purchaseOrders }: { purchaseOrders: any }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Purchase Orders" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Purchase Orders</h1>
                    <Link href="/purchase-orders/create">
                        <Button>Buat PO Baru</Button>
                    </Link>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No. PO</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Supplier</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {purchaseOrders.data.map((po: any) => (
                                <TableRow key={po.id}>
                                    <TableCell className="font-medium">{po.po_number}</TableCell>
                                    <TableCell>{po.po_date}</TableCell>
                                    <TableCell>{po.supplier?.name}</TableCell>
                                    <TableCell>
                                        <Badge variant={
                                            po.status === 'approved' ? 'default' : 
                                            po.status === 'received' ? 'secondary' : 
                                            po.status === 'draft' ? 'outline' : 'destructive'
                                        }>
                                            {po.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Link href={`/purchase-orders/${po.id}`}>
                                            <Button variant="outline" size="sm">
                                                Detail
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {purchaseOrders.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="text-center text-muted-foreground">
                                        Tidak ada data.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                    <div className="p-4 border-t">
                        <Pagination links={purchaseOrders.links} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
