<script setup lang="ts">
import ImpersonationBanner from '@/Components/ImpersonationBanner.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div class="d-flex flex-column min-vh-100">
        <ImpersonationBanner />

        <div class="d-flex flex-grow-1">
            <aside class="bg-dark text-white p-3" style="width: 240px; flex-shrink: 0">
                <Link href="/" class="d-block text-white text-decoration-none fs-5 fw-semibold mb-4">
                    {{ $page.props.appName }}
                </Link>

                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <Link href="/admin/klant" class="nav-link text-white">{{ t('layouts.klant.dashboard') }}</Link>
                    </li>
                </ul>
            </aside>

            <div class="flex-grow-1 d-flex flex-column">
                <main class="flex-grow-1 container-fluid py-4">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
