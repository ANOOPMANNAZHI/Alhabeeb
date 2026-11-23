# LAMASAT Ayurveda Clinic — Functional specification

> Text of the clinic's review draft (23 November 2026). Original: `LAMASAT_Clinic_Web_Application_Specification.docx`. Where this and `docs/decisions.md` differ, the decision wins.

LAMASAT Ayurveda Clinic web application

This review draft describes the nine modules the clinic wants to carry forward. Use it to review what each member of staff sees, what they record, and what should happen when they complete a task.

**Confirmed scope.** Three photographs of the existing application show 25 menu entries, two account actions, and preset and custom times on the booking screen.

**Proposed design.** The fields, staff responsibilities, workflow rules, and acceptance checks below are recommendations. The clinic can validate them against its forms and daily practice before development.

Screen directory

| Module | Screens | Where to find the specification |
|---|---|---|
| Front Desk | 01–06 | Reception and booking • pp. 2–3 |
| Clinical | 07 | Consultation record • p. 4 |
| Pharmacy | 08–11 | Stock, sales, and invoices • p. 5 |
| Accounts | 12–15 | Billing, dues, and tax reporting • p. 6 |
| Insurance Management | 16–17 | Insurance records and status • p. 7 |
| Package Management | 18–19 | Packages and session use • p. 7 |
| Home Service | 20–21 | Home visits and assignments • p. 8 |
| Reports & Analytics | 22 | Management reporting • p. 8 |
| Reports & Admin | 23–25 | Operational reports and settings • p. 9 |

How to review the scope

Start with one patient visit. Have reception, the practitioner, pharmacy, and accounts review the handoffs on page 10. Use the decisions on page 11 to record where the proposed process needs to change.


## MODULE 1 — SCREENS 01–03

Front Desk

Reception uses the same patient record to register a person, reserve time, and monitor arrivals. A booking belongs to a particular visit so a returning patient can have several appointments without needing another profile.


### 01 Dashboard

Give reception a live working list of appointments for the selected day.

**Screen details.** Show time, patient, service, assigned staff member, and status. Place arrivals awaiting consultation and follow-ups due today within reach. Allow filtering by staff member and status.

**Staff actions and rules.** Open a booking, register an arrival, or start a new booking. After a status change, update the appointment list and its counts together.


### 02 New Booking

Find the patient first, then show eligible times for the selected service and staff member.

**Screen details.** Patient, service, date, duration, practitioner or therapist, start time, and booking notes. The photos show 10:00 AM, 01:00 PM, 04:00 PM, 07:00 PM, and a custom time option. Confirm the complete slot list.

**Staff actions and rules.** Offer registration when no patient matches. Check the full duration against bookings, breaks, and blocked time when saving. If two users request the same resource, show the second user the conflict and retain their form entries. Record a reason when cancelling or rescheduling.


### 03 Patients

Maintain the directory reception searches before registering a patient.

**Screen details.** Suggested identifiers include a generated patient ID, full name, phone number, and date of birth or age. Confirm mandatory contact fields and how staff record an approximate age.

**Staff actions and rules.** Search by name, number, or ID. Show likely duplicates before saving. Allow separate profiles for family members sharing a phone number. Open booking and history from the profile.


## MODULE 1 — SCREENS 04–06

Patient history and staff schedules

Reception needs to know when a patient last visited and what to do next. Practitioners may need the clinical record for the same visit. The clinic should agree which details each user can open.


### 04 Patient History

Present one timeline for the selected patient, starting with the latest visit.

**Screen details.** Show visit date, service, practitioner, booking outcome, and follow-up link. For authorized users, link the consultation, medicine advice, and related invoices to that visit.

**Staff actions and rules.** Filter by date or visit type and open a source record. Correct information in its source record so the timeline stays consistent. Apply the same access rules when opening a direct link.


### 05 Follow-ups

Give staff a daily worklist of patients who need contact or another appointment.

**Screen details.** Patient, phone, source visit, follow-up reason, due date, assigned staff member, and contact outcome. Include overdue, due today, and upcoming views.

**Staff actions and rules.** Record contact attempts, defer with a new date, or book a return visit. Link the new booking to the follow-up. If it is cancelled, show the linked cancellation and apply the clinic’s agreed reopening rule.


### 06 Staff Availability

Show when an eligible practitioner or therapist can take a booking.

**Screen details.** Daily or weekly view of shifts, breaks, blocked periods, clinic bookings, and assigned home visits. Include staff, date, and service filters. Add room availability if the clinic reserves treatment rooms.

**Staff actions and rules.** Start a booking from a free interval. Limit shift changes to designated staff. Flag existing appointments affected by a proposed schedule change for review before the change takes effect.

