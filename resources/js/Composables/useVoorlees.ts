import { computed, ref } from 'vue';

export type VoorleesStatus = 'idle' | 'playing' | 'paused';

/**
 * Course content stores short locale codes (available_locales: ["nl", "en"]),
 * but SpeechSynthesisUtterance.lang wants a full BCP-47 tag for the best
 * voice match. Falls back to the bare code for anything not listed --
 * most browsers still resolve that to a reasonable voice.
 */
const BCP47_BY_LOCALE: Record<string, string> = {
    nl: 'nl-NL',
    en: 'en-US',
    de: 'de-DE',
    fr: 'fr-FR',
};

export function localeToBcp47(locale: string): string {
    return BCP47_BY_LOCALE[locale] ?? locale;
}

/**
 * "Voorlees" (read-aloud) via the browser's own SpeechSynthesis API --
 * no server-side TTS in v1, per CLAUDE.md §1. One instance of this
 * composable can read one thing at a time (a block, a question); the
 * caller is responsible for supplying the right text and a BCP-47
 * locale (e.g. "nl-NL", not just "nl") matching the content being
 * read, since only the caller knows which block/question it's reading
 * and in what locale that content is stored.
 */
export function useVoorlees() {
    const status = ref<VoorleesStatus>('idle');
    const speed = ref(1);
    const supported = typeof window !== 'undefined' && 'speechSynthesis' in window;

    function play(text: string, locale: string): void {
        if (!supported || text.trim() === '') return;

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = locale;
        utterance.rate = speed.value;
        utterance.onend = () => {
            status.value = 'idle';
        };
        utterance.onerror = () => {
            status.value = 'idle';
        };

        window.speechSynthesis.speak(utterance);
        status.value = 'playing';
    }

    function pause(): void {
        if (!supported || status.value !== 'playing') return;

        window.speechSynthesis.pause();
        status.value = 'paused';
    }

    function resume(): void {
        if (!supported || status.value !== 'paused') return;

        window.speechSynthesis.resume();
        status.value = 'playing';
    }

    function stop(): void {
        if (!supported) return;

        window.speechSynthesis.cancel();
        status.value = 'idle';
    }

    /**
     * Takes effect on the next play() call -- the Web Speech API doesn't
     * support changing an in-progress utterance's rate, and restarting
     * automatically would jump the reading position unexpectedly.
     */
    function setSpeed(rate: number): void {
        speed.value = rate;
    }

    return {
        status: computed(() => status.value),
        speed: computed(() => speed.value),
        supported,
        play,
        pause,
        resume,
        stop,
        setSpeed,
    };
}
