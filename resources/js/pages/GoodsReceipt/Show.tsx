import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index, show } from '@/routes/goods-receipts';
import { show as showPo } from '@/routes/purchase-orders';

export default function Show({ goodsReceipt }: { goodsReceipt: any }) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Goods Receipts', href: index.url() },
        { title: goodsReceipt.receipt_number, href: show.url({ goods_receipt: goodsReceipt.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`GR ${goodsReceipt.receipt_number}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-5xl">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold flex items-center gap-3">
                        Penerimaan: {goodsReceipt.receipt_number}
                    </h1>
                    <div className="flex gap-2">
                        <Link href={index.url()}>
                            <Button variant="outline">Kembali</Button>
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="bg-card p-4 rounded-md border space-y-2">
                        <h3 className="font-semibold text-lg border-b pb-2">Informasi Penerimaan</h3>
                        <div className="grid grid-cols-3 gap-2 text-sm">
                            <div className="text-muted-foreground">No. GR:</div>
                            <div className="col-span-2 font-medium">{goodsReceipt.receipt_number}</div>
                            
                            <div className="text-muted-foreground">Tanggal:</div>
                            <div className="col-span-2">{goodsReceipt.receipt_date}</div>
                            
                            <div className="text-muted-foreground">Diterima Oleh:</div>
                            <div className="col-span-2">{goodsReceipt.received_by?.name || '-'}</div>
                            
                            <div className="text-muted-foreground">Catatan:</div>
                            <div className="col-span-2">{goodsReceipt.notes || '-'}</div>
                        </div>
                    </div>
                    
                    <div className="bg-card p-4 rounded-md border space-y-2">
                        <h3 className="font-semibold text-lg border-b pb-2">Referensi PO</h3>
                        <div className="grid grid-cols-3 gap-2 text-sm">
                            <div className="text-muted-foreground">No. PO:</div>
                            <div className="col-span-2">
                                <Link href={showPo.url({ purchase_order: goodsReceipt.purchase_order_id })} className="text-primary hover:underline font-medium">
                                    {goodsReceipt.purchase_order?.po_number}
                                </Link>
                            </div>
                            
                            <div className="text-muted-foreground">Supplier:</div>
                            <div className="col-span-2">{goodsReceipt.purchase_order?.supplier?.name || '-'}</div>
                        </div>
                    </div>
                </div>

                <div className="bg-card p-4 rounded-md border mt-4">
                    <h3 className="font-semibold text-lg mb-4">Item Diterima & Tercatat (Ledger)</h3>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Obat</TableHead>
                                <TableHead>No. Batch (Lot)</TableHead>
                                <TableHead>Tgl Kadaluarsa</TableHead>
                                <TableHead className="w-24">Qty Diterima</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {goodsReceipt.items.map((item: any) => (
                                <TableRow key={item.id}>
                                    <TableCell className="font-medium">{item.drug_batch?.drug?.name || '-'}</TableCell>
                                    <TableCell>{item.batch_number}</TableCell>
                                    <TableCell>{item.expired_date}</TableCell>
                                    <TableCell>{item.qty}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    );
}
