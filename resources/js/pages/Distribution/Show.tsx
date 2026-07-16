import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { approve, reject, exportPdf, index as indexRoute } from '@/routes/distributions';

interface DistributionItem {
    id: number;
    drug_id: number;
    qty_requested: number;
    qty_approved: number | null;
    drug: {
        name: string;
        unit: string;
    };
}

interface Distribution {
    id: number;
    distribution_number: string;
    unit_name: string;
    status: 'pending' | 'approved' | 'rejected' | 'completed';
    requested_by: { name: string };
    approved_by: { name: string } | null;
    notes: string | null;
    created_at: string;
    items: DistributionItem[];
    stock_transactions?: any[];
}

interface Props {
    distribution: Distribution;
}

export default function Show({ distribution }: Props) {
    const { data, setData, post, processing } = useForm({
        items: distribution.items.reduce((acc, item) => ({
            ...acc,
            [item.id]: item.qty_requested
        }), {})
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'approved': return 'bg-green-100 text-green-800';
            case 'rejected': return 'bg-red-100 text-red-800';
            case 'completed': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const handleApprove = (e: React.FormEvent) => {
        e.preventDefault();
        post(approve.url({ distribution: distribution.id }));
    };

    const handleReject = () => {
        if (confirm('Yakin ingin menolak permintaan distribusi ini?')) {
            post(reject.url({ distribution: distribution.id }));
        }
    };

    return (
        <AppLayout title={`Distribusi ${distribution.distribution_number}`}>
            <Head title={`Distribusi - ${distribution.distribution_number}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-gray-800 mb-1">
                                        {distribution.distribution_number}
                                    </h2>
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(distribution.status)}`}>
                                        {distribution.status.toUpperCase()}
                                    </span>
                                </div>
                                <div className="text-right text-sm text-gray-600">
                                    <p>Tgl: {new Date(distribution.created_at).toLocaleDateString('id-ID')}</p>
                                    <p>Unit Peminta: <span className="font-semibold">{distribution.unit_name}</span></p>
                                    <p>Pemohon: {distribution.requested_by.name}</p>
                                    {distribution.approved_by && (
                                        <p>Diproses Oleh: {distribution.approved_by.name}</p>
                                    )}
                                </div>
                            </div>

                            {distribution.notes && (
                                <div className="mb-6 p-4 bg-gray-50 rounded-md text-sm">
                                    <span className="font-semibold block mb-1">Catatan:</span>
                                    <p>{distribution.notes}</p>
                                </div>
                            )}

                            <h3 className="text-lg font-semibold mb-4 border-b pb-2">Detail Item (Obat)</h3>
                            <form onSubmit={handleApprove}>
                                <div className="overflow-x-auto mb-6">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Obat</th>
                                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Diminta</th>
                                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Disetujui</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {distribution.items.map((item) => (
                                                <tr key={item.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                        {item.drug.name} <span className="text-gray-500 font-normal">({item.drug.unit})</span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                        {item.qty_requested}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                        {distribution.status === 'pending' ? (
                                                            <input
                                                                type="number"
                                                                min="0"
                                                                max={item.qty_requested}
                                                                className="w-24 text-right rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                                                // @ts-ignore
                                                                value={data.items[item.id]}
                                                                onChange={e => setData('items', { ...data.items, [item.id]: e.target.value })}
                                                            />
                                                        ) : (
                                                            <span className="font-semibold">{item.qty_approved}</span>
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                {distribution.status === 'approved' && distribution.stock_transactions && distribution.stock_transactions.length > 0 && (
                                    <div className="mt-8">
                                        <h3 className="text-lg font-semibold mb-4 border-b pb-2">Riwayat Pengeluaran Batch (FEFO)</h3>
                                        <div className="bg-blue-50 p-4 rounded-md overflow-x-auto">
                                            <table className="min-w-full text-sm">
                                                <thead>
                                                    <tr>
                                                        <th className="text-left py-2 px-4">Obat ID</th>
                                                        <th className="text-left py-2 px-4">Batch ID</th>
                                                        <th className="text-right py-2 px-4">Qty Dikeluarkan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {distribution.stock_transactions.map((trx) => (
                                                        <tr key={trx.id} className="border-t border-blue-100">
                                                            <td className="py-2 px-4">{trx.drug_id}</td>
                                                            <td className="py-2 px-4">{trx.drug_batch_id}</td>
                                                            <td className="text-right py-2 px-4 font-mono font-semibold">{trx.qty}</td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                )}

                                <div className="flex justify-end gap-4 mt-8 pt-4 border-t border-gray-200">
                                    {distribution.status === 'approved' && (
                                        <a
                                            href={exportPdf.url({ distribution: distribution.id })}
                                            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Cetak Bukti (PDF)
                                        </a>
                                    )}
                                    <Link
                                        href={indexRoute.url()}
                                        className="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 font-semibold"
                                    >
                                        Kembali
                                    </Link>
                                    
                                    {distribution.status === 'pending' && (
                                        <>
                                            <button
                                                type="button"
                                                disabled={processing}
                                                onClick={handleReject}
                                                className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50 font-semibold"
                                            >
                                                Tolak Permintaan
                                            </button>
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50 font-semibold"
                                            >
                                                Approve & Eksekusi FEFO
                                            </button>
                                        </>
                                    )}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
