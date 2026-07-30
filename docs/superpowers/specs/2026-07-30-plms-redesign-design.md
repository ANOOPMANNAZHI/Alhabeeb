# PLMS Redesign: Laravel API + Next.js Frontend

**Date:** 2026-07-30  
**Status:** Approved  
**Scope:** Complete rewrite of current Laravel 5.7 Blade monolith into a Laravel 11 REST API backend + Next.js 15 frontend, reusing the existing database.

---

## 1. Overview

The current PLMS (Property/Lease Management System) is a Laravel 5.7 monolithic application with 6 nwidart modules (BackOffice, General, Maintenance, Masters, Menu, Sales), Blade templating, and ~100+ database entities. Migration files are outdated — many fields were added directly to the live database.

The redesign produces two independent projects:
- `plms-api` — Laravel 11 REST API (Sanctum auth, Spatie Permission)
- `plms-web` — Next.js 15 frontend (Ant Design 5, Tailwind CSS)

Both will live in new folders outside the current project directory on a separate server.

---

## 2. Repository & Folder Structure

```
C:\laragon\www\
├── plms_backup\        ← existing app (untouched during development)
├── plms-api\           ← new Laravel 11 API
└── plms-web\           ← new Next.js 15 frontend
```

### plms-api structure
```
plms-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     ← base API controllers
│   │   └── Middleware/
│   └── Models/
├── Modules/
│   └── {Module}/
│       ├── Http/Controllers/Api/
│       ├── Models/
│       ├── Services/            ← business logic
│       ├── Routes/api.php
│       └── Transformers/        ← API Resources
├── database/
│   └── migrations/              ← reverse-engineered from live DB
├── config/
│   └── sanctum.php
└── routes/api.php
```

### plms-web structure
```
plms-web/
├── app/
│   ├── (auth)/login/
│   ├── (dashboard)/
│   │   ├── masters/
│   │   ├── sales/
│   │   ├── backoffice/
│   │   └── maintenance/
│   └── layout.tsx
├── components/
│   ├── ui/                      ← shared Ant Design wrappers
│   └── modules/                 ← module-specific components
├── lib/
│   └── api.ts                   ← Axios client with Sanctum
└── types/                       ← TypeScript types from API shapes
```

---

## 3. Tech Stack

### Backend (plms-api)
| Concern | Package |
|---|---|
| Framework | Laravel 11 |
| Auth | Laravel Sanctum (SPA cookie-based) |
| Permissions | spatie/laravel-permission |
| Activity Log | spatie/laravel-activitylog |
| Excel Export | maatwebsite/excel |
| PDF Export | barryvdh/laravel-dompdf or mpdf/mpdf |
| Module structure | nwidart/laravel-modules |
| Model generation | reliese/laravel (from live DB) |
| Migration generation | kitloong/laravel-migrations-generator |
| QR Code | simplesoftwareio/simple-qrcode |

### Frontend (plms-web)
| Concern | Package |
|---|---|
| Framework | Next.js 15 (App Router) |
| Language | TypeScript (strict) |
| UI Components | Ant Design 5 |
| Styling | Tailwind CSS |
| HTTP Client | Axios (withCredentials + withXSRFToken) |
| Server State | TanStack React Query |
| Global State | Zustand (auth session, permissions) |

---

## 4. Authentication

**Method:** Laravel Sanctum SPA authentication (cookie-based, no tokens stored in localStorage)

**Flow:**
1. `GET /sanctum/csrf-cookie` — obtain CSRF token
2. `POST /api/auth/login` — submit credentials, receive session cookie
3. All subsequent requests include cookie + `X-XSRF-TOKEN` header automatically
4. `POST /api/auth/logout` — invalidate session

**Configuration:**
- `SANCTUM_STATEFUL_DOMAINS` = Next.js domain (e.g. `plms-web.yourdomain.com`)
- `SESSION_DOMAIN` = shared root domain
- CORS configured to allow Next.js origin with credentials

**Route Protection:**
- Next.js middleware: redirect unauthenticated users to `/login`
- Laravel middleware: `auth:sanctum` on all `/api/v1/*` routes
- Role checks: `middleware('role:admin')` or `middleware('permission:buildings.create')`

---

## 5. API Design

### Conventions
- Base prefix: `/api/v1/`
- RESTful resource routes per module
- All responses in consistent envelope:

```json
{
  "data": { },
  "message": "Success",
  "status": 200
}
```

- Paginated lists:
```json
{
  "data": [ ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 450
  }
}
```

- Validation errors:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["The field is required."]
  }
}
```

### Route Examples
```
GET    /api/v1/masters/buildings
POST   /api/v1/masters/buildings
GET    /api/v1/masters/buildings/{id}
PUT    /api/v1/masters/buildings/{id}
DELETE /api/v1/masters/buildings/{id}

