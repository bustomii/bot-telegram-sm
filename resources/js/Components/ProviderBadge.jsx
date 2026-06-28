export default function ProviderBadge({ provider, accountType }) {
    if (!provider) {
        return null;
    }

    return (
        <span
            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-white/10"
            style={{ backgroundColor: `${provider.color}22`, color: provider.color }}
        >
            <span
                className="h-2 w-2 rounded-full"
                style={{ backgroundColor: provider.color }}
            />
            {provider.name}
            {accountType ? <span className="opacity-70">· {accountType}</span> : null}
        </span>
    );
}

export function StatusBadge({ status }) {
    const styles = {
        connected: 'bg-emerald-500/15 text-emerald-300 ring-emerald-500/30',
        disconnected: 'bg-slate-500/15 text-slate-300 ring-slate-500/30',
        error: 'bg-rose-500/15 text-rose-300 ring-rose-500/30',
    };

    const labels = {
        connected: 'Terhubung',
        disconnected: 'Terputus',
        error: 'Error',
    };

    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${styles[status] ?? styles.disconnected}`}>
            {labels[status] ?? status}
        </span>
    );
}
