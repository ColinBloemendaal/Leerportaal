import { ref } from 'vue';

export type CookieConsentChoice = 'essential' | 'all';

const COOKIE_NAME = 'cookie_consent';
const COOKIE_MAX_AGE_DAYS = 365;

function readCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

function writeCookie(name: string, value: string): void {
    const maxAge = COOKIE_MAX_AGE_DAYS * 24 * 60 * 60;

    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}; samesite=lax`;
}

const choice = ref<CookieConsentChoice | null>(readStoredChoice());

function readStoredChoice(): CookieConsentChoice | null {
    const raw = readCookie(COOKIE_NAME);

    return raw === 'essential' || raw === 'all' ? raw : null;
}

/**
 * CLAUDE.md §8 (GDPR): "Cookie consent (functional-only default, no
 * non-essential without consent)." Nothing in this codebase sets a
 * non-essential cookie or loads a non-essential script today (no
 * analytics, no marketing pixels) -- the session/CSRF/tenant cookies
 * auth and multi-tenancy depend on are strictly necessary and are never
 * gated by this. This composable is the enforcement point for whenever
 * that changes: hasConsent('analytics') must be checked before any
 * future non-essential script is injected, and it's false until the
 * visitor explicitly opts in via the banner -- "functional-only" is the
 * default for every visitor who hasn't answered yet, not just an
 * initial UI state.
 *
 * Deliberately a plain browser cookie, not a user preference column:
 * this has to work for anonymous, pre-login visitors too, and the
 * choice needs to survive across the custom-domain/tenant-cookie/
 * unbranded-fallback resolution paths the same way any other cookie
 * does.
 */
export function useCookieConsent() {
    function hasAnswered(): boolean {
        return choice.value !== null;
    }

    function hasConsent(category: 'essential' | 'analytics'): boolean {
        if (category === 'essential') {
            return true;
        }

        return choice.value === 'all';
    }

    function acceptAll(): void {
        choice.value = 'all';
        writeCookie(COOKIE_NAME, 'all');
    }

    function acceptEssentialOnly(): void {
        choice.value = 'essential';
        writeCookie(COOKIE_NAME, 'essential');
    }

    return {
        choice,
        hasAnswered,
        hasConsent,
        acceptAll,
        acceptEssentialOnly,
    };
}