**Decision for reception.** Confirm which services reserve a practitioner, therapist, room, or combination, and whether travel buffers reduce availability.


## MODULE 2 — SCREEN 07

Clinical

The practitioner documents what happened during a specific visit and gives reception and pharmacy the information they need for the next step. A blank consultation form is the best reference for the clinic’s terminology.


### 07 Consultation

Open the consultation from the booked visit. Keep patient identity, practitioner, and visit date visible while recording notes.

**Screen details.** Proposed sections include presenting complaint, relevant history, examination or Ayurvedic assessment, clinical impression, plan, medicine advice, and review instructions. Confirm the clinic’s assessment fields and mandatory entries with its lead practitioner.

**Staff actions and rules.** Save a draft, reopen it, and complete the record. Keep a clear distinction between draft and completed notes. Record the author and time of completion. If a completed record needs a correction, preserve the original entry and identify the amendment.

Proposed handoffs

| Recipient | What the practitioner passes on | Decision to confirm |
|---|---|---|
| Pharmacy | Selected medicine, form, quantity or instructions, and source visit. | Confirm if advice creates a dispensing work item and how unavailable items are recorded. |
| Reception | Suggested review date, follow-up reason, or recommended therapy. | Confirm whether a follow-up is created automatically and who books the next visit. |
| Patient | A prescription or visit summary in the clinic’s approved format. | Obtain a blank sample and agree which details are printed. |

Visit review example

A returning patient is seen twice in one week. Opening the second visit should show its own draft and allow the practitioner to review the first completed consultation. Completing the second visit should add a separate history entry.


## MODULE 3 — SCREENS 08–11

Pharmacy

Pharmacy staff maintain medicine stock and record what is actually dispensed. One sale should be traceable from its invoice to the quantity removed from stock.


### 08 Stock Master

Maintain the medicine catalogue and available stock.

**Screen details.** Medicine code, name, form, pack size, dispensing unit, selling price, quantity, and active status. Add batch number and expiry per stock lot if the clinic tracks them.

**Staff actions and rules.** Receive stock or adjust a quantity with a reason. Confirm pack-to-unit conversion where purchases and dispensing use different units. Keep earlier transactions accessible after deactivating a medicine.


### 09 Medicine Billing

Prepare a sale for a patient or an eligible counter customer.

**Screen details.** Medicine, quantity dispensed, unit, rate, permitted discount, tax if applicable, and total. Display available stock and any linked prescription.

**Staff actions and rules.** Validate quantities at issue. Create the invoice and stock movement together, including when the user retries after a lost connection. Confirm whether a sale can proceed with insufficient stock.


### 10 Medicine Invoices

Retrieve an issued invoice and the items it contains.

**Screen details.** Search by invoice number, date, patient, and payment status. Display line amounts, invoice total, payments and balance where applicable, and any linked return.

**Staff actions and rules.** Open and print a copy. Restrict reversals to authorized staff and capture the reason. Apply any financial reversal and the agreed stock disposition to the original sale.


### 11 Medicine Report

Show what was sold during a selected period.

**Screen details.** Filter by date and medicine. Suggested columns include quantity dispensed, gross amount, discounts, returns, and net sales. Confirm whether the clinic also expects expiry or stock-on-hand reporting.

**Staff actions and rules.** Open the contributing invoices from a total. Agree whether returns appear on their processing date or the original sale date, and label the period basis on the report.


## MODULE 4 — SCREENS 12–15

Accounts

Accounts staff bill for services, allocate collections to invoices, and explain outstanding balances. The menu distinguishes clinic billing from medicine billing. The final invoice and receipt model requires a clinic decision.


### 12 Clinic Billing

Build an invoice from services provided to the patient.

**Screen details.** Patient, visit, service lines, quantities, rates, discounts, tax where applicable, amount paid, payment method, and balance. Show package coverage or insurer responsibility if approved.

**Staff actions and rules.** Review the charges, issue the invoice, and record the payment. Identify services already covered by a package. Preserve issued prices when the service catalogue changes.


### 13 Due Amounts

Give accounts staff a list of invoices with unpaid balances.

**Screen details.** Patient or payer, invoice reference, issue date, original charge, collections, adjustments, and balance. Confirm whether pharmacy and insurer balances belong in this view.

**Staff actions and rules.** Open an invoice, allocate a partial or full collection, and issue a receipt. Retain the individual payment dates and recalculate the remaining amount after each allocation.


### 14 Clinic Billing Report

Reconcile services billed and money received across a period.

