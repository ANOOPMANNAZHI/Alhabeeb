# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
Internal staff of a property management company in Oman (PLMS = Property Lease Management System).
- **ARE** (area relationship executive): looks after the tenants of their assigned buildings; follows up rent, renewals, bounced cheques.
- **ARE team lead / back office manager / admin**: see all buildings (team lead: their AREs' buildings) and supervise follow-up.
- Back office, accounts, legal, call centre and lawyers use other modules of the same app.

Confirmed for the Bounced Cheques page (2026-10-22): AREs and back office managers open it from their dashboard card to chase each open bounce until it is settled: call the tenant, get a replacement cheque or cash, make the bounce receipt, or redeposit the cheque.

## Product Purpose
Runs the whole lease life cycle for buildings the company manages for landlords: leads, tenant contracts, PDCs (post-dated cheques), receipts, renewals, terminations, legal cases, maintenance, landlord invoices, and posting to Microsoft Dynamics AX. Success is rent collected on time and every exception (bounce, overdue, expiry) followed up by the right person.

## Operating Context
- Desktop browsers in the office, long working sessions, dense tables; dashboards per role open filtered lists.
- Money is OMR with 3 decimals. Dates are shown dd/mm/yyyy.
- Tenants paying by PDC; a bounced cheque is settled by a bounce receipt, replaced by cash, or redeposited.

## Capabilities and Constraints
- Laravel 5.7 + Blade, Bootstrap 4, jQuery; shell `layouts.plms-app` (blue sidebar, Roboto).
- Live server is Windows WAMP with PHP below 7.4: no arrow functions, nullsafe or match in PHP.
- Data visibility follows role: an ARE sees only buildings currently assigned to them.

## Brand Commitments
- Redesigned screens follow the Legal module design system (`design-system/plms-legal/MASTER.md`, `public/css/legal.css`, scoped `.lg`) so they read as one family inside the existing PLMS shell. Confirmed by the user for the Bounced Cheques page, 2026-10-22.

## Product Principles
- Show what needs action, not everything that ever happened.
- Every number on a dashboard card matches the list it opens.
- Staff act from the list: the facts needed to follow up (who, how much, since when, how to reach them) sit on the row.
