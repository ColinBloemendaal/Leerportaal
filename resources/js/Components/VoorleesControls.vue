<script setup lang="ts">
import { localeToBcp47, useVoorlees } from '@/Composables/useVoorlees';
import { useI18n } from 'vue-i18n';

/**
 * Drop this next to any block or question's rendered text to give it a
 * play/pause/stop + speed "read aloud" control. No consuming lesson/quiz
 * player page exists yet (same recurring situation as the rest of this
 * phase's block/question player components), so this is a standalone,
 * reusable component for when one does.
 */
const props = defineProps<{
    text: string;
    /** Short content locale code, e.g. "nl" -- not a full BCP-47 tag. */
    locale: string;
}>();

const { status, speed, supported, play, pause, resume, stop, setSpeed } = useVoorlees();
const { t } = useI18n();

function toggle(): void {
    if (status.value === 'playing') {
        pause();
    } else if (status.value === 'paused') {
        resume();
    } else {
        play(props.text, localeToBcp47(props.locale));
    }
}

function onSpeedChange(event: Event): void {
    setSpeed(Number((event.target as HTMLSelectElement).value));
}
</script>

<template>
    <div v-if="supported" class="d-flex align-items-center gap-2">
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            :aria-label="
                status === 'playing' ? t('components.voorleesControls.pause') : t('components.voorleesControls.play')
            "
            @click="toggle"
        >
            {{ status === 'playing' ? '⏸' : '▶' }}
        </button>
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            :disabled="status === 'idle'"
            :aria-label="t('components.voorleesControls.stop')"
            @click="stop"
        >
            ⏹
        </button>
        <select
            class="form-select form-select-sm w-auto"
            :aria-label="t('components.voorleesControls.speed')"
            :value="speed"
            @change="onSpeedChange"
        >
            <option :value="0.75">0.75x</option>
            <option :value="1">1x</option>
            <option :value="1.25">1.25x</option>
            <option :value="1.5">1.5x</option>
        </select>
    </div>
</template>
