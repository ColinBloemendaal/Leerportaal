<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const impersonation = computed(() => page.props.impersonation);

function stop() {
    router.delete('/impersonate');
}
</script>

<template>
    <div
        v-if="impersonation"
        class="alert alert-warning rounded-0 mb-0 d-flex justify-content-between align-items-center"
    >
        <span>
            <strong>{{ impersonation.impersonatorName }}</strong> is impersonating
            <strong>{{ impersonation.targetName }}</strong
            >.
        </span>
        <button type="button" class="btn btn-sm btn-outline-dark" @click="stop">Stop impersonating</button>
    </div>
</template>
