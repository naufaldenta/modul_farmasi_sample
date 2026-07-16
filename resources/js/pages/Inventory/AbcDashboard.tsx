import React, { useMemo } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, ComposedChart, Line } from 'recharts';

interface Drug {
    id: number;
    name: string;
    code: string;
    abc_class: 'A' | 'B' | 'C' | null;
    avg_daily_usage: number;
    price: number;
}

export default function AbcDashboard({ drugs }: { drugs: Drug[] }) {
    
    const chartData = useMemo(() => {
        // Calculate value and sort descending
        let data = drugs.map(d => ({
            name: d.name,
            value: Number(d.avg_daily_usage) * Number(d.price) * 30, // 30 days period
            class: d.abc_class,
            price: Number(d.price),
            usage: Number(d.avg_daily_usage)
        })).sort((a, b) => b.value - a.value);

        let totalValue = data.reduce((acc, curr) => acc + curr.value, 0);
        let cumulativeValue = 0;

        return data.map(item => {
            cumulativeValue += item.value;
            let percentage = totalValue > 0 ? (cumulativeValue / totalValue) * 100 : 0;
            return {
                ...item,
                cumulativePercentage: Math.round(percentage * 100) / 100
            };
        });
    }, [drugs]);

    const breadcrumbs = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dashboard Pareto ABC', href: '/inventory/abc' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="ABC Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Chart Section */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Grafik Pareto (Nilai Pemakaian)</h3>
                        <div className="h-96">
                            <ResponsiveContainer width="100%" height="100%">
                                <ComposedChart
                                    data={chartData}
                                    margin={{ top: 20, right: 20, bottom: 20, left: 20 }}
                                >
                                    <CartesianGrid stroke="#f5f5f5" />
                                    <XAxis dataKey="name" tick={{fontSize: 10}} interval={0} angle={-45} textAnchor="end" height={80} />
                                    <YAxis yAxisId="left" />
                                    <YAxis yAxisId="right" orientation="right" domain={[0, 100]} />
                                    <Tooltip />
                                    <Legend />
                                    <Bar yAxisId="left" dataKey="value" name="Nilai (Rp)" fill="#4f46e5" />
                                    <Line yAxisId="right" type="monotone" dataKey="cumulativePercentage" name="Persentase Kumulatif (%)" stroke="#ef4444" />
                                </ComposedChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    {/* Table Section */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Daftar Klasifikasi ABC</h3>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Obat</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Usage (Harian)</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai 30 Hari (Rp)</th>
                                            <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas ABC</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {chartData.map((item, index) => (
                                            <tr key={index}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{drugs.find(d => d.name === item.name)?.code}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{item.usage.toFixed(2)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.value)}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center">
                                                    <span className={`px-2 py-1 text-xs font-semibold rounded-full ${
                                                        item.class === 'A' ? 'bg-green-100 text-green-800' :
                                                        item.class === 'B' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {item.class || '-'}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
