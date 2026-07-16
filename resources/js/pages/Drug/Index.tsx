import { Head, useForm } from '@inertiajs/react';
import { store, update, destroy as destroyRoute } from '@/routes/drugs';
import AppLayout from '@/layouts/app-layout';
import { FormEvent, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Pagination from '@/components/Pagination';
import { Textarea } from '@/components/ui/textarea';

interface Category {
    id: number;
    name: string;
}

interface Drug {
    id: number;
    category_id: number;
    category?: Category;
    code: string;
    name: string;
    unit: string;
    min_stock: number;
    max_stock: number;
    abc_class: string;
    description: string;
}

interface Props {
    drugs: {
        data: Drug[];
        links: any[];
    };
    categories: Category[];
}

export default function Index({ drugs, categories }: Props) {
    const { data, setData, post, put, delete: destroy, reset, errors, processing } = useForm({
        category_id: '',
        code: '',
        name: '',
        unit: '',
        min_stock: 0,
        max_stock: 0,
        abc_class: 'C',
        description: '',
    });

    const [isEditing, setIsEditing] = useState<number | null>(null);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditing) {
            put(update.url({ drug: isEditing }), {
                onSuccess: () => {
                    reset();
                    setIsEditing(null);
                },
            });
        } else {
            post(store.url(), {
                onSuccess: () => reset(),
            });
        }
    };

    const handleEdit = (drug: Drug) => {
        setIsEditing(drug.id);
        setData({
            category_id: drug.category_id.toString(),
            code: drug.code,
            name: drug.name,
            unit: drug.unit,
            min_stock: drug.min_stock,
            max_stock: drug.max_stock,
            abc_class: drug.abc_class,
            description: drug.description || '',
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus?')) {
            destroy(destroyRoute.url({ drug: id }));
        }
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Data Obat', href: '/drugs' }]}>
            <Head title="Data Obat" />

            <div className="flex flex-col gap-6 p-6">
                <div className="bg-white p-6 rounded-lg shadow-sm border border-sidebar-border">
                    <h3 className="text-lg font-medium mb-4">{isEditing ? 'Edit Obat' : 'Tambah Obat'}</h3>
                    <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Kategori</label>
                            <select 
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                value={data.category_id}
                                onChange={e => setData('category_id', e.target.value)}
                            >
                                <option value="">Pilih Kategori</option>
                                {categories.map(c => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                            {errors.category_id && <p className="text-red-500 text-sm mt-1">{errors.category_id}</p>}
                        </div>
                        
                        <div>
                            <label className="block text-sm font-medium mb-1">Kategori ABC</label>
                            <select 
                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                value={data.abc_class}
                                onChange={e => setData('abc_class', e.target.value)}
                            >
                                <option value="A">A (Fast Moving)</option>
                                <option value="B">B (Medium Moving)</option>
                                <option value="C">C (Slow Moving)</option>
                            </select>
                            {errors.abc_class && <p className="text-red-500 text-sm mt-1">{errors.abc_class}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">Kode Obat</label>
                            <Input value={data.code} onChange={e => setData('code', e.target.value)} placeholder="OBT-001" />
                            {errors.code && <p className="text-red-500 text-sm mt-1">{errors.code}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">Nama Obat</label>
                            <Input value={data.name} onChange={e => setData('name', e.target.value)} placeholder="Paracetamol" />
                            {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">Satuan</label>
                            <Input value={data.unit} onChange={e => setData('unit', e.target.value)} placeholder="Strip/Botol/Box" />
                            {errors.unit && <p className="text-red-500 text-sm mt-1">{errors.unit}</p>}
                        </div>

                        <div className="flex gap-4">
                            <div className="w-1/2">
                                <label className="block text-sm font-medium mb-1">Stok Min</label>
                                <Input type="number" value={data.min_stock} onChange={e => setData('min_stock', parseInt(e.target.value) || 0)} />
                                {errors.min_stock && <p className="text-red-500 text-sm mt-1">{errors.min_stock}</p>}
                            </div>
                            <div className="w-1/2">
                                <label className="block text-sm font-medium mb-1">Stok Max</label>
                                <Input type="number" value={data.max_stock} onChange={e => setData('max_stock', parseInt(e.target.value) || 0)} />
                                {errors.max_stock && <p className="text-red-500 text-sm mt-1">{errors.max_stock}</p>}
                            </div>
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium mb-1">Deskripsi</label>
                            <Textarea value={data.description} onChange={e => setData('description', e.target.value)} />
                        </div>

                        <div className="md:col-span-2 flex gap-2">
                            <Button type="submit" disabled={processing} className="w-32">
                                {isEditing ? 'Update' : 'Simpan'}
                            </Button>
                            {isEditing && (
                                <Button type="button" variant="outline" onClick={() => { setIsEditing(null); reset(); }}>
                                    Batal
                                </Button>
                            )}
                        </div>
                    </form>
                </div>

                <div className="bg-white rounded-lg shadow-sm border border-sidebar-border overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm whitespace-nowrap">
                            <thead className="bg-muted">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Kode</th>
                                    <th className="px-4 py-3 font-medium">Kategori</th>
                                    <th className="px-4 py-3 font-medium">Nama Obat</th>
                                    <th className="px-4 py-3 font-medium">Satuan</th>
                                    <th className="px-4 py-3 font-medium">ABC</th>
                                    <th className="px-4 py-3 font-medium text-right">Min/Max</th>
                                    <th className="px-4 py-3 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {drugs.data.map(drug => (
                                    <tr key={drug.id} className="border-t border-sidebar-border">
                                        <td className="px-4 py-3 font-mono">{drug.code}</td>
                                        <td className="px-4 py-3">{drug.category?.name}</td>
                                        <td className="px-4 py-3">{drug.name}</td>
                                        <td className="px-4 py-3">{drug.unit}</td>
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-1 rounded text-xs font-medium ${
                                                drug.abc_class === 'A' ? 'bg-red-100 text-red-800' :
                                                drug.abc_class === 'B' ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-green-100 text-green-800'
                                            }`}>
                                                {drug.abc_class}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {drug.min_stock} / {drug.max_stock}
                                        </td>
                                        <td className="px-4 py-3 space-x-2">
                                            <Button variant="outline" size="sm" onClick={() => handleEdit(drug)}>Edit</Button>
                                            <Button variant="destructive" size="sm" onClick={() => handleDelete(drug.id)}>Hapus</Button>
                                        </td>
                                    </tr>
                                ))}
                                {drugs.data.length === 0 && (
                                    <tr>
                                        <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">Belum ada data obat</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    <div className="p-4 border-t">
                        <Pagination links={drugs.links} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
