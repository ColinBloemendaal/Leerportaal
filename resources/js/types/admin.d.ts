export interface PlatformDashboardStats {
    resellerCount: number;
    userCount: number;
    courseCount: number;
    billedRevenue: { cents: number };
    pendingRevenue: { cents: number };
    storageUsedBytes: number;
    storageIncludedBytes: number;
}
