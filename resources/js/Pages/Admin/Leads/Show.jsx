import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';

export default function LeadShow({ lead }) {
    const noteForm = useForm({ admin_notes: lead.admin_notes ?? '' });
    const rejectForm = useForm({ reject_reason: '' });

    const submitNote = (e) => {
        e.preventDefault();
        noteForm.post(route('admin.leads.notes', lead.id));
    };

    const submitReject = (e) => {
        e.preventDefault();
        rejectForm.post(route('admin.leads.reject', lead.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Detail Lead: {lead.name ?? 'Tanpa Nama'}
                </h2>
            }
        >
            <Head title={`Lead - ${lead.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div className="rounded-lg bg-white p-6 shadow">
                            <h3 className="mb-4 font-medium text-gray-900">Informasi Dasar</h3>
                            <dl className="space-y-2 text-sm">
                                <div className="flex justify-between"><dt className="text-gray-500">Status</dt><dd className="font-medium">{lead.status}</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">Telegram</dt><dd>@{lead.telegram_username} ({lead.telegram_id})</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">Tujuan</dt><dd>{lead.join_purpose ?? '-'}</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">Pengalaman</dt><dd>{lead.trading_experience ?? '-'}</dd></div>
                            </dl>
                        </div>

                        <div className="rounded-lg bg-white p-6 shadow">
                            <h3 className="mb-4 font-medium text-gray-900">Data HFM</h3>
                            <dl className="space-y-2 text-sm">
                                <div className="flex justify-between"><dt className="text-gray-500">Wallet ID</dt><dd>{lead.wallet_id ?? '-'}</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">MT5 ID</dt><dd>{lead.mt5_id ?? '-'}</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">Deposit</dt><dd>${lead.hfm_deposit ?? '0'}</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">Equity</dt><dd>${lead.hfm_equity ?? '0'}</dd></div>
                                <div className="flex justify-between"><dt className="text-gray-500">IB Status</dt><dd>{lead.hfm_ib_status ?? '-'}</dd></div>
                            </dl>
                        </div>
                    </div>

                    {lead.status === 'VERIFIED' && (
                        <div className="flex gap-3 rounded-lg bg-white p-6 shadow">
                            <PrimaryButton onClick={() => router.post(route('admin.leads.approve', lead.id))}>
                                Approve Member
                            </PrimaryButton>
                        </div>
                    )}

                    <form onSubmit={submitNote} className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 font-medium text-gray-900">Catatan Admin</h3>
                        <textarea
                            className="w-full rounded-md border-gray-300 shadow-sm"
                            rows={3}
                            value={noteForm.data.admin_notes}
                            onChange={(e) => noteForm.setData('admin_notes', e.target.value)}
                        />
                        <PrimaryButton className="mt-3" disabled={noteForm.processing}>Simpan Catatan</PrimaryButton>
                    </form>

                    <form onSubmit={submitReject} className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 font-medium text-red-700">Tolak Member</h3>
                        <InputLabel value="Alasan Penolakan" />
                        <TextInput
                            className="mt-1 block w-full"
                            value={rejectForm.data.reject_reason}
                            onChange={(e) => rejectForm.setData('reject_reason', e.target.value)}
                        />
                        <PrimaryButton className="mt-3 bg-red-600 hover:bg-red-700" disabled={rejectForm.processing}>
                            Reject
                        </PrimaryButton>
                    </form>

                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 font-medium text-gray-900">Riwayat Aktivitas</h3>
                        <div className="space-y-3">
                            {lead.activities?.map((activity) => (
                                <div key={activity.id} className="border-l-4 border-indigo-400 pl-4 text-sm">
                                    <p className="font-medium">{activity.action}</p>
                                    <p className="text-gray-500">
                                        {activity.from_status} → {activity.to_status}
                                    </p>
                                    <p className="text-xs text-gray-400">
                                        {new Date(activity.created_at).toLocaleString('id-ID')}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
