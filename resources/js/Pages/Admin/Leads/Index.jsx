import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function LeadsIndex({ leads, filters, statuses }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">Daftar Leads</h2>
                    <a
                        href={route('admin.leads.export')}
                        className="rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700"
                    >
                        Export CSV
                    </a>
                </div>
            }
        >
            <Head title="Leads" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-4 flex flex-wrap gap-2">
                        <Link
                            href={route('admin.leads.index')}
                            className={`rounded-full px-3 py-1 text-sm ${!filters.status ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'}`}
                        >
                            Semua
                        </Link>
                        {statuses.map((s) => (
                            <Link
                                key={s.value}
                                href={route('admin.leads.index', { status: s.value })}
                                className={`rounded-full px-3 py-1 text-sm ${filters.status === s.value ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'}`}
                            >
                                {s.label}
                            </Link>
                        ))}
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Telegram</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Wallet</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Deposit</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {leads.data.map((lead) => (
                                    <tr key={lead.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 text-sm">{lead.name ?? '-'}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">@{lead.telegram_username ?? lead.telegram_id}</td>
                                        <td className="px-6 py-4">
                                            <span className="rounded-full bg-gray-100 px-2 py-1 text-xs">{lead.status}</span>
                                        </td>
                                        <td className="px-6 py-4 text-sm">{lead.wallet_id ?? '-'}</td>
                                        <td className="px-6 py-4 text-sm">${lead.hfm_deposit ?? '0'}</td>
                                        <td className="px-6 py-4">
                                            <Link href={route('admin.leads.show', lead.id)} className="text-indigo-600 hover:underline text-sm">
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <div className="flex justify-center gap-2 p-4">
                            {leads.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    className={`px-3 py-1 text-sm rounded ${link.active ? 'bg-indigo-600 text-white' : 'bg-gray-100'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
