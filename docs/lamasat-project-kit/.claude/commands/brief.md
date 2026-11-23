---
description: Write the requirement brief for a spec screen
argument-hint: <screen number, e.g. 02>
---

Use the **product-spec-analyst** subagent to write `docs/briefs/$ARGUMENTS-*.md` for spec screen $ARGUMENTS.

It must read `docs/spec/functional-spec.md`, `docs/spec/screens.md`, `docs/decisions.md`, `docs/permissions.md` and the matching screen in `docs/prototype/index.html`, then produce Goal, Users and access, Fields, Actions and rules (each citing spec or decision), Handoffs, Acceptance checks (Given/When/Then) and Open questions with owners.

Finish by listing the open questions to me so I can take them to the clinic.
