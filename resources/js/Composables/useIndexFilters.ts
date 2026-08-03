import type { FilterQuery } from '@/types/filtering';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

/**
 * Wires a single index page's search/sort/filter controls to the
 * server -- every param round-trips through the URL query string so the
 * page is bookmarkable/shareable, matching FilterRequestData's
 * ?search=&sort=&direction=&filter[x]=y shape on the PHP side.
 */
export function useIndexFilters(path: string, initial: FilterQuery) {
    const search = ref(initial.search ?? '');
    const sort = ref(initial.sort);
    const direction = ref(initial.direction);
    const filters = ref<Record<string, string>>({ ...initial.filters });

    function reload() {
        router.get(
            path,
            {
                search: search.value || undefined,
                sort: sort.value ?? undefined,
                direction: direction.value,
                filter: filters.value,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function sortBy(column: string) {
        if (sort.value === column) {
            direction.value = direction.value === 'asc' ? 'desc' : 'asc';
        } else {
            sort.value = column;
            direction.value = 'asc';
        }

        reload();
    }

    let searchDebounce: ReturnType<typeof setTimeout> | undefined;

    watch(search, () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(reload, 300);
    });

    watch(
        filters,
        () => {
            reload();
        },
        { deep: true },
    );

    return { search, sort, direction, filters, sortBy };
}
