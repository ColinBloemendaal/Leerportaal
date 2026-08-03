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
                        <Link href="/admin/platform" class="nav-link text-white">{{
                            t('layouts.admin.dashboard')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/platform/resellers" class="nav-link text-white">{{
                            t('layouts.admin.resellers')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/platform/users" class="nav-link text-white">{{
                            t('layouts.admin.users')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/platform/activity" class="nav-link text-white">{{
                            t('layouts.admin.activity')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/platform/health" class="nav-link text-white">{{
                            t('layouts.admin.health')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/exports" class="nav-link text-white">{{
                            t('layouts.admin.myExports')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/notifications" class="nav-link text-white">
                            {{ t('common.notifications') }}
                            <span v-if="$page.props.notificationsUnreadCount > 0" class="badge text-bg-danger ms-1">
                                {{ $page.props.notificationsUnreadCount }}
                            </span>
                        </Link>
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
