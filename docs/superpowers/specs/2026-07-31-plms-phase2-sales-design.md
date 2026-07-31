# PLMS Phase 2 — Sales Module Design

**Date:** 2026-07-31  
**Scope:** Sales Enquiries, Tenants, Tenant Contracts, Landlord Contracts  
**Approach:** Full CRUD + status-based workflow (no role checks — Phase 5 adds permissions)

---

## 1. Architecture

Single nwidart module: `Modules/Sales/`, mirroring the Masters module structure.

### Module layout

```
Modules/Sales/
├── app/
│   ├── Models/
│   │   ├── Tenant.php              (table: tenant)
│   │   ├── TenantContract.php      (table: tenant_contracts)
│   │   ├── LandlordContract.php    (table: landlord_contract)
│   │   └── SalesEnquiry.php        (table: sales_enquiries)
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── TenantController.php
│   │   │   ├── TenantContractController.php
│   │   │   ├── LandlordContractController.php
│   │   │   └── SalesEnquiryController.php
│   │   └── Resources/
│   │       ├── TenantResource.php
│   │       ├── TenantContractResource.php
│   │       ├── LandlordContractResource.php
│   │       └── SalesEnquiryResource.php
│   └── Providers/
│       ├── SalesServiceProvider.php
│       └── RouteServiceProvider.php
├── Database/Factories/
│   ├── TenantFactory.php
│   ├── TenantContractFactory.php
│   ├── LandlordContractFactory.php
│   └── SalesEnquiryFactory.php
└── routes/api.php
```

Routes registered via `RouteServiceProvider::mapApiRoutes()` under prefix `api/v1` with `auth:sanctum` middleware, identical to Masters module.

---

## 2. API Routes

### Sales Enquiries
```
GET    /api/v1/sales/enquiries
POST   /api/v1/sales/enquiries
GET    /api/v1/sales/enquiries/{id}
PUT    /api/v1/sales/enquiries/{id}
DELETE /api/v1/sales/enquiries/{id}
```

**List filters:** `search` (name or enquiry no), `sales_type` (1=tenant, 2=landlord), `per_page`

### Tenants
```
GET    /api/v1/sales/tenants
POST   /api/v1/sales/tenants
GET    /api/v1/sales/tenants/{id}
PUT    /api/v1/sales/tenants/{id}
DELETE /api/v1/sales/tenants/{id}
```

**List filters:** `search` (name or tenant code), `per_page`

### Tenant Contracts
```
GET    /api/v1/sales/tenant-contracts
POST   /api/v1/sales/tenant-contracts
GET    /api/v1/sales/tenant-contracts/{id}
PUT    /api/v1/sales/tenant-contracts/{id}
DELETE /api/v1/sales/tenant-contracts/{id}
PATCH  /api/v1/sales/tenant-contracts/{id}/transition
```

**List filters:** `search` (contract no), `building_id`, `unit_id`, `tenant_contract_status`, `tenant_renewal_termination_status`, `per_page`

### Landlord Contracts
```
GET    /api/v1/sales/landlord-contracts
POST   /api/v1/sales/landlord-contracts
GET    /api/v1/sales/landlord-contracts/{id}
PUT    /api/v1/sales/landlord-contracts/{id}
DELETE /api/v1/sales/landlord-contracts/{id}
PATCH  /api/v1/sales/landlord-contracts/{id}/transition
```

**List filters:** `search` (contract no or name), `building_id`, `vendor_id`, `landlord_contract_status`, `per_page`

---

## 3. Workflow

### Tenant Contract state machine

Field: `tenant_renewal_termination_status` (integer)  
Secondary field: `tenant_contract_status` (0=inactive, 1=active)

| Action | From status | Sets renewal_status | Sets contract_status |
|--------|-------------|--------------------|--------------------|
| `activate` | 0 | 0 | 1 |
| `request-renewal` | 0 | 1 | — |
| `approve-renewal` | 1 | 5 | — |
| `reject-renewal` | 1 | 4 | — |
| `request-termination` | 1 | 7 | — |
| `terminate` | 7 | 8 | 0 |

Invalid transitions return `422 Unprocessable Entity` with message `Invalid transition`.

**Endpoint:** `PATCH /api/v1/sales/tenant-contracts/{id}/transition`  
**Body:** `{ "action": "activate" }` (validated against allowed values)

### Landlord Contract state machine

Field: `landlord_contract_status` (integer)

| Action | From status | Sets status |
|--------|-------------|-------------|
| `approve` | 0 | 1 |
| `reject` | 1 | 2 |

**Endpoint:** `PATCH /api/v1/sales/landlord-contracts/{id}/transition`  
**Body:** `{ "action": "approve" }`

---

## 4. Auto-numbering

