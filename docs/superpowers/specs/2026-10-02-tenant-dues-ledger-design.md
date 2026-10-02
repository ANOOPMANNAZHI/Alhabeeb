# Tenant Dues Ledger — design

Agreed with the user 2026-10-02.

## Problem

The Tenant Aging Report (database functions `tenantrentreceivable*_v2`) and the
Receivables Follow-up (`ReceivableFigures`) each work out what a tenant owes in
their own way, so the same contract shows different amounts. A comparison on
plms_live (02/10/2026) matched only 2,602 contracts. These were the problems:

- The Aging report keeps charging rent every month after an active contract's
  end date and never caps it at the contract value (~140 contracts, e.g.
  TAG2500918 223,614).
- The Aging report run with a date only drops contracts whose only receipt is
  a deposit (~25).
- The Aging report counts one month too few when a termination is still in
  progress (steps 501–503) and ends in the contract's end month.
- The Aging report counts receipts and terminations dated after the as-on date.
- The Follow-up uses rent × months, not the contract value, for contracts that
  ended without being terminated (11).
- The Follow-up only counts payments up to its cutoff, so tenants who have
  already paid are still listed for follow-up.
- The Aging report's buckets are calendar months worked out from "paid till",
  not real days overdue.

## Answers from the user

- A bounced cheque never creates a receipt, so rent receipts = rent actually paid.
- A tenant who stays after the contract ends is taken over manually: no rent
  is added after the end date, and the contract isn't flagged.

## Design

One calculation, used by both reports.

**Contracts:** approved (`work_flow_processes_code = 108`), in active buildings
(`building_status = 1`), not in `legal`, effective date on or before the as-on date.

**Rent lines (`RentLedger`):** one line per rent period, due on the day the
period starts (the effective date, then the same day each month, with no month
overflow). A line counts if it starts on or before the as-on date and on or
before the contract end date. For a contract terminated (505, termination date
on or before the as-on date), the termination date takes the place of the end
date.

**Total due:**
- Terminated by the as-on date: the contract value if terminated on the end
  date; otherwise days × rent / 30, where days = termination − start − 1,
  rounded half to even (the existing v2 rule). A termination date before the
  start date gives 0.
- Ended by the as-on date and not terminated: the contract value.
- Still running: lines × rent, never more than the contract value.

The lines are trimmed from the latest one, or the last line is topped up, so
they add up to the total.

**Paid:** rent receipts (`receipts_generation_type = 0`, not deleted) dated on
or before a "receipts up to" date. Deposits are not rent, but a contract with
only a deposit still counts. Payments clear the oldest lines first. Balance =
max(0, due − paid).

**Aging report:** as-on date = the picked date; receipts up to the same date.
Every balance above 0 is shown. Each unpaid line is aged by real days
(as-on − due date) into 0–30, 31–60, 61–90, 91–120, 121–180 and 180+. The
buckets always add up to the balance.

**Receivables Follow-up:** dues up to the cutoff (picked month − 2); receipts up
to **today**. Contracts owing 30 or more are listed. "Not Paid From" = due date
of the oldest unpaid line. The cache is rebuilt daily.

## Out of scope

- The legacy Rent Receivable V2 report (PDF/Excel) stays on its database
  functions for now. Moving it onto the same calculation is a follow-on.
- The Legal Receivable report.
