<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light py-4">
        <div class="w-100" style="max-width: 420px">
            <div class="text-center mb-4">
                <Link href="/" class="text-decoration-none fs-4 fw-semibold text-body">
                    {{ $page.props.appName ?? 'Leerportaal' }}
                </Link>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