Generated server-side on `store`; client never sends these fields.

| Entity | Field | Format | Logic |
|--------|-------|--------|-------|
| SalesEnquiry | `sales_enquiry_no` | `TTENQ{n}` (tenant) / `LENQ{n}` (landlord) | `enquiry_index` = max(enquiry_index per sales_type) + 1 |
| SalesEnquiry | `enquiry_index` | integer | max per `sales_type` + 1 |
| Tenant | `tenant_code` | derive from max existing | max(`tenant_code` numeric suffix) + 1 |
| TenantContract | `tenant_contract_no` | `TAG{n}` | max numeric suffix of existing + 1 |
| LandlordContract | `landlord_contract_no` | `LC-{n}` | max numeric suffix + 1 |

`created_by` always set from `$request->user()->id`.  
`work_flow_processes_code` defaults to `108` (Phase 5 will wire real workflow engine).

---

## 5. Resources (fields returned)

### TenantResource
id, tenant_code, tenant_name, tenant_type_id, tenant_contact_no, tenant_contact_email, tenant_contact_address, tenant_secondary_address, tenant_pc, location_id, tenant_company_name, tenant_acc_no, tenant_status, bank_id, resident_id, passport_no, com_reg_no, gsm_no, nationalities_id, tenant_resident_exp_date, tenant_gender, tenant_date_of_birth, tenant_employer_name, tenant_personal_email, tenant_ice_name, tenant_ice_contact_no, designation, status, created_at, updated_at

### SalesEnquiryResource
id, sales_enquiry_no, enquiry_index, sales_enquiry_name, sales_email, sales_contact_address, sales_region, tenant_type_id, sales_no_of_unit, sales_mobile_no, sales_company_name, sales_move_in_date, building_type_id, sales_type, sales_size, sales_mode_id, sales_referred_by, sales_note, enquiry_owner, assigned_person, work_flow_processes_code, created_at, updated_at

### TenantContractResource
id, tenant_contract_no, tenant_id, occupant_id, building_id, unit_id, sale_enquiry_id, tenant_contract_address, tenant_contract_no_members, tenant_contract_duration, tenant_contract_duration_type, tenant_contract_muncipality_agr_no, tenant_contract_agreement_amt, tenant_contract_start_date, tenant_contract_effective_date, tenant_contract_valid_from_date, tenant_contract_valid_to_date, tenant_contract_payment_type, tenant_contract_status, tenant_contract_registered_in, tenant_contract_deposit_amt, tenant_contract_receipt_no, tenant_contract_receipt_date, tenant_contract_note, tenant_contract_rent, tenant_contract_value, tenant_contract_penalty, tenant_penalty_start_date, tenant_penalty_valid_to_date, tenant_penalty_invoice_amt, pdc_check, deposit_check, tenant_renewal_termination_status, tenant_contract_is_reg_municipality, status, work_flow_processes_code, created_at, updated_at

### LandlordContractResource
id, landlord_contract_no, landlord_contract_name, building_id, vendor_id, sale_enquiry_id, landlord_contract_address, landlord_contract_duration, landlord_contract_duration_type, landlord_contract_management_fee, landlord_contract_percentage, landlord_contract_amt, landlord_contract_agreement_amt, landlord_contract_payment_type, landlord_contract_status, management_id, landlord_marketing_executive, landlord_contract_note, start_date, end_date, landlord_contract_valid_from_date, landlord_contract_valid_to_date, landlord_free_lease_period, management_fee_type, management_method, landlord_renewal_termination_status, user_id, work_flow_processes_code, created_at, updated_at

---

## 6. Frontend Pages

### Routes
```
/sales/enquiries          — Sales Enquiries list + CRUD modal
/sales/tenants            — Tenants list + CRUD modal
/sales/tenant-contracts   — Tenant Contracts list + CRUD modal + workflow buttons
/sales/landlord-contracts — Landlord Contracts list + CRUD modal + workflow buttons
```

### Sidebar (`menuStore.ts`)
```
Masters
  ├── Buildings
  ├── Units
  ├── Employees
  └── Lookups
Sales
  ├── Enquiries          → /sales/enquiries
  ├── Tenants            → /sales/tenants
  ├── Tenant Contracts   → /sales/tenant-contracts
  └── Landlord Contracts → /sales/landlord-contracts
```

### List columns

**Enquiries:** Enquiry No, Name, Mobile, Type (Tenant/Landlord), Move-in Date, Assigned Person, Created At

**Tenants:** Code, Name, Contact No, Email, Company, Status

**Tenant Contracts:** Contract No, Tenant ID, Building ID, Unit ID, Start Date, End Date, Rent, Contract Status, Renewal Status

**Landlord Contracts:** Contract No, Building ID, Vendor ID, Start Date, End Date, Amount, Status

