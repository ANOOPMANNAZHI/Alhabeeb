# LAMASAT Clinic — Claude Code project kit

Everything Claude Code needs to build the Lamasat Ayurveda Clinic application in **Laravel + Vue (Inertia)**: project memory, 18 specialist subagents, slash commands, permissions, and the spec, decisions, data model, design system and build plan as docs. The approved clickable prototype is included for reference.

## Contents
```
CLAUDE.md                       project memory, loaded automatically by Claude Code
.claude/
  settings.json                 permissions (allow artisan/composer/npm/pest; deny .env, db wipes) + Pint hook
  agents/                       18 subagents (see CLAUDE.md for the list)
  commands/                     /brief /build-screen /new-module /acceptance /review /decision /design-check
docs/
  spec/functional-spec.md       the clinic's specification (text)
  spec/LAMASAT_Clinic_Web_Application_Specification.docx   original
  spec/screens.md               25 screens: route, roles, prototype reference, status
  decisions.md                  open decisions D-01…D-24 + prototype assumptions
  architecture.md               folders, patterns, approved packages
  domain-model.md               tables, relations, ledgers, enums
  permissions.md                roles × screens/actions, audited events
  design-system.md              tokens, type, components, charts (from the prototype)
  acceptance-scenarios.md       the spec's 9 walkthrough scenarios as Given/When/Then
  build-plan.md                 phased plan with milestones
  briefs/                       per-screen briefs (empty; /brief fills it)
  adr/                          architecture decision records
  prototype/index.html          clickable prototype (open in a browser)
```

## Start the project
```bash
# 1. New Laravel app with the Vue starter kit (Inertia + Vue + TypeScript + Tailwind)
laravel new lamasat-clinic --vue --pest
cd lamasat-clinic

# 2. Copy this kit into the project root (merges CLAUDE.md, .claude/ and docs/)
cp -R /path/to/lamasat-project-kit/. .

# 3. Core packages
composer require spatie/laravel-permission spatie/laravel-activitylog barryvdh/laravel-dompdf maatwebsite/excel brick/money laravel/horizon
composer require --dev larastan/larastan

# 4. Open Claude Code in the project
claude
```

Then, in Claude Code:
```
/build-screen 00-foundation      # shell, auth, roles, settings, shared components
/brief 01                         # write the Dashboard brief
/build-screen 01                  # build it end to end
```
Follow `docs/build-plan.md` for the order. Record every clinic answer with `/decision`.

## Notes
- The Pint hook in `.claude/settings.json` needs `jq`; remove the `hooks` block if you do not have it.
- Prototype choices that the clinic has not confirmed are marked **ASSUMED** in `docs/decisions.md`. Review them with the clinic before Phase 1 is signed off.
