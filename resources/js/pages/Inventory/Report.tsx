import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { receipts, distributions, returns } from '@/routes/reports';

export default function Report() {
    const { data: receiptsData, setData: setReceiptsData } = useForm({
        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0],
        format: 'pdf',
    });

    const { data: distData, setData: setDistData } = useForm({
        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0],
        format: 'pdf',
    });

    return (
        <AppLayout title="Laporan & Ekspor">
            <Head title="Laporan & Ekspor" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {/* Laporan Penerimaan */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 className="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Laporan Penerimaan Obat</h2>
                            <form action={receipts.url()} method="GET" target="_blank" className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                    <input 
                                        type="date" 
                                        name="start_date" 
                                        value={receiptsData.start_date}
                                        onChange={e => setReceiptsData('start_date', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                                    <input 
                                        type="date" 
                                        name="end_date" 
                                        value={receiptsData.end_date}
                                        onChange={e => setReceiptsData('end_date', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Format</label>
                                    <select 
                                        name="format" 
                                        value={receiptsData.format}
                                        onChange={e => setReceiptsData('format', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                    >
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                    </select>
                                </div>
                                <button type="submit" className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold">
                                    Unduh Laporan
                                </button>
                            </form>
                        </div>

                        {/* Laporan Distribusi */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 className="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Laporan Distribusi Obat</h2>
                            <form action={distributions.url()} method="GET" target="_blank" className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                    <input 
                                        type="date" 
                                        name="start_date" 
                                        value={distData.start_date}
                                        onChange={e => setDistData('start_date', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                                    <input 
                                        type="date" 
                                        name="end_date" 
                                        value={distData.end_date}
                                        onChange={e => setDistData('end_date', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Format</label>
                                    <select 
                                        name="format" 
                                        value={distData.format}
                                        onChange={e => setDistData('format', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                    >
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                    </select>
                                </div>
                                <button type="submit" className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold">
                                    Unduh Laporan
                                </button>
                            </form>
                        </div>

                        {/* Daftar Obat Retur */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                            <h2 className="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Daftar Retur Obat (ED &lt; 90 Hari)</h2>
                            <p className="text-gray-600 mb-4 text-sm">Unduh daftar batch obat yang sudah kadaluarsa atau akan kadaluarsa dalam waktu 90 hari ke depan untuk proses retur ke supplier.</p>
                            <form action={returns.url()} method="GET" target="_blank">
                                <div className="flex gap-4">
                                    <div className="w-1/3">
                                        <select name="format" className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200">
                                            <option value="pdf">PDF</option>
                                            <option value="excel">Excel</option>
                                        </select>
                                    </div>
                                    <button type="submit" className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-semibold">
                                        Unduh Daftar Retur
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
