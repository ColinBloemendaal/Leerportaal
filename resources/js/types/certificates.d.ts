export interface CertificateVerification {
    data: {
        recipient_name: string | null;
        course_title: string | null;
        issued_at: string;
        verification_code: string;
    };
}
