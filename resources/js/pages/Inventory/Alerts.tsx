import React from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { read } from '@/routes/alerts';

interface Alert {
    id: number;
    drug_id: number;
    message: string;
    status: 'unread' | 'read';
    created_at: string;
    drug: {
        id: number;
        name: string;
        code: string;
    };
}

export default function Alerts({ alerts }: { alerts: Alert[] }) {
    const { post } = useForm();

    const markAsRead = (id: number) => {
        post(read.url({ alert: id }), {
            preserveScroll: true,
        });
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Reorder Alerts', href: '/alerts' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reorder Alerts" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="space-y-4">
                                {alerts.length === 0 && (
                                    <p className="text-gray-500">Tidak ada notifikasi saat ini.</p>
                                )}
                                {alerts.map((alert) => (
                                    <div 
                                        key={alert.id} 
                                        className={`p-4 border rounded-lg flex justify-between items-center ${
                                            alert.status === 'unread' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'
                                        }`}
                                    >
                                        <div>
                                            <p className={`text-sm ${alert.status === 'unread' ? 'font-bold text-red-800' : 'text-gray-700'}`}>
                                                {alert.message}
                                            </p>
                                            <p className="text-xs text-gray-500 mt-1">
                                                {new Date(alert.created_at).toLocaleString('id-ID')}
                                            </p>
                                        </div>
                                        {alert.status === 'unread' && (
                                            <button
                                                onClick={() => markAsRead(alert.id)}
                                                className="px-4 py-2 bg-white text-sm border border-gray-300 rounded hover:bg-gray-100"
                                            >
                                                Tandai Dibaca
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