GET    /api/v1/sales/tenants
GET    /api/v1/sales/tenant-contracts
POST   /api/v1/backoffice/invoices
GET    /api/v1/backoffice/receipts/{id}/pdf    ← streams PDF
GET    /api/v1/backoffice/reports/receivables/excel ← streams Excel
```

### API Resources (Transformers)
Every model has a corresponding Laravel API Resource class for consistent, typed response shaping. No raw model serialization exposed to the frontend.

---

## 6. Database Strategy

### Problem
Current migration files are incomplete — many columns were added directly to the live database without migrations. The live DB is the source of truth.

### Solution: Reverse-engineer from live database

**Steps:**
1. Export full schema from live DB: `mysqldump --no-data plms_db > schema.sql`
2. In `plms-api`, install `kitloong/laravel-migrations-generator`
3. Run: `php artisan migrate:generate` — generates one migration per table reflecting actual columns
4. Install `reliese/laravel`, run: `php artisan code:models` — generates Eloquent models with fillable, casts, relationships
5. Verify: fresh `php artisan migrate` on clean DB reproduces identical schema
6. On new server: restore DB dump, point `plms-api` `.env` to it

### Module → Table Mapping
| Module | Key Tables |
|---|---|
| Masters | buildings, units, employees, banks, vendors, regions, locations, designations, amenity_types, unit_types, payment_methods, countries, nationalities |
| Sales | tenants, tenant_contracts, landlord_contracts, sales_enquiries, sales_activities, sales_users, sales_notes |
| BackOffice | invoices, receipts, pdcs, renewals, general_ledgers, deposit_refunds, landlord_invoices, landlord_payments |
| Maintenance | complaint_enquiries, amc_contracts, amc_schedules, amc_tasks, maintenance_invoices |
| General | workflows, workflow_processes, process_assigns, process_assign_users |
| Menu | menus |
| Core | users, roles, permissions, activity_log, logs, configuration, notifications |

---

## 7. Frontend Architecture

### Layout Structure
```
RootLayout
├── AuthLayout       → /login (no sidebar)
└── DashboardLayout  → all protected routes
      ├── Sidebar    ← menu items from /api/v1/menu (role-driven)
      ├── Header     ← user info, notifications, logout
      └── <Page />   ← module pages
```

### Permission-driven Menu
- Menu items fetched from `/api/v1/menu` after login
- Items filtered by user's Spatie permissions
- Zustand stores: `useAuthStore` (user, permissions), `useMenuStore` (nav items)

### Standard Module Page Pattern
Every module follows this consistent pattern:

**List page:**
- Ant Design `Table` with server-side pagination
- Search/filter bar above table
- Action buttons: Create (opens modal/drawer), Edit, Delete

**Create/Edit:**
- Ant Design `Form` inside a `Modal` or `Drawer`
- React Query `useMutation` for submit
- Optimistic updates or refetch on success

**Detail view:**
- `Descriptions` component or dedicated page
- Related records in nested tabs

### Reports & File Downloads
- PDF/Excel generated server-side by Laravel API
- Next.js triggers download:
```typescript
const blob = await api.get('/backoffice/reports/receivables/excel', {
  responseType: 'blob'
})
const url = URL.createObjectURL(blob.data)
// trigger <a> download
```

### API Client
```typescript
// lib/api.ts
const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' }
})
```

---

## 8. Build Order & Phases

### Phase 0 — Foundation
**plms-api:**
- Laravel 11 install, Sanctum + Spatie Permission + nwidart modules configured
- DB reverse-engineering: migrations + models generated
- Auth endpoints: `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`
- Base API Resource class + response format helper
- CORS configured

**plms-web:**
- Next.js 15 install with TypeScript
- Ant Design 5 + Tailwind CSS configured
- Axios client with Sanctum setup
- React Query + Zustand configured
- DashboardLayout + AuthLayout
- Login page + route middleware (auth guard)

### Phase 1 — Masters Module
Full CRUD API + frontend for:
Buildings, Units, Employees, Banks, Vendors, Regions, Locations, Designations, Amenity Types, Unit Types, Payment Methods, Countries, Nationalities

### Phase 2 — Sales Module
Tenants, Tenant Contracts, Landlord Contracts, Sales Enquiries, Sales Activities

### Phase 3 — Back Office Module
Invoices, Receipts, PDCs, Renewals, General Ledger, Deposit Refunds, PDF/Excel reports

### Phase 4 — Maintenance Module
Complaint management, AMC Contracts, Schedules, Workflow-driven processes

### Phase 5 — General / Workflow Module
Workflow engine, Process assignments, User assignments (reused across modules)

### Phase 6 — Dashboards & Reports
Role-based dashboards (CEO, MD, Sales Head, Facility Manager, Back Office Manager, Sales Coordinator, Sales Person), all specialized reports

### Phase 7 — Cutover
1. UAT on new system with production data copy
2. DB dump from old server → restore on new server
3. `plms-api` + `plms-web` deployed and configured on new server
4. DNS/reverse proxy switched to new server
5. Old system decommissioned

---

## 9. Deployment (New Server)

```
New Server
├── plms-api/    → PHP 8.2+, served by Nginx/Apache, port 8000 or subdomain
├── plms-web/    → Node.js, Next.js production build, port 3000 or subdomain
└── MySQL        → Restored DB from old server dump
```

- `plms-api` domain: `api.plms.yourdomain.com`
- `plms-web` domain: `plms.yourdomain.com`
- SSL on both
- `SESSION_DOMAIN=.yourdomain.com` (shared cookie domain)

---

## 10. Out of Scope

- Mobile app (may be added later — Sanctum token auth can be added when needed)
- GraphQL (REST is sufficient)
- Real-time features (WebSockets) — not in current system, not in redesign
- Legacy SOAP integration — to be revisited in Phase 3+ if needed
