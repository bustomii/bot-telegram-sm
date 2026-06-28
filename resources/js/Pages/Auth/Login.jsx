import TelegramLoginButton from '@/Components/TelegramLoginButton';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { Bot, Mail, Sparkles } from 'lucide-react';

export default function Login({ status, canResetPassword, telegramBotUsername }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="min-h-screen bg-slate-950 text-white">
            <Head title="Masuk" />
            <div className="pointer-events-none fixed inset-0 overflow-hidden">
                <div className="absolute left-1/4 top-0 h-[28rem] w-[28rem] rounded-full bg-indigo-600/20 blur-3xl" />
                <div className="absolute bottom-0 right-1/4 h-[24rem] w-[24rem] rounded-full bg-cyan-500/10 blur-3xl" />
            </div>

            <div className="relative mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10">
                <div className="grid w-full gap-10 lg:grid-cols-2 lg:items-center">
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-4 py-1.5 text-sm text-indigo-200">
                            <Sparkles className="h-4 w-4" />
                            Multi-provider auto reply platform
                        </div>
                        <div className="flex items-center gap-4">
                            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400">
                                <Bot className="h-7 w-7 text-white" />
                            </div>
                            <div>
                                <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                    Auto Reply Hub
                                </h1>
                                <p className="mt-1 text-slate-400">
                                    Kelola banyak akun chat & balasan otomatis dari satu dashboard.
                                </p>
                            </div>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-3">
                            {[
                                { name: 'Telegram', color: '#229ED9', ready: true },
                                { name: 'Discord', color: '#5865F2', ready: false },
                                { name: 'WhatsApp', color: '#25D366', ready: false },
                            ].map((provider) => (
                                <div
                                    key={provider.name}
                                    className="rounded-2xl border border-white/10 bg-white/5 p-4"
                                >
                                    <div
                                        className="mb-2 h-2 w-2 rounded-full"
                                        style={{ backgroundColor: provider.color }}
                                    />
                                    <p className="font-medium">{provider.name}</p>
                                    <p className="text-xs text-slate-400">
                                        {provider.ready ? 'Siap pakai' : 'Segera hadir'}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-slate-900/80 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                        <div className="mb-6 text-center">
                            <h2 className="text-xl font-semibold">Masuk ke dashboard</h2>
                            <p className="mt-1 text-sm text-slate-400">
                                Gunakan akun Telegram biasa atau email admin.
                            </p>
                        </div>

                        {status && (
                            <div className="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                                {status}
                            </div>
                        )}

                        <div className="space-y-4">
                            <TelegramLoginButton botUsername={telegramBotUsername} />
                            <InputError message={errors.telegram} className="text-center" />

                            <div className="relative py-2">
                                <div className="absolute inset-0 flex items-center">
                                    <div className="w-full border-t border-white/10" />
                                </div>
                                <div className="relative flex justify-center text-xs uppercase">
                                    <span className="bg-slate-900 px-3 text-slate-500">atau email</span>
                                </div>
                            </div>

                            <form onSubmit={submit} className="space-y-4">
                                <div>
                                    <InputLabel htmlFor="email" value="Email" className="text-slate-300" />
                                    <div className="relative mt-1">
                                        <Mail className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            name="email"
                                            value={data.email}
                                            className="block w-full border-white/10 bg-white/5 pl-10 text-white focus:border-indigo-400 focus:ring-indigo-400"
                                            autoComplete="username"
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                    </div>
                                    <InputError message={errors.email} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="password" value="Password" className="text-slate-300" />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        name="password"
                                        value={data.password}
                                        className="mt-1 block w-full border-white/10 bg-white/5 text-white focus:border-indigo-400 focus:ring-indigo-400"
                                        autoComplete="current-password"
                                        onChange={(e) => setData('password', e.target.value)}
                                    />
                                    <InputError message={errors.password} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-between">
                                    <label className="flex items-center">
                                        <Checkbox
                                            name="remember"
                                            checked={data.remember}
                                            onChange={(e) => setData('remember', e.target.checked)}
                                        />
                                        <span className="ms-2 text-sm text-slate-400">Ingat saya</span>
                                    </label>
                                    {canResetPassword && (
                                        <Link
                                            href={route('password.request')}
                                            className="text-sm text-indigo-300 hover:text-indigo-200"
                                        >
                                            Lupa password?
                                        </Link>
                                    )}
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-xl bg-gradient-to-r from-indigo-500 to-cyan-500 px-4 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                                >
                                    Masuk dengan Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
