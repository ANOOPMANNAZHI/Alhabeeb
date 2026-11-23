---
description: Write and run a spec acceptance scenario as a Pest test
argument-hint: <scenario name>
---

Use the **test-engineer** subagent to implement the acceptance scenario "$ARGUMENTS" from `docs/acceptance-scenarios.md` as `tests/Feature/Acceptance/<Name>Test.php`, using factories and the real actions (no mocks of domain logic). Run it and report the exact output. If behaviour is missing, list which screen/agent must implement it; do not weaken the test.
