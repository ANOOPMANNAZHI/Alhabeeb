# Renting workflow (tenant) + ARE performance and allowance — design

Status: draft for approval, 19/10/2026. Tenant renting only. Landlord leasing is out of scope.

## 1. Why

- **Renting takes too many steps.** A new rental passes 8 enquiry stages. Four of them (Unassigned → Assigned → In Progress → Preliminary Doc) are clicked through in the same minute: the median time in each is 0 days for the 1,309 enquiries won since 2025.
  - There is a second, parallel "direct contract" path with its own form and approval menu: 442 contracts since 2025.
  - Rent, dates, payment term and deposit are typed twice.
  - Cheques are typed by hand.
  - The work after "Won" (PDC, municipality, deposit receipt) is spread over separate menus with no reminder.
- **Renewals take too many screens.** There are about 4,400 renewals since 2025, three times the new rentals. Only 23 needed approval, yet every renewal goes through 4 screens.
- **The user wants the sales manager to track each ARE's performance and pay the ARE's allowance from it.** "There should not be any gaps and vulnerabilities": an ARE must not be able to inflate their own numbers, and the figures must not change silently after payment.
- **Today, nothing in PLMS calculates performance or allowance.** Credit for a new letting comes from the free "marketing executive" pick on the contract. For 2025+ new contracts that pick was an ARE 578 times, maintenance staff 723, an accountant 97, a technician 35, and so on. It cannot be used for pay.

## 2. Goals

1. **One renting workflow** for new rentals. The enquiry path and the direct path merge into it. It has 3 working steps:
   - Draft
   - Approve
   - Complete and activate
2. **Renewal in 2 steps** for normal cases: Renew from the Due list, then Activate. Approval is only needed for exceptions.
3. **Every figure is entered once.** The PDC schedule is generated from rent × payment term.
4. **A monthly ARE scorecard** that the system calculates. Nobody types or picks the figures.
   - The sales manager reviews it.
   - Finance approves the allowance from it.
5. **Figures are frozen after approval.** Any later change shows as an adjustment in the next month and never rewrites a paid month.
6. **Every rule that pays money has a control** (section 6).

## 3. Non-goals and anti-goals

**Non-goals:**
- Landlord leasing.
- Payroll. PLMS produces the approved allowance list (screen and Excel); salary payment stays where it is.
- Sales person commission. See open question Q2.
- Rewriting existing contracts or history. Old contracts stay as they are; the new flow applies to contracts created after go-live.

**Anti-goals** (it fails if any of these happens, even if it "works"):
- An ARE can raise their own score by choosing who gets credit, back-dating, splitting a contract, or re-letting the same tenant.
- An allowance is paid for a letting or renewal whose money never arrived.
- A paid month's numbers change without a visible adjustment.
- The same person both makes and approves a contract, a scorecard or a payout.
- Staff need more clicks than today.

## 4. Roles

Existing role names are kept.

| Role | In this design |
|---|---|
| sales_person | Owns enquiries (leads). Can start a Draft. |
| are | Owns the buildings assigned in `are_buildings` (`assign_from` / `assign_too`). Starts Drafts and renewals for those buildings only. Sees only their own scorecard. |
| are_team_lead | Sees their team's scorecards. Approves renewal exceptions (as today, stage 302). |
| sales_head = "sales manager" | Approves new rentals. Reviews and submits ARE scorecards. Maintains allowance rules (with finance approval). |
| backoffice_executive / manager | Completion step (municipality, deposit receipt, documents, cheque numbers). Activates. |
| finance_manager | Approves allowance rule changes and each month's payout. |
| super_admin | Cannot approve anything they made (same rule as everyone). |

The design names `sales_head` as the "sales manager". If a different role is meant, see Q1.

## 5. The workflows

### 5.1 New rental

1. **Lead (optional).** An enquiry, as today. The Unassigned / Assigned / In Progress list stays as a lead tracker for sales. It is no longer required before a contract.
2. **Draft** (sales_person, or the ARE of the building). One form:
   - Tenant: pick an existing tenant or create one. Fields are prefilled from the enquiry: name, mobile, email, type.
   - Unit: only vacant units, or units vacating before the start date.
   - Rent, start date, term, payment term, deposit and marketing source.
   - **The PDC schedule is generated** (dates and amounts). At this stage staff only add cheque numbers and bank, or leave them for Completion.
   - **The unit is NOT marked occupied yet.** It is shown as "Reserved" (draft-held) for at most N days (default 7). After that the draft expires and the unit is released.
3. **Approve** (sales_head; the person who made the draft cannot approve it). Approve, Refer back with a note, or Reject.
   - Auto-flags that must be acknowledged before approving:
     - Rent below the unit's last rent or the minimum rent.
     - Start date more than 7 days in the past.
     - The same tenant left the same unit less than 90 days ago (a re-let, not a new letting).
     - Free months or a discount.
     - Deposit below one month's rent.
