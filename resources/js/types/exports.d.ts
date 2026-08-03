export interface ExportRow {
    id: number;
    resource_type: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    failure_reason: string | null;
    expires_at: string | null;
    created_at: string;
    download_url: string | null;
}
