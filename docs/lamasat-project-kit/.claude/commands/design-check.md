---
description: Compare a built page with the prototype and design system
argument-hint: <page, e.g. Front Desk dashboard>
---

Use the **ui-ux-reviewer** subagent to compare "$ARGUMENTS" with the same screen in `docs/prototype/index.html` and with `docs/design-system.md`. If the app is running, capture Playwright screenshots at 1440 px and 400 px in light and dark. Return findings by severity with file:line.
