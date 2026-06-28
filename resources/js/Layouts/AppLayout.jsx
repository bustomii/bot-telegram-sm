import { Link, usePage } from '@inertiajs/react';
import {
    Bot,
    LayoutDashboard,
    LogOut,
    Menu,
    Settings,
    Users,
    X,
} from 'lucide-react';
import { useState } from 'react';

const navigation = [
    { name: 'Leads HFM', href: 'admin.leads.index', icon: Users },
    { name: 'Bot HFM', href: 'admin.bot-config.edit', icon: Settings },
];

export default function AppLayout({ title, children }) {
    const user = usePage().props.auth.user;
    const flash = usePage().props.flash;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100">
            <div className="pointer-events-none fixed inset-0 overflow-hidden">
                <div className="absolute -left-24 top-0 h-96 w-96 rounded-full bg-indigo-600/20 blur-3xl" />
                <div className="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl" />
            </div>

            <div className="relative lg:flex">
                <aside
                    className={`fixed inset-y-0 left-0 z-40 w-72 transform border-r border-white/10 bg-slate-950/95 backdrop-blur-xl transition lg:static lg:translate-x-0 ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <div className="flex h-16 items-center gap-3 border-b border-white/10 px-6">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-400">
                            <Bot className="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <p className="text-sm font-semibold">HFM Bot Admin</p>
                            <p className="text-xs text-slate-400">Legacy HFM management</p>
                        </div>
                        <button
                            type="button"
                            className="ml-auto rounded-lg p-2 text-slate-400 hover:bg-white/5 lg:hidden"
                            onClick={() => setSidebarOpen(false)}
                        >
                            <X className="h-5 w-5" />
                        </button>
                    </div>

                    <nav className="space-y-6 p-4">
                        <div>
                            <p className="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                HFM
                            </p>
                            <div className="space-y-1">
                                {navigation.map((item) => {
                                    const Icon = item.icon;
                                    const active = route().current(item.href) || route().current(`${item.href.split('.')[0]}.*`);

                                    return (
                                        <Link
                                            key={item.name}
                                            href={route(item.href)}
                                            className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ${
                                                active
                                                    ? 'bg-indigo-500/20 text-indigo-200 ring-1 ring-indigo-400/30'
                                                    : 'text-slate-300 hover:bg-white/5 hover:text-white'
                                            }`}
                                        >
                                            <Icon className="h-4 w-4" />
                                            {item.name}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    </nav>

                    <div className="absolute bottom-0 left-0 right-0 border-t border-white/10 p-4">
                        <div className="mb-3 flex items-center gap-3 rounded-xl bg-white/5 p-3">
                            {user.telegram_photo_url ? (
                                <img
                                    src={user.telegram_photo_url}
                                    alt=""
                                    className="h-10 w-10 rounded-full object-cover"
                                />
                            ) : (
                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/30 text-sm font-semibold">
                                    {user.name?.charAt(0) ?? '?'}
                                </div>
                            )}
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium">{user.name}</p>
                                <p className="truncate text-xs text-slate-400">
                                    {user.telegram_username ? `@${user.telegram_username}` : user.email}
                                </p>
                            </div>
                        </div>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 px-4 py-2.5 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"
                        >
                            <LogOut className="h-4 w-4" />
                            Keluar
                        </Link>
                    </div>
                </aside>

                <div className="min-h-screen flex-1 lg:min-w-0">
                    <header className="sticky top-0 z-30 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
                        <div className="flex h-16 items-center gap-4 px-4 sm:px-8">
                            <button
                                type="button"
                                className="rounded-lg p-2 text-slate-400 hover:bg-white/5 lg:hidden"
                                onClick={() => setSidebarOpen(true)}
                            >
                                <Menu className="h-5 w-5" />
                            </button>
                            <div className="flex items-center gap-2">
                                <LayoutDashboard className="h-5 w-5 text-indigo-400" />
                                <h1 className="text-lg font-semibold">{title}</h1>
                            </div>
                        </div>
                    </header>

                    <main className="px-4 py-6 sm:px-8 sm:py-8">
                        {(flash?.success || flash?.error) && (
                            <div
                                className={`mb-6 rounded-xl border px-4 py-3 text-sm ${
                                    flash.error
                                        ? 'border-rose-500/30 bg-rose-500/10 text-rose-200'
                                        : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200'
                                }`}
                            >
                                {flash.success || flash.error}
                            </div>
                        )}
                        {children}
                    </main>
                </div>
            </div>
        </div>
    );
}
