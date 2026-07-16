import { Head, useForm } from '@inertiajs/react';
import { store, update, destroy as destroyRoute } from '@/routes/drug-categories';
import AppLayout from '@/layouts/app-layout';
import { FormEvent, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Pagination from '@/components/Pagination';
import { Textarea } from '@/components/ui/textarea';

interface Category {
    id: number;
    name: string;
    description: string;
}

interface Props {
    categories: {
        data: Category[];
        links: any[];
    };
}

export default function Index({ categories }: Props) {
    const { data, setData, post, put, delete: destroy, reset, errors, processing } = useForm({
        name: '',
        description: '',
    });

    const [isEditing, setIsEditing] = useState<number | null>(null);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditing) {
            put(update.url({ drug_category: isEditing }), {
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

    const handleEdit = (cat: Category) => {
        setIsEditing(cat.id);
        setData({
            name: cat.name,
            description: cat.description || '',
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus?')) {
            destroy(destroyRoute.url({ drug_category: id }));
        }
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Kategori Obat', href: '/drug-categories' }]}>
            <Head title="Kategori Obat" />

            <div className="flex flex-col md:flex-row gap-6 p-6">
                <div className="w-full md:w-1/3">
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-sidebar-border">
                        <h3 className="text-lg font-medium mb-4">{isEditing ? 'Edit Kategori' : 'Tambah Kategori'}</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium mb-1">Nama Kategori</label>
                                <Input 
                                    value={data.name} 
                                    onChange={e => setData('name', e.target.value)} 
                                    placeholder="contoh: Analgesik" 
                                />
                                {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">Deskripsi</label>
                                <Textarea 
                                    value={data.description} 
                                    onChange={e => setData('description', e.target.value)} 
                                    placeholder="Opsional" 
                                />
                                {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing} className="w-full">
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
                </div>

                <div className="w-full md:w-2/3">
                    <div className="bg-white rounded-lg shadow-sm border border-sidebar-border overflow-hidden">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Nama</th>
                                    <th className="px-4 py-3 font-medium">Deskripsi</th>
                                    <th className="px-4 py-3 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {categories.data.map(category => (
                                    <tr key={category.id} className="border-t border-sidebar-border">
                                        <td className="px-4 py-3">{category.name}</td>
                                        <td className="px-4 py-3 text-muted-foreground">{category.description || '-'}</td>
                                        <td className="px-4 py-3 space-x-2">
                                            <Button variant="outline" size="sm" onClick={() => handleEdit(category)}>Edit</Button>
                                            <Button variant="destructive" size="sm" onClick={() => handleDelete(category.id)}>Hapus</Button>
                                        </td>
                                    </tr>
                                ))}
                                {categories.data.length === 0 && (
                                    <tr>
                                        <td colSpan={3} className="px-4 py-8 text-center text-muted-foreground">Belum ada data kategori</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    <div className="p-4 border-t">
                        <Pagination links={categories.links} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
