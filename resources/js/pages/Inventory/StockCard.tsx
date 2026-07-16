import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { BreadcrumbItem } from '@/components/breadcrumbs';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useState } from 'react';

interface Drug {
    id: number;
    name: string;
    code: string;
}

interface StockTransaction {
    id: number;
    drug_batch_id: number;
    type: 'masuk' | 'keluar' | 'penyesuaian';
    qty: number;
    transaction_date: string;
    user: { name: string } | null;
    drug_batch: { batch_number: string, expired_date: string } | null;
}

interface Props {
    drugs: Drug[];
    selectedDrug: Drug | null;
    transactions: StockTransaction[];
    filters: { start_date: string; end_date: string };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Kartu Stok', href: '/stock-card' },
];

export default function StockCard({ drugs, selectedDrug, transactions, filters }: Props) {
    const [drugId, setDrugId] = useState<string>(selectedDrug?.id.toString() || '');
    const [startDate, setStartDate] = useState(filters.start_date);
    const [endDate, setEndDate] = useState(filters.end_date);

    const handleFilter = () => {
        router.get('/stock-card', { drug_id: drugId, start_date: startDate, end_date: endDate }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kartu Stok Digital" />

            <div className="flex flex-col gap-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Kartu Stok Digital</CardTitle>
                        <CardDescription>Riwayat mutasi stok (masuk/keluar) per obat.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col md:flex-row gap-4 items-end mb-6">
                            <div className="w-full md:w-1/3">
                                <Label htmlFor="drug">Pilih Obat</Label>
                                <Select value={drugId} onValueChange={setDrugId}>
                                    <SelectTrigger id="drug">
                                        <SelectValue placeholder="-- Pilih Obat --" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {drugs.map(d => (
                                            <SelectItem key={d.id} value={d.id.toString()}>{d.code} - {d.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="w-full md:w-1/4">
                                <Label htmlFor="start_date">Dari Tanggal</Label>
                                <Input id="start_date" type="date" value={startDate} onChange={e => setStartDate(e.target.value)} />
                            </div>
                            <div className="w-full md:w-1/4">
                                <Label htmlFor="end_date">Sampai Tanggal</Label>
                                <Input id="end_date" type="date" value={endDate} onChange={e => setEndDate(e.target.value)} />
                            </div>
                            <Button onClick={handleFilter}>Filter</Button>
                        </div>

                        {selectedDrug && (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Waktu</TableHead>
                                        <TableHead>Tipe Mutasi</TableHead>
                                        <TableHead>Batch</TableHead>
                                        <TableHead>ED</TableHead>
                                        <TableHead className="text-right">Qty</TableHead>
                                        <TableHead>Petugas</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {transactions.map(t => (
                                        <TableRow key={t.id}>
                                            <TableCell>{new Date(t.transaction_date).toLocaleString('id-ID')}</TableCell>
                                            <TableCell>
                                                <span className={`px-2 py-1 rounded text-xs font-semibold ${t.type === 'masuk' ? 'bg-green-100 text-green-800' : t.type === 'keluar' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'}`}>
                                                    {t.type.toUpperCase()}
                                                </span>
                                            </TableCell>
                                            <TableCell>{t.drug_batch?.batch_number}</TableCell>
                                            <TableCell>{t.drug_batch?.expired_date}</TableCell>
                                            <TableCell className="text-right font-mono">{t.type === 'masuk' ? '+' : t.type === 'keluar' ? '-' : ''}{t.qty}</TableCell>
                                            <TableCell>{t.user?.name || '-'}</TableCell>
                                        </TableRow>
                                    ))}
                                    {transactions.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                                                Tidak ada mutasi pada periode ini.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
