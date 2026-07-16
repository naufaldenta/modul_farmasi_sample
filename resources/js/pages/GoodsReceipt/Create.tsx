import { Head, useForm, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Trash2, ArrowRight } from 'lucide-react';
import { FormEventHandler, useEffect } from 'react';
import { create, store, index as indexRoute } from '@/routes/goods-receipts';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Goods Receipts', href: '/goods-receipts' },
    { title: 'Terima Barang', href: '/goods-receipts/create' },
];

export default function Create({ purchaseOrder, approvedPOs }: { purchaseOrder: any, approvedPOs: any[] }) {
    const { data, setData, post, processing, errors } = useForm({
        purchase_order_id: purchaseOrder?.id || '',
        receipt_date: new Date().toISOString().split('T')[0],
        notes: '',
        items: purchaseOrder?.items?.map((item: any) => ({
            purchase_order_item_id: item.id,
            drug_id: item.drug_id,
            drug_name: item.drug.name,
            batch_number: '',
            expired_date: '',
            qty: item.qty
        })) || []
    });

    // When a PO is selected, reload the page with the PO data
    const handlePoChange = (value: string) => {
        router.get(create.url(), { po_id: value });
    };

    useEffect(() => {
        if (purchaseOrder?.items) {
            setData((data) => ({
                ...data,
                purchase_order_id: purchaseOrder.id,
                items: purchaseOrder.items.map((item: any) => ({
                    purchase_order_item_id: item.id,
                    drug_id: item.drug_id,
                    drug_name: item.drug.name,
                    batch_number: '',
                    expired_date: '',
                    qty: item.qty
                }))
            }));
        }
    }, [purchaseOrder]);

    const handleItemChange = (index: number, field: string, value: any) => {
        const newItems = [...data.items] as any[];
        newItems[index][field] = value;
        setData('items', newItems);
    };

    // To handle multiple batches for a single PO item
    const duplicateItemRow = (index: number) => {
        const itemToDuplicate = data.items[index];
        const newItems = [...data.items];
        newItems.splice(index + 1, 0, {
            ...itemToDuplicate,
            batch_number: '',
            qty: 0 // Prompt user to enter the remaining qty
        });
        setData('items', newItems);
    };

    const removeItem = (index: number) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(store.url());
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Terima Barang (GR)" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-5xl">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Terima Barang</h1>
                    <Link href={indexRoute.url()}>
                        <Button variant="outline">Kembali</Button>
                    </Link>
                </div>

                {!purchaseOrder ? (
                    <div className="bg-card p-6 rounded-md border text-center space-y-4">
                        <h2 className="text-lg font-medium">Pilih Purchase Order</h2>
                        <p className="text-muted-foreground">Silakan pilih Purchase Order yang sudah di-approve untuk mulai menerima barang.</p>
                        
                        <div className="max-w-md mx-auto flex gap-2">
                            <Select onValueChange={handlePoChange}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih PO..." />
                                </SelectTrigger>
                                <SelectContent>
                                    {approvedPOs.map(po => (
                                        <SelectItem key={po.id} value={po.id.toString()}>
                                            {po.po_number} - {po.supplier.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                ) : (
                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid grid-cols-2 gap-4 bg-card p-4 rounded-md border">
                            <div className="space-y-2">
                                <Label>No. Purchase Order</Label>
                                <div className="font-medium text-lg pt-1">{purchaseOrder.po_number}</div>
                                <div className="text-sm text-muted-foreground">{purchaseOrder.supplier.name}</div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="receipt_date">Tanggal Terima</Label>
                                <Input 
                                    id="receipt_date" 
                                    type="date" 
                                    value={data.receipt_date} 
                                    onChange={(e) => setData('receipt_date', e.target.value)} 
                                />
                                {errors.receipt_date && <p className="text-sm text-destructive">{errors.receipt_date}</p>}
                            </div>
                        </div>

                        <div className="bg-card p-4 rounded-md border space-y-4">
                            <h2 className="text-lg font-semibold">Daftar Item Diterima</h2>
                            
                            {errors.items && <p className="text-sm text-destructive">{errors.items}</p>}

                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Obat</TableHead>
                                        <TableHead>No. Batch (Lot)</TableHead>
                                        <TableHead>Tanggal Kadaluarsa</TableHead>
                                        <TableHead className="w-24">Qty</TableHead>
                                        <TableHead className="w-16"></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.items.map((item: any, index: number) => (
                                        <TableRow key={index}>
                                            <TableCell className="font-medium">
                                                {item.drug_name}
                                                <div className="text-xs text-muted-foreground mt-1 cursor-pointer hover:text-primary flex items-center gap-1" onClick={() => duplicateItemRow(index)}>
                                                    <Plus className="h-3 w-3" /> Tambah Batch Lain
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Input 
                                                    placeholder="No Batch"
                                                    value={item.batch_number} 
                                                    onChange={(e) => handleItemChange(index, 'batch_number', e.target.value)} 
                                                />
                                                {errors[`items.${index}.batch_number`] && (
                                                    <div className="text-xs text-destructive mt-1">Wajib</div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Input 
                                                    type="date" 
                                                    value={item.expired_date} 
                                                    onChange={(e) => handleItemChange(index, 'expired_date', e.target.value)} 
                                                />
                                                {errors[`items.${index}.expired_date`] && (
                                                    <div className="text-xs text-destructive mt-1">Wajib</div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Input 
                                                    type="number" 
                                                    min="1" 
                                                    value={item.qty} 
                                                    onChange={(e) => handleItemChange(index, 'qty', parseInt(e.target.value))} 
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <Button type="button" variant="ghost" size="icon" onClick={() => removeItem(index)}>
                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="space-y-2 bg-card p-4 rounded-md border">
                            <Label htmlFor="notes">Catatan Tambahan (Opsional)</Label>
                            <Input 
                                id="notes" 
                                value={data.notes} 
                                onChange={(e) => setData('notes', e.target.value)} 
                            />
                        </div>

                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="outline" onClick={() => router.get(indexRoute.url())}>
                                Batal
                            </Button>
                            <Button type="submit" disabled={processing || data.items.length === 0}>
                                Simpan Penerimaan
                            </Button>
                        </div>
                    </form>
                )}
            </div>
        </AppLayout>
    );
}
