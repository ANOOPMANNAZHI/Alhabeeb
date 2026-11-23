# Domain model

Money columns are `*_paise` BIGINT. ⟂ = append-only ledger. Enums in brackets.

## People and catalogue
- **users** — name, username, mobile, email?, is_active; roles via spatie. **staff_profiles** (user_id, kind [practitioner|therapist|other], colour, registration_no).
- **patients** — patient_code (LMS-00001), name, mobile, alt_mobile, gender, dob?, age_years?, age_is_approximate, address, locality, notes, consent_at, legacy_ref.
- **services** — name, kind [consultation|therapy], duration_min, rate_paise, needs_room_type [consult|therapy|null], home_eligible, sac_code, tax_category_id, is_active.
- **rooms** — name, type [consult|therapy], is_active.
- **staff_shifts** — user_id, weekday, starts_at, ends_at, break_starts_at, break_ends_at, effective_from/to.
- **blocked_periods** — user_id?, room_id?, date, starts_at, ends_at, reason.

## Front desk
- **bookings** — booking_no, patient_id, service_id, staff_id, room_id?, date, starts_at, duration_min, status [booked|arrived|in_progress|completed|cancelled|no_show], notes, follow_up_id?, patient_package_id?, created_by.
- **booking_events** ⟂ — booking_id, from_status, to_status, reason, old_date/time, new_date/time, by, at.
- **follow_ups** — patient_id, source_booking_id, reason, due_on, assigned_staff_id, owner_id, status [open|booked|closed|reopened], booking_id?.
- **follow_up_attempts** ⟂ — follow_up_id, outcome, note, by, at.

## Clinical
- **consultations** — booking_id (unique), patient_id, practitioner_id, status [draft|completed], complaint, history, prakriti, vikriti, agni, nadi, bp, weight_kg, impression, plan, review_on, review_reason, completed_by, completed_at.
- **consultation_amendments** ⟂ — consultation_id, reason, text, by, at.
- **medicine_advice** — consultation_id, medicine_id, quantity, instructions.
- **dispensing_items** — consultation_id, patient_id, status [pending|dispensed|not_available], medicine_invoice_id?.

## Pharmacy
- **medicines** — code, name, form, purchase_pack, dispensing_unit, units_per_pack, price_paise (per unit), hsn_code, tax_category_id, is_active.
- **stock_lots** — medicine_id, batch_no, expiry_on, received_on.
- **stock_movements** ⟂ — stock_lot_id, type [receipt|sale|return|adjustment|write_off|opening], quantity (± units), reference_type/id, reason, by, at.
- **medicine_invoices** — invoice_no, issued_on, patient_id? / counter_name, consultation_id?, subtotal/tax/total_paise, status [issued|reversed], idempotency_key.
- **medicine_invoice_lines** — invoice_id, medicine_id, stock_lot_id, quantity, rate_paise, discount_paise, tax_rate_bp, tax_paise, line_total_paise.

## Billing
- **clinic_invoices** — invoice_no, issued_on, patient_id, subtotal/discount/tax/total_paise, status, package_sale_id?.
- **clinic_invoice_lines** — invoice_id, booking_id?, service_id, description, quantity, rate_paise, discount_paise, covered_by_package_id?, tax fields.
- **payments** ⟂ — receipt_no, received_on, patient_id, method [cash|upi|card|bank_transfer|insurance], amount_paise, reference (UPI ref / card last 4).
- **payment_allocations** ⟂ — payment_id, invoice_type, invoice_id, amount_paise.
- **reversals** ⟂ — document_type, document_id, reason, stock_disposition [restock|write_off]?, by, at.
- **number_sequences** — type, financial_year, next_value.
- **tax_categories** — name, rate_bp, is_exempt.

## Packages
- **package_types** — name, sessions, price_paise, validity_days, is_active; **package_type_service** pivot.
- **patient_packages** — patient_id, package_type_id, purchased_on, valid_to, sessions_total, clinic_invoice_id.
- **package_redemptions** ⟂ — patient_package_id, booking_id / home_visit_id, redeemed_on, restored_at?.

## Home service
- **home_visits** — visit_no, patient_id, address, locality, landmark, contact_mobile, service_id, date, starts_at, duration_min, staff_id?, status [unassigned|scheduled|completed|cancelled], travel_charge_paise, notes, cancel_reason.

## Insurance
- **insurance_cases** — case_no, patient_id, insurer, insurer_ref, booking_id? / invoice ref, status, owner_id, claimed_paise, remarks.
- **insurance_status_changes** ⟂ — case_id, from_status, to_status, changed_on, reason, evidence_path?, by.

## Platform
- **settings**, **activity_log** (spatie), **idempotency_keys**, **schedule_locks** (staff/room + date sentinel rows), **otp_codes**.
