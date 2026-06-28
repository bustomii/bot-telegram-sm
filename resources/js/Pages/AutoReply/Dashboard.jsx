import ProviderBadge, { StatusBadge } from '@/Components/ProviderBadge';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Link2, MessageSquare, Plus, Zap } from 'lucide-react';

function StatCard({ label, value, icon: Icon, accent = 'indigo' }) {
    const accents = {
        indigo: 'from-indigo-500/20 to-indigo-500/5 text-indigo-300',
        emerald: 'from-emerald-500/20 to-emerald-500/5 text-emerald-300',
        cyan: 'from-cyan-500/20 to-cyan-500/5 text-cyan-300',
        amber: 'from-amber-500/20 to-amber-500/5 text-amber-300',
    };

    return (
        <div className={`rounded-2xl border border-white/10 bg-gradient-to-br ${accents[accent]} p-5`}>
            <div className="mb-3 flex items-center justify-between">
                <p className="text-sm text-slate-400">{label}</p>
                <Icon className="h-5 w-5 opacity-80" />
            </div>
            <p className="text-3xl font-bold text-white">{value}</p>
        </div>
    );
}

export default function Dashboard({ stats, accounts, providers }) {
    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard Auto Reply" />

            <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 className="text-2xl font-bold text-white">Ringkasan Auto Reply</h2>
                    <p className="mt-1 text-slate-400">
                        Pantau akun chat dan aturan balasan otomatis Anda.
                    </p>
                </div>
                <Link
                    href={route('auto-reply.accounts.index')}
                    className="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-400"
                >
                    <Plus className="h-4 w-4" />
                    Hubungkan Akun
                </Link>
            </div>

            <div className="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total Akun" value={stats.total_accounts} icon={Link2} />
                <StatCard label="Terhubung" value={stats.connected_accounts} icon={Zap} accent="emerald" />
                <StatCard label="Aturan Aktif" value={stats.active_rules} icon={MessageSquare} accent="cyan" />
                <StatCard label="Pesan Hari Ini" value={stats.messages_today} icon={MessageSquare} accent="amber" />
            </div>

            <div className="grid gap-6 xl:grid-cols-3">
                <div className="xl:col-span-2">
                    <div className="rounded-2xl border border-white/10 bg-white/5 p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold">Akun Terbaru</h3>
                            <Link
                                href={route('auto-reply.accounts.index')}
                                className="inline-flex items-center gap-1 text-sm text-indigo-300 hover:text-indigo-200"
                            >
                                Lihat semua
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>

                        {accounts.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-white/10 px-6 py-10 text-center">
                                <p className="text-slate-400">Belum ada akun chat terhubung.</p>
                                <Link
                                    href={route('auto-reply.accounts.index')}
                                    className="mt-4 inline-flex items-center gap-2 text-sm font-medium text-indigo-300"
                                >
                                    <Plus className="h-4 w-4" />
                                    Hubungkan Telegram Bot
                                </Link>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {accounts.slice(0, 5).map((account) => (
                                    <Link
                                        key={account.id}
                                        href={route('auto-reply.accounts.show', account.id)}
                                        className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-white/10 bg-slate-900/50 p-4 transition hover:border-indigo-400/30 hover:bg-slate-900"
                                    >
                                        <div>
                                            <p className="font-medium">{account.label}</p>
                                            <p className="mt-1 text-sm text-slate-400">
                                                {account.display_name}
                                                {account.username ? ` · @${account.username}` : ''}
                                            </p>
                                        </div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <ProviderBadge
                                                provider={account.provider}
                                                accountType={account.account_type}
                                            />
                                            <StatusBadge status={account.status} />
                                            <span className="text-xs text-slate-500">
                                                {account.auto_reply_rules_count} aturan
                                            </span>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                <div className="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h3 className="mb-4 text-lg font-semibold">Provider</h3>
                    <div className="space-y-3">
                        {providers.map((provider) => (
                            <div
                                key={provider.id}
                                className={`rounded-xl border p-4 ${
                                    provider.is_enabled
                                        ? 'border-white/10 bg-slate-900/40'
                                        : 'border-white/5 bg-slate-900/20 opacity-60'
                                }`}
                            >
                                <div className="flex items-center justify-between gap-2">
                                    <ProviderBadge provider={provider} />
                                    <span className="text-xs text-slate-500">
                                        {provider.is_enabled ? 'Aktif' : 'Segera hadir'}
                                    </span>
                                </div>
                                <p className="mt-2 text-xs text-slate-400">
                                    Tipe: {(provider.supported_account_types ?? []).join(', ')}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
