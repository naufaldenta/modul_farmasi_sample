import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Pagination from '@/components/Pagination';
import { create, show } from '@/routes/goods-receipts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Goods Receipts',
        href: '/goods-receipts',
    },
];

export default function Index({ goodsReceipts }: { goodsReceipts: any }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Goods Receipts" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Penerimaan Barang (Goods Receipt)</h1>
                    <Link href={create.url()}>
                        <Button>Terima Barang</Button>
                    </Link>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>No. GR</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>No. PO</TableHead>
                                <TableHead>Penerima</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {goodsReceipts.data.map((gr: any) => (
                                <TableRow key={gr.id}>
                                    <TableCell className="font-medium">{gr.receipt_number}</TableCell>
                                    <TableCell>{gr.receipt_date}</TableCell>
                                    <TableCell>{gr.purchase_order?.po_number}</TableCell>
                                    <TableCell>{gr.received_by?.name}</TableCell>
                                    <TableCell>
                                        <Link href={show.url({ goods_receipt: gr.id })}>
                                            <Button variant="outline" size="sm">
                                                Detail
                                            </Button>
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {goodsReceipts.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="text-center text-muted-foreground">
                                        Tidak ada data penerimaan.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                    <div className="p-4 border-t">
                        <Pagination links={goodsReceipts.links} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
