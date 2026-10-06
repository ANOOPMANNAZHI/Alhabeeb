# Receipt flag: Direct deposit to landlord — design

Date: 2026-10-06 · Status: approved by user ("ok go ahead")

## Goal
Some tenants pay the landlord directly (cash, transfer, or a cheque in the landlord's name). The income is the
landlord's; we only earn commission. Today cashiers type "DIR DEP IN LL A/c" into the cheque-no text and pick bank
32 (Cash In Transit). Give them a proper yes/no flag on the receipt so these can be identified.

## Out of scope (user decisions)
- AX posting: unchanged. Bank selection: unchanged.
- Reports: unchanged. Deducting these in the Normal Management report waits for the user's confirmation.
- Existing receipts are not flagged automatically (no data changes); they can be marked by hand.
- PDC table: no flag.

## Data
Migration `2026_10_06_000004_add_landlord_direct_deposit_to_receipts` adds to `receipts_generation`:
- `receipts_generation_paid_to_landlord` boolean not null default false
- `receipts_generation_landlord_marked_by` integer null (users.id)
- `receipts_generation_landlord_marked_at` timestamp null

Plus permission `mark_receipt_landlord_deposit`, given to the roles that hold `request_approval_receipt`.

## Behaviour
- Rent / Deposit / General receipt forms (create + edit) get a checkbox "Direct deposit to landlord".
  Saving sets the flag; marked_by / marked_at change only when the flag's value changes.
- Receipt view: badge "Paid directly to landlord" (with who/when) and, for users with the permission, a
  Mark / Unmark button (POST `receiptLandlordDeposit`) that changes only the flag, for any receipt status
  (also approved/posted). Never touches amounts, status or posting.
- Receipt lists (main tab list): marker next to the receipt no and a filter "Direct deposit to landlord: All / Yes / No".

## Testing
Feature tests: flag saved on create for rent/deposit/general, kept/cleared on edit, toggle route needs the
permission and records who/when, list filter returns only flagged receipts.
