<script setup lang="ts">
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface CourseOption {
    id: number;
    title: string;
}

interface CursistOption {
    id: number;
    name: string;
}

interface GroupOption {
    id: number;
    name: string;
}

defineOptions({ layout: ResellerAdminLayout });

defineProps<{
    courses: { data: CourseOption[] };
    cursisten: { data: CursistOption[] };
    groups: { data: GroupOption[] };
}>();

const { t } = useI18n();

type Mode = 'individual' | 'bulk' | 'group';
const mode = ref<Mode>('individual');

const individualForm = useForm({ course_id: '', user_id: '' });
const bulkForm = useForm<{ course_id: string; user_ids: number[] }>({ course_id: '', user_ids: [] });
const groupForm = useForm({ course_id: '', group_id: '' });

function submit() {
    if (mode.value === 'individual') {
        individualForm.post('/admin/reseller/assignments');
    } else if (mode.value === 'bulk') {
        bulkForm.post('/admin/reseller/assignments/bulk');
    } else {
        groupForm.post('/admin/reseller/assignments/group');
    }
}
</script>

<template>
    <Link href="/admin/reseller/assignments" class="d-inline-block mb-3">{{ t('admin.reseller.assignments.back') }}</Link>

    <h1 class="h4 mb-4">{{ t('admin.reseller.assignments.assignCourse') }}</h1>

    <div class="btn-group mb-4" role="group">
        <button
            type="button"
            class="btn"
            :class="mode === 'individual' ? 'btn-primary' : 'btn-outline-primary'"
            @click="mode = 'individual'"
        >
            {{ t('admin.reseller.assignments.modeIndividual') }}
        </button>
        <button type="button" class="btn" :class="mode === 'bulk' ? 'btn-primary' : 'btn-outline-primary'" @click="mode = 'bulk'">
            {{ t('admin.reseller.assignments.modeBulk') }}
        </button>
        <button type="button" class="btn" :class="mode === 'group' ? 'btn-primary' : 'btn-outline-primary'" @click="mode = 'group'">
            {{ t('admin.reseller.assignments.modeGroup') }}
        </button>
    </div>

    <form v-if="mode === 'individual'" class="col-md-6" @submit.prevent="submit">
        <div class="mb-3">
            <label class="form-label" for="individual-course">{{ t('admin.reseller.assignments.course') }}</label>
            <select
                id="individual-course"
                v-model="individualForm.course_id"
                class="form-select"
                :class="{ 'is-invalid': individualForm.errors.course_id }"
            >
                <option value="" disabled>{{ t('admin.reseller.assignments.selectCourse') }}</option>
                <option v-for="course in courses.data" :key="course.id" :value="course.id">{{ course.title }}</option>
            </select>
            <div v-if="individualForm.errors.course_id" class="invalid-feedback">{{ individualForm.errors.course_id }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="individual-user">{{ t('admin.reseller.assignments.cursist') }}</label>
            <select
                id="individual-user"
                v-model="individualForm.user_id"
                class="form-select"
                :class="{ 'is-invalid': individualForm.errors.user_id }"
            >
                <option value="" disabled>{{ t('admin.reseller.assignments.selectCursist') }}</option>
                <option v-for="cursist in cursisten.data" :key="cursist.id" :value="cursist.id">{{ cursist.name }}</option>
            </select>
            <div v-if="individualForm.errors.user_id" class="invalid-feedback">{{ individualForm.errors.user_id }}</div>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="individualForm.processing">
            {{ t('admin.reseller.assignments.assign') }}
        </button>
    </form>

    <form v-else-if="mode === 'bulk'" class="col-md-6" @submit.prevent="submit">
        <div class="mb-3">
            <label class="form-label" for="bulk-course">{{ t('admin.reseller.assignments.course') }}</label>
            <select id="bulk-course" v-model="bulkForm.course_id" class="form-select" :class="{ 'is-invalid': bulkForm.errors.course_id }">
                <option value="" disabled>{{ t('admin.reseller.assignments.selectCourse') }}</option>
                <option v-for="course in courses.data" :key="course.id" :value="course.id">{{ course.title }}</option>
            </select>
            <div v-if="bulkForm.errors.course_id" class="invalid-feedback">{{ bulkForm.errors.course_id }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="bulk-users">{{ t('admin.reseller.assignments.cursisten') }}</label>
            <select
                id="bulk-users"
                v-model="bulkForm.user_ids"
                class="form-select"
                :class="{ 'is-invalid': bulkForm.errors.user_ids }"
                multiple
                size="8"
            >
                <option v-for="cursist in cursisten.data" :key="cursist.id" :value="cursist.id">{{ cursist.name }}</option>
            </select>
            <div class="form-text">{{ t('admin.reseller.assignments.multiSelectHint') }}</div>
            <div v-if="bulkForm.errors.user_ids" class="invalid-feedback">{{ bulkForm.errors.user_ids }}</div>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="bulkForm.processing">
            {{ t('admin.reseller.assignments.assign') }}
        </button>
    </form>

    <form v-else class="col-md-6" @submit.prevent="submit">
        <div class="mb-3">
            <label class="form-label" for="group-course">{{ t('admin.reseller.assignments.course') }}</label>
            <select id="group-course" v-model="groupForm.course_id" class="form-select" :class="{ 'is-invalid': groupForm.errors.course_id }">
                <option value="" disabled>{{ t('admin.reseller.assignments.selectCourse') }}</option>
                <option v-for="course in courses.data" :key="course.id" :value="course.id">{{ course.title }}</option>
            </select>
            <div v-if="groupForm.errors.course_id" class="invalid-feedback">{{ groupForm.errors.course_id }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="group-group">{{ t('admin.reseller.assignments.group') }}</label>
            <select id="group-group" v-model="groupForm.group_id" class="form-select" :class="{ 'is-invalid': groupForm.errors.group_id }">
                <option value="" disabled>{{ t('admin.reseller.assignments.selectGroup') }}</option>
                <option v-for="group in groups.data" :key="group.id" :value="group.id">{{ group.name }}</option>
            </select>
            <div v-if="groupForm.errors.group_id" class="invalid-feedback">{{ groupForm.errors.group_id }}</div>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="groupForm.processing">
            {{ t('admin.reseller.assignments.assign') }}
        </button>
    </form>
</template>
