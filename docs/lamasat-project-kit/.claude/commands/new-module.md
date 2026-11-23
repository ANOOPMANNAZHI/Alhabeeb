---
description: Scaffold a domain module skeleton
argument-hint: <ModuleName>
---

Scaffold the `$ARGUMENTS` module following `docs/architecture.md`:

- `app/Domain/$ARGUMENTS/{Models,Actions,Data,Enums,Events,Listeners,Policies,Queries,Exceptions}`
- `app/Http/Controllers/$ARGUMENTS/` and `app/Http/Requests/$ARGUMENTS/`
- `resources/js/Pages/$ARGUMENTS/` with `Index.vue` using `AppLayout`, `PageHeader`, `FilterBar`, `DataTable`
- `routes/web/$ARGUMENTS.php` (kebab-case URLs) included from `routes/web.php`
- `tests/Feature/$ARGUMENTS/`

Do not add business logic; leave TODOs that reference the brief. Show me the tree when done.
