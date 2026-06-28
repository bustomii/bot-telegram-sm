import ProviderBadge, { StatusBadge } from '@/Components/ProviderBadge';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Copy, Pencil, Plus, RefreshCw, Trash2 } from 'lucide-react';
import { useState } from 'react';

const triggerLabels = {
    exact: 'Exact match',
    contains: 'Mengandung kata',
    starts_with: 'Dimulai dengan',
    regex: 'Regex',
    default: 'Default (fallback)',
};

function RuleForm({ accountId, triggerTypes, initialData = null, onCancel }) {
    const isEdit = !!initialData;

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: initialData?.name ?? '',
        trigger_type: initialData?.trigger_type ?? 'contains',
        trigger_pattern: initialData?.trigger_pattern ?? '',
        response_message: initialData?.response_message ?? '',
        is_active: initialData?.is_active ?? true,
        match_case_sensitive: initialData?.match_case_sensitive ?? false,
        priority: initialData?.priority ?? 10,
    });

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('auto-reply.rules.update', [accountId, initialData.id]), {
                onSuccess: () => onCancel?.(),
            });
            return;
        }

        post(route('auto-reply.rules.store', accountId), {
            onSuccess: () => {
                reset();
                onCancel?.();
            },
        });
    };

    return (
        <form onSubmit={submit} className="rounded-2xl border border-indigo-400/20 bg-indigo-500/5 p-5">
            <h4 className="font-semibold">{isEdit ? 'Edit Aturan' : 'Aturan Baru'}</h4>
            <div className="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label className="text-sm text-slate-300">Nama aturan</label>
                    <input
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"
                    />
                    {errors.name && <p className="mt-1 text-sm text-rose-400">{errors.name}</p>}
                </div>
                <div>
                    <label className="text-sm text-slate-300">Prioritas</label>
                    <input
                        type="number"
                        min="0"
                        max="9999"
                        value={data.priority}
                        onChange={(e) => setData('priority', Number(e.target.value))}
                        className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"
                    />
                </div>
                <div>
                    <label className="text-sm text-slate-300">Trigger</label>
                    <select
                        value={data.trigger_type}
                        onChange={(e) => setData('trigger_type', e.target.value)}
                        className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"
                    >
                        {Object.entries(triggerTypes).map(([value, label]) => (
                            <option key={value} value={value}>
                                {label}
                            </option>
                        ))}
                    </select>
                </div>
                {data.trigger_type !== 'default' && (
                    <div>
                        <label className="text-sm text-slate-300">Pola / Kata kunci</label>
                        <input
                            value={data.trigger_pattern}
                            onChange={(e) => setData('trigger_pattern', e.target.value)}
                            placeholder="halo, /start, ^help"
                            className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"
                        />
                        {errors.trigger_pattern && (
                            <p className="mt-1 text-sm text-rose-400">{errors.trigger_pattern}</p>
                        )}
                    </div>
                )}
            </div>

            <div className="mt-4">
                <label className="text-sm text-slate-300">Balasan otomatis</label>
                <textarea
                    rows={4}
                    value={data.response_message}
                    onChange={(e) => setData('response_message', e.target.value)}
                    className="mt-1 w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white"
                    placeholder="Halo! Terima kasih sudah menghubungi kami..."
                />
                {errors.response_message && (
                    <p className="mt-1 text-sm text-rose-400">{errors.response_message}</p>
                )}
            </div>

            <div className="mt-4 flex flex-wrap gap-4 text-sm text-slate-300">
                <label className="flex items-center gap-2">
                    <input
                        type="checkbox"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                    />
                    Aktif
                </label>
                <label className="flex items-center gap-2">
                    <input
                        type="checkbox"
                        checked={data.match_case_sensitive}
                        onChange={(e) => setData('match_case_sensitive', e.target.checked)}
                    />
                    Case sensitive
                </label>
            </div>

            <div className="mt-5 flex justify-end gap-2">
                {onCancel && (
                    <button
                        type="button"
                        onClick={onCancel}
                        className="rounded-xl border border-white/10 px-4 py-2 text-sm"
                    >
                        Batal
                    </button>
                )}
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                >
                    {processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Tambah Aturan'}
                </button>
            </div>
        </form>
    );
}

