<script setup lang="ts">
import AppFooter from '@/Components/AppFooter.vue';
import ImpersonationBanner from '@/Components/ImpersonationBanner.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div class="d-flex flex-column min-vh-100">
        <ImpersonationBanner />

        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <Link href="/" class="navbar-brand">
                    {{ $page.props.appName }}
                </Link>

                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#appNavbar"
                    aria-controls="appNavbar"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon" />
                </button>

                <div id="appNavbar" class="collapse navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <Link href="/dashboard" class="nav-link">{{ t('layouts.app.dashboard') }}</Link>
                        </li>
                        <li class="nav-item">
                            <Link href="/admin/exports" class="nav-link">{{ t('layouts.app.myExports') }}</Link>
                        </li>
                        <li class="nav-item">
                            <Link href="/notifications" class="nav-link">
                                {{ t('common.notifications') }}
                                <span v-if="$page.props.notificationsUnreadCount > 0" class="badge text-bg-danger ms-1">
                                    {{ $page.props.notificationsUnreadCount }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1 container py-4">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
