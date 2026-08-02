export type AssignmentStatus = 'not_started' | 'in_progress' | 'completed';

export interface CursistAssignmentRow {
    assignmentId: number;
    courseId: number;
    courseTitle: string;
    status: AssignmentStatus;
    progressPercent: number;
    isOverdue: boolean;
    deadlineAt: string | null;
}
