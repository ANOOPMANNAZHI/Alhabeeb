# LAMASAT Ayurveda Clinic — project memory for Claude Code

Web application for **Lamasat Ayurveda Clinic, Panampilly Nagar, Kochi, Kerala**. It replaces the clinic's current app (25 menu entries in 9 modules) and is built from the functional specification in `docs/spec/` and the approved clickable prototype in `docs/prototype/index.html`.

## Stack
- **Backend:** Laravel 12+ (latest stable), PHP 8.3+, MySQL 8, Redis (cache, queue), Horizon
- **Frontend:** Inertia.js 2 + Vue 3 (`<script setup lang="ts">`) + TypeScript + Tailwind CSS 4 + Vite (Laravel Vue starter kit)
- **Key packages:** spatie/laravel-permission, spatie/laravel-activitylog, barryvdh/laravel-dompdf, maatwebsite/excel, brick/money, Pest 3, Larastan, Laravel Pint
- **Locale:** India — currency INR stored as integer **paise**, shown `₹1,23,456.00`; timezone `Asia/Kolkata`; dates `dd/mm/yyyy`; times `10:00 AM`; mobiles `+91 98470 23441`; GST

## Where things are
| Need | Read |
|---|---|
| What each screen must do | `docs/spec/functional-spec.md`, `docs/spec/screens.md` |
| Agreed answers and assumptions | `docs/decisions.md` (newest wins over the spec) |
| Tables, relations, ledgers | `docs/domain-model.md` |
| Folders, patterns, packages | `docs/architecture.md` |
| Who can do what, audited events | `docs/permissions.md` |
| Look and feel, components, charts | `docs/design-system.md`, `docs/prototype/index.html` |
| Test scenarios to prove | `docs/acceptance-scenarios.md` |
| Order of work | `docs/build-plan.md` |
| Per-screen requirements | `docs/briefs/` (written by `product-spec-analyst`) |

## Subagents (`.claude/agents/`)
Delegate by area. Typical flow for a screen: `product-spec-analyst` (brief) → `laravel-architect` (plan) → `database-engineer` → module specialist + `laravel-backend-engineer` → `vue-frontend-engineer` → `test-engineer` → `ui-ux-reviewer` + `code-reviewer` (+ `security-privacy-auditor` for auth/PII/exports).

| Agent | Area |
|---|---|
| product-spec-analyst | briefs, open questions, decisions log |
| laravel-architect | architecture, patterns, dependencies, ADRs |
| database-engineer | migrations, indexes, factories, seeders |
| laravel-backend-engineer | actions, requests, controllers, policies, events |
| vue-frontend-engineer | Inertia/Vue pages and shared components |
| ui-ux-reviewer | prototype fidelity, copy, a11y, responsive |
| auth-permissions-specialist | login, OTP, roles, policies, audit |
| booking-scheduling-specialist | screens 01–06 |
| clinical-consultation-specialist | screen 07 |
| pharmacy-inventory-specialist | screens 08–11 |
| billing-accounts-specialist | screens 12–15, numbering, GST |
| packages-homecare-insurance-specialist | screens 16–21 |
| reports-analytics-engineer | dashboards and reports (01, 16, 18, 21, 22, 23) |
| test-engineer | Pest, acceptance scenarios, Playwright |
| code-reviewer | diff review |
| security-privacy-auditor | OWASP, DPDP Act 2023 |
| devops-release-engineer | Sail, CI, deploy, backups |
| data-migration-specialist | import from the old system |

## Slash commands (`.claude/commands/`)
`/brief <screen>` · `/build-screen <screen>` · `/new-module <Module>` · `/acceptance <scenario>` · `/review` · `/decision <text>` · `/design-check <page>`

## Non-negotiable rules
1. **Never invent business rules.** If the spec and `docs/decisions.md` do not answer it, list the question with its owner and stop that part.
2. **Money is integer paise** through `App\Support\Money`. No floats anywhere in money paths.
3. **Issued documents are immutable** (invoices, receipts, completed consultations, audit entries). Corrections are reversals or amendments with a reason.
4. **Every write path**: FormRequest → policy → Action in `DB::transaction()` → locks where needed → audit → domain event. Controllers stay thin.
5. **Idempotency keys** on creating invoices, receipts, stock movements and bookings; a retry returns the original result.
6. **Booking conflicts** are checked over the full duration against staff, room, patient, breaks, blocked time and home visits (+ travel buffer), under a lock.
7. **Gap-free document numbers** per type per Indian financial year, allocated in the issuing transaction.
8. **Reports state their date basis** and every total opens the records behind it.
9. **UI follows the prototype**: shared components, tokens only, FilterBar on lists, full detail pages with Back, drawers for forms, works at 400 px and in dark mode.
10. **Patient data is personal data** (DPDP Act 2023): least privilege, no PII in logs, exports permissioned and audited.

## Commands
```bash
composer run dev                      # server + queue + vite + logs
php artisan migrate --seed            # local only
./vendor/bin/pest --parallel          # tests
./vendor/bin/pint && ./vendor/bin/phpstan analyse
npm run typecheck && npm run build
```

## Definition of done (per screen)
Brief exists → migrations + factories → actions + policies → Vue page matching prototype → Pest tests incl. acceptance and authorisation → Pint/PHPStan/typecheck clean → UI review and code review findings resolved → `docs/screens.md` status updated.
