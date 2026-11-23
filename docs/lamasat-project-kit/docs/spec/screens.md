# Screen inventory

Prototype anchor: open `docs/prototype/index.html`, sign in with any demo account, then go to the anchor (e.g. `index.html#dashboard`). Detail pages in the prototype use `#record` (booking, invoices, insurance case, package, home visit).

| # | Screen | Module | Suggested route | Prototype | Roles (proposed, see permissions.md) | Status |
|---|---|---|---|---|---|---|
| 01 | Dashboard | Front Desk | `/` | `#dashboard` | Reception, Practitioner, Clinic Manager, Admin | Not started |
| 02 | New Booking | Front Desk | `/bookings/create` | `#new-booking` | Reception, Admin | Not started |
| 03 | Patients | Front Desk | `/patients` | `#patients` | All staff (read); Reception (edit) | Not started |
| 04 | Patient History | Front Desk | `/patients/{patient}` | `#patient-history` | Reception, Practitioner, Accounts, Admin (clinical links: Practitioner+) | Not started |
| 05 | Follow-ups | Front Desk | `/follow-ups` | `#follow-ups` | Reception, Practitioner, Admin | Not started |
| 06 | Staff Availability | Front Desk | `/availability` | `#availability` | Reception, Practitioner, Admin (shift edits: designated) | Not started |
| 07 | Consultation | Clinical | `/visits/{booking}/consultation` | `#consultation` | Practitioner, Admin | Not started |
| 08 | Stock Master | Pharmacy | `/pharmacy/medicines` | `#stock` | Pharmacy, Admin | Not started |
| 09 | Medicine Billing | Pharmacy | `/pharmacy/sales/create` | `#medicine-billing` | Pharmacy, Admin | Not started |
| 10 | Medicine Invoices | Pharmacy | `/pharmacy/invoices` | `#medicine-invoices` | Pharmacy, Accounts, Admin (reverse: Pharmacy lead, Admin) | Not started |
| 11 | Medicine Report | Pharmacy | `/pharmacy/reports/sales` | `#medicine-report` | Pharmacy, Accounts, Admin | Not started |
| 12 | Clinic Billing | Accounts | `/billing/invoices/create` | `#clinic-billing` | Accounts, Reception, Admin | Not started |
| 13 | Due Amounts | Accounts | `/billing/dues` | `#due-amounts` | Accounts, Admin | Not started |
| 14 | Clinic Billing Report | Accounts | `/billing/reports/billing` | `#billing-report` | Accounts, Clinic Manager, Admin | Not started |
| 15 | Tax Report | Accounts | `/billing/reports/gst` | `#tax-report` | Accounts, Admin | Not started |
| 16 | Insurance Dashboard | Insurance | `/insurance` | `#insurance-dashboard` | Insurance Coordinator, Accounts, Reception, Admin | Not started |
| 17 | Insurance Status | Insurance | `/insurance/cases` | `#insurance-status` | Insurance Coordinator, Accounts, Reception, Admin | Not started |
| 18 | Packages Dashboard | Packages | `/packages` | `#packages-dashboard` | Reception, Accounts, Practitioner, Admin | Not started |
| 19 | Packages | Packages | `/packages/sold` | `#packages` | Reception, Accounts, Admin | Not started |
| 20 | Home Service | Home Service | `/home-visits` | `#home-service` | Home Service Coordinator, Reception, Admin | Not started |
| 21 | Home Service Dashboard | Home Service | `/home-visits/dispatch` | `#home-dashboard` | Home Service Coordinator, Reception, Admin | Not started |
| 22 | Reports & Analytics | Reports & Analytics | `/analytics` | `#analytics` | Clinic Manager, Accounts, Admin | Not started |
| 23 | Booking & OT Reports | Reports & Admin | `/reports/bookings` | `#booking-reports` | Reception, Accounts, Clinic Manager, Admin | Not started |
| 24 | Audit Log | Reports & Admin | `/admin/audit` | `#audit-log` | Clinic Manager, Admin | Not started |
| 25 | Settings | Reports & Admin | `/admin/settings` | `#app-settings` | Admin (per setting group) | Not started |
| — | Sign in (password + mobile OTP) | Account | `/login` | first screen | Everyone | Not started |
| — | Record detail pages (booking, invoices, case, package, home visit) | Shared | `/<resource>/{id}` | `#record` | As parent list | Not started |

Status values: Not started · Brief · In progress · In review · Done.
