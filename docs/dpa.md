---
version: 2026-08-04
---

# Data Processing Agreement

This Data Processing Agreement ("DPA") forms part of the agreement between the platform operator ("Processor") and the reseller ("Controller") for use of the Leerportaal platform, and reflects the requirements of Article 28 of the General Data Protection Regulation (GDPR).

**Version**: 2026-08-04. A reseller must review and re-accept this document whenever the version changes; the app tracks acceptance per reseller against this exact version string (`config('gdpr.dpa_version')`), not just "has this reseller ever accepted a DPA."

## 1. Subject matter and duration

The Processor processes personal data on the Controller's behalf for the duration of the Controller's use of the platform, ending when the Controller's account is closed and the retention/erasure obligations in Sections 7-8 below have been fulfilled.

## 2. Nature and purpose of processing

Hosting, storing, and processing personal data submitted by or on behalf of the Controller in the course of operating an e-learning platform: course delivery, quiz/assessment grading, certificate issuance, progress tracking, billing for course assignments, and platform notifications.

## 3. Categories of data subjects

- The Controller's own staff and administrators.
- The Controller's klanten (customer organizations) and their staff.
- The Controller's cursisten (end learners).

## 4. Categories of personal data

Name, email address, course progress and completion records, quiz/assessment answers and scores, certificates issued, billing and invoicing records, and account activity logs. See the Controller's own data export (Settings -> My data, or the platform admin equivalent) for the complete, current list of what is actually stored about any one data subject.

## 5. Processor obligations

The Processor shall:

- Process personal data only on the Controller's documented instructions, as implemented through the platform's own features.
- Ensure persons authorized to process personal data are bound by confidentiality.
- Implement appropriate technical and organizational security measures, including tenant-level data isolation, encryption of sensitive fields, audit logging, and access controls -- see `docs/security.md` if published, or the platform's own security documentation.
- Assist the Controller in responding to data subject requests (access, erasure, portability) via the platform's own first-class export and erasure features.
- Notify the Controller without undue delay after becoming aware of a personal data breach affecting the Controller's data.
- Make available to the Controller information necessary to demonstrate compliance with this DPA, and allow for audits.
- Delete or return all personal data at the end of the provision of services, except where retention is required by law (see Section 7).

## 6. Sub-processors

The Processor may engage sub-processors to provide parts of the service (hosting, email delivery, payment processing, error tracking). The current list is maintained at [`docs/subprocessors.md`](subprocessors.md) and kept up to date as it changes. The Controller consents to the sub-processors listed there as of the version of this DPA it has accepted.

## 7. Data retention

Personal data is retained according to the platform's own configurable retention policies (notifications, exports, activity log) and the legal retention requirements for billing records (invoices are never deleted, per the Controller's own billing obligations). See the platform's retention configuration for the current defaults and any reseller-specific overrides the Controller has set.

## 8. Return or deletion of data

On termination of the Controller's account, the Processor will delete the Controller's personal data within a reasonable period, except where retention is required by law (e.g. issued invoices). The Controller may request a full data export before termination via the platform's own export features.

## 9. Liability

Each party is responsible for its own compliance with applicable data protection law in its respective role (Processor / Controller) as described in this document.

---

*This is a template document, not reviewed by qualified legal counsel for any specific jurisdiction. Have this reviewed before relying on it as an actual contractual DPA.*
