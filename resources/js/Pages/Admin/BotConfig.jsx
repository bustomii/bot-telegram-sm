import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function BotConfig({ settings, webhookUrl }) {
    const { flash } = usePage().props;

    const { data, setData, post, processing, errors } = useForm({
        telegram_bot_token: settings.telegram_bot_token ?? '',
        telegram_webhook_secret: settings.telegram_webhook_secret ?? '',
        admin_group_chat_id: settings.admin_group_chat_id ?? '',
        community_link: settings.community_link ?? '',
        hfm_referral_link: settings.hfm_referral_link ?? '',
        hfm_api_url: settings.hfm_api_url ?? '',
        hfm_api_key: settings.hfm_api_key ?? '',
        hfm_ib_id: settings.hfm_ib_id ?? '',
        min_deposit: settings.min_deposit ?? 20,
        welcome_message: settings.welcome_message ?? '',
        is_active: settings.is_active ?? true,
        pdf_registration: null,
        pdf_ib_step1: null,
        pdf_ib_step2: null,
    });

    const hasPdfFiles = data.pdf_registration || data.pdf_ib_step1 || data.pdf_ib_step2;

    const saveSettings = (options = {}) => {
        post(route('admin.bot-config.update'), {
            ...(hasPdfFiles ? { forceFormData: true } : {}),
            preserveScroll: true,
            ...options,
        });
    };

    const submit = (e) => {
        e.preventDefault();
        saveSettings();
    };

    const setWebhook = () => {
        saveSettings({
            onSuccess: () => {
                post(route('admin.bot-config.webhook'), { preserveScroll: true });
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Konfigurasi Bot Telegram
                </h2>
            }
        >
            <Head title="Konfigurasi Bot" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-green-800">{flash.success}</div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-red-800">{flash.error}</div>
                    )}
                    {Object.keys(errors).length > 0 && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-red-800">
                            Gagal menyimpan konfigurasi. Periksa field yang ditandai merah di bawah.
                        </div>
                    )}

                    <form onSubmit={submit} className="space-y-6">
                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">Telegram Bot</h3>

                            <div className="space-y-4">
                                <div>
                                    <InputLabel value="Bot Token" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.telegram_bot_token}
                                        onChange={(e) => setData('telegram_bot_token', e.target.value)}
                                        placeholder="123456:ABC-DEF..."
                                    />
                                    <InputError message={errors.telegram_bot_token} />
                                </div>

                                <div>
                                    <InputLabel value="Webhook Secret" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.telegram_webhook_secret}
                                        onChange={(e) => setData('telegram_webhook_secret', e.target.value)}
                                    />
                                    <InputError message={errors.telegram_webhook_secret} />
                                </div>

                                <div>
                                    <InputLabel value="Admin Group Chat ID" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.admin_group_chat_id}
                                        onChange={(e) => setData('admin_group_chat_id', e.target.value)}
                                        placeholder="-1001234567890"
                                    />
                                    <InputError message={errors.admin_group_chat_id} />
                                </div>

                                <div>
                                    <InputLabel value="Link Komunitas" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.community_link}
                                        onChange={(e) => setData('community_link', e.target.value)}
                                        placeholder="https://t.me/..."
                                    />
                                    <InputError message={errors.community_link} />
                                </div>

                                <div className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        id="is_active"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="rounded border-gray-300 text-indigo-600"
                                    />
                                    <label htmlFor="is_active" className="text-sm text-gray-700">Bot Aktif</label>
                                </div>

                                <div className="rounded-md bg-gray-50 p-3 text-sm text-gray-600">
                                    <p className="font-medium text-gray-700">Webhook URL:</p>
                                    <p className="mt-1 break-all font-mono text-xs">{webhookUrl}</p>
                                    <p className="mt-2 text-xs">Pastikan URL di atas dapat diakses via HTTPS sebelum set webhook.</p>
                                </div>

                                <PrimaryButton type="button" onClick={setWebhook} disabled={processing}>
                                    Set Webhook Telegram
                                </PrimaryButton>
                            </div>
                        </div>

                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">Integrasi HFM API</h3>

                            <div className="space-y-4">
                                <div>
                                    <InputLabel value="HFM Referral Link" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.hfm_referral_link}
                                        onChange={(e) => setData('hfm_referral_link', e.target.value)}
                                    />
                                </div>

                                <div>
                                    <InputLabel value="HFM API URL" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.hfm_api_url}
                                        onChange={(e) => setData('hfm_api_url', e.target.value)}
                                    />
                                </div>

                                <div>
                                    <InputLabel value="HFM API Key" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.hfm_api_key}
                                        onChange={(e) => setData('hfm_api_key', e.target.value)}
                                    />
                                </div>

                                <div>
                                    <InputLabel value="HFM IB ID" />
                                    <TextInput
                                        className="mt-1 block w-full"
                                        value={data.hfm_ib_id}
                                        onChange={(e) => setData('hfm_ib_id', e.target.value)}
                                    />
                                </div>

                                <div>
                                    <InputLabel value="Minimum Deposit ($)" />
                                    <TextInput
                                        type="number"
                                        className="mt-1 block w-full"
                                        value={data.min_deposit}
                                        onChange={(e) => setData('min_deposit', e.target.value)}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 className="mb-4 text-lg font-medium text-gray-900">Pesan & Dokumen</h3>

                            <div className="space-y-4">
                                <div>
                                    <InputLabel value="Pesan Selamat Datang" />
                                    <textarea
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows={5}
                                        value={data.welcome_message}
                                        onChange={(e) => setData('welcome_message', e.target.value)}
                                    />
                                </div>

                                <div>
                                    <InputLabel value="PDF Registrasi HFM" />
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        className="mt-1 block w-full text-sm"
                                        onChange={(e) => setData('pdf_registration', e.target.files[0])}
                                    />
                                    {settings.pdf_registration && (
                                        <p className="mt-1 text-xs text-gray-500">File saat ini: {settings.pdf_registration}</p>
                                    )}
                                </div>

                                <div>
                                    <InputLabel value="PDF Ubah IB Step 1" />
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        className="mt-1 block w-full text-sm"
                                        onChange={(e) => setData('pdf_ib_step1', e.target.files[0])}
                                    />
                                    {settings.pdf_ib_step1 && (
                                        <p className="mt-1 text-xs text-gray-500">File saat ini: {settings.pdf_ib_step1}</p>
                                    )}
                                </div>

                                <div>
                                    <InputLabel value="PDF Ubah IB Step 2" />
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        className="mt-1 block w-full text-sm"
                                        onChange={(e) => setData('pdf_ib_step2', e.target.files[0])}
                                    />
                                    {settings.pdf_ib_step2 && (
                                        <p className="mt-1 text-xs text-gray-500">File saat ini: {settings.pdf_ib_step2}</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end">
                            <PrimaryButton disabled={processing}>Simpan Konfigurasi</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
