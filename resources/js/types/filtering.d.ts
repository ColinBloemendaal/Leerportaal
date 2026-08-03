export interface PaginatedCollection<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        total: number;
    };
}

export interface FilterQuery {
    search: string | null;
    sort: string | null;
    direction: 'asc' | 'desc';
    filters: Record<string, string>;
}
