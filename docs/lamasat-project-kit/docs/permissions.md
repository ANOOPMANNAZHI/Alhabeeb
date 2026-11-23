# Roles, permissions and audit

Roles: Reception · Practitioner · Therapist · Pharmacy · Accounts · Insurance Coordinator · Home Service Coordinator · Clinic Manager · Administrator. Proposed matrix (prototype defaults, confirm with the clinic manager — decision D-07 and spec "Permissions and phone use").

| Screen | Recep. | Pract. | Ther. | Pharm. | Acct. | Ins. | Home | Mgr | Admin |
|---|---|---|---|---|---|---|---|---|---|
| 01 Dashboard | ✓ | ✓ | ✓ | | | | | ✓ | ✓ |
| 02 New Booking | ✓ | | | | | | | ✓ | ✓ |
| 03 Patients | ✓ edit | view | view | view | view | view | view | ✓ | ✓ |
| 04 Patient History | ✓ (no notes) | ✓ | ✓ (no notes) | | ✓ (no notes) | | | ✓ | ✓ |
| 05 Follow-ups | ✓ | ✓ | | | | | | ✓ | ✓ |
| 06 Staff Availability | ✓ | ✓ | ✓ | | | | ✓ | ✓ | ✓ |
| 07 Consultation | | ✓ | | | | | | view | ✓ |
| 08–11 Pharmacy | | | | ✓ | 10, 11 view | | | ✓ view | ✓ |
| 12 Clinic Billing | ✓ | | | | ✓ | | | | ✓ |
| 13–15 Accounts | | | | | ✓ | | | 14 view | ✓ |
| 16–17 Insurance | ✓ | | | | ✓ | ✓ | | ✓ | ✓ |
| 18–19 Packages | ✓ | 18 view | | | ✓ | | | ✓ | ✓ |
| 20–21 Home Service | ✓ | | 21 own | | | | ✓ | ✓ | ✓ |
| 22 Analytics | | | | | ✓ | | | ✓ | ✓ |
| 23 Booking & OT Reports | ✓ | | | | ✓ | | | ✓ | ✓ |
| 24 Audit Log | | | | | | | | ✓ | ✓ |
| 25 Settings | | | | | | | | groups | ✓ |

## Sensitive actions (separate permissions)
`booking.cancel`, `booking.reschedule`, `schedule.change`, `consultation.complete`, `consultation.amend`, `stock.adjust`, `medicine_invoice.reverse`, `clinic_invoice.reverse`, `payment.void`, `package.correct`, `settings.rates`, `settings.access`, `reports.export`, `patients.export`.

## Audited events (with reason where marked *)
Booking created / rescheduled* / cancelled* / status changed · shift or blocked time changed* · consultation completed / amended* · stock received / adjusted* · invoice issued / reversed* · receipt issued / voided* · package sold / redeemed / restored / corrected* · home visit assigned / reassigned* / cancelled* · insurance status changed* · rate changed · permission changed · export run · sign-in / failed sign-in / OTP lockout.
