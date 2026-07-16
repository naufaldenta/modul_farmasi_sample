import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { generate } from '@/routes/forecast';
import { BreadcrumbItem } from '@/components/breadcrumbs';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

interface Drug {
    id: number;
    name: string;
    code: string;
}

interface Forecast {
    id: number;
    period_date: string;
    method: string;
    predicted_qty: number;
    actual_qty: number | null;
    mae: number | null;
    mape: number | null;
}

interface Props {
    drugs: Drug[];
    selectedDrug: Drug | null;
    forecasts: Forecast[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Forecasting', href: '/forecast' },
];

export default function Forecast({ drugs, selectedDrug, forecasts }: Props) {
    const handleDrugChange = (val: string) => {
        router.get('/forecast', { drug_id: val }, { preserveState: true });
    };

    const chartData = forecasts.map(f => ({
        name: f.period_date,
        Predicted: f.predicted_qty,
        Actual: f.actual_qty ?? undefined,
    }));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Forecasting" />

            <div className="flex flex-col gap-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Forecasting & Evaluasi (SMA)</CardTitle>
                        <CardDescription>Pilih obat untuk melihat prediksi kebutuhan bulan depan dan akurasi (MAE/MAPE) bulan-bulan sebelumnya.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col md:flex-row gap-4 items-end mb-6">
                            <div className="w-full md:w-1/3">
                                <Label htmlFor="drug">Pilih Obat</Label>
                                <Select value={selectedDrug?.id.toString() || ''} onValueChange={handleDrugChange}>
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
                        </div>

                        <div className="bg-slate-50 p-4 border rounded-md mb-6">
                            <h3 className="font-semibold mb-4 text-sm border-b pb-2">Generate Forecasting Baru</h3>
                            <form action={generate.url()} method="POST" className="flex flex-col md:flex-row gap-4 items-end" onSubmit={(e) => {
                                e.preventDefault();
                                router.post(generate.url(), {
                                    method: (document.getElementById('method') as HTMLSelectElement).value,
                                    periods: (document.getElementById('periods') as HTMLInputElement).value,
                                }, { preserveState: true });
                            }}>
                                <div className="w-full md:w-1/3">
                                    <Label htmlFor="method">Metode Forecasting</Label>
                                    <select id="method" name="method" className="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="sma">Simple Moving Average (SMA)</option>
                                        <option value="wma">Weighted Moving Average (WMA)</option>
                                        <option value="linear_regression">Linear Regression</option>
                                    </select>
                                </div>
                                <div className="w-full md:w-1/4">
                                    <Label htmlFor="periods">Periode History (Bulan)</Label>
                                    <Input id="periods" name="periods" type="number" defaultValue="3" min="1" max="12" />
                                </div>
                                <div>
                                    <Button type="submit">Generate Forecast</Button>
                                </div>
                            </form>
                        </div>

                        {selectedDrug && forecasts.length > 0 && (
                            <div className="flex flex-col gap-8">
                                <div className="h-[400px] w-full border rounded p-4">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <LineChart data={chartData} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis dataKey="name" />
                                            <YAxis />
                                            <Tooltip />
                                            <Legend />
                                            <Line type="monotone" dataKey="Predicted" stroke="#8884d8" activeDot={{ r: 8 }} />
                                            <Line type="monotone" dataKey="Actual" stroke="#82ca9d" />
                                        </LineChart>
                                    </ResponsiveContainer>
                                </div>

                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Periode</TableHead>
                                            <TableHead>Metode</TableHead>
                                            <TableHead className="text-right">Prediksi (Qty)</TableHead>
                                            <TableHead className="text-right">Aktual (Qty)</TableHead>
                                            <TableHead className="text-right">MAE</TableHead>
                                            <TableHead className="text-right">MAPE (%)</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {forecasts.map(f => (
                                            <TableRow key={f.id}>
                                                <TableCell>{f.period_date}</TableCell>
                                                <TableCell className="uppercase">{f.method}</TableCell>
                                                <TableCell className="text-right">{f.predicted_qty}</TableCell>
                                                <TableCell className="text-right">{f.actual_qty ?? '-'}</TableCell>
                                                <TableCell className="text-right">{f.mae ?? '-'}</TableCell>
                                                <TableCell className="text-right">{f.mape ? `${f.mape}%` : '-'}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                        
                        {selectedDrug && forecasts.length === 0 && (
                            <p className="text-muted-foreground text-center py-8">Belum ada data forecasting untuk obat ini.</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
