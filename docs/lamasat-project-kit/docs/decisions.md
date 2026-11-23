# Decisions log

Newest entry wins over the spec. Each item: **id · status · owner · decision · screens**. Status: OPEN (needs the clinic), ASSUMED (prototype default, build it but confirm), AGREED.

## Prototype assumptions (build as default, confirm with the clinic)
| ID | Status | Owner | Decision | Screens |
|---|---|---|---|---|
| D-01 | ASSUMED | Clinic manager | Clinic in Kochi; INR; dates dd/mm/yyyy; Asia/Kolkata; closed Sundays; hours Mon–Sat 9 AM–9 PM | All |
| D-02 | ASSUMED | Reception lead | Preset start times 10:00 AM, 01:00 PM, 04:00 PM, 07:00 PM plus custom; editable in Settings | 02, 25 |
| D-03 | OPEN | Lead practitioner | Consultation fields: complaint, history, Prakriti, Vikriti, Agni, Nadi, BP, weight, impression, plan, medicine advice, review. Needs the clinic's blank form | 07 |
| D-04 | ASSUMED | Reception lead | Booking statuses: Booked, Arrived, In progress, Completed, Cancelled, No-show | 01, 02, 23 |
| D-05 | ASSUMED | Reception lead | Consultations reserve a practitioner + consult room; therapies a therapist + therapy room | 02, 06 |
| D-06 | ASSUMED | Therapy coordinator | Travel buffer 30 minutes before and after a home visit reduces availability | 06, 20, 21 |
| D-07 | ASSUMED | Lead practitioner | Clinical notes visible to Practitioner, Clinic Manager, Admin only; reception sees outcome only | 04, 07 |
| D-08 | ASSUMED | Lead practitioner | Completing a consultation auto-creates a dispensing item (if medicines) and a follow-up (if review date) | 05, 07, 09 |
| D-09 | ASSUMED | Reception lead | A cancelled booking linked to a follow-up reopens that follow-up | 02, 05 |
| D-10 | ASSUMED | Reception lead | Family members may share a mobile; duplicate check warns but allows a separate profile | 03 |
| D-11 | ASSUMED | Pharmacy lead | Medicine sale is blocked when stock is insufficient | 09 |
| D-12 | ASSUMED | Pharmacy lead | Track batch and expiry per stock lot; FEFO | 08, 09 |
| D-13 | ASSUMED | Pharmacy lead | Reversal chooses restock (sealed) or write-off; returns reported on processing date | 10, 11 |
| D-14 | OPEN | Accounts lead | GST: clinic healthcare services exempt; Ayurvedic medicines 5% (placeholder). Confirm categories, HSN/SAC, rounding, export format | 09, 12, 15 |
| D-15 | ASSUMED | Insurance coordinator | One insurance case = one approval request; stages Pre-approval requested → Approved → Claim submitted → Query raised → Rejected / Settled | 16, 17 |
| D-16 | ASSUMED | Accounts lead | Due Amounts shows clinic and pharmacy balances; insurer balances stay in Insurance | 13 |
| D-17 | ASSUMED | Therapy coordinator | Package session is used when the linked visit is completed; cancellation restores it; home visits of eligible services qualify | 18, 19, 20 |
| D-18 | ASSUMED | Accounts lead | Payment methods: Cash, UPI, Card, Bank transfer, Insurance | 09, 12, 13 |
| D-19 | ASSUMED | Therapy coordinator | Home visit travel charge ₹200 default, editable per visit | 20 |
| D-20 | ASSUMED | Clinic manager | Package sale counts once in invoiced revenue; each delivered session counts in completed visits | 22 |
| D-21 | OPEN | Clinic manager | Meaning of "OT", its columns and counting rules — OT report blocked until defined | 23 |
| D-22 | ASSUMED | Clinic manager | Sign-in by username + password or mobile OTP; passwords reset by the administrator | Sign in |
| D-23 | OPEN | System owner | Data to migrate at launch and sample export | Migration |
| D-24 | OPEN | Accounts lead | Final invoice and receipt model (one invoice per visit vs per day; combined clinic + pharmacy invoice?) | 09, 12, 13 |

## Agreed
_None yet. Add entries with `/decision`._