4. **Complete and activate** (backoffice). The contract page shows a checklist:
   - Cheque numbers for every PDC row.
   - Deposit receipt (the receipt itself, not typed contract fields).
   - Municipality number and file.
   - Occupant and documents.

   Activation is blocked until the required items are done; which items are required is configurable. On activation:
   - Unit occupied.
   - Tenant pushed to AX.
   - Vacancy loss closed.
   - Contract status Active.
   - Workflow code 108, so existing reports keep working.
5. **Counted for performance** only when the first rent receipt is posted and approved (section 6).

### 5.2 Renewal

1. **Due** (ARE). As today, contracts ending in 90–120 days appear on the Due list for the building's ARE.
   - The ARE must choose **Renew** or **Vacating** by D-60 (60 days before the end date).
   - If no choice has been made by D-60, it shows as overdue on the ARE's scorecard and on the team lead's and manager's dashboards.
2. **Renew** opens the renewal contract form, prefilled:
   - New dates.
   - Rent = rent revisal amount, or the renewal form, or the current rent (existing `RentRevisal::renewalRent` rule).
   - The PDC schedule.

   The ARE confirms the form.
   - **Normal renewal** (rent ≥ current and term ≥ 12 months): no approval. It goes straight to Completion (cheque numbers, municipality).
   - **Exception** (rent reduction, short term, other): approval by are_team_lead or sales_head, as today's stage 302.
3. **Activate.** The new contract becomes active on its start date. The existing cron is kept.
4. **Counted** when the first receipt of the new contract is posted.

**Vacating** feeds the existing termination flow (501…505). The vacant days from then on count against the building's occupancy (section 7).

### 5.3 What is removed or merged

- The separate direct-contract path, form and approval menus are replaced by 5.1.
- Typing rent, dates and payment term again at Final Documentation is removed.
- The contract fields for deposit amount, receipt no, receipt date and receipt amount are replaced by the deposit receipt.
- Municipality entry moves to the Completion checklist. The other three places it could be typed become read-only.
- Unit occupancy is set in one place: activation.
- The "marketing executive" pick stays as information only. **It is never used for credit.**

## 6. Controls (no gaps)

