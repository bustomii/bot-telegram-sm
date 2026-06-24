import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ statusCounts, recentLeads, totalLeads, activeMembers }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard Bot Telegram
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <p className="text-sm text-gray-500">Total Leads</p>
                            <p className="text-3xl font-bold text-gray-900">{totalLeads}</p>
                        </div>
                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <p className="text-sm text-gray-500">Member Aktif</p>
                            <p className="text-3xl font-bold text-green-600">{activeMembers}</p>
                        </div>
                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <p className="text-sm text-gray-500">Menunggu Verifikasi</p>
                            <p className="text-3xl font-bold text-yellow-600">
                                {statusCounts.VERIFIED?.count ?? 0}
                            </p>
                        </div>
                    </div>

                    <div className="mb-6 overflow-hidden rounded-lg bg-white shadow">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-medium text-gray-900">Status Leads</h3>
                        </div>
                        <div className="grid grid-cols-2 gap-3 p-6 sm:grid-cols-3 lg:grid-cols-4">
                            {Object.entries(statusCounts).map(([key, item]) => (
                                <Link
                                    key={key}
                                    href={route('admin.leads.index', { status: key })}
                                    className="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"
                                >
                                    <p className="text-xs text-gray-500">{item.label}</p>
                                    <p className="text-2xl font-semibold text-gray-900">{item.count}</p>
                                </Link>
                            ))}
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Telegram</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Terakhir Aktif</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {recentLeads.map((lead) => (
                                        <tr key={lead.id} className="hover:bg-gray-50">
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <Link href={route('admin.leads.show', lead.id)} className="text-indigo-600 hover:underline">
                                                    {lead.name ?? '-'}
                                                </Link>
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                @{lead.telegram_username ?? '-'}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                    {lead.status}
                                                </span>
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {lead.last_activity_at ? new Date(lead.last_activity_at).toLocaleString('id-ID') : '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
