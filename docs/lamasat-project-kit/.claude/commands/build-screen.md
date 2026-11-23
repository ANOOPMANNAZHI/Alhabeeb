---
description: Build a spec screen end to end with the right subagents
argument-hint: <screen number or 00-foundation>
---

Build screen $ARGUMENTS following `docs/build-plan.md` and the Definition of done in `CLAUDE.md`.

1. If `docs/briefs/$ARGUMENTS-*.md` does not exist (and this is not `00-foundation`), run `/brief $ARGUMENTS` first and stop if it has blocking open questions.
2. **laravel-architect**: short plan — files, migrations, actions, events, components, which agent does each step.
3. **database-engineer**: migrations, models, factories, seeders.
4. The module specialist (see the table in `CLAUDE.md`) with **laravel-backend-engineer**: actions, requests, policies, controllers, routes, events.
5. **vue-frontend-engineer**: page(s) matching the prototype, using shared components.
6. **test-engineer**: Pest tests incl. authorisation and any acceptance scenario for this screen; run them.
7. **ui-ux-reviewer** and **code-reviewer** in parallel; fix blockers and should-fix items.
8. Run Pint, PHPStan, typecheck, tests. Update the status column in `docs/spec/screens.md`.

Report what was built, test results with output, and anything left open.
