<script setup lang="ts">
import type { FileUploadPayload } from '@/types/questions';

defineProps<{ payload: FileUploadPayload; questionId: string | number }>();

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
            :aria-label="`File upload for question ${questionId}`"
            @change="onChange"
        />
        <div v-if="payload.max_size_bytes" class="form-text">
            Maximum size: {{ Math.round(payload.max_size_bytes / 1024 / 1024) }} MB.
        </div>
    </div>
</template>
