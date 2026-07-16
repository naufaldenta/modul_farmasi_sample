import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, Truck } from 'lucide-react';

export default function Show({ purchaseOrder }: { purchaseOrder: any }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Purchase Orders', href: '/purchase-orders' },
        { title: purchaseOrder.po_number, href: `/purchase-orders/${purchaseOrder.id}` },
    ];

    const approve = () => {
        if (confirm('Apakah Anda yakin ingin menyetujui Purchase Order ini?')) {
            router.post(`/purchase-orders/${purchaseOrder.id}/approve`);
        }
    };

    const totalAmount = purchaseOrder.items.reduce((sum: number, item: any) => sum + (item.qty * parseFloat(item.price)), 0);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`PO ${purchaseOrder.po_number}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-5xl">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-3">
                            {purchaseOrder.po_number}
                            <Badge variant={
                                purchaseOrder.status === 'approved' ? 'default' : 
                                purchaseOrder.status === 'received' ? 'secondary' : 
                                purchaseOrder.status === 'draft' ? 'outline' : 'destructive'
                            }>
                                {purchaseOrder.status}
                            </Badge>
                        </h1>
                    </div>
                    <div className="flex gap-2">
                        {purchaseOrder.status === 'draft' && (
                            <Button onClick={approve} className="gap-2">
                                <CheckCircle className="h-4 w-4" /> Approve PO
                            </Button>
                        )}
                        {purchaseOrder.status === 'approved' && (
                            <Link href={`/goods-receipts/create?po_id=${purchaseOrder.id}`}>
                                <Button variant="secondary" className="gap-2">
                                    <Truck className="h-4 w-4" /> Terima Barang (GR)
                                </Button>
                            </Link>
                        )}
                        <Link href="/purchase-orders">
                            <Button variant="outline">Kembali</Button>
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="bg-card p-4 rounded-md border space-y-2">
                        <h3 className="font-semibold text-lg border-b pb-2">Informasi Supplier</h3>
                        <div className="grid grid-cols-3 gap-2 text-sm">
                            <div className="text-muted-foreground">Nama:</div>
                            <div className="col-span-2 font-medium">{purchaseOrder.supplier.name}</div>
                            
                            <div className="text-muted-foreground">Kontak:</div>
                            <div className="col-span-2">{purchaseOrder.supplier.contact}</div>
                            
                            <div className="text-muted-foreground">Lead Time:</div>
                            <div className="col-span-2">{purchaseOrder.supplier.lead_time_days} Hari</div>
                        </div>
                    </div>
                    
                    <div className="bg-card p-4 rounded-md border space-y-2">
                        <h3 className="font-semibold text-lg border-b pb-2">Informasi PO</h3>
                        <div className="grid grid-cols-3 gap-2 text-sm">
                            <div className="text-muted-foreground">Tanggal:</div>
                            <div className="col-span-2">{purchaseOrder.po_date}</div>
                            
                            <div className="text-muted-foreground">Dibuat Oleh:</div>
                            <div className="col-span-2">{purchaseOrder.created_by?.name || 'Sistem'} ({purchaseOrder.is_auto ? 'Otomatis ROP' : 'Manual'})</div>
                            
                            <div className="text-muted-foreground">Disetujui Oleh:</div>
                            <div className="col-span-2">{purchaseOrder.approved_by?.name || '-'}</div>
                        </div>
                    </div>
                </div>

                <div className="bg-card p-4 rounded-md border mt-4">
                    <h3 className="font-semibold text-lg mb-4">Item Pesanan</h3>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Obat</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>Qty</TableHead>
                                <TableHead>Harga Satuan</TableHead>
                                <TableHead>Subtotal</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {purchaseOrder.items.map((item: any) => (
                                <TableRow key={item.id}>
                                    <TableCell className="font-medium">{item.drug?.name}</TableCell>
                                    <TableCell>{item.drug?.category?.name || '-'}</TableCell>
                                    <TableCell>{item.qty} {item.drug?.unit}</TableCell>
                                    <TableCell>Rp {parseFloat(item.price).toLocaleString('id-ID')}</TableCell>
                                    <TableCell>Rp {(item.qty * parseFloat(item.price)).toLocaleString('id-ID')}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    <div className="flex justify-end pt-4 mt-4 border-t">
                        <div className="text-xl font-bold">
                            Total: Rp {totalAmount.toLocaleString('id-ID')}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
