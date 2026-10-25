# PLMS Legal — Design System (MASTER)

Source of truth for every Legal module screen (case lists, approvals, case page, stages master, modals, reports).
Generated with ui-ux-pro-max (`legal case management back-office ... --density 8 --motion 2 --variance 3`) and curated:
the tool's suggested "Exaggerated Minimalism" style (oversized type) and Fira Code headings were rejected — wrong for a
dense back-office tool and off-brand next to the rest of PLMS.

## Style
**Data-Dense Dashboard** (styles.csv): KPI cards row, compact tables with sticky headers and row hover, filter bar,
minimal padding (8–16px), maximum information without clutter. Status colours carry meaning, always with a text label.

## Scope & implementation
- Laravel 5.7 Blade, Bootstrap 4 + jQuery, existing `layouts.plms-app` shell (blue sidebar, Roboto, Material inputs).
- All styles live in `public/css/legal.css`, scoped under `.lg` (page wrapper) so no other module changes.
- Include with `@section('css') <link href="{{ asset('public/css/legal.css') }}" rel="stylesheet"> @endsection`.
- Existing JS hooks on list pages are kept: `.search_fields`, `.sort_url`, `#enquiry-search`, `#pagination_ajax`,
  `#url_route`, `#show` (advanced search), `#myModal` (ajax modals).

## Tokens (CSS variables on `.lg`)
| Token | Value | Use |
|---|---|---|
| `--lg-primary` | `#2563EB` | primary actions, links, current stage |
| `--lg-primary-strong` | `#1D4ED8` | hover / pressed |
| `--lg-accent` | `#059669` | positive money: collected, paid, completed |
| `--lg-ink` | `#0F172A` | titles, key values |
| `--lg-text` | `#334155` | body |
| `--lg-muted` | `#64748B` | labels, meta (4.6:1 on white) |
| `--lg-border` | `#E2E8F0` | card / table borders |
| `--lg-subtle` | `#F1F5F9` | table header, KV background |
| `--lg-bg` | `#F8FAFC` | page background inside cards |
| success / warning / danger / info / neutral | `#047857/#ECFDF5`, `#B45309/#FFFBEB`, `#B91C1C/#FEF2F2`, `#1D4ED8/#EFF6FF`, `#475569/#F1F5F9` | badges, alerts (text / background, all ≥ 4.5:1) |

Spacing scale 4 · 8 · 12 · 16 · 24 · 32. Radius 8 (cards), 6 (inputs/buttons), 999 (badges).
Type: Roboto (app font). 20/600 page title · 15/600 card title · 14 body · 13 table · 12 labels/meta (never smaller).
Numbers: `font-variant-numeric: tabular-nums`, right-aligned, 3 decimals (OMR) via `numberFormat()`.

## Components
`lg-page-head` (title, sub-line, status chips, actions) · `lg-card` (+`__head`, `__body`) · `lg-kpis`/`lg-kpi` ·
`lg-kv` (key/value grid) · `lg-badge--{success,warning,danger,info,neutral}` + `lg-prio--{1,2,3}` · `lg-btn--{primary,secondary,ghost,danger,warning}` ·
`lg-table` (sticky head, hover, `.num`, empty state) · `lg-filters` · `lg-stepper` · `lg-timeline` · `lg-alert--*` · `lg-tabs` · `lg-modal`.

## Rules
- Contrast ≥ 4.5:1 for text; status never by colour alone (badge text always present).
- Visible focus ring (`:focus-visible`, 2px primary) on buttons, links, inputs.
- Buttons ≥ 36px high, 8px apart; icon-only buttons get `aria-label`.
- Labels above inputs (no placeholder-only labels); errors next to the field or in the modal's alert.
- Motion: 150–200ms colour/shadow transitions only; `prefers-reduced-motion` disables them.
- Tables scroll horizontally inside their card (`.lg-table-wrap`), never the page.
- Responsive checks: 375, 768, 1024, 1440.
