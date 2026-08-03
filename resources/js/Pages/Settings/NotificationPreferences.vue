<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

interface PreferenceRow {
    type: string;
    label: string;
    channels: Record<string, boolean>;
}

interface ChannelOption {
    value: string;
    label: string;
}

interface DigestFrequencyOption {
    value: string;
    label: string;
}

defineProps<{
    preferences: PreferenceRow[];
    channels: ChannelOption[];
    digestFrequency: string;
    digestFrequencyOptions: DigestFrequencyOption[];
}>();

const { t } = useI18n();

function toggle(row: PreferenceRow, channel: ChannelOption, event: Event) {
    const enabled = (event.target as HTMLInputElement).checked;

    router.put(
        '/settings/notifications',
        { type: row.type, channel: channel.value, enabled },
        { preserveScroll: true, preserveState: true },
    );
}

function changeDigestFrequency(event: Event) {
    const frequency = (event.target as HTMLSelectElement).value;

    router.put(
        '/settings/notifications/digest-frequency',
        { frequency },
        { preserveScroll: true, preserveState: true },
    );
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('settings.notificationPreferences.title') }}</h1>

    <div class="mb-4" style="max-width: 20rem">
        <label for="digest-frequency" class="form-label">{{
            t('settings.notificationPreferences.emailFrequency')
        }}</label>
        <select id="digest-frequency" class="form-select" :value="digestFrequency" @change="changeDigestFrequency">
            <option v-for="option in digestFrequencyOptions" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <div class="form-text">{{ t('settings.notificationPreferences.emailFrequencyHelp') }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('settings.notificationPreferences.notification') }}</th>
                <th v-for="channel in channels" :key="channel.value" scope="col">{{ channel.label }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in preferences" :key="row.type">
                <td>{{ row.label }}</td>
                <td v-for="channel in channels" :key="channel.value">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        :checked="row.channels[channel.value]"
                        :aria-label="`${channel.label} for ${row.label}`"
                        @change="toggle(row, channel, $event)"
                    />
                </td>
            </tr>
        </tbody>
    </table>
</template>
