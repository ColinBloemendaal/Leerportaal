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

export interface KlantCursistSummary {
    userId: number;
    name: string;
    assignedCount: number;
    inProgressCount: number;
    completedCount: number;
}

export interface KlantDashboardStats {
    cursistCount: number;
    cursisten: KlantCursistSummary[];
}

export interface KlantSpend {
    klantId: number | null;
    klantName: string;
    subtotal: { cents: number };
}

export interface InvoiceHistoryEntry {
    id: number;
    periodStart: string;
    periodEnd: string;
    status: string;
    total: { cents: number };
    issuedAt: string | null;
    paidAt: string | null;
}

export interface ResellerBillingDashboardStats {
    currentPeriodStart: string | null;
    currentPeriodEnd: string | null;
    currentPeriodSubtotal: { cents: number };
    klantBreakdown: KlantSpend[];
    history: InvoiceHistoryEntry[];
}