| # | Gap | Control |
|---|---|---|
| C1 | ARE chooses who gets credit | Credit is fixed by the system: the ARE assigned to the building (in `are_buildings`) on the **activation date**. Nobody can edit it. If a building changes ARE mid-month, each item goes to the ARE on that item's date. |
| C2 | Counting a letting whose money never came | A letting or renewal counts only when the **first rent receipt is posted and approved**. Deposit receipt is required too (configurable). |
| C3 | Bounced first cheque / early exit | **Clawback**: if the first cheque bounces and is not replaced within 30 days, or the contract is terminated / closed within 90 days, the item is reversed as a negative adjustment in the month it happens. |
| C4 | Back-dating | A start date more than 7 days before the draft date is flagged at approval and counts in the month it is **activated**, never in a closed month. Future-dated system dates are rejected (today's data has `sales_enquiries.created_at` in 2027–2054). |
| C5 | Re-letting the same tenant to look like a new letting | Same tenant + same unit within 90 days = renewal / re-let, not a new letting. |
| C6 | Splitting one deal into many | One contract per unit per period. Overlapping contracts on a unit are blocked. Short-term (< 6 months) lettings score at a reduced weight. |
| C7 | Cheap deals to hit numbers | Rent below the unit's last rent or minimum needs approval with a reason. The scorecard shows rent achieved vs last rent, and a below-target letting scores less. |
| C8 | Maker approves own work | Draft maker ≠ approver. Scorecard: system calculates → sales_head submits → finance_manager approves. Rule changes: sales_head proposes → finance_manager approves. Nobody approves their own allowance. Super admin follows the same rule. |
| C9 | Numbers change after payment | **Month lock**: the scorecard is calculated on day 3 for the previous month and frozen with every underlying item (contract, receipt no, amount). Any later change (bounce, reversal, late receipt, correction) is an adjustment in the next open month with a reason. |
| C10 | ARE sees or acts on others' buildings | Lists and actions are scoped to the user's buildings (as done for inspection). Fix today's broken permission checks: middleware attached to method names that don't exist in TenantStageController, SalesEnquiryController and TenantRenewalController; TenantContractController has no permission check at all. |
| C11 | Silent edits | Every stage change, approval, flag acknowledgement, rule change and scorecard action is written to an audit log (who, when, before/after). Scorecard items link to the source record. |
| C12 | Rule edited to suit a month | Allowance rules have an effective-from date and are versioned. A month is always calculated with the rules in force for that month. Changing a past month's rules is not possible. |
| C13 | Collection inflated by recording receipts not yet banked | Collections count **posted, approved** receipts. Bounced cheques reverse. Cash receipts count on deposit. |
| C14 | Units held in draft to block others | A draft reserve expires after 7 days (configurable). Expired and rejected drafts count on the scorecard as information. |

## 7. ARE scorecard (monthly, per ARE)

Everything is calculated over the ARE's buildings on each item's date (C1).

| Measure | Definition | Source |
|---|---|---|
| New lettings | Contracts activated in the month that pass C2, minus clawbacks (C3) | tenant_contracts + receipts |
| Renewals done | Renewals activated (counted per C2) / contracts due in the month | renewals + tenant_contracts |
| On-time renewal | Renew/Vacating decided by D-60, and the new contract complete before the old one ends | renewals dates |
| Rent achieved | New rent vs previous rent, for renewals and new lettings | contracts |
| Occupancy | Occupied unit-days / total unit-days | units + contracts (existing vacancy-loss data) |
| Vacancy days | Average days a unit stood vacant before being let | vacancy loss |
| Collection | Receipts posted in the month / rent due in the month (existing rent outstanding rules) | RentOutstanding, receipts |
| Overdue receivables | Amount over 60 days at month end | receivable follow-up |
| Cheques in hand | Active contracts whose remaining term is fully covered by PDCs | pdc |
| Penalties | Clawbacks, missed D-60 decisions, legal cases opened | as above |

**Allowance** = sum of rule lines. Each line is either:
- an amount per item (e.g. per new letting, per on-time renewal), or
- a slab on a percentage (e.g. collection ≥ 95% → X, 90–95% → Y).

The lines are capped per ARE per month and gated (e.g. no allowance when collection < Z%). The rule table is configurable (C12); the amounts are not decided in this design (Q3).

**Screens:**
- **ARE:** "My scorecard". The current month runs live (marked provisional); past months are frozen. Each figure drills down to its contracts and receipts.
- **Team lead / sales manager:** all AREs side by side, ranked, with trend over 6 months and drill-down. Submit button for the month.
- **Finance:** month approval, Excel of the payout, adjustments list.

## 8. Constraints

- Laravel 5.x, PHP < 7.4 on live (no arrow functions), PostgreSQL. Existing modules.
- Keep workflow codes 101–109 and 300–305 readable, so existing reports, dashboards and the AX push keep working. New contracts still end at 108 / `tenant_contract_status = 1`.
- No change to existing contract data. Migrations add new tables and columns only.
- Live upload is manual via `server_upload_files`. Each phase must work on its own.

## 9. Delivery in phases (builder may refine)

1. **Controls first:** permission fixes (C10), audit log (C11), the future-date guard (C4), occupancy set at activation only. No new screens.
2. **Renewal 2-step** with the generated PDC schedule.
3. **New rental 3-step**, merging the direct path, with the Completion checklist.
4. **Scorecard** (live and frozen months), with drill-down and manager view.
5. **Allowance rules**, approval chain and payout Excel.

## 10. Left to the builder

Table and column names; screen layout (follow the legal UI style, `design-system/plms-legal/MASTER.md`); how the scorecard is stored (snapshot rows per item recommended); cron timing; how existing in-progress enquiries at stages 101–107 are carried over (finish in the old flow is acceptable).

## 11. Calls made by the designer (user to check)

- **D1. Which month an item counts in.**
  - An item counts in the month its last condition is met. For a letting or renewal that is usually the month the first rent receipt is approved.
  - Credit goes to the ARE of the building on the **activation date** (C1).
  - A receipt approved after a month is locked counts in the next open month. Locked months are never reopened for this.
- **D2. "Posted and approved" receipt.**
  - Means: `receipts_generation_approval_status` in (3 approved, 6 sent to AX), not cancelled, not deleted.
  - A cheque that later bounces reverses the item in the month of the bounce (C3).
  - A cash receipt counts on its approved receipt.
- **D3. No separate composite score.**
  - The manager view ranks by allowance and shows every measure side by side.
  - Weights (short-term, below-last-rent) are lines in the versioned rule table (C12), not fixed in code.
- **D4. Renewal completion.**
  - Backoffice adds cheque numbers and municipality, as today. That is their checklist, not an extra ARE step.
  - The start-date cron still activates the contract, so receipts never block.
  - An incomplete checklist shows as overdue for backoffice.
  - The renewal counts for the ARE only after its first receipt (D1).
- **D5. Permission fixes (C10).**
  - First map who uses each screen today (from the role grants on live).
  - Then enforce only those grants, so nobody loses access they use.
  - Tightening beyond that is a separate, announced step.
- **D6. "Reserved".**
  - A lock on the draft side only: the unit cannot be drafted twice.
  - Other modules keep seeing it as vacant until activation, so vacancy loss and reports are unchanged.
- **D7. Deposit receipt requirement.**
  - Only for new lettings.
  - Renewals carry the deposit over and are never blocked by it.
  - Old contracts are untouched.

## 12. Open questions for the user

(see conversation)
