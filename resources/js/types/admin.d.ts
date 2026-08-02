export interface PlatformDashboardStats {
    resellerCount: number;
    userCount: number;
    courseCount: number;
    billedRevenue: { cents: number };
    pendingRevenue: { cents: number };
    storageUsedBytes: number;
    storageIncludedBytes: number;
}

export interface ResellerDashboardStats {
    klantCount: number;
    cursistCount: number;
    assignmentCount: number;
    billedSpend: { cents: number };
    pendingSpend: { cents: number };
}