**Screen details.** Filters include invoice date or payment date, service, practitioner, and payment method as applicable. Show billed amount, discounts, collections, adjustments, and outstanding balances with clear date definitions.

**Staff actions and rules.** Open the invoice or receipt behind a total. Count later collections on their receipt date. Separate period activity from a balance outstanding as of a specified date.


### 15 Tax Report

Prepare the breakdown requested by the clinic’s accountant.

**Screen details.** Reporting period, invoice reference, tax category, taxable base, tax amount, and adjustments. Confirm applicable categories, rounding, invoice coverage, and output format.

**Staff actions and rules.** Trace each subtotal to issued documents and adjustments. Validate the report against an accountant-approved sample before using it for period reporting.


## MODULES 5 AND 6 — SCREENS 16–19

Insurance and package management

Insurance Management tracks an insurance record through the clinic’s process. Package Management tracks what a patient purchased and how much of the package has been used.


### 16 Insurance Dashboard

Identify insurance records that require attention.

**Screen details.** Group records by approved status, insurer, and age. Include claimed or outstanding amounts only if the clinic manages reimbursement claims.

**Staff actions and rules.** Select a status total to open its underlying cases. Confirm whether the unit being counted is a patient policy, an approval request, or an invoice claim.


### 17 Insurance Status

Maintain the current state and history of an insurance record.

**Screen details.** Patient, insurer, reference, related visit or invoice, status, last update, staff owner, and remarks. Required fields depend on the insurance process.

**Staff actions and rules.** Record each change with its date and reason. Confirm who supplies updates, what evidence is needed, and whether the clinic receives any data from an external system.


### 18 Packages Dashboard

Identify patient packages that need action.

**Screen details.** Show active packages, unused sessions, exhausted packages, and packages nearing expiry if validity dates apply. Filter by package or patient.

**Staff actions and rules.** Open the individual package and inspect the visits behind its remaining balance. Count each purchase separately if the same patient has bought the package twice.


### 19 Packages

Maintain package definitions and the packages sold to individual patients.

**Screen details.** Catalogue view: name, eligible services, sessions, price, and validity. Patient view: purchaser, purchase date, invoice, validity dates, sessions used, and balance.

**Staff actions and rules.** Assign or sell a package and link a session to the qualifying visit. Confirm when a session is consumed, when a cancellation restores it, and whether home visits qualify.


## MODULES 7 AND 8 — SCREENS 20–22

Home service and clinic analytics

Home Service coordinates visits at a patient’s address. Reports & Analytics gives management a cross-clinic view assembled from bookings, completed visits, and financial records.


### 20 Home Service

Create and maintain a visit outside the clinic.

**Screen details.** Patient, service address, landmark or directions, contact number, requested service, visit date, expected duration, assigned staff, and visit notes. Record travel charges only if the clinic applies them.

**Staff actions and rules.** Assign, reschedule, cancel, or complete a visit. Retain the booked address for that visit. Check for overlapping clinic appointments when the same staff serve both locations.


### 21 Home Service Dashboard

Give the coordinator a daily dispatch list.

**Screen details.** Show today’s visits, unassigned requests, scheduled visits, completions, and cancellations. Include time, area, service, and assigned staff in each row.

**Staff actions and rules.** Open a request, assign an available person, or review an incomplete visit. Reflect a reassignment in the schedules of both the original and replacement staff member.


### 22 Reports & Analytics

Provide a management view with agreed definitions and traceable totals.

**Screen details.** Candidate measures include bookings by status, completed visits, returning patients, collections, medicine sales, package usage, and home visits. Use date and department filters where the underlying records support them.

**Staff actions and rules.** Open supporting records from each measure. Display how the selected date is applied. Explicitly label invoice totals and cash collections. Confirm how package sales and redemptions contribute to each measure.

A reporting decision worth settling early

A prepaid therapy package can generate one sale and several completed visits. A manager counting visits needs each delivered session. An accounts user reviewing sales needs the package invoice counted once.


## MODULE 9 — SCREENS 23–25

Reports and administration

This area brings together operational reports, the audit trail, and system settings. Give each staff group access to the functions required for its work.


### 23 Booking & OT Reports

Report bookings and the activity the clinic currently calls “OT”.

**Screen details.** For bookings, show date, time, patient, service, assigned staff, and status. Filter by date, service, and staff. The meaning of OT and its required columns are pending.

**Staff actions and rules.** Drill from a booking total to the matching appointments. Ask the clinic for an anonymized OT report, its counting rules, and the event that creates each entry before designing that view.


### 24 Audit Log

Help an authorized reviewer establish who made a material change.

