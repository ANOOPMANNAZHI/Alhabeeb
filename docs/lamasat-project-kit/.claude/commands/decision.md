---
description: Record a clinic decision and update affected briefs
argument-hint: <decision text, e.g. "D-11: allow sale with insufficient stock — Pharmacy lead">
---

Use the **product-spec-analyst** subagent to record this decision in `docs/decisions.md`: $ARGUMENTS

Add date (today), owner, the decision, screens affected, and mark any superseded ASSUMED entry as replaced. Then update the affected briefs in `docs/briefs/` and list code or tests that now need to change.
