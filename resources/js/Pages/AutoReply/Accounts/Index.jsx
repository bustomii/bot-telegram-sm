import ProviderBadge, { StatusBadge } from '@/Components/ProviderBadge';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Link2, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

function ConnectAccountModal({ providers, onClose }) {
    const enabledProviders = providers.filter((p) => p.is_enabled);
    const [selectedProvider, setSelectedProvider] = useState(enabledProviders[0]?.key ?? 'telegram');

    const { data, setData, post, processing, errors, reset } = useForm({
        provider_key: selectedProvider,
        account_type: 'bot',
        label: '',
        bot_token: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('auto-reply.accounts.store'), {
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div className="w-full max-w-lg rounded-2xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
                <h3 className="text-lg font-semibold">Hubungkan Akun Chat</h3>
                <p className="mt-1 text-sm text-slate-400">
                    Tambahkan bot Telegram dari @BotFather untuk auto reply.
                </p>

                <form onSubmit={submit} className="mt-6 space-y-4">
                    <div>
                        <label className="text-sm text-slate-300">Provider</label>
                        <select
                            value={data.provider_key}
                            onChange={(e) => {
                                setSelectedProvider(e.target.value);
                                setData('provider_key', e.target.value);
                            }}
                            className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-white"
                        >
                            {providers.map((provider) => (
                                <option
                                    key={provider.key}
                                    value={provider.key}
                                    disabled={!provider.is_enabled}
                                >
                                    {provider.name} {provider.is_enabled ? '' : '(segera hadir)'}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="text-sm text-slate-300">Label akun</label>
                        <input
                            value={data.label}
                            onChange={(e) => setData('label', e.target.value)}
                            placeholder="Contoh: Bot CS Toko"
                            className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-white"
                        />
                        {errors.label && <p className="mt-1 text-sm text-rose-400">{errors.label}</p>}
                    </div>

                    <div>
                        <label className="text-sm text-slate-300">Bot Token</label>
                        <input
                            type="password"
                            value={data.bot_token}
                            onChange={(e) => setData('bot_token', e.target.value)}
                            placeholder="123456:ABC-DEF..."
                            className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-white"
                        />
                        {errors.bot_token && <p className="mt-1 text-sm text-rose-400">{errors.bot_token}</p>}
                    </div>

                    <div className="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            onClick={onClose}
                            className="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            {processing ? 'Menghubungkan...' : 'Hubungkan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default function AccountsIndex({ accounts, providers }) {
    const [showModal, setShowModal] = useState(false);

    return (
        <AppLayout title="Akun Chat">
            <Head title="Akun Chat" />

            <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 className="text-2xl font-bold">Akun Chat</h2>
                    <p className="mt-1 text-slate-400">
                        Kelola banyak akun dari berbagai provider dalam satu tempat.
                    </p>
                </div>
                <button
                    type="button"
                    onClick={() => setShowModal(true)}
                    className="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white"
                >
                    <Plus className="h-4 w-4" />
                    Tambah Akun
                </button>
            </div>

            <div className="mb-8 grid gap-4 md:grid-cols-3">
                {providers.map((provider) => (
                    <div
                        key={provider.id}
                        className="rounded-2xl border border-white/10 bg-white/5 p-5"
                    >
                        <ProviderBadge provider={provider} />
                        <p className="mt-3 text-sm text-slate-400">
                            {provider.is_enabled
                                ? 'Siap dihubungkan'
                                : 'Provider ini akan segera tersedia.'}
                        </p>
                    </div>
                ))}
            </div>

            {accounts.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-white/10 px-6 py-16 text-center">
                    <Link2 className="mx-auto h-10 w-10 text-slate-500" />
                    <p className="mt-4 text-slate-400">Belum ada akun terhubung.</p>
                </div>
            ) : (
                <div className="grid gap-4 lg:grid-cols-2">
                    {accounts.map((account) => (
                        <div
                            key={account.id}
                            className="rounded-2xl border border-white/10 bg-slate-900/50 p-5"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 className="text-lg font-semibold">{account.label}</h3>
                                    <p className="mt-1 text-sm text-slate-400">
                                        {account.display_name}
                                        {account.username ? ` · @${account.username}` : ''}
                                    </p>
                                </div>
                                <StatusBadge status={account.status} />
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2">
                                <ProviderBadge provider={account.provider} accountType={account.account_type} />
                                <span className="rounded-full bg-white/5 px-2.5 py-1 text-xs text-slate-400">
                                    {account.auto_reply_rules_count} aturan
                                </span>
                                <span className="rounded-full bg-white/5 px-2.5 py-1 text-xs text-slate-400">
                                    {account.message_logs_count} log
                                </span>
                            </div>

                            {account.status_message && (
                                <p className="mt-3 text-sm text-rose-300">{account.status_message}</p>
                            )}

                            <div className="mt-5 flex flex-wrap gap-2">
                                <Link
                                    href={route('auto-reply.accounts.show', account.id)}
                                    className="inline-flex items-center gap-2 rounded-xl bg-indigo-500/20 px-4 py-2 text-sm font-medium text-indigo-200 ring-1 ring-indigo-400/30"
                                >
                                    Kelola Aturan
                                    <ArrowRight className="h-4 w-4" />
                                </Link>
                                <Link
                                    href={route('auto-reply.accounts.destroy', account.id)}
                                    method="delete"
                                    as="button"
                                    className="inline-flex items-center gap-2 rounded-xl border border-rose-500/30 px-4 py-2 text-sm text-rose-300"
                                >
                                    <Trash2 className="h-4 w-4" />
                                    Hapus
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {showModal && (
                <ConnectAccountModal providers={providers} onClose={() => setShowModal(false)} />
            )}
        </AppLayout>
    );
}
