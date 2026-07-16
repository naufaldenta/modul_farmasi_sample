import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { BreadcrumbItem } from '@/components/breadcrumbs';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';

interface Props {
    hasData: boolean;
    compliance: {
        total_transactions: number;
        compliant_transactions: number;
        compliance_rate: number;
    } | null;
    waste: {
        fefo: { waste_qty: number; waste_value: number };
        fifo: { waste_qty: number; waste_value: number };
        savings_qty: number;
        savings_value: number;
    } | null;
    forecast: {
        overall_mae: number;
        overall_mape: number;
        evaluated_drugs: number;
        details: Array<{ drug: string; mae: number; mape: number }>;
    } | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Research Report', href: '/research-report' },
];

export default function ResearchReport({ hasData, compliance, waste, forecast }: Props) {
    const [loading, setLoading] = useState(false);

    const runEvaluation = () => {
        setLoading(true);
        router.get('/research-report', { evaluate: true }, {
            onFinish: () => setLoading(false)
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Research Report & Evaluation" />

            <div className="flex flex-col gap-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Laporan Evaluasi Riset</h1>
                        <p className="text-muted-foreground">Kalkulasi metrik akademik untuk data historis.</p>
                    </div>
                    <Button onClick={runEvaluation} disabled={loading}>
                        {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                        {loading ? 'Mengevaluasi Data...' : 'Jalankan Evaluasi (Data Historis)'}
                    </Button>
                </div>

                {!hasData && !loading && (
                    <Card>
                        <CardContent className="py-12 flex items-center justify-center text-center flex-col">
                            <p className="text-muted-foreground">Klik tombol di atas untuk memulai evaluasi data historis.</p>
                            <p className="text-sm text-muted-foreground">Proses ini mungkin memakan waktu beberapa detik karena mengevaluasi puluhan ribu record.</p>
                        </CardContent>
                    </Card>
                )}

                {hasData && (
                    <div className="grid gap-6 md:grid-cols-2">
                        {/* FEFO Compliance */}
                        <Card>
                            <CardHeader>
                                <CardTitle>1. Kepatuhan FEFO</CardTitle>
                                <CardDescription>Persentase transaksi keluar yang mengambil batch ber-ED terdekat.</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="flex justify-between border-b pb-2">
                                        <span className="text-muted-foreground">Total Transaksi Keluar</span>
                                        <span className="font-medium">{compliance?.total_transactions.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div className="flex justify-between border-b pb-2">
                                        <span className="text-muted-foreground">Transaksi FEFO Compliant</span>
                                        <span className="font-medium">{compliance?.compliant_transactions.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div className="flex justify-between items-center">
                                        <span className="text-muted-foreground font-semibold">Tingkat Kepatuhan (Rate)</span>
                                        <span className="text-2xl font-bold text-green-600">{compliance?.compliance_rate}%</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Waste Simulation */}
                        <Card>
                            <CardHeader>
                                <CardTitle>2. Simulasi Pengurangan Waste</CardTitle>
                                <CardDescription>Perbandingan kerugian ED (FEFO vs FIFO).</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Strategi</TableHead>
                                            <TableHead className="text-right">Waste (Qty)</TableHead>
                                            <TableHead className="text-right">Kerugian (Rp)</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow>
                                            <TableCell className="font-medium">FIFO (Simulasi)</TableCell>
                                            <TableCell className="text-right">{waste?.fifo.waste_qty.toLocaleString('id-ID')}</TableCell>
                                            <TableCell className="text-right">Rp {waste?.fifo.waste_value.toLocaleString('id-ID')}</TableCell>
                                        </TableRow>
                                        <TableRow>
                                            <TableCell className="font-medium">FEFO (Aktual)</TableCell>
                                            <TableCell className="text-right">{waste?.fefo.waste_qty.toLocaleString('id-ID')}</TableCell>
                                            <TableCell className="text-right">Rp {waste?.fefo.waste_value.toLocaleString('id-ID')}</TableCell>
                                        </TableRow>
                                        <TableRow className="bg-muted/50 font-bold">
                                            <TableCell>Penghematan</TableCell>
                                            <TableCell className="text-right text-green-600">{waste?.savings_qty.toLocaleString('id-ID')}</TableCell>
                                            <TableCell className="text-right text-green-600">Rp {waste?.savings_value.toLocaleString('id-ID')}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>

                        {/* Forecast Split */}
                        <Card className="md:col-span-2">
                            <CardHeader>
                                <CardTitle>3. Evaluasi Akurasi Forecast</CardTitle>
                                <CardDescription>Data Split (9 bulan Train, 3 bulan Test) menggunakan Simple Moving Average (SMA-3).</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-3 gap-4 mb-6">
                                    <div className="bg-muted p-4 rounded-lg text-center">
                                        <p className="text-sm text-muted-foreground mb-1">Obat Dievaluasi</p>
                                        <p className="text-2xl font-bold">{forecast?.evaluated_drugs}</p>
                                    </div>
                                    <div className="bg-muted p-4 rounded-lg text-center">
                                        <p className="text-sm text-muted-foreground mb-1">Overall MAE</p>
                                        <p className="text-2xl font-bold">{forecast?.overall_mae}</p>
                                    </div>
                                    <div className="bg-muted p-4 rounded-lg text-center">
                                        <p className="text-sm text-muted-foreground mb-1">Overall MAPE</p>
                                        <p className="text-2xl font-bold">{forecast?.overall_mape}%</p>
                                    </div>
                                </div>

                                <h3 className="font-medium mb-3">Detail Per Obat (Top 10)</h3>
                                <div className="border rounded">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Nama Obat</TableHead>
                                                <TableHead className="text-right">Average MAE</TableHead>
                                                <TableHead className="text-right">Average MAPE (%)</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {forecast?.details.slice(0, 10).map((d, i) => (
                                                <TableRow key={i}>
                                                    <TableCell className="font-medium">{d.drug}</TableCell>
                                                    <TableCell className="text-right">{d.mae}</TableCell>
                                                    <TableCell className="text-right">{d.mape}%</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
