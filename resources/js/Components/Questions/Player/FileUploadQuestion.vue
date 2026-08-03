<script setup lang="ts">
import type { FileUploadPayload } from '@/types/questions';
import { useI18n } from 'vue-i18n';

defineProps<{ payload: FileUploadPayload; questionId: string | number }>();
const { t } = useI18n();

// Just the file picker -- actually persisting the upload (to Media, tied
// to a quiz attempt) is the quiz_attempts/question_answers task's job,
// which doesn't exist yet.
const file = defineModel<File | null>({ default: null });

function onChange(event: Event): void {
    file.value = (event.target as HTMLInputElement).files?.[0] ?? null;
}
</script>

<template>
    <div>
        <input
            :id="`question-${questionId}-file`"
            type="file"
            class="form-control"
            :accept="payload.allowed_mime_types.join(',')"
            :aria-label="t('questions.player.fileUpload.uploadAria', { id: questionId })"
            @change="onChange"
        />
        <div v-if="payload.max_size_bytes" class="form-text">
            {{ t('questions.player.fileUpload.maxSize', { size: Math.round(payload.max_size_bytes / 1024 / 1024) }) }}
        </div>
    </div>
</template>
