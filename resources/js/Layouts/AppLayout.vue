<script setup lang="ts">
import AppFooter from '@/Components/AppFooter.vue';
import ImpersonationBanner from '@/Components/ImpersonationBanner.vue';
import { Link } from '@inertiajs/vue3';
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
                        <slot name="nav" />
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