**Screen details.** Show timestamp, staff account, action, affected record reference, and reason where required. Candidate events include rescheduling, invoice reversal, stock adjustment, and package corrections.

**Staff actions and rules.** Filter by date, staff member, module, or record. Restrict viewing to designated users. Keep ordinary staff actions from altering audit entries and limit sensitive detail in the event description.


### 25 Settings

Maintain operational values used across the application.

**Screen details.** Candidate groups include clinic details, working hours, time slots, service catalogue and rates, invoice format and numbering, payment methods, and staff access. Confirm which groups the existing clinic administrator maintains.

**Staff actions and rules.** Apply access by setting group. Record who changes rates or permissions. Test that a new service price applies to future bills and that existing issued documents retain the billed amount.

Shared account actions

Change password and Sign out are visible throughout the photographed menu. Confirm who manages staff credentials. After sign-out, returning to a protected page on the same phone should require authentication.


## CROSS MODULE WORKFLOWS — ACCEPTANCE

Clinic walkthrough and acceptance checks

Use a fictional patient and test records to walk through these scenarios. The expected results are proposed acceptance checks. The clinic should agree the underlying rules before using them for sign-off.

| Scenario | Demonstration and expected result |
|---|---|
| Booking and return visit | Register a patient, reserve a slot, mark arrival, complete a consultation, and book a review. Both visits appear under one patient ID. Each consultation is linked to the correct visit. |
| Overlapping resources | Try two bookings for the same person or required room at overlapping times. Once the first booking is saved, the second user sees the conflict and can select another time. |
| Pharmacy checkout | With 10 units available, dispense 2. Retry after a simulated interrupted response. One issued invoice remains, and the stock balance is 8. Reprinting leaves stock unchanged. |
| Partial collection | Issue a service invoice for 1,000. Collect 600 today and 400 later. The due balance falls from 400 to zero. The two receipt dates appear separately, and total billed remains 1,000. |
| Package redemption | Start with 7 sessions. On the agreed redemption event, use one session. The balance becomes 6 and the visit is linked. Retry completion, then test cancellation using the agreed restoration rule. |
| Home visit assignment | Assign a therapist to a home visit, then check their availability for an overlapping clinic booking. Reassign the visit and verify that both staff schedules reflect the change. |
| Insurance follow-up | Move one sample record through the clinic’s approved stages. The case history preserves each change, and the dashboard count links to the case in its current state. |
| Permissions and phone use | On a phone, complete a booking using preset and custom times. Verify that reception can see the approved visit details and that clinical notes and administrative actions respect the agreed access rules. |

Reconcile reports before approval

Choose a single test day and trace dashboard totals to the bookings, invoices, receipts, returns, and package sessions behind them. Resolve disagreements over dates and counting rules with the person who owns the report.


## CLIENT REVIEW — PENDING DECISIONS

Decisions and materials for sign off

A clinic representative can close each item by supplying a blank form, an anonymized sample, or a demonstration. The suggested owner is the person best placed to explain the current process.

| Suggested owner | Question or material to provide | What it determines |
|---|---|---|
| Reception lead | Demonstrate one new booking, one reschedule, and one cancellation. Identify shared rooms, staff, breaks, and travel buffers. | Booking fields, resource conflicts, schedule changes, and statuses. |
| Lead practitioner | Provide the consultation form, prescription layout, and follow-up instructions. Identify who may view and amend each part. | Clinical fields, note visibility, history, and handoffs. |
| Pharmacy lead | Show a delivery, a pack sold in smaller units, a return, and a sample medicine invoice. Explain batch and expiry tracking. | Stock units, dispensing rules, invoice corrections, and valuation of returns. |
| Accounts lead | Provide clinic and pharmacy invoices, receipts, tax report, and a partial-payment example. Clarify patient and insurer dues. | Invoice boundaries, payment allocation, adjustments, and report calculations. |
| Insurance coordinator | Show one case from opening to closure, including where updates come from. | Status meanings, reference fields, financial links, and any integration. |
| Therapy coordinator | Demonstrate a package sale, a missed session, and a home visit. Explain expiry, travel fees, and eligibility. | Session balances, exceptions, home visit billing, and scheduling. |
| Clinic manager | Identify who uses each report, define OT, and provide sample management and OT reports. Confirm staff permissions. | Reporting scope, management measures, and access approval. |
| System owner | Identify data needed at launch and provide a sample export with identifying details removed. | Migration feasibility and reconciliation of patient histories, stock, dues, and unused sessions. |

Approval record

Record the agreed decisions, reviewed forms and report versions, reviewer names, and approval date. Carry open decisions forward with a named owner before estimating the affected screens.

