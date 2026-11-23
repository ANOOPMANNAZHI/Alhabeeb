# Architecture

## Approach
Modular monolith. Laravel serves Inertia responses; Vue pages render them. No separate public API in v1 (add Sanctum + API resources later for a mobile app).

## Folders
```
app/
  Domain/
    FrontDesk/      Patient, Booking, FollowUp, StaffShift, BlockedPeriod, Room, Service
    Clinical/       Consultation, ConsultationAmendment, MedicineAdvice
    Pharmacy/       Medicine, StockLot, StockMovement, MedicineInvoice(+Line), DispensingItem
    Billing/        ClinicInvoice(+Line), Payment, PaymentAllocation, Reversal, NumberSequence
    Packages/       PackageType, PatientPackage, PackageRedemption
    HomeService/    HomeVisit
    Insurance/      InsuranceCase, InsuranceStatusChange
    Reports/        Queries only
    Admin/          Setting, User/Staff profile
    each module:    Models/ Actions/ Data/ Enums/ Events/ Listeners/ Policies/ Queries/ Exceptions/
  Http/Controllers/<Module>/   thin; one action per write
  Http/Requests/<Module>/
  Support/          Money, Idempotency, Numbering, Sms, Pdf, DateFormat
resources/js/
  Layouts/AppLayout.vue
  Components/       shared UI (see design-system.md)
  Pages/<Module>/   Index.vue, Show.vue, Create.vue …
  composables/      useFilters, useMoney, useDate, usePermissions
  types/            generated/handwritten TS types for Inertia props
routes/web/<module>.php
tests/Feature/<Module>/, tests/Feature/Acceptance/, tests/Unit/
```

## Patterns
- **Action classes** for every state change; invoked by controllers, jobs and import commands alike.
- **Data objects** (readonly classes) between request and action.
- **Domain events** for handoffs between modules; listeners are idempotent.
- **Money**: `App\Support\Money` wraps `brick/money` with INR and paise; casts `MoneyCast` on `*_paise` columns.
- **Numbering**: `Numbering::next('clinic_invoice', $date)` locks `number_sequences` row per type + financial year; format `CI/2026-27/000123`.
- **Idempotency**: middleware reads `Idempotency-Key` header (Inertia sends it from `useForm` via a composable); stores request hash + response for 24 h.
- **Concurrency**: `lockForUpdate()` on staff-day/room-day sentinel rows (`schedule_locks`) for bookings; on stock lots for sales.
- **Audit**: activitylog with `causer`, `subject`, `event`, `reason`, sanitized `properties`.
- **Settings**: `settings` table (group, key, value JSON) cached; groups match screen 25.
- **PDF**: dompdf Blade views in `resources/views/pdf/` (invoice, receipt, visit summary).
- **Exports**: maatwebsite/excel export classes reuse the same Query classes as the screen.

## Approved packages
spatie/laravel-permission · spatie/laravel-activitylog · barryvdh/laravel-dompdf · maatwebsite/excel · brick/money · laravel/horizon · (dev) larastan/larastan, pestphp/pest, laravel/pint. Frontend: Inertia Vue 3, Tailwind 4, @vueuse/core, a small chart layer written in SVG (as in the prototype) — no heavy chart library without an ADR.
