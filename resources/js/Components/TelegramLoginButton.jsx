import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

export default function TelegramLoginButton({ botUsername }) {
    const containerRef = useRef(null);

    useEffect(() => {
        if (!botUsername || !containerRef.current) {
            return;
        }

        window.onTelegramAuth = (user) => {
            router.post(route('auth.telegram.callback'), user);
        };

        containerRef.current.innerHTML = '';
        const script = document.createElement('script');
        script.src = 'https://telegram.org/js/telegram-widget.js?22';
        script.async = true;
        script.setAttribute('data-telegram-login', botUsername);
        script.setAttribute('data-size', 'large');
        script.setAttribute('data-radius', '12');
        script.setAttribute('data-onauth', 'onTelegramAuth(user)');
        script.setAttribute('data-request-access', 'write');
        containerRef.current.appendChild(script);

        return () => {
            delete window.onTelegramAuth;
        };
    }, [botUsername]);

    if (!botUsername) {
        return (
            <div className="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                Setel <code className="rounded bg-black/20 px-1">TELEGRAM_LOGIN_BOT_USERNAME</code> di
                environment untuk login Telegram.
            </div>
        );
    }

    return <div ref={containerRef} className="flex justify-center" />;
}
