# Design system (from the approved prototype)

Open `docs/prototype/index.html` to see every component in context. Port these tokens into `resources/css/app.css` as CSS variables and Tailwind theme values.

## Layout
- Inset-sheet shell: navigation on the sage canvas, working screen on a rounded white sheet (radius 18 px). Sidebar 236 px, grouped by the 9 modules with 15 px line icons; current page = white pill with saffron dot.
- Top bar: breadcrumb, global patient search (`/` to focus), role/user menu.
- Page: title (Bricolage 30/700), one-line description, actions right; content max-width 1240 px; gaps 14–18 px.
- Lists: FilterBar card above a table card. Detail: full page with “← Back to …”. Forms: right drawer (540 px, rounded, blurred scrim).

## Colour tokens
| Token | Light | Dark | Use |
|---|---|---|---|
| canvas | #E9ECE5 | #0D100E | app background |
| bg | #F7F8F5 | #131714 | sheet |
| surface | #FFFFFF | #191E1A | cards |
| surface-2 | #F3F5F0 | #1F2520 | table heads, inputs bg |
| ink | #141815 | #EDF1EC | text |
| muted | #646C66 | #99A39B | secondary text |
| line / line-2 | #E6E9E2 / #D5DAD1 | #262D28 / #333C35 | borders |
| brand (neem) | #1E4D33 | #8FD4A6 | primary buttons, links |
| accent (saffron) | #E9A93A | #F0B955 | highlights, current item |
| ok / warn / bad / info / violet | #21794A / #A86A06 / #C0392B / #2A62A8 / #7048B5 | lighter variants | status only |
| chart s1–s5 | #2a78d6 #eb6834 #1baf7a #eda100 #e87ba4 | #3987e5 #d95926 #199e70 #c98500 #d55181 | categorical, fixed order |

## Type
Headings and big figures: **Bricolage Grotesque** (600–800). UI text: **Geist** 14 px. IDs, times, codes: **Geist Mono**. Tabular numbers in tables and KPIs.

## Components
PageHeader · FilterBar (search, SearchSelect, DatePicker range, count, “Clear filters (n)”) · DataTable (sticky head, row click → detail) · StatusPill (dot + text, colour per status map) · KpiCard (label, value, ▲/▼ % vs previous, subtitle, sparkline, click → list) · Drawer · Modal · DatePicker (calendar popover, Today/Clear, shows “Mon, 23/11/2026”) · SearchSelect (type to filter, highlighted match, keyboard) · MoneyText · PatientCell (initials avatar) · Timeline · ScheduleLanes (availability) · Toast (pill, bottom centre) · EmptyState · SignIn (split screen, password + OTP).

## Charts
One y-axis; thin marks; bars radius 4; lines 2 px with endpoint dot and direct label; recessive solid grid; legend with values for ≥2 series; hover tooltip on every mark; categorical colours in fixed order (never by rank); status colours only for status; segmented 100% bar for part-to-whole (≤ 6 parts) instead of donuts.

## Copy
User’s words, active voice. Buttons say what happens (“Issue receipt”). Toasts confirm (“Receipt RC/2026-27/000123 issued”). Errors explain and say how to fix. Dates dd/mm/yyyy, money ₹1,23,456.00, mobiles 98470 23441.
