import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Trash2 } from 'lucide-react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Purchase Orders', href: '/purchase-orders' },
    { title: 'Buat Baru', href: '/purchase-orders/create' },
];

export default function Create({ suppliers, drugs }: { suppliers: any[], drugs: any[] }) {
    const { data, setData, post, processing, errors } = useForm({
        supplier_id: '',
        po_date: new Date().toISOString().split('T')[0],
        items: [{ drug_id: '', qty: 1, price: 0 }]
    });

    const addItem = () => {
        setData('items', [...data.items, { drug_id: '', qty: 1, price: 0 }]);
    };

    const removeItem = (index: number) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const handleItemChange = (index: number, field: string, value: any) => {
        const newItems = [...data.items] as any[];
        newItems[index][field] = value;
        
        // Auto-fill price if drug is selected
        if (field === 'drug_id') {
            const selectedDrug = drugs.find(d => d.id.toString() === value.toString());
            if (selectedDrug) {
                newItems[index]['price'] = selectedDrug.price;
            }
        }
        
        setData('items', newItems);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/purchase-orders');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Buat Purchase Order" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-4xl">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Buat Purchase Order</h1>
                    <Link href="/purchase-orders">
                        <Button variant="outline">Kembali</Button>
                    </Link>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid grid-cols-2 gap-4 bg-card p-4 rounded-md border">
                        <div className="space-y-2">
                            <Label htmlFor="supplier_id">Supplier</Label>
                            <Select 
                                value={data.supplier_id} 
                                onValueChange={(v) => setData('supplier_id', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Supplier" />
                                </SelectTrigger>
                                <SelectContent>
                                    {suppliers.map(supplier => (
                                        <SelectItem key={supplier.id} value={supplier.id.toString()}>
                                            {supplier.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.supplier_id && <p className="text-sm text-destructive">{errors.supplier_id}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="po_date">Tanggal PO</Label>
                            <Input 
                                id="po_date" 
                                type="date" 
                                value={data.po_date} 
                                onChange={(e) => setData('po_date', e.target.value)} 
                            />
                            {errors.po_date && <p className="text-sm text-destructive">{errors.po_date}</p>}
                        </div>
                    </div>

                    <div className="bg-card p-4 rounded-md border space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold">Daftar Item Obat</h2>
                            <Button type="button" onClick={addItem} variant="secondary" size="sm">
                                <Plus className="mr-2 h-4 w-4" /> Tambah Item
                            </Button>
                        </div>
                        
                        {errors.items && <p className="text-sm text-destructive">{errors.items}</p>}

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Obat</TableHead>
                                    <TableHead className="w-24">Qty</TableHead>
                                    <TableHead className="w-48">Harga Satuan</TableHead>
                                    <TableHead className="w-48">Subtotal</TableHead>
                                    <TableHead className="w-16"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {data.items.map((item, index) => (
                                    <TableRow key={index}>
                                        <TableCell>
                                            <Select 
                                                value={item.drug_id} 
                                                onValueChange={(v) => handleItemChange(index, 'drug_id', v)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Pilih Obat" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {drugs.map(drug => (
                                                        <SelectItem key={drug.id} value={drug.id.toString()}>
                                                            {drug.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
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
                                            <Input 
                                                type="number" 
                                                min="0" 
                                                value={item.price} 
                                                onChange={(e) => handleItemChange(index, 'price', parseFloat(e.target.value))} 
                                            />
                                        </TableCell>
                                        <TableCell>
                                            Rp {(item.qty * item.price).toLocaleString('id-ID')}
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
                        
                        <div className="flex justify-end pt-4 border-t">
                            <div className="text-lg font-bold">
                                Total: Rp {data.items.reduce((sum, item) => sum + (item.qty * item.price), 0).toLocaleString('id-ID')}
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing || data.items.length === 0}>
                            Simpan Purchase Order
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
