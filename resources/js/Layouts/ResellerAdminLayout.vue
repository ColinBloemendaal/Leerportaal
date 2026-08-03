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
                        <Link href="/admin/reseller" class="nav-link text-white">{{
                            t('layouts.reseller.dashboard')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/klanten" class="nav-link text-white">{{ t('layouts.reseller.klanten') }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/reseller/courses" class="nav-link text-white">{{
                            t('layouts.reseller.courses')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/reseller/assignments" class="nav-link text-white">{{
                            t('layouts.reseller.assignments')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/reseller/attempts" class="nav-link text-white">{{
                            t('layouts.reseller.attempts')
                        }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/invites" class="nav-link text-white">{{ t('layouts.reseller.invites') }}</Link>
                    </li>
                    <li class="nav-item">
                        <Link href="/admin/exports" class="nav-link text-white">{{
                            t('layouts.reseller.myExports')
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
