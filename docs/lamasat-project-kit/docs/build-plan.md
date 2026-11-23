# Build plan

Each phase ends with a demo to the clinic using the prototype as the yardstick and the relevant acceptance scenarios passing.

| Phase | Scope | Screens | Lead agents | Exit criteria |
|---|---|---|---|---|
| 0 Foundation | Repo, CI, Sail; auth (password + OTP), roles/permissions, AppLayout, shared components, Money, Numbering, Idempotency, Audit, Settings shell, DemoSeeder | Sign in, 25 (shell) | laravel-architect, auth-permissions-specialist, vue-frontend-engineer, devops-release-engineer | Sign-in works for each role; menu by role; CI green |
| 1 Front desk | Services, rooms, shifts, patients, bookings + conflicts, dashboard, availability, follow-ups, patient history | 01–06 | booking-scheduling-specialist | Scenarios 1 (booking part), 2, 8 |
| 2 Clinical | Consultation draft/complete/amend, handoffs, visit summary PDF | 07 | clinical-consultation-specialist | Scenario 1 complete |
| 3 Pharmacy | Medicines, lots, ledger, billing, invoices, reversals, report | 08–11 | pharmacy-inventory-specialist | Scenario 3 |
| 4 Accounts | Clinic billing, receipts, allocations, dues, billing report, GST report, PDFs | 12–15 | billing-accounts-specialist | Scenario 4; GST sample signed by accountant |
| 5 Packages | Types, sales, redemption, dashboard | 18–19 | packages-homecare-insurance-specialist | Scenario 5 |
| 6 Home service | Visits, dispatch, routes | 20–21 | packages-homecare-insurance-specialist | Scenario 6 |
| 7 Insurance | Cases, status history, dashboard | 16–17 | packages-homecare-insurance-specialist | Scenario 7 |
| 8 Reporting | Analytics, booking reports, audit log UI, exports | 22–24 | reports-analytics-engineer | Scenario 9 |
| 9 Launch | Data migration, security audit, performance, UAT, training, go-live | — | data-migration-specialist, security-privacy-auditor, test-engineer | Reconciliation signed; all scenarios green |

Before Phase 1: resolve or explicitly accept D-02, D-04, D-05, D-07, D-10. Before Phase 4: D-14, D-24. Before Phase 8: D-21.
