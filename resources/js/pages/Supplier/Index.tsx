import { Head, useForm } from '@inertiajs/react';
import { store, update, destroy as destroyRoute } from '@/routes/suppliers';
import AppLayout from '@/layouts/app-layout';
import { FormEvent, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Pagination from '@/components/Pagination';
import { Textarea } from '@/components/ui/textarea';

interface Supplier {
    id: number;
    name: string;
    contact_person: string;
    phone: string;
    address: string;
}

interface Props {
    suppliers: {
        data: Supplier[];
        links: any[];
    };
}

export default function Index({ suppliers }: Props) {
    const { data, setData, post, put, delete: destroy, reset, errors, processing } = useForm({
        name: '',
        contact_person: '',
        phone: '',
        address: '',
    });

    const [isEditing, setIsEditing] = useState<number | null>(null);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditing) {
            put(update.url({ supplier: isEditing }), {
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

    const handleEdit = (supplier: Supplier) => {
        setIsEditing(supplier.id);
        setData({
            name: supplier.name,
            contact_person: supplier.contact_person || '',
            phone: supplier.phone || '',
            address: supplier.address || '',
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus?')) {
            destroy(destroyRoute.url({ supplier: id }));
        }
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Supplier', href: '/suppliers' }]}>
            <Head title="Supplier" />

            <div className="flex flex-col md:flex-row gap-6 p-6">
                <div className="w-full md:w-1/3">
                    <div className="bg-white p-6 rounded-lg shadow-sm border border-sidebar-border">
                        <h3 className="text-lg font-medium mb-4">{isEditing ? 'Edit Supplier' : 'Tambah Supplier'}</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium mb-1">Nama Supplier</label>
                                <Input 
                                    value={data.name} 
                                    onChange={e => setData('name', e.target.value)} 
                                    placeholder="PT. ABC" 
                                />
                                {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">Contact Person</label>
                                <Input 
                                    value={data.contact_person} 
                                    onChange={e => setData('contact_person', e.target.value)} 
                                    placeholder="Nama kontak" 
                                />
                                {errors.contact_person && <p className="text-red-500 text-sm mt-1">{errors.contact_person}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">Nomor Telepon</label>
                                <Input 
                                    value={data.phone} 
                                    onChange={e => setData('phone', e.target.value)} 
                                    placeholder="0812..." 
                                />
                                {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">Alamat</label>
                                <Textarea 
                                    value={data.address} 
                                    onChange={e => setData('address', e.target.value)} 
                                    placeholder="Alamat lengkap" 
                                />
                                {errors.address && <p className="text-red-500 text-sm mt-1">{errors.address}</p>}
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
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm whitespace-nowrap">
                                <thead className="bg-muted">
                                    <tr>
                                        <th className="px-4 py-3 font-medium">Nama</th>
                                        <th className="px-4 py-3 font-medium">Kontak</th>
                                        <th className="px-4 py-3 font-medium">Telepon</th>
                                        <th className="px-4 py-3 font-medium">Alamat</th>
                                        <th className="px-4 py-3 font-medium">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {suppliers.data.map(supplier => (
                                        <tr key={supplier.id} className="border-t border-sidebar-border">
                                            <td className="px-4 py-3">{supplier.name}</td>
                                            <td className="px-4 py-3 text-muted-foreground">{supplier.contact_person || '-'}</td>
                                            <td className="px-4 py-3 text-muted-foreground">{supplier.phone || '-'}</td>
                                            <td className="px-4 py-3 text-muted-foreground max-w-xs truncate">{supplier.address || '-'}</td>
                                            <td className="px-4 py-3 space-x-2">
                                                <Button variant="outline" size="sm" onClick={() => handleEdit(supplier)}>Edit</Button>
                                                <Button variant="destructive" size="sm" onClick={() => handleDelete(supplier.id)}>Hapus</Button>
                                            </td>
                                        </tr>
                                    ))}
                                    {suppliers.data.length === 0 && (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">Belum ada data supplier</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="p-4 border-t">
                        <Pagination links={suppliers.links} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