### Workflow buttons
Shown in Actions column alongside Edit/Delete. Only valid next actions rendered based on current status:

| Current renewal_status | Buttons shown |
|------------------------|---------------|
| 0 (Normal) | Activate, Request Renewal |
| 1 (Requested) | Approve Renewal, Reject Renewal |
| 5 (Renewed) | Request Termination |
| 7 (Termination Req.) | Terminate |
| 4, 8 | — (terminal states) |

### Form fields (Option B — all meaningful)

**Enquiry form:** sales_enquiry_name*, sales_mobile_no*, alternative_no, sales_email, sales_contact_address, sales_type* (tenant/landlord select), tenant_type_id, sales_region, sales_no_of_unit, sales_move_in_date, building_type_id, sales_size, sales_mode_id, sales_referred_by, sales_company_name, enquiry_owner, assigned_person, sales_note

**Tenant form:** tenant_name*, tenant_contact_no, tenant_contact_email, tenant_contact_address, tenant_secondary_address, tenant_pc, location_id, tenant_type_id, bank_id, tenant_acc_no, tenant_company_name, resident_id, passport_no, com_reg_no, gsm_no, nationalities_id, tenant_resident_exp_date, tenant_gender, tenant_date_of_birth, tenant_employer_name, tenant_personal_email, tenant_ice_name, tenant_ice_contact_no, tenant_post_box, designation, tenant_status

**Tenant Contract form:** tenant_id* (select tenant), building_id*, unit_id*, sale_enquiry_id, tenant_contract_address, tenant_contract_no_members, tenant_contract_duration, tenant_contract_duration_type, tenant_contract_start_date*, tenant_contract_valid_from_date, tenant_contract_valid_to_date*, tenant_contract_payment_type*, tenant_contract_rent*, tenant_contract_agreement_amt, tenant_contract_deposit_amt, tenant_contract_receipt_no, tenant_contract_receipt_date, tenant_contract_receipt_amt, tenant_contract_muncipality_agr_no, tenant_contract_registered_in, tenant_contract_note, pdc_check, deposit_check, tenant_contract_penalty, tenant_penalty_start_date, tenant_penalty_valid_to_date, unit_usage

**Landlord Contract form:** building_id*, vendor_id*, sale_enquiry_id, landlord_contract_name*, landlord_contract_address, landlord_contract_duration, landlord_contract_duration_type, landlord_contract_payment_type*, landlord_contract_amt, landlord_contract_agreement_amt, landlord_contract_management_fee, landlord_contract_percentage, management_fee_type, management_method, management_id, start_date*, end_date*, landlord_contract_valid_from_date, landlord_contract_valid_to_date, landlord_free_lease_period, landlord_marketing_executive, landlord_contract_note

*required fields

### React Query hooks
```
hooks/sales/useEnquiries.ts         — useEnquiries, useCreateEnquiry, useUpdateEnquiry, useDeleteEnquiry
hooks/sales/useTenants.ts           — useTenants, useCreateTenant, useUpdateTenant, useDeleteTenant
hooks/sales/useTenantContracts.ts   — useTenantContracts, useCreateTenantContract,
                                      useUpdateTenantContract, useDeleteTenantContract,
                                      useTransitionTenantContract
hooks/sales/useLandlordContracts.ts — useLandlordContracts, useCreateLandlordContract,
                                      useUpdateLandlordContract, useDeleteLandlordContract,
                                      useTransitionLandlordContract
```

---

## 7. Testing

```
tests/Feature/Sales/
├── SalesEnquiryTest.php      — 6 tests: list, create, show, update, delete, unauthenticated
├── TenantTest.php            — 6 tests: list, create, show, update, delete, unauthenticated
├── TenantContractTest.php    — 12 tests: CRUD (6) + workflow (activate, request-renewal,
│                               approve-renewal, reject-renewal, request-termination,
│                               terminate) + invalid transition (422)
└── LandlordContractTest.php  — 9 tests: CRUD (6) + workflow (approve, reject) + invalid (422)
```

Total: ~33 tests

**Factory dependencies:**
- `TenantContractFactory` creates a `Tenant` record first (same pattern as `UnitFactory` → `BuildingFactory`)
- `LandlordContractFactory` uses hardcoded `building_id=1`, `vendor_id=1` (FK constraints removed in Phase 1)

---

## 8. Out of Scope for Phase 2

- Role-based access control (Phase 5)
- Real workflow engine integration (Phase 5) — `work_flow_processes_code=108` hardcoded
- PDC management (Phase 3)
- Invoice generation (Phase 3)
- Renewal form documents (Phase 3)
- `sales` table (workflow step table used by old engine, not needed until Phase 5)
- `tenant_contract_status` sub-table (Phase 5)
- Email notifications
