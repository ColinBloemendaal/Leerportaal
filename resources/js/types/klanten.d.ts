export interface ResellerKlant {
    id: number;
    name: string;
    created_at: string;
}

export interface PaginatedKlanten {
    data: ResellerKlant[];
    meta: {
        current_page: number;
        last_page: number;
        total: number;
    };
}
