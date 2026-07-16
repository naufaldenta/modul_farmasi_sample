import AppLayout from '@/layouts/app-layout';
import { Head, useForm, Link } from '@inertiajs/react';
import { store, index as indexRoute } from '@/routes/distributions';

interface Drug {
    id: number;
    name: string;
}

interface Props {
    drugs: Drug[];
}

export default function Create({ drugs }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        unit_name: '',
        notes: '',
        items: [{ drug_id: '', qty_requested: '' }]
    });

    const addItem = () => {
        setData('items', [...data.items, { drug_id: '', qty_requested: '' }]);
    };

    const removeItem = (index: number) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const updateItem = (index: number, field: string, value: string) => {
        const newItems = [...data.items];
        newItems[index] = { ...newItems[index], [field]: value };
        setData('items', newItems);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store.url());
    };

    return (
        <AppLayout title="Buat Permintaan Distribusi">
            <Head title="Buat Distribusi" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h2 className="text-2xl font-semibold mb-6">Form Permintaan Distribusi</h2>

                            <form onSubmit={submit}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Unit / Ruangan Peminta</label>
                                        <input
                                            type="text"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            value={data.unit_name}
                                            onChange={e => setData('unit_name', e.target.value)}
                                            required
                                        />
                                        {errors.unit_name && <div className="text-red-500 text-sm mt-1">{errors.unit_name}</div>}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Catatan</label>
                                        <input
                                            type="text"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            value={data.notes}
                                            onChange={e => setData('notes', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="mb-6">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-medium text-gray-900">Daftar Obat</h3>
                                        <button
                                            type="button"
                                            onClick={addItem}
                                            className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                                        >
                                            + Tambah Item
                                        </button>
                                    </div>

                                    {data.items.map((item, index) => (
                                        <div key={index} className="flex gap-4 items-end mb-4 bg-gray-50 p-4 rounded-lg">
                                            <div className="flex-1">
                                                <label className="block text-sm font-medium text-gray-700">Obat</label>
                                                <select
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    value={item.drug_id}
                                                    onChange={e => updateItem(index, 'drug_id', e.target.value)}
                                                    required
                                                >
                                                    <option value="">Pilih Obat</option>
                                                    {drugs.map(drug => (
                                                        <option key={drug.id} value={drug.id}>{drug.name}</option>
                                                    ))}
                                                </select>
                                                {/* @ts-ignore */}
                                                {errors[`items.${index}.drug_id`] && <div className="text-red-500 text-sm mt-1">{errors[`items.${index}.drug_id`]}</div>}
                                            </div>
                                            <div className="w-48">
                                                <label className="block text-sm font-medium text-gray-700">Qty Diminta</label>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    value={item.qty_requested}
                                                    onChange={e => updateItem(index, 'qty_requested', e.target.value)}
                                                    required
                                                />
                                            </div>
                                            {data.items.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeItem(index)}
                                                    className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 h-[42px] mb-[2px]"
                                                >
                                                    Hapus
                                                </button>
                                            )}
                                        </div>
                                    ))}
                                </div>

                                <div className="flex justify-end gap-4 mt-8 pt-4 border-t border-gray-200">
                                    <Link
                                        href={indexRoute.url()}
                                        className="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 font-semibold"
                                    >
                                        Batal
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 font-semibold"
                                    >
                                        Simpan Permintaan
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
