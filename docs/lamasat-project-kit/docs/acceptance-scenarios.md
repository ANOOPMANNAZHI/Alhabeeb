# Acceptance scenarios (from the spec, “Clinic walkthrough and acceptance checks”)

Each becomes `tests/Feature/Acceptance/<Name>Test.php`. Rules marked ASSUMED follow `docs/decisions.md`.

1. **Booking and return visit** — Given a new patient, when reception registers them, books a slot, marks arrival, the practitioner completes a consultation with a review date, and reception books the review from Follow-ups, then both visits are under one patient ID and each consultation links to its own visit.
2. **Overlapping resources** — Given two users booking the same practitioner or room for overlapping times, when the first saves, then the second gets a conflict naming the clash, keeps their entries, and can pick another time.
3. **Pharmacy checkout** — Given 10 units on hand, when 2 are dispensed and the request is retried after a lost response, then one invoice exists and stock is 8; reprinting leaves stock at 8.
4. **Partial collection** — Given a ₹1,000 service invoice, when ₹600 is collected today and ₹400 later, then the due balance goes 400 → 0, both receipts keep their dates, and billed stays ₹1,000.
5. **Package redemption** — Given a package with 7 sessions left, when the linked visit is completed, then balance is 6 and the visit is linked; completing again keeps 6; cancelling the visit restores 7 (D-17).
6. **Home visit assignment** — Given a therapist assigned to a home visit, when an overlapping clinic booking is attempted, then it is refused (incl. travel buffer); after reassigning the visit both schedules reflect the change.
7. **Insurance follow-up** — Given a case, when it moves through the approved stages, then history keeps every change and the dashboard count links to the case in its current state.
8. **Permissions and phone use** — On a 400 px screen, reception completes a booking with preset and custom times, sees visit details but not clinical notes; administrative actions follow the matrix.
9. **Reconcile reports** — For one test day, dashboard totals equal the sum of the bookings, invoices, receipts, returns and package sessions behind them, using each report’s stated date basis.
