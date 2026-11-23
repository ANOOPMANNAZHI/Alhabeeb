---
description: Review the current changes with code, UI and security reviewers
argument-hint: [git range, default: working tree]
---

Review $ARGUMENTS (if nothing is given, review the current working tree diff).

Run in parallel:
- **code-reviewer** on the diff
- **ui-ux-reviewer** on any changed `resources/js/**` pages
- **security-privacy-auditor** if the diff touches auth, policies, uploads, exports, patient or clinical data

Merge the findings into one list ranked blocker → should fix → nit with file:line and the fix. Do not change code unless I ask.
