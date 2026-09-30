# System Email Templates — C3 (Staff & Legal) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move the staff-facing messages — complaint assigned / sub-assigned (technician SMS), enquiry new / assigned / reassigned / rejected, and the 13 legal alerts — onto system templates sent by `SystemMessenger`, and delete `ComplaintStageEmail`, `SalesEnquiryEmail`, `SalesAssignEmail`, `SalesRejectEmail`, `LegalEmail` and their views.

**Architecture:** Stacks on C2 (`feature/system-email-templates-c3` from `feature/system-email-templates-c2`). Same pattern as C2: send sites call small domain services (`StaffMessages`, `LegalMessages`) that build values and loop recipients; the surrounding conditions (`status == 5`, role lists, action keys) and in-app events stay exactly as they are.

**Tech Stack:** Laravel 5.7 modules, PostgreSQL, PHPUnit 7 (feature tests with `DatabaseTransactions`, `Mail::fake`, SMS stub).

**Spec:** `docs/superpowers/specs/2026-09-29-system-email-templates-design.md` (C3 section).

## Global Constraints

- PHP < 7.4 syntax only; Laravel 5.7 APIs. `$PHP` = `C:/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe`.
- Active off = not sent; Send SMS switch. Defaults: SMS **on** for `complaint_assigned`, `complaint_subassigned` (today they SMS every time); all others SMS off. `legal_case_referback` and `legal_advisor_approved` arrive **off** (commented out today).
- Recipients are deduplicated by user id; users without email → `skipped_no_address` (no SMS unless switched on).
- Delivery errors never break the action — important here because legal alerts run inside posting flows (PDC, receipts, rental income, deposit refund).
- Default wording signed "Al Habib Team"; the enquiry-new email goes to the customer and is worded for them.

## Review Focus

1. **Posting flows (PDC / receipt / invoice / deposit refund) for a contract under legal where the `legals` row is missing** → posting completes (today `$legal->subject` on null crashes after posting); alerts still go with contract values. Test in Task 4.
2. **Reassign with several notify users, or none** → each gets one email; none → nothing, no crash (today `->first()` on an empty list crashes). Test in Task 3.
3. **Enquiry rejected** → staff greeted by their employee name (today blank). Test in Task 3.
4. **Technician without email but with mobile** → SMS still sent (switch on by default), email `skipped_no_address`. Test in Task 2.
5. **Same user holding two of the listed roles** (e.g. are + backoffice_manager) → one message. Test in Task 4.

---

## Tasks

### Task 1: Values + C3 defaults + seed
- `MessageValues::assignment($checklist, $complaint)` → works_code, work_desc, complaint_no, complaint_date (d/m/Y H:i), complaint_mobile, building, unit; `MessageValues::enquiry($enquiry)` → enquiry_no, enquiry_type, enquirer_name.
- Defaults (stage C3) for: `complaint_assigned`, `complaint_subassigned`, `enquiry_new` (audience any), `enquiry_assigned`, `enquiry_reassigned`, `enquiry_rejected`, and legal: `legal_deposit_refund_posted`, `legal_rental_income_posted` ({invoice_no}), `legal_pdc_posted` ({cheque_no}), `legal_receipt_posted` ({receipt_no}), `legal_contract_revoked`, `legal_case_received`, `legal_case_approved`, `legal_case_referback` (off), `legal_advisor_approved` (off), `legal_advisor_referback`, `legal_case_closed`, `legal_advisor_closed`, `legal_status_note` ({status_note}). Legal placeholders: employee_name + tenant_name, contract_no, building, unit (+ the specific one).
- Unit test: C3 keys exist with the switches above (existing completeness test covers placeholders). Seed migration `2026_09_30_000004_seed_c3_system_templates.php`.

### Task 2: Complaint assigned / sub-assigned
- `StaffMessages::complaintAssigned($key, $complaint, $checklist, $users)` loops users (dedupe), recipient email `user->email`, mobile `employee_contact_no ?: employee_secondary_no`, values assignment + employee, related `['complaint', id]`.
- Wire `ComplaintStageController` storeGroupAssign (~:537-553) and storeGroupSubAssign (~:758-780): the subject/textContent building and the email/SMS loop become one call; the in-app event above stays.
- Feature tests: user with email → mail + SMS (switch on); user without email → SMS only, email skipped; switch off → nothing.

### Task 3: Enquiry messages
- `StaffMessages::enquiryNew($enquiry)` → to the enquirer (`sales_email`, `sales_mobile_no ?: alternative_no`), audience any.
- `StaffMessages::enquiryToStaff($key, $enquiry, $users)` for assigned / reassigned / rejected.
- Wire `SalesEnquiryController@store` (~:290-301), `TenantStageController` store (~:217-231), storeGroupAssign (~:2394-2408), assignNotify (~:2652-2671: all `$users_notify`, not the first only; the `$usersList->first()` crash on an empty list goes away), moveNextStage CL (~:1024-1041).
- Feature tests: new → enquirer; reassign to two users → two mails; empty user list → no crash; rejected greets employee name.

### Task 4: Legal alerts
- `LegalMessages::toUsers($key, $users, $contract, array $extra = [], $relatedLegalId = null)`: dedupe by id, values contract + employee + extra, related `['legal', id]` or `['contract', id]`.
- Wire the 13 sites: DepositRefundController ~:890-911, InvoiceController ~:540-563, RoutinesController ~:279-300 and ~:952-970, TenantContractEditController ~:759-777 and ~:1385-1407, LegalController ~:612-626, ~:638-653 (referback, template off), ~:659-673 (advisor approved, template off), ~:689-701, ~:1169-1184, ~:1191-1206, ~:1323-1337. Keep every surrounding `if`, role query, event and redirect; remove `$legal->subject/textContent` assignments only where nothing else uses them (the `LegalApprove` / `LegalReferBack` events read `text`/`content`, not `subject`) — check each.
- Guard: where the old code dereferenced `$legal` (possibly null), the new call uses the contract directly.
- Feature tests: helper sends one mail per distinct user; a user with two roles gets one; the invoice / deposit posting helpers called for a status-5 contract without a `legals` row don't throw (service-level); switched-off templates produce `skipped_off`.

### Task 5: Remove old Mailables + wrap-up
- Delete `Maintenance/Emails/ComplaintStageEmail`, `Sales/Emails/SalesEnquiryEmail`, `SalesAssignEmail`, `SalesRejectEmail`, `Masters/Emails/LegalEmail` and their views; grep for leftovers (backup copies excluded).
- Full suite, syntax scan, final fresh review; fix Critical/Important with RED→GREEN tests.
- Deploy: code first, then `2026_09_30_000004`.