export default function AccountShow({ account, triggerTypes, recentLogs }) {
    const [showForm, setShowForm] = useState(false);
    const [editingRule, setEditingRule] = useState(null);

    const copyWebhook = async () => {
        await navigator.clipboard.writeText(account.webhook_url);
    };

    return (
        <AppLayout title={account.label}>
            <Head title={`${account.label} · Auto Reply`} />

            <Link
                href={route('auto-reply.accounts.index')}
                className="mb-6 inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white"
            >
                <ArrowLeft className="h-4 w-4" />
                Kembali ke daftar akun
            </Link>

            <div className="mb-8 rounded-2xl border border-white/10 bg-white/5 p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-2xl font-bold">{account.label}</h2>
                            <StatusBadge status={account.status} />
                        </div>
                        <p className="mt-2 text-slate-400">
                            {account.display_name}
                            {account.username ? ` · @${account.username}` : ''}
                        </p>
                        <div className="mt-3">
                            <ProviderBadge provider={account.provider} accountType={account.account_type} />
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link
                            href={route('auto-reply.accounts.reconnect', account.id)}
                            method="post"
                            as="button"
                            className="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 text-sm"
                        >
                            <RefreshCw className="h-4 w-4" />
                            Sync Webhook
                        </Link>
                    </div>
                </div>

                <div className="mt-5 rounded-xl border border-white/10 bg-slate-950/60 p-4">
                    <p className="text-xs uppercase tracking-wide text-slate-500">Webhook URL</p>
                    <div className="mt-2 flex flex-wrap items-center gap-2">
                        <code className="flex-1 break-all text-sm text-cyan-300">{account.webhook_url}</code>
                        <button
                            type="button"
                            onClick={copyWebhook}
                            className="inline-flex items-center gap-1 rounded-lg border border-white/10 px-3 py-1.5 text-xs"
                        >
                            <Copy className="h-3.5 w-3.5" />
                            Salin
                        </button>
                    </div>
                </div>
            </div>

            <div className="grid gap-6 xl:grid-cols-3">
                <div className="space-y-4 xl:col-span-2">
                    <div className="flex items-center justify-between">
                        <h3 className="text-lg font-semibold">Aturan Auto Reply</h3>
                        {!showForm && !editingRule && (
                            <button
                                type="button"
                                onClick={() => setShowForm(true)}
                                className="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold"
                            >
                                <Plus className="h-4 w-4" />
                                Tambah
                            </button>
                        )}
                    </div>

                    {showForm && (
                        <RuleForm
                            accountId={account.id}
                            triggerTypes={triggerTypes}
                            onCancel={() => setShowForm(false)}
                        />
                    )}

                    {editingRule && (
                        <RuleForm
                            accountId={account.id}
                            triggerTypes={triggerTypes}
                            initialData={editingRule}
                            onCancel={() => setEditingRule(null)}
                        />
                    )}

                    <div className="space-y-3">
                        {(account.auto_reply_rules ?? []).length === 0 ? (
                            <div className="rounded-xl border border-dashed border-white/10 px-6 py-10 text-center text-slate-400">
                                Belum ada aturan. Tambahkan aturan pertama untuk mulai auto reply.
                            </div>
                        ) : (
                            account.auto_reply_rules.map((rule) => (
                                <div
                                    key={rule.id}
                                    className="rounded-2xl border border-white/10 bg-slate-900/50 p-5"
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h4 className="font-semibold">{rule.name}</h4>
                                                <span
                                                    className={`rounded-full px-2 py-0.5 text-xs ${
                                                        rule.is_active
                                                            ? 'bg-emerald-500/15 text-emerald-300'
                                                            : 'bg-slate-500/15 text-slate-400'
                                                    }`}
                                                >
                                                    {rule.is_active ? 'Aktif' : 'Nonaktif'}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-sm text-slate-400">
                                                {triggerLabels[rule.trigger_type] ?? rule.trigger_type}
                                                {rule.trigger_pattern ? `: "${rule.trigger_pattern}"` : ''}
                                                {' · '}Prioritas {rule.priority}
                                            </p>
                                        </div>
                                        <div className="flex gap-2">
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setShowForm(false);
                                                    setEditingRule(rule);
                                                }}
                                                className="rounded-lg border border-white/10 p-2 text-slate-300"
                                            >
                                                <Pencil className="h-4 w-4" />
                                            </button>
                                            <Link
                                                href={route('auto-reply.rules.destroy', [account.id, rule.id])}
                                                method="delete"
                                                as="button"
                                                className="rounded-lg border border-rose-500/30 p-2 text-rose-300"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Link>
                                        </div>
                                    </div>
                                    <div className="mt-4 rounded-xl bg-black/20 p-4 text-sm text-slate-300 whitespace-pre-wrap">
                                        {rule.response_message}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>

                <div>
                    <h3 className="mb-4 text-lg font-semibold">Log Pesan Terbaru</h3>
                    <div className="space-y-2">
                        {recentLogs.length === 0 ? (
                            <p className="text-sm text-slate-500">Belum ada aktivitas.</p>
                        ) : (
                            recentLogs.map((log) => (
                                <div
                                    key={log.id}
                                    className="rounded-xl border border-white/10 bg-slate-900/40 p-3 text-sm"
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <span
                                            className={
                                                log.direction === 'in'
                                                    ? 'text-cyan-300'
                                                    : 'text-emerald-300'
                                            }
                                        >
                                            {log.direction === 'in' ? 'Masuk' : 'Keluar'}
                                        </span>
                                        <span className="text-xs text-slate-500">
                                            {new Date(log.created_at).toLocaleString('id-ID')}
                                        </span>
                                    </div>
                                    <p className="mt-2 line-clamp-3 text-slate-300">{log.content}</p>
                                    {log.rule && (
                                        <p className="mt-1 text-xs text-indigo-300">Rule: {log.rule.name}</p>
                                    )}
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
