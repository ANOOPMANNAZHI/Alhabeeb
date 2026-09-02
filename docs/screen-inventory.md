# PLMS Screen Inventory

Complete inventory of all screens (views), their routes, buttons/actions, filters, table columns, and form fields across the PLMS Laravel-Modules application. Generated to support a screen-by-screen redesign effort.

## Table of Contents

1. [Current Navigation Menu](#current-navigation-menu)
2. [BackOffice Module](#backoffice-module)
3. [Sales Module](#sales-module)
4. [Maintenance Module](#maintenance-module)
5. [Masters Module](#masters-module)
6. [General Module](#general-module)
7. [Menu Module](#menu-module)

---

## Current Navigation Menu

Live sidebar structure as stored in the `menu` table (managed dynamically via the [Menu Module](#menu-module) — Menu List / Add-Edit Menu screens), not hardcoded in views. **192 active items, 4 inactive** (shown but grayed out below). Each leaf item's route name links it back to its screen documentation in the sections below — search the route name (e.g. `tenantRenewal.index`) to jump to its entry.

- Dashboard — route: `dashboard`
- **Sales Module** `(fa-cart-plus)`
  - **Masters** `(fa-podcast)`
    - Price Range — route: `priceRange.index`
    - Country — route: `country.index`
    - Currency — route: `currency.index`
  - **Operations** `(fa-gamepad)`
    - **Renting Enquiry** `(fa-phone-square)`
      - Add Enquiry — route: `enquiry.index`
      - Tenant — route: `tenantEnquiries`
    - **Renting Enquiry Stage** `(fa-male)`
      - Unassigned — route: `leadAssign.index`
      - Assigned — route: `leadAssign.assignedList`
      - In-Progress — route: `inprogressList`
      - Preliminary Documentation — route: `documentationList`
      - Approval — route: `preliminaryApprovalList`
      - Won — route: `wonList`
      - Close — route: `closedList`
    - **Landlord Enquiry** `(fa-bars)`
      - Landlord — route: `landlordEnquiries`
    - **Landlord Enquiry Stage** `(fa-home)`
      - Add Enquiry — route: `enquiry.index`
      - Landlord Enquiry — route: `landlordLead.index`
      - Landlord Loss — route: `contractApprovalLoss`
      - Landlord Won — route: `contractApprovalWon`
  - **Reports** `(fa-folder)`
    - Sales Enquiry Report — route: `showSalesEnquiries`
- **PLM Module** `(fa-university)`
  - **Masters** `(fa-podcast)`
    - Unit Utility — route: `unit-utility.index`
    - Building Amenity — route: `building-amentity.index`
    - Building Insurance — route: `building-insurance.index`
    - Home Amenity — route: `homeUtility.index`
    - Management Type — route: `managementType.index`
    - Building Type — route: `buildingType.index`
    - Unit Type — route: `unitType.index`
    - Amenity Type — route: `amentityType.index`
    - Tenant Type — route: `tenantType.index`
    - Tenant Status — route: `tenantStatus.index`
    - Designation — route: `designation.index`
    - Location — route: `location.index`
    - Bank — route: `bank.index`
    - Payment Method — route: `paymentMethod.index`
    - Invoice Type — route: `invoiceType.index`
    - Reason Inactive Always — route: `reason.index` _(inactive)_
    - Enquiry Source — route: `enquirySource.index`
  - **Operations** `(fa-gamepad)`
    - Tenant — route: `tenants.index`
    - Building — route: `building.index`
    - Unit — route: `unit.index`
    - Final Documentation — route: `finalDocumentationList`
    - Tenant Contract — route: `tenant-contract.index`
    - Tenant Direct/Revoke Contract Pending List — route: `tenantContractPendingRevoke`
    - Tenant Direct/Revoke Contract Approve — route: `tenantContractApprovedRevoke`
    - **Tenant Renewal** `(fa-repeat)`
      - Due For Renewal — route: `tenantRenewal.index`
      - Contract Under Renewal — route: `tenantContract.underRenewal`
      - Contract Approval — route: `renewalContractApproval`
      - Contract Renewal — route: `tenantRenewalContract`
      - Renewed Contract — route: `tenantRenewedContract`
    - **Tenant Termination** `(fa-hourglass-end)`
      - Open For Termination — route: `tenantTermination.index`
      - Approval — route: `tenantTerminationApproval`
      - Handover Unassigned — route: `handoverUnassigned`
      - Handover Assigned — route: `handoverAssigned`
      - Takenover For Termination — route: `takeoverForTermination`
      - Terminated Contract — route: `tenantTerminatedContract`
    - Landlord Final Documentation — route: `landlordContract.index`
    - Landlord Contract Documentation Pending Approval — route: `landlordPendingApproval`
    - Landlord Documentation / Contract Approval — route: `contractApprovalList`
    - Landlord Contract — route: `landlord-contract.index`
    - **Landlord Renewal** `(fa-reply-all)`
      - Due for Renewal — route: `landlordRenewal.index`
      - Contract Renewal — route: `renewalContract`
      - Contract Approval — route: `landlordContractApproval`
      - Renewed Contract — route: `landlordContractRenewed`
    - **Landlord Termination** `(fa fa-cog)`
      - Open For Termination — route: `landlordTermination.index`
      - LC Termination Verification — route: `LCTerminationVerify`
      - Approval — route: `LCTerminationApproval`
      - Terminated Landlord Contract — route: `TerminatedContractLandlord`
    - ARE Building Assign — route: `areBuildingAssign.index`
    - Tenancy — route: `tenancyDetails.index`
    - **Key Module** `(icon-key)`
      - Key — route: `keyManagement.index`
      - Key Accept — route: `keyManagement.create`
    - **Legal** `(fa-gavel)`
      - Legal Case List — route: `legalCase.index`
      - Plms Approval — route: `plmsApproval`
      - Lawyer Approval — route: `lawyerApproval`
      - Active Cases — route: `activeCases`
      - Closed Legal Cases — route: `closedLegalCases`
    - ARE'S — route: `group_are_list`
    - **Transaction** `(fa-money)`
      - Cheque Bounce — route: `tenantPdcBounce`
      - Receipts — route: `receiptsTabViewList`
      - Receipts Approval — route: `receiptsRequestForApprovalTabViewList`
      - Landlord Invoice Approval — route: `landlordInvoiceApproval`
      - Landlord Payment — route: `landlordPayment.index`
      - Landlord Payment Approval — route: `landlordPaymentApproval`
      - Maintenance Invoice — route: `maintenanceInvoice.index`
      - Maintenance Invoice Approval — route: `maintenanceInvoiceApproval`
      - Maintenance Payment — route: `maintenancePayment.index`
      - Maintenance Payment Approval — route: `maintenancePaymentApproval`
      - Deposit Refund — route: `depositRefund.index`
      - Deposit Refund Approval — route: `depositRefundApproval`
      - General Ledger — route: `generalLedger.index`
      - General Ledger Approval — route: `generalLedgerApproval`
      - Generate Maintenance Invoice — route: `groupInvoiceGeneration`
    - **Routines** `(fa-repeat)`
      - PDC Posting — route: `pdcPosting`
      - Rental Income Posting — route: `rentalIncomePosting`
      - Tenant Receipt Posting — route: `tenantReceiptPosting`
      - Cost Recognition — route: `costRecognition`
  - **Mail** `(fa-envelope)`
    - Compose — route: `massMail.create`
    - Inbox — route: `massMail.index`
  - **Reports** `(fa-folder)`
    - Building Details — route: `showBuildingDetails`
    - Building Unit Details — route: `showBuildingUnitDetails`
    - Tenancy Details — route: `showtenancyDetailsReport`
    - Furnished Unit — route: `showFurnishedUnitReport`
    - Tenant Contract — route: `showTenantContractReport`
    - Tenant Contract Expiry — route: `showTenantContractExpiryReport`
    - Tenant Contract Renewal — route: `showTenantContractRenewalReport`
    - Corporate Tenant — route: `showCorporateTenantReport`
    - Rent Receipt — route: `showRentReceiptReport`
    - General Receipt — route: `showGeneralReceiptReport`
    - Legal Rent — route: `showLegalRentAmountReport`
    - Cheque Return — route: `showchequeReturnReport`
    - Tenant Details — route: `showTenantDetailsReport`
    - Legal Case — route: `showLegalCaseReport`
    - Early Termination — route: `showEarlyTerminationReport`
    - Termination — route: `showTerminationReport`
    - Employee Tenant Contract — route: `showEmployeeTenantContractReport`
    - Deposit Rent — route: `showDepositRentReport`
    - Payment History — route: `showPaymentHistoryReport`
    - Tenant Receivables — route: `showtenantReceivablesReport`
    - Unit Takeover — route: `showUnitTakeoverReport`
    - Vacancy Loss — route: `showVacancyLossReport`
    - Tenant Receivable v2 — route: `showtenantReceivablesReportV2`
    - Vacant Unit — route: `showVacantUnitReport`
    - Legal Receivable v2 — route: `showLegalReceivablesReportV2`
    - Normal Management Report v2 — route: `showNormalManagementReportV2`
  - **Finance Reports** `(fa-folder)`
    - Expense details — route: `showExpenseDetailsReport`
    - Rental income — route: `showRentalIncomeReport`
  - Summary — route: `contract-details.index`
- **Maintenance Module** `(fa-tty)`
  - **Masters** `(fa-podcast)`
    - Contract Work Link Inactive Always — route: `workLink.index` _(inactive)_
    - Vendor Type Inactive Always — route: `vendorType.index` _(inactive)_
    - Inventory — route: `inventory.index`
    - Work — route: `work.index`
    - Sub Work — route: `subWork.index`
    - Complaint Reason Inactive Always — route: `complaintReason.index` _(inactive)_
  - **Operations** `(fa-gamepad)`
    - Vendor — route: `vendors.index`
    - **Complaint Enquiry** `(fa-building-o)`
      - Add Enquiry — route: `complaint.create`
      - Complaint — route: `complaint.index`
    - **Complaint Stage** `(fa-steam)`
      - Un Assigned — route: `complaintStage.index`
      - Assigned — route: `complaintAssignedList`
      - Sub Assigned — route: `complaintSubAssignedList`
      - Review — route: `complaintReviewList`
      - Closed Ticket — route: `complaintClosedList`
      - Closed Complaint — route: `complaintCompletelyClosedList`
    - **AMC** `(fa-building-o)`
      - Contract — route: `amcContract.index`
      - Schedule — route: `amcSchedule.index`
      - Task — route: `amcTask.index`
  - **Reports** `(fa-folder)`
    - Maintenance — route: `showMaintenanceReport`
    - Complaint Ticket — route: `showComplaintTicketReport`
    - Complaint Status — route: `showComplaintStatusReport`
    - Monthly Tenancy Details — route: `showMonthlyTenancyReport`
    - MERA Rent Receipt Report — route: `showMeraRentReceiptReport`
    - Leasing Consultant Performance — route: `showLeasingConsultantPerformance`
    - Service Report — route: `showServiceReport`
- **Administrator Module** `(fa-users)`
  - **User Management** `(fa-user-plus)`
    - Employee — route: `employee.index`
    - Job Category — route: `jobCategory.index`
    - Workflow — route: `workFlow.index`
    - Workflow Stage — route: `workFlowProcess.index`
    - Process Assign — route: `processAssign.index`
    - Action — route: `action.index`
    - Process Action Link — route: `processActionLink.index`
  - **Menu Management** `(fa-bars)`
    - Menu — route: `menu.index`
    - Role — route: `role.index`
  - **Setting** `(fa-cogs)`
    - System Configuration — route: `settings.index`
    - System Log — route: `logs.index`
- Global Search — route: `contractGlobleSearch`

---

## BackOffice Module

The largest and most complex module — tenant/landlord contract renewal, contract editing, termination, handover, PDC, invoicing, receipts, general ledger, and reports.

### Tenant Contract Renewal

#### Screen: Tenant - Due For Renewal (Renewal Due List)
- **Route:** `GET tenantRenewal` (`tenantRenewal.index`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_due_list.blade.php`
- **Purpose:** Lists tenant contracts approaching expiry (within the configured "renewal_notification" months window) eligible for renewal/vacating decisions. Entry point of the renewal workflow.
- **Buttons/Actions:** Advance Search toggle; View; Email (opens modal to email renewal form PDF); PDF (generate downloadable PDF)
- **Table columns:** Contract No, Building, Unit No, Start Date, End Date, Rent, Tenant, Email Count, Discussion Type, Action
- **Modals used:** `#myModal` — renewal template PDF/email modal

#### Screen: View Tenant Renewal Due
- **Route:** `GET tenantRenewal/{id}` (`tenantRenewal.show`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_due_view.blade.php`
- **Buttons/Actions:** Renewing (note modal → `tenantRenewalStageDue`, moves to "Under Renewal"); Vacating (note modal → begins termination)
- **Modals used:** `#myModal` (contract-type/renewal-type note form); `#myModalNote` (quick note entry)

#### Screen: Tenant - Contract Under Renewal (List)
- **Route:** `GET tenantContractUnderRenewal` (`tenantContract.underRenewal`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_contract_underRenewal_list.blade.php`
- **Purpose:** Lists contracts accepted for renewal, under active renewal processing.
- **Buttons/Actions:** View
- **Table columns:** Contract No, Building, Unit No, Start Date, End Date, Rent, Tenant, Discussion Type, Action

#### Screen: Tenant Renewal Due View (Under Renewal context)
- **Route:** `GET tenantContractUnderRenewal/{tenant_contract}` (`tenantContract.underRenewalView`)
- **View file:** same as "View Tenant Renewal Due", different action buttons
- **Buttons/Actions:** Normal Renewal (contract-type modal → `tenantRenewalStage`); Send for Approval (modal → `tenantRenewalStage`)
- **Modals used:** `#myModal` — Contract Type/note capture (`contractType.blade.php`)

#### Screen: Contract Type / Send for Approval Note (Modal)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/contractType.blade.php`
- **Form fields:** Type of the Contract (select), Comment (textarea, required)
- **Buttons/Actions:** Save → `contractUnderRenewalTypeStore`

#### Screen: Renewal Contract Creation
- **Route:** `GET contract/{id}/tenantRenewal/contract` (`tenantRenewalNewContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renew_contract_create.blade.php`
- **Purpose:** Creates the new renewal contract record from the old contract's data.
- **Buttons/Actions:** Add (submit) → `tenantRenewal.store`; document row add/remove/delete
- **Form fields:** Renewal Agreement No (auto), Old Agreement No/Date (readonly), Building/Tenant/Unit (readonly), Unit Usage, Occupant Name/Mob/Email, Start/Effective/Valid To Date, Rent, Contract Value (auto), Duration, Deposit Electric/Water, Vacant Since, Rent Paid by Previous Tenant, Municipality Agr. No, Contract Registered In, Registered Date, Payment Term, Remark, Status, PDC, Deposit checkbox, uploads, Deposit Rent Amount, Guarantee Cheque Amount, Receipt No/Date/Amount

#### Screen: Renewal Contract Update (Edit Renewal Contract)
- **Route:** `GET contract/{id}/edit` (`newContract.edit`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renew_contract_edit.blade.php`
- **Buttons/Actions:** Save/Update → PUT `tenantRenewal.update`

#### Screen: Contract Renewal (Pending Send-for-Approval List)
- **Route:** `GET/POST tenantRenewal/contract` (`tenantRenewalContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_contract_list.blade.php`
- **Purpose:** Lists renewal contracts (created, editable) assigned to the current user's role.
- **Buttons/Actions:** Edit; View; Approve/Send for Approval (`sendApprovalFromRenewal`); PDC Generation/View; View Previous Contract (rows without a new contract yet)
- **Table columns:** Old Contract, New Contract, Building, Unit No, Municipal Reg No, Start Date, End Date, Rent, Tenant, Action

#### Screen: Previous Contract View And Action
- **Route:** `GET previousContractViewAndAction/{id}` (`previousContractViewAndAction`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_previous_contract_view.blade.php`
- **Purpose:** Read-only view of the old/expiring contract when a renewal hasn't yet produced a new contract record.
- **Buttons/Actions:** Create Renew Contract → `tenantRenewalNewContract`

#### Screen: Tenant Renewal Contract View (New Contract Show)
- **Route:** `GET contract/{id}/{stage}/newContractShow/` (`newContractShow`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renew_contract_view.blade.php`
- **Buttons/Actions:** Send For Approval; Edit; Save (Note form) → `storeRenewalNote`
- **Form fields:** Renewal Note (textarea, required, max 200 chars)

#### Screen: Contract Approval (Pending Approval List)
- **Route:** `GET/POST tenantRenewal/renewalContractApproval` (`renewalContractApproval`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_contract_approval_list.blade.php`
- **Purpose:** Queue for renewal contracts pending the current user's approval.
- **Buttons/Actions:** Approve (modal → status 5); Reject (modal → status 4); View
- **Table columns:** New Contract, Building, Unit No, Municipal Reg No, Start Date, End Date, Rent, Tenant, Action
- **Modals used:** `#myModal` — Approve/Reject note modal

#### Screen: Tenant Renewal Contract Approval View
- **Route:** `GET renewalContractApproval/{id}/{stage}/newContractApprovalShow/` (`newContractApprovalShow`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewal_contract_approval_view.blade.php`
- **Buttons/Actions:** Approve; Reject; Save (Note) → `storeRenewalNote`
- **Modals used:** `#myModal` — Approve/Reject note capture

#### Screen: Renewed Contract (List)
- **Route:** `GET/POST renewedContract` (`tenantRenewedContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewed_contract_list.blade.php`
- **Purpose:** Lists fully renewed/approved contracts not yet registered in the municipality.
- **Buttons/Actions:** View; Edit (only when not registered in municipality); PDC Generation/View; Generate/View Invoice
- **Table columns:** Old Contract, New Contract, Building, Unit No, Municipal Reg No, Start Date, End Date, Rent, Tenant, Action

#### Screen: Tenant Renewal Contract View (Renewed Contract Show)
- **Route:** `GET renewedContract/{id}/` (`renewedContractShow`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/tenant_renewed_contract_view.blade.php`
- **Buttons/Actions:** Send For Approval; Edit

#### Screen: Renewed Contract Edit / Register in Municipality
- **Route:** `GET renewedContract/{tenant_contract}/edit` (`renewedContract.edit`)
- **View file:** same template with "Register Contract" section
- **Buttons/Actions:** Save → `renewedContract.save`
- **Form fields:** Comment (textarea, required), Registered in Municipality (checkbox)

#### Screen: Tenant PDC (renewal context)
- **Route:** `GET {path}/{subpath}/pdc/{id}` (`pdcGeneration`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/contract_pdc.blade.php`
- **Buttons/Actions:** Process (generate N cheque rows); Add/Delete row; Save → `pdc.store`; Import Excel; Download template
- **Table columns:** Sl No, Cheque No, Cheque Dt, Amt, Stage, Receipt Type, Bank Name, Rec Dt, From/To Dt, Dep Dt, Clear Dt, Cancelled Dt, Cancelled, Bounce Reason, Receipt Voucher, Cheque Acknowledge, Remark

#### Screen: Tenant Invoice (renewal context)
- **Route:** `GET {path}/{subpath}/invoice/{id}` (`invoiceGeneration`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/contract_invoices.blade.php`
- **Buttons/Actions:** View; Post (when invoice status != 3)
- **Table columns:** Sl. No, Invoice No, Invoice Dt, Invoice Amt, Desc, Action

### Notes — action-only routes (Tenant Renewal)
`cronRenewalContract`, `tenantRenewalStage`, `sendApprovalFromRenewal`, `tenantRenewalRequestSearch`, `generatePdfEmail`, `testRenewal` — workflow transitions/redirects/cron, no dedicated screens.

---

### Landlord Contract Renewal

#### Screen: Landlord - Due For Renewal (Renewal Due List)
- **Route:** `GET landlordRenewal` (`landlordRenewal.index`), `GET landlordRenewal/renewalDue` (`renewalDue`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_due_list.blade.php`
- **Purpose:** Lists landlord contracts (duration type "open") approaching expiry (within 90 days).
- **Table columns:** Contract No, Building, Landlord, Start Date, End Date, Rent/Amt, Action

#### Screen: View Landlord Renewal Due
- **Route:** `GET renewalContract/{id}/` (`landlordRenewalContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_due_view.blade.php`
- **Buttons/Actions:** Renewing (→ `landlordRenewalStage`, starts renewal creation); Vacating (→ starts termination)

#### Screen: Landlord Renewal Contract Creation
- **Route:** `GET renewalContract/{id}/{status}/newContractCreate` (`landlordRenewalContractCreate`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php`
- **Buttons/Actions:** Add (submit) → `landlordRenewalContractAdd`
- **Form fields:** Agreement No (auto), Building/Vendor (from old contract), Duration Type, Management Fee, Percentage, Contract Amount, Management Type, Free Lease Period, Marketing Executive, Valid From/To Date, Payment Type, Status, Note, Start Date, Close Activity, Management Method, Management Fee Type

#### Screen: Landlord Renewal Contract Edit
- **Route:** `GET landlordRenewal/newContractEdit/{id}/{status}` (`landlordRenewalContractEdit`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php`
- **Buttons/Actions:** Save → `landlordRenewalContractUpdate`

#### Screen: Landlord Contract Renewal (Pending List)
- **Route:** `GET landlordRenewal/renewalContract` (`renewalContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_list.blade.php`
- **Buttons/Actions:** Renew Contract (rows without a new contract yet); View; Edit; Sent for Approval
- **Table columns:** Old Contract, New Contract, Building, Landlord, Start Date, Action

#### Screen: Landlord Contract Renewal View
- **Route:** `GET renewalContract/{id}/` (`landlordRenewalContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_view.blade.php`
- **Buttons/Actions:** Sent For Approval; Edit; Save (note) → `landlordRenewalNoteStore`

#### Screen: Landlord Renewal Contract Edit (New Contract)
- **Route:** `GET renewalContract/{id}/landlordRenewal` (`landlordRenewalNewContractEdit`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php`
- **Buttons/Actions:** Save → `landlordRenewalNewContractUpdate`

#### Screen: Landlord Renewal Approval (Pending List)
- **Route:** `GET landlordRenewal/contractApproval` (`landlordContractApproval`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_approval_list.blade.php`
- **Buttons/Actions:** View
- **Table columns:** Old Contract, New Contract, Building, Landlord, Start Date, Action

#### Screen: Landlord Contract Approval View
- **Route:** `GET contractApproval/{id}/landlordRenewal` (`landlordApprovalContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_approval_contract_view.blade.php`
- **Buttons/Actions:** Approve (modal); Reject (modal); Save (note)
- **Modals used:** `#myModal` — Approve/Reject note capture

#### Screen: Landlord Contract Renewed (List)
- **Route:** `GET landlordRenewal/contractRenewed` (`landlordContractRenewed`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_contract_renewed_list.blade.php`
- **Buttons/Actions:** View
- **Table columns:** Old Contract, New Contract, Building, Landlord, Start Date, End Date, Action

#### Screen: Landlord Renewed Contract View
- **Route:** `GET contractRenewed/{id}/landlordRenewal/` (`landlordRenewedContract`)
- **View file:** `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewed_contract_view.blade.php`
- **Purpose:** Read-only summary of a fully renewed landlord contract with note history.

### Notes — action-only routes (Landlord Renewal)
`landlordSentForApproval`, `landlordNewContractShow` — workflow transitions, no standalone screens.

---

### Tenant Contract Edit / Revoke

#### Screen: Tenant Contract (Direct) List
- **Route:** `GET tenant-contract` (`tenant-contract.index`)
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/contract_list.blade.php`
- **Purpose:** Main tenant-contract listing with row actions for the whole lifecycle.
- **Buttons/Actions:** Revoke; Edit; View; Send For Approval; Change Status (modal); Add Municipality (modal); Generate/View Invoice; Discussion (modal); PDC Generation/View
- **Table columns:** Agreement No, Tenant, Building, Unit No, Unit Type, Start Date, End Date, Rent, status badges, Tenant Contact No, Action

#### Screen: Tenant Contract Creation
- **Route:** `GET tenant-contract/create` (`tenant-contract.create`)
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/tenant_contract_creation.blade.php`
- **Buttons/Actions:** Save → `tenant-contract.store`
- **Form fields:** Tenant, Building, Unit, Nationality, Bank, Location, Occupant, Contract Code (auto), Start/Effective/Valid To Date, Rent, Payment Type, Registered In, Duration, Deposit, PDC settings, Marketing Executive, Document uploads

#### Screen: Tenant Contract View
- **Route:** `GET tenant-contract/{id}` (`tenant-contract.show`)
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/view.blade.php`
- **Buttons/Actions:** Send For Approval; Revoke; Edit; Close (modal); Accept/Reject (modals); Change Status/Move to Legal (modal); Add Municipality (modal); Generate/View Invoice; PDC Generation/View

#### Screen: Tenant Contract Edit
- **Route:** `GET tenant-contract/{id}/edit` (`tenant-contract.edit`)
- **View file:** same as Creation, pre-populated
- **Buttons/Actions:** Save/Update → PUT `tenant-contract.update`

#### Screen: Tenant Contract Revoke
- **Route:** `GET tenant-contract/{id}/tenantRevoke/` (`tenantRevoke`)
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/tenant_contract_revoke.blade.php`
- **Buttons/Actions:** Save → `storeTenantRevoke`

#### Screen: Tenant Direct/Revoke Contract Pending / Approved Lists
- **Route:** `GET tenantContractPendingRevoke`; `GET tenantContractApprovedRevoke`
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/contract_list.blade.php` (shared template)
- **Purpose:** Pending vs approved/revoked direct-contract lists.

#### Screen: Tenant Contract Revoke/Direct Approval View
- **Route:** `GET {view}/{id}/{stage}/tenantRevokeProcess/` (`leadAssign.revokeProcess`)
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/tenant_revoke_approval.blade.php`
- **Buttons/Actions:** Accept/Reject/Close (modals → `directContractApprovalAcceptReject`)

#### Screen: Tenant PDC (Acceptance) / Add Tenant PDC
- **Route:** `GET tenant-contract/tenant-pdc/{id}` (`tenantPdcView`); `GET tenant-contract/add-pdc/{id}` (`addTenantPdf`)
- **View file:** `Modules/BackOffice/Resources/views/TenantContract/pdc_acceptanace.blade.php` / `add_tenant_pdc.blade.php`
- **Purpose:** Older/alternate PDC acceptance and standalone add-PDC screens (superseded in most flows by `pdc.edit`/`pdcView`).

#### Screen: PDC Print Preview
- **Route:** `GET pdc/printPreview/{id}` (`PdcprintPreview`); `GET pdc/printPreview/{id}/{stage}` (`PdcprintPreviewstage`)
- **Purpose:** Print-friendly PDC schedule, no interactive actions.

### Notes — action-only routes (Tenant Contract Edit)
`tenantSendForApproval` — workflow transition + redirect, no view.

---

### Landlord Direct Contract

#### Screen: Landlord Contract List
- **Route:** `GET landlord-contract` (`landlord-contract.index`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordContract/landlord_contract_list.blade.php`
- **Buttons/Actions:** Edit; View; Send For Approval; Generate/View Invoice
- **Table columns:** Contract No, Agreement Date, Landlord, Management Type, Building, Duration (Open/Close), Valid To, Action

#### Screen: Add Landlord Contract (Direct)
- **Route:** `GET landlord-contract/create` (`landlord-contract.create`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php`
- **Purpose:** Creates a new direct landlord contract, also spawning a Sales Enquiry (direct-contract type).
- **Buttons/Actions:** Save → `landlord-contract.store`
- **Form fields:** Building, Vendor, Duration Type, Management Fee/Percentage, Contract Amount, Management Type, Free Lease Period, Marketing Executive, Valid From/To Date, Payment Type, Note, Start Date, Close Activity, Management Method/Fee Type, Agreement Date

#### Screen: Landlord Contract View
- **Route:** `GET landlord-contract/{id}` (`landlord-contract.show`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordContract/view.blade.php`
- **Buttons/Actions:** Close Contract (modal → `closeLandlordContract`)

#### Screen: Landlord Contract Edit
- **Route:** `GET landlord-contract/{id}/edit` (`landlord-contract.edit`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php`
- **Buttons/Actions:** Save/Update → PUT `landlord-contract.update`

### Notes — action-only routes (Landlord Direct Contract)
`landlordSendForApproval` — workflow transition + redirect, no view.

---

### Tenant Termination & Handover

#### Screen: Open For Termination (List)
- **Route:** `GET tenantTermination` (`tenantTermination.index`)
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_open_list.blade.php`
- **Buttons/Actions:** View; Edit; Cancel Early Termination (AJAX); Send For Approval; Handover/Accept (→ `tenantTerminationStage`); Renew (→ `tenantRenewalStage`)
- **Table columns:** Contract No, Building, Unit, Tenant, Start Date, End Date, Rent, Municipal Reg No, OS, Action

#### Screen: Create/Edit Early Termination Request
- **Route:** `GET tenantTermination/create` (`tenantTermination.create`)
- **View file:** `Modules/BackOffice/Resources/views/Termination/early_tenant_termination_create.blade.php`
- **Form fields:** Agreement No, Building Name, Unit, Tenant Name (all autocomplete)
- **Buttons/Actions:** Save → `tenantTermination.store` / PUT `.update`

#### Screen: Open For Termination View
- **Route:** `GET tenantTermination/{id}` (`tenantTermination.show`)
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_open_view.blade.php`
- **Buttons/Actions:** Renewing; Handover; Send For Approval; Edit; per-row View/Invoice/PDC/Receipt links; inline editable remark/dates (AJAX); Document upload

#### Screen: Approval (List) / View
- **Route:** `GET/POST tenantTerminationApproval`; `GET tenantTerminationApproval/{termination}/tenantTerminationApprovalView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_approval_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** View; Approve (modal); Reject (modal)
- **Table columns:** Contract No, Building, Unit, Tenant, Start Date, End Date, Rent, Municipal Reg No, OS, Action

#### Screen: Termination Handover Unassigned (List) / View
- **Route:** `GET/POST handoverUnassigned`; `GET handoverUnassigned/{id}/handoverUnassignedView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_handover_unassigned_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** View; Assign (group-assign modal)
- **Table columns:** Contract No, Building, Unit, Tenant, Location, Tenant Contact No, Building PC, OS, Action

#### Screen: Termination Handover Assigned (List) / View
- **Route:** `GET/POST handoverAssigned`; `GET handoverAssigned/{termination}/handoverAssignedView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_handover_assigned_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** View; Edit; ReAssign (modal); Refer Back for Renewal/to normal; Reminder; Send for Review; TakenOver; Send Mail; Inspection Create
- **Table columns:** Contract No, Building, Building No, Unit No, Unit Type, Tenant, Location, Tenant Contact No, Building PC, OS, Resubmit status, Action

#### Screen: Termination Handover Assigned — Sales Person List / View
- **Route:** `GET/POST handoverAssignedView` (list); `GET handoverAssignedView/{termination}/handoverAssignedView/` (view)
- **View file:** `..._salesPerson.blade.php` / `_salesperson.blade.php`
- **Purpose:** Sales-person-facing read/limited-action variant.

#### Screen: Handover Assigned Inspection (Create) / Edit
- **Route:** `GET handoverAssigned/{id}/handoverAssignedInspection/`; `GET handoverAssigned/{termination}/handoverAssignedInspectionEdit/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_handover_assigned_inspection.blade.php` / `_edit.blade.php`
- **Buttons/Actions:** TakenOver; Save (inspection form) → `terminationInspectionStore`/`Update`; image upload; signature iframe
- **Form fields:** Work items grouped by category, quantities/amounts, remarks, uploads, other dues/amounts

#### Screen: Take Over For Termination (List) / View
- **Route:** `GET/POST takeoverForTermination`; `GET takeoverForTermination/{id}/takeoverForTerminationView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_takenover_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** Terminate (modal); Resubmit (modal); View
- **Table columns:** Contract No, Building, Unit, Tenant, Start Date, End Date, Rent, Municipal Reg No, Action

#### Screen: Termination Signature Capture
- **Route:** `GET terminationStage/signature` (`tenantTerminationSignature`)
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_signature.blade.php`
- **Purpose:** Embedded iframe signature pad, POSTs to `terminationSignatureStore`.

#### Screen: Terminated Contract (List) / View
- **Route:** `GET/POST tenantTerminatedContract`; `GET tenantTerminatedContract/{id}/tenantTerminatedContractView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/tenant_termination_terminated_contracts_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** View; Penalty (modal)
- **Table columns:** Contract No, Building, Unit, Tenant, Start Date, End Date, Last Paid Date, Terminated Date, Rent, Municipal Reg No, Action

### Notes — action-only routes (Tenant Termination)
`tenantTerminationStage`, `tenantTerminationReview`, `tenantTerminationOpenStatus`, `InspectionReminder`, `terminationEmail`, `inspectionSendMail` — workflow transitions/notifications, no dedicated screens.

---

### Landlord Termination

#### Screen: Open For Termination (List) / Create/Edit / View
- **Route:** `GET landlordTermination` (`landlordTermination.index`); `GET landlordTermination/create`; `GET landlordTermination/{id}`
- **View file:** `Modules/BackOffice/Resources/views/Termination/landlord_termination_open_list.blade.php` / `add_landlord_termination_open.blade.php` / `_open_view.blade.php`
- **Buttons/Actions:** Early Termination; View; Renewing (AJAX); Verify (→ `landlordTerminationStage`); Edit (when status==1)
- **Table columns:** Contract No, Building, Landlord, Start Date, End Date, Action

#### Screen: LC Termination Verification (List) / View
- **Route:** `GET/POST LCTerminationVerify`; `GET LCTerminationVerify/{termination}/LCTerminationVerifyView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/landlord_termination_verification_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** Send for Approval; View; Renewing (when rejected); per tenant-contract row: PDC/View/Invoice links
- **Table columns:** Contract No, Building, Landlord, Start Date, End Date, Terminated Date, Status, Action

#### Screen: Approval (List) / View
- **Route:** `GET/POST LCTerminationApproval`; `GET LCTerminationApproval/{termination}/LCTerminationApprovalView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/landlord_termination_approval_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** View; Approve; Reject (Approve fully processes termination: cancels tenant contracts/PDCs/invoices, marks units vacant)
- **Table columns:** Contract No, Building, Landlord, Start Date, End Date, Action

#### Screen: Terminated Landlord Contract (List) / View
- **Route:** `GET/POST TerminatedContractLandlord`; `GET TerminatedContractLandlord/{termination}/TerminatedContractLandlordView/`
- **View file:** `Modules/BackOffice/Resources/views/Termination/landlord_termination_terminated_contracts_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** View
- **Table columns:** Contract No, Building, Landlord, Start Date, End Date, Terminated Date, Action

#### Screen: Tenant Contract / PDC / Invoice Views (by Landlord Termination drill-down)
- **Route:** `tenantContractByLandlord[Approval]`, `LandlordByTenantPdcView`/`LandlordApprovalByTenantPdcView`, `LandlordByTenantInvoiceView`/`LandlordApprovalByTenantInvoiceView`
- **View file:** `landlord_termination_tenant_contract_view.blade.php`, `_pdc_view.blade.php`, `_invoice_view.blade.php`
- **Purpose:** Read-only drill-downs of an affected tenant contract from the verify/approval screens.

### Notes — action-only / dead routes (Landlord Termination)
`landlordTerminationStage`, `landlordTerminationChangeStatus` — workflow transitions, no views. `chekOutstanding` — controller method doesn't exist; dead route.

---

### Invoice

#### Screen: Tenant Invoice — Contract Invoices List
- **Route:** `GET invoice/{id}` (`invoice.show`)
- **View file:** `Modules/BackOffice/Resources/views/Invoice/contract_invoices.blade.php`
- **Buttons/Actions:** View; Post (per row, when status != 3, confirm → posts to AX)
- **Table columns:** Sl. No, Invoice No, Invoice Dt, Invoice Amt, Desc, Action

#### Screen: Tenant Invoice — Invoice Detail View
- **Route:** `GET invoice/tenant-rent-invoice-details/{id}` (`tenantRentInvoiceDetails`)
- **View file:** `Modules/BackOffice/Resources/views/Invoice/invoice_view.blade.php`
- **Buttons/Actions:** POST (when status != 3)
- **Table columns:** Distribution Details — Account Code, Description, Type, Dr Amt, Cr Amt + Total

---

### PDC (Post-Dated Cheques)

#### Screen: Tenant PDC — Edit/Manage Cheques
- **Route:** `GET pdc/{contract_id}/edit` (`pdc.edit`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/contract_pdc.blade.php`
- **Buttons/Actions:** Import Excel; Download template; Stage dropdown + Print; Process (bulk generate rows); Add/Delete row; Save → `pdc.store`; per-row Cancel Reason (Bounce/Exchange/Replace)
- **Table columns:** Sl No., Cheque No, Cheque Dt, Amt, Stage, Receipt Type, Bank Name, Rec Dt, From/To Dt, Dep Dt, Clear Dt, Cancelled Dt, Cancelled, Bounce Reason, Receipt Voucher, Cheque Acknowledge, Remark
- **Modals used:** PDC Exchange modal

#### Screen: Tenant PDC — View (read-only)
- **Route:** `GET/POST tenant-pdc-view/tenant-contract/{id}` (`pdcView`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/contract_pdc_view.blade.php`
- **Buttons/Actions:** Print

#### Screen: Cheque Bounce — Search & Bounce/Exchange Management
- **Route:** `GET/POST pdc/tenantPdcBounce` (`tenantPdcBounce`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/pdc_cheque_bounce.blade.php`
- **Buttons/Actions:** Search (3 filter groups); Cancel Reason select; Bounce/Exchange modal actions
- **Table columns:** Cheque No, Cheque Date, Bank, Building Name/Code, Unit No, Tenant Name/Code, Agr No, Cheque Status, Receipt No, Receipt Status
- **Modals used:** PDC Exchange modal; PDC Bounce modal

#### Screen: Bounced Cheques List
- **Route:** `GET pdc/getBouncedCheques` (`getBouncedCheques`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/bounced_cheque_list.blade.php`
- **Purpose:** Read-only report of all cheques currently bounced (ARE-filtered).
- **Table columns:** Building Name, Unit No, Tenant Name, Mobile No, Contract From, Contract To, Rent pm, Rent Paid Upto, Last Paid Date, Bounce Cheque No, Reason

### Invoice / PDC / Key / Routine Posting / Payments / Receipts / Ledger / Reports

#### Screen: Tenant Invoice List (Generate/View Invoices for a Contract)
- **Route:** `GET /invoice/{contract_id}` (`invoice.show`)
- **View file:** `Modules/BackOffice/Resources/views/Invoice/contract_invoices.blade.php`
- **Purpose:** Shows all rent invoices generated for a tenant contract. On first visit for an approved contract with no existing invoices, the controller auto-generates the full invoice schedule and their accounting dimensions before rendering. Only accessible for contracts with `tenant_contract_status == 1` (approved).
- **Buttons/Actions:**
  - Per-row "View" — links to Invoice Details screen
  - Per-row "Post" (shown if `tenant_invoice_status != 3`) — posts the invoice to Microsoft Dynamics AX via `tenantInvoicePosting`, with JS confirm
- **Table columns:** Sl.No, Invoice No, Invoice Dt, Invoice Amt, Desc, Action (View / Post)

#### Screen: Tenant Rent Invoice Details
- **Route:** `GET /invoice/tenant-rent-invoice-details/{id}` (`tenantRentInvoiceDetails`)
- **View file:** `Modules/BackOffice/Resources/views/Invoice/invoice_view.blade.php`
- **Purpose:** Read-only detail/print-style view of a single tenant invoice, including contract info, AX posting info, and debit/credit distribution breakdown.
- **Buttons/Actions:** "POST" button (shown if `tenant_invoice_status != 3`) — posts invoice to AX via `tenantInvoicePosting` with JS confirm
- **Table columns:** Distribution Details — Account Code, Description, Type, Dr Amt, Cr Amt (with totals row)
- **Form fields:** Display-only: Invoice No, Invoice Date, Building Name/Code, Unit No/Code, Tenant Name/Code, Agreement No, Agreement Date, Description, Invoice Rent Amount, AX-Batch Id, AX-Invoice No, Division, Buildings

#### Screen: Tenant PDC Entry (Add/Edit PDC Cheques for a Contract)
- **Route:** `GET /pdc/{contract_id}/edit` (`pdc.edit`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/contract_pdc.blade.php`
- **Purpose:** Entry/maintenance screen for post-dated cheques tied to a tenant contract — add rows, bulk-generate a series, mark bounce/exchange/replace, save. Accessible only for renewed or workflow-approved contracts.
- **Buttons/Actions:**
  - "Import User Data" (Excel import, posts to `import`)
  - "Click Here to Download the Excel" (`export`)
  - Stage dropdown + "Print" (links to `PdcprintPreviewstage`)
  - "+ Add Row" / "− Delete Row" per line
  - "Process" (bulk-generates N cheque rows based on payment term)
  - "Save" (submits to `pdc.store`)
  - Inline Exchange modal ("Exchange"/"SKIP")
- **Table columns:** Cheque No, Cheque Dt, Amt, Stage, Receipt Type (Rent/Deposit/Others), Bank Name, Rec Dt, From Dt, To Dt, Dep Dt, Clear Dt, Cancelled Dt, Cancelled (Bounce/Exchange/Replace), Bounce Reason, Receipt Voucher, Cheque Acknowledge, Remark
- **Form fields:** Editable inline grid of all table columns; read-only header (PDC Transaction No/Date, Building Name/Code, Unit No, Tenant Name, Agreement No, Start/End Date, Rent PM, Mode of Payment, Rent Paid Up To, Payment Term) + "No of Cheques" + "Process" control
- **Modals used:** PDC Exchange modal (Cheque No, Cheque Dt, Amount, Stage, Bank Name, Rec Dt; Exchange/Skip)

#### Screen: Tenant PDC View (Read-only)
- **Route:** `GET|POST /tenant-pdc-view/tenant-contract/{id}` (`pdcView`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/contract_pdc_view.blade.php`
- **Purpose:** Read-only view of all PDC cheques for a contract. Requires `work_flow_processes_code >= 106`.
- **Buttons/Actions:** "Print" (links to `PdcprintPreview`)
- **Table columns:** Cheque No, Cheque Dt, Amt, Stage, Bank Name, Rec Dt, Dep Dt, Clear Dt, Cancelled Dt, Cancelled, Bounce Reason, Receipt Voucher, Receipt Type

#### Screen: Cheque Bounce Search / Bounce & Exchange
- **Route:** `GET|POST /pdc/tenantPdcBounce` (`tenantPdcBounce`)
- **View file:** `Modules/BackOffice/Resources/views/Pdc/pdc_cheque_bounce.blade.php` (search partial: `Pdc/cheque_bounce_search.blade.php`)
- **Purpose:** Search outstanding PDC cheques by date range, cheque-number range, or bank, then mark cheques as Bounced/Exchanged/Replaced.
- **Buttons/Actions:** Three "Search" buttons (per search mode); per-row "Cancel Reason" dropdown (Bounce/Exchange/Replace) triggers AJAX modal; "Exchange"/"Skip" and "Save"/"Skip" modal buttons
- **Filters/Search:** Start/End Cheque Date; From/To Cheque No; Bank Name (mutually exclusive modes)
- **Table columns:** Cheque No, Cheque Date, Bank, Building Name, Building Code, Unit No, Tenant Name, Tenant Code, Agr No, Cheque Status, Receipt No, Receipt Status
- **Form fields:** Bounce modal — Bounce Reason dropdown (Insufficient Funds, Signature Missing, Signature Mismatch, Word mismatch, Stop Payment, Refer to Drawer, Correction, Stale Cheque, Misc); Exchange modal — Cheque No, Cheque Dt, Amount, Stage, Bank Name, Rec Dt
- **Modals used:** "PDC Exchange" modal, "PDC Bounce" modal

#### Screen: Key List / Key Management
- **Route:** `GET /keyManagement` (`keyManagement.index`)
- **View file:** `Modules/BackOffice/Resources/views/Key/key_list.blade.php` (ajax rows: `Key/key_list_ajax.blade.php`)
- **Purpose:** Lists keys currently held (staff/tenant/landlord) per building/unit, scoped to active keys with team/engineer/facility-manager scoping; supports handover.
- **Buttons/Actions:** "Advance Search" toggle; per-row "Handover To Tenant" (`keys_accept_for_tenant`) and "Handover To Landlord" (`keys_accept_for_landlord`) — open AJAX modal (`keyAcceptAginstLandlordTenant`)
- **Filters/Search:** Advance search (dynamic field/operation/value/AND-OR builder) on Building, Unit; inline quick-search on Building, Unit, Role, User
- **Table columns:** Building, Unit, Role, User, Action
- **Modals used:** "myModal" — Key Handover form (AJAX-loaded)

#### Screen: Key Accept (Scan)
- **Route:** `GET /keyManagement/create` (`keyManagement.create`)
- **View file:** `Modules/BackOffice/Resources/views/Key/key_accept.blade.php`
- **Purpose:** QR-code/webcam scanning screen to accept/check-in a key by scanning a unit's QR code; posts to `keyManagement.store`.
- **Buttons/Actions:** Webcam/Camera toggle icons; QR scan canvas
- **Note:** `GET|POST /keyManagement/keyAccept` (`keyAccept`) maps to `KeyController@keyAccept` but no such method exists — likely dead/legacy route.

#### Screen: Key Request Search (My Keys / Advance Search Results)
- **Route:** `GET|POST /keyRequestSearch/keyManagement` (`keyRequestSearch`)
- **View file:** `Modules/BackOffice/Resources/views/Key/key_list.blade.php`
- **Purpose:** Advance-search results scoped to current user's held keys (unless super_admin).
- **Buttons/Actions:** Same as Key List (Handover to Tenant/Landlord)

#### Screen: PDC Bulk Posting
- **Route:** `GET|POST /pdcPosting` (`pdcPosting`)
- **View file:** `Modules/BackOffice/Resources/views/Routine/pdc_posting_list.blade.php`
- **Purpose:** Lists uncleared/unposted rent & deposit PDC cheques for bulk bank-deposit posting; generates ReceiptsGeneration + dimension entries and pushes to AX.
- **Buttons/Actions:** "Search"; per-row checkbox + "Select All"; Bank dropdown + Deposit Date + "Post" (submits to `pdcPost`, requires ≥1 selected); infinite-scroll pagination
- **Filters/Search:** Date From/To; Cheque No (alt mode); sort links on Cheque No, Building Name, Bank
- **Table columns:** Checkbox, Sl No, Cheque No, Cheque Date, Receipt Type, Building Name, Building Code, Unit No, Tenant Name, Tenant Code, Agr No, Bank, Amount (+ Total)
- **Form fields:** Bank (required), Deposit Date (required) — applied to checked rows

#### Screen: Rental Income Posting
- **Route:** `GET|POST /pdcPosting/rentalIncomePosting` (`rentalIncomePosting`)
- **View file:** `Modules/BackOffice/Resources/views/Routine/rental_income_posting_list.blade.php`
- **Purpose:** Lists active unposted tenant rent invoices for bulk posting to AX (`rentalPost`).
- **Buttons/Actions:** "Search"; "Select All Buildings"; per-row/select-all checkboxes; "Post"
- **Filters/Search:** Date From/To; Building Name (multi-select tokenized autocomplete)
- **Table columns:** Checkbox, Sl No, Invoice No, Invoice Date, Building Name, Building Code, Unit No, Tenant Name, Tenant Code, Agr No, Invoice Amount (+ Total)

#### Screen: Tenant Receipt Posting
- **Route:** `GET|POST /pdcPosting/tenantReceiptPosting` (`tenantReceiptPosting`)
- **View file:** `Modules/BackOffice/Resources/views/Routine/receipt_posting_list.blade.php`
- **Purpose:** Lists active/approved tenant receipts within a date range for bulk AX posting (`receiptPost`).
- **Table columns:** Checkbox, Sl No, Rec No, Date, Bldg Name, Bldg Code, Unit No, Tenant Name, Tenant Code, Agr No, Payment Method, Amt, Receipt, Bank, Dim1–Dim5 (+ Total)

#### Screen: Cost Recognition (Landlord Invoice Bulk Posting)
- **Route:** `GET|POST /pdcPosting/costRecognition` (`costRecognition`)
- **View file:** `Modules/BackOffice/Resources/views/Routine/cost_recognition_list.blade.php`
- **Purpose:** Lists landlord invoice distribution-breakup lines (unposted) for bulk cost-recognition posting (`costPost`).
- **Table columns:** Checkbox, Sl No, Voucher No., Building Name, Landlord Name, Landlord Code, Agr No, Period, Amount (+ Total)

#### Screen: Tenant Invoice Posting (action, not a page)
- **Route:** `GET /tenantInvoicePosting/{invoice_id}` (`tenantInvoicePosting`)
- **Purpose:** Action endpoint pushing a single invoice + dimension lines to AX, updates status to Posted, sends legal notifications, redirects back.

### Maintenance Payment

#### Screen: Maintenance Payment List
- **Route:** `GET /maintenancePayment` (`maintenancePayment.index`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/maintenance_payment_list.blade.php`
- **Purpose:** Lists all maintenance payments with status-driven actions.
- **Buttons/Actions:** "Advance Search" toggle; "Add"; per-row View, Edit (when not posted & status in [0,1,5]), Approve/UnApprove/Send-for-Approval/Send-for-UnApproval, Post, Delete/Cancel (AJAX modal)
- **Table columns:** Sl No, Payment No, Start Dt, Contractor Name, Contractor Code, Payment Method, Bank, Amount, Status, Action
- **Modals used:** "myModal" — Cancel/Delete confirmation

#### Screen: Add / Edit Maintenance Payment
- **Route:** `GET /maintenancePayment/create` (`maintenancePayment.create`) and `GET /maintenancePayment/{id}/edit` (`maintenancePayment.edit`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/add_maintenance_payment.blade.php`
- **Form fields:** Payment No (readonly, auto), Payment Date (required), Contractor Name (autocomplete, required), Contractor Code (readonly), Payment Method (radio Cash/Cheque, required), Bank (required if Cheque), Cheque No (conditional), Amount (required), Comment (textarea), AX Batch ID (readonly), AX Payment No (readonly)

#### Screen: Maintenance Payment View
- **Route:** `GET /maintenancePayment/{id}` (`maintenancePayment.show`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/maintenance_payment_view.blade.php`
- **Buttons/Actions:** Edit, UnApprove, Approve, Send for Approval, Send for UnApproval, Post, Delete/Cancel (all conditional)

#### Screen: Maintenance Payment Approval List
- **Route:** `GET /maintenancePayment/maintenancePaymentApproval` (`maintenancePaymentApproval`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/maintenance_payment_approval_list.blade.php`
- **Table columns:** Sl No, Payment No, Payment Dt, Contractor Name/Code, Payment Method, Bank, Amount, Purpose, Action

#### Screen: Maintenance Payment Approval Detail
- **Route:** `GET /maintenancePaymentApproval/{id}` (`maintenancePaymentApprovalShow`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/maintenance_payment_approval_view.blade.php`
- **Buttons/Actions:** "Approve", "UnApprove", "Reject"

### Landlord Payment

#### Screen: Landlord Payment List
- **Route:** `GET landlordPayment` (`landlordPayment.index`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/landlord_payment_list.blade.php`
- **Buttons/Actions:** "Add"; per-row View, Edit, Approve, Unapprove, Send For Approval, Send For UnApproval, Post to AX, Delete/Cancel
- **Table columns:** Sl No, Payment No, Payment Dt, Landlord Name, Landlord Code, Agreement No, Building, Invoice No, Amount, Status, Action
- **Modals used:** Cancel/Delete reason modal

#### Screen: Add / Edit Landlord Payment
- **Route:** `GET landlordPayment/create` (`landlordPayment.create`), `GET landlordPayment/{id}/edit` (`landlordPayment.edit`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/add_landlord_payment.blade.php`
- **Form fields:** Payment No (readonly, auto), Payment Date; Landlord Name (autocomplete), Landlord Code (readonly), Agreement No (autocomplete, AJAX lookup of invoice); Invoice No (readonly), Currency, Payment Method (Cheque/Bank Transfer), Bank, Cheque No, Invoice Amount (readonly), Balance Amount (readonly, auto-calc), Amount, Comment; AX Batch ID/Payment No (readonly)

#### Screen: Landlord Payment View
- **Route:** `GET landlordPayment/{id}` (`landlordPayment.show`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/landlord_payment_view.blade.php`
- **Buttons/Actions:** Approve, UnApprove, Edit, Send for Approval, Send for UnApproval, Post to AX, Delete/Cancel

#### Screen: Landlord Payment Approval List / Detail
- **Route:** `GET landlordPayment/landlordPaymentApproval` (`landlordPaymentApproval`); `GET landlordPaymentApproval/{id}` (`landlordPaymentApprovalShow`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/landlord_payment_approval_list.blade.php` / `landlord_payment_approval_view.blade.php`
- **Buttons/Actions:** Approve, UnApprove, Reject, View
- **Table columns:** Sl No, Payment No, Payment Dt, Landlord Name, Landlord Code, Agreement No, Building, Invoice No, Amount, Purpose, Action

### Deposit Refund

#### Screen: Deposit Refund List
- **Route:** `GET depositRefund` (`depositRefund.index`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/deposit_refund_list.blade.php`
- **Buttons/Actions:** "Add"; per-row View, Edit, Approve, Send for Approval, Unapprove, Send for Draft/Unapproval, Delete/Cancel (AJAX modal), Post
- **Table columns:** Sl No, Refund No, Refund Dt, Deposit Receipt No, Building Name/Code, Unit No, Tenant Name/Code, Agreement No, Payment Method, Refund Amount, Status, Action

#### Screen: Add / Edit Deposit Refund
- **Route:** `GET depositRefund/create` (`depositRefund.create`), `GET depositRefund/{id}/edit` (`depositRefund.edit`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/add_deposit_refund.blade.php`
- **Buttons/Actions:** "Save" (validates total debit == total credit); Add/Remove dimension row
- **Form fields:** Refund No (readonly, auto), Refund Date; Building Name (autocomplete), Building Code (readonly), Unit, Tenant Name (autocomplete), Tenant Code, Agreement No (readonly), Valid From/To (readonly); Deposit Receipt No (autocomplete); Payment Method (Cheque/Cash), Cheque No, Bank, Refund Amount, Comment (readonly, auto); Dimension Details table (Account, Type, Dr/Cr Amt) with totals

#### Screen: Deposit Refund View
- **Route:** `GET depositRefund/{id}` (`depositRefund.show`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/deposit_refund_view.blade.php`
- **Buttons/Actions:** Edit, Approve, Send for Approval, UnApprove, Send for UnApproval, Post, Delete/Cancel

#### Screen: Deposit Refund Approval List / View
- **Route:** `GET depositRefund/depositRefundApproval` (`depositRefundApproval`); `GET depositRefundApproval/{id}` (`depositRefundApprovalShow`)
- **View file:** `Modules/BackOffice/Resources/views/Transaction/deposit_refund_approval_list.blade.php` / `deposit_refund_approval_view.blade.php`
- **Buttons/Actions:** Approve, UnApprove, Reject, View

### Rent Receipts

#### Screen: Receipt Tab List (Rent / Deposit / General)
- **Route:** `GET/POST receiptsTabViewList/rentReceiptGeneration/{tab?}/{data?}` (`receiptsTabViewList`, `receiptsTabViewListtab`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/receipt_list.blade.php`
- **Purpose:** Main tabbed receipt listing (Rent/Deposit/General) with inline/advance search and an ARE collection filter.
- **Buttons/Actions:** Tab switch; "Add"; per-row Edit, View, Print, Approve/UnApprove toggle, Send For Approval, Request For Draft/Unapprove, Delete, Post to AX
- **Table columns:** Sl No, Rec No, Rec Date, Tenant Name/Code, Agre. No, Unit No, Bldg. Name, (Rent) Eff. From/To Date, Pay Method, Amount, Status, Action

#### Screen: Rent Receipt Print Preview
- **Route:** `GET rentReceiptGeneration/printPreview/{id}` (`printPreview`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/print_view.blade.php`
- **Purpose:** Print-friendly standalone receipt voucher document.
- **Form fields (display):** Receipt No, Date, "Received with thanks from", Cash/Cheque No, amount in words (Rials Omani), "Being" description, optional Comment line, amount (R.O.), footer note

#### Screen: Agreement-Level Receipt List
- **Route:** `GET/POST receiptsAgreementViewList/rentReceiptGeneration/{id}/{tab}` (`receiptsAgreementViewList`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/receipt_agreement_list.blade.php`
- **Purpose:** Lists all receipts belonging to one tenant contract/agreement.
- **Table columns:** Sl No, Rec No, Rec Date, Bldg. Name/Code, Tenant Name, Agre. No, (Rent) Eff. From/To Date, Pay Method, Amount, Status, Action

#### Screen: Add / Edit General Receipt
- **Routes:** `GET/POST rentReceiptGeneration/generalReceiptCreation` (`addGeneralReceipt`); `GET/POST rentReceiptGeneration/generalReceiptEdit/{id}` (`updateGeneralReceipt`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/general_receipt_form.blade.php`
- **Form fields:** Mobile No/Resident ID lookup; Building/Unit/Agreement (autocomplete/select); Receipt No (readonly, auto), Receipt Date; Payment Method (Cheque/Cash/Bank Transfer); Bank; Cheque/Transaction No; Amount; Comment; Remark; Distribution Details table (Account Code, Type, Dr/Cr Amt, Narration) with totals

#### Screen: Add / Edit Deposit Receipt
- **Routes:** `GET/POST rentReceiptGeneration/depositReceiptCreation` (`addDepositReceipt`); `GET/POST rentReceiptGeneration/depositReceiptEdit/{id}` (`updateDepositReceipt`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/deposit_receipt_form.blade.php`
- **Purpose:** Create/edit a "Deposit" receipt; amount auto-fills from contract rent; supports Cash "Bounce" cheque-replacement flow.
- **Form fields:** Mobile No/Resident ID; Building/Unit/Agreement/Tenant (auto-filled); Receipt No, Receipt Date; Payment Method; Type (Normal/Bounce) revealing Bounce Cheque No select for Cash; Bank; Cheque/Transaction No; Amount (auto = contract rent); Comment; Remark

#### Screen: Receipt Approval Queue List
- **Route:** `GET/POST receiptsRequestApproval/{tab?}` (`receiptsRequestForApprovalTabViewList`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/receipt_request_for_approval_list.blade.php`
- **Buttons/Actions:** Tab switch; View, Approve, Reject, Print
- **Table columns:** Sl No, Rec No, (Rent) Eff. From/To Date, Bldg. Name/Code, Tenant Name/Code, Agre. No, Pay Method, Amount, Status, Action

#### Screen: Add Rent Receipt
- **Route:** `GET rentReceiptGeneration/create` (`rentReceiptGeneration.create`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/rent_payment_receipt_create.blade.php`
- **Form fields:** Mobile No/Resident ID lookup; Building/Unit/Agreement (auto-filled); Receipt No, Receipt Date, Date Eff From/To; Payment Method (Cheque/Cash/Bank Transfer); Type (Normal/Bounce/Replace) for Cash/BT; Bank; Cheque/Transaction No; Amount (auto = term × rent); Comment; Remark

#### Screen: Rent Receipt Detail View
- **Route:** `GET rentReceiptGeneration/{id}` (`rentReceiptGeneration.show`)
- **View file:** `Modules/BackOffice/Resources/views/Receipt/rent_payment_receipt_view.blade.php`
- **Buttons/Actions:** Approve/UnApprove toggle, Delete, Post to AX, Edit, Print, Send for Approval, Request for Draft/Unapprove, Approve/Reject pair

### Landlord Invoice

#### Screen: Landlord Invoice Approval List
- **Route:** `GET landlordInvoiceApproval` (`landlordInvoiceApproval`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordInvoice/invoice_list.blade.php`
- **Table columns:** Sl No., Voucher No., Voucher Date, Landlord Name/Code, Agreement No, Payment Term, Doc Type, Ref No., Contract Amt, Purpose, Action

#### Screen: Add Landlord Invoice Booking (Generate Invoice)
- **Route:** `GET landlord-contract/{landlordContract}/invoiceGenerate` (`landlordInvoiceGenerate`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordInvoice/add_invoice.blade.php`
- **Buttons/Actions:** "SAVE"; "Distribution Break up" (opens modal)
- **Form fields:** Voucher No. (auto), Voucher Date; read-only contract details; Ref No. (required), Description; Distribution Details table (mostly system-generated)
- **Modals used:** "Distribution Break up" modal — monthly rent-split rows

#### Screen: Landlord Invoice Booking / Approval View / Edit
- **Routes:** `GET landlord-contract/{landlordContract}/invoice` (`landlordInvoiceShow`); `GET landlord-contract/{landlordContract}/invoiceApproval` (`landlordInvoiceApprovalShow`); `GET landlord-contract/{landlordContract}/invoiceEdit` (`landlordInvoice.edit`)
- **View file:** `Modules/BackOffice/Resources/views/LandlordInvoice/view_invoice.blade.php` / `add_invoice.blade.php`
- **Buttons/Actions:** POST to AX, Edit, Approve/Send for Approve, Unapprove/Send For Draft, Delete; approval variant: Approve, Unapprove, Reject

### General Ledger

#### Screen: General Ledger List / Approval List
- **Route:** `GET generalLedger` (`generalLedger.index`); `GET generalLedgerApproval` (`generalLedgerApproval`)
- **View file:** `Modules/BackOffice/Resources/views/GeneralLedger/generalLedger_list.blade.php`
- **Buttons/Actions:** Add, View, Edit, Post, Delete, Approve/Unapprove toggle, Send for Approval, Send for Draft
- **Table columns:** Sl No., Ledger Type, Voucher No., JV Ref No., Doc Date, Bank, Amount, Status/Purpose, Action

#### Screen: Add / Edit General Ledger
- **Routes:** `GET generalLedger/create` (`generalLedger.create`); `GET generalLedger/{id}/edit` (`generalLedger.edit`)
- **View file:** `Modules/BackOffice/Resources/views/GeneralLedger/add_generalLedger.blade.php`
- **Form fields:** Voucher No. (auto), Ledger Type (required, 4 types), JV Ref No., Doc Date, Description, Bank (conditional), Amount (readonly), Agreement No. (Landlord Invoice type only), Dimension Details rows (Account Code, Building Code, Unit Code, JV Desc, Dr/Cr Amt, Recovery, Dim1/2) with totals

#### Screen: General Ledger View / Approval View
- **Routes:** `GET generalLedger/{id}` (`generalLedger.show`); `GET generalLedgerApproval/{id}` (`generalLedgerApproval.show`)
- **View file:** `Modules/BackOffice/Resources/views/GeneralLedger/view_general_ledger.blade.php`
- **Buttons/Actions:** Post, Approve/UnApprove, Send for Approval, Send for Unapproval, Delete, Edit; approval variant: Approve, Unapprove, Reject

### Mass Mail

#### Screen: Mass Mail List
- **Route:** `GET massMail` (`massMail.index`)
- **View file:** `Modules/BackOffice/Resources/views/MassMail/list.blade.php`
- **Table columns:** Sl No., Subject, Content (truncated), time, Action

#### Screen: Compose Mail
- **Route:** `GET massMail/create` (`massMail.create`)
- **View file:** `Modules/BackOffice/Resources/views/MassMail/mass_mail.blade.php`
- **Buttons/Actions:** Save (dispatches sendMassMailJob per recipient); Cc toggle
- **Form fields:** To (tokenized autocomplete, required), Cc (tokenized autocomplete), Subject (required), Content (TinyMCE rich text, required)

#### Screen: Compose Mail View
- **Route:** `GET massMail/{id}` (`massMail.show`)
- **View file:** `Modules/BackOffice/Resources/views/MassMail/view.blade.php`
- **Purpose:** Read-only sent mail detail — To/Cc lists (failed sends highlighted), Subject, Content, Created At/By

### Contract Details

#### Screen: Building / Unit / Tenant Contract Details Search
- **Route:** `GET contract-details` (`contract-details.index`)
- **View file:** `Modules/BackOffice/Resources/views/ContractDetails/contract_details_search.blade.php`
- **Purpose:** Search a tenant contract by Building/Unit/Tenant; view full contract/tenant/occupant/payment info + history; jump to related actions.
- **Buttons/Actions:** View contract, PDC Generation/View, Generate/View Invoice, Receipts, Deposit Refund, "Move to Legal" (prompt), Save Remark
- **Filters/Search:** Building Name (autocomplete, required), Unit (autocomplete/select), Tenant Name (autocomplete/select)
- **Table columns:** Contract history — Sl No, Contract No, Start/End Dt, Rent, Last Paid, Terminated Date, Status, Action

### Global Search

#### Screen: Global Search
- **Route:** `GET/POST contractGlobleSearch` (`contractGlobleSearch`)
- **View file:** `Modules/BackOffice/Resources/views/GlobalSearch/index.blade.php`
- **Purpose:** Cross-module search (Sales, BackOffice, Maintenance, Inspection, Leasing) by a chosen field.
- **Buttons/Actions:** Search; tab links per module; deep links to record detail screens
- **Filters/Search:** Field selector (~13 fields incl. Building Code/Name, Unit No, Tenant Name/Mobile/Email, ID No, Location, Company, Unit Type, Municipality Agreement No, Service Report No, Complaint No) + Value input

### Reports

Criteria-form → generated PDF/Excel screens (each: a filter form + "Generate" button POSTing to a `*Pdf` route that opens the report in a new tab):

- **New Tenant For Period** — `showTenantContractReport` / `tenantContractReportPdf` — fields: start_date, end_date, download_type
- **Corporate Tenant (Multiple Flats)** — `showCorporateTenantReport` / `corporateTenantReportPdf` — filter_type (Tenant Name/Code), autocomplete, download_type
- **Report on Contract Expiry** — `showTenantContractExpiryReport` / `tenantContractExpiryReportPdf` — start/end date, filter_type (Building Name/ARE), download_type
- **Tenant Contract Renewal Status** — `showTenantContractRenewalReport` / `tenantContractRenewalReportPdf` — start/end date, download_type
- **Early Termination Report** — `showEarlyTerminationReport` / `tenantEarlyTerminationReportPdf` — start/end date, download_type
- **Termination Report** — `showTerminationReport` / `tenantTerminationReportPdf` — start/end date, download_type
- **Report on Flats Rented - Employee Wise** — `showEmployeeTenantContractReport` / `employeeTenantContractReportPdf` (dompdf) — start/end date, tenant_marketing_executive dropdown, download_type
- **Report on Deposit for Rent / E,W** — `showDepositRentReport` / `depositRentReportPdf` — start/end date, building_name dropdown, download_type
- **Rent Receipt** — `showRentReceiptReport` / `rentReceiptReportPdf` — start/end date, filter_type (Building Name/Code), management_type, download_type
- **General Receipt** — `showGeneralReceiptReport` / `generalReceiptReportPdf` — start/end date, filter_type (Building Name/Code/Account Code), download_type
- **Tenant's Payment History - Agreement Wise** — `showPaymentHistoryReport` / `paymentHistoryReportPdf` — building_name (cascades Unit/Tenant), unit, tenant, download_type; inline AJAX preview panel
- **Rent Amount Collected Through Legal Case** — `showLegalRentAmountReport` / `legalRentAmountReportPdf` — start/end date, download_type
- **Tenancy Details by Building Wise** — `showtenancyDetailsReport` / `tenancyDetailsReportPdf` — filter_type (Building Name/Code/Type/ARE/Tenant Name), value field, download_type
- **Tenant Receivable as on (Date Range)** — `showtenantReceivablesReport` / `tenantReceivablesReportPdf` — filter_type, end_date (required), download_type
- **Tenant Receivable v2 as on (Date)** — `showtenantReceivablesReportV2` / `tenantReceivablesReportPdfV2` (FPDF A3 landscape or Excel)
- **Legal Receivable v2 as on (Date)** — `showLegalReceivablesReportV2` / `legalReceivablesReportPdfV2`
- **Normal Management Report v2** — `showNormalManagementReportV2` — building_id, month, year; async SSE progress flow (`normalManagementReportV2Stream` + `normalManagementReportV2Download/{token}`, zipped if multi-building) with a progress-bar modal
- **Cheque Return Statement** — `showchequeReturnReport` / `chequeReturnReportPdf` — start/end date, filter_type (Building Name/ARE), download_type
- **Unit Take Over Status** — `showUnitTakeoverReport` / `unitTakeoverReportPdf` — start/end date, filter_type (All Buildings/Building Name/Hand Over Date), download_type
- **Legal Case of (Month, Year)** — `showLegalCaseReport` / `legalCaseReportPdf` — start/end date, download_type
- **Monthly Tenancy Details** — `showMonthlyTenancyReport` / `monthlyTenancyReportPdf` (raw SQL, dompdf/Excel) — as_on_date, location_id, management_id, download_type
- **MERA Rent Receipt Report** — `showMeraRentReceiptReport` / `meraRentReceiptReportDownload` — report_month, building_ids[] checkboxes (≥1 required, Select All/Deselect All), download_type; single file or ZIP
- **Leasing Consultant Performance** — `showLeasingConsultantPerformance` — pure analytics dashboard (no PDF), Chart.js charts + KPI cards, AJAX-refreshed (`leasingConsultantPerformanceData`) on filter change (Period YTD/Q1-Q4/Custom, Year, Custom range)
- **Rental Income Report** — `showRentalIncomeReport` / `rentalIncomeReportPdf` — start/end date, building_name (required), download_type
- **Expenses Details** — `showExpenseDetailsReport` / `expenseDetailsReportPdf` — start/end date, building_name (required), report_type (Summary/Detailed), download_type

---

## Sales Module

Covers the Tenant and Landlord lead pipelines (enquiry → assign → in progress → preliminary approval → documentation → final approval → won/lost → contract), plus the call center dashboard and reports.

**Dead/duplicate files not documented in detail:** `SalesEnquiryController.php.bak`, `TenantContractController--.php`, `TenantContractController.php.bak`, `enquiry_form.blade.php.bak`, `enquiry_form_old.blade.php`, and `*-29-1-2019*` / `*_old*` suffixed Blade files under `TenantSales/` and `LandlordSales/`. Route registrations `Route::resource('landloardLeadAssign', ...)` and `Route::resource('landlordLead', ...)` both point at `LandlordStageController` alongside the primary `landlordLeadAssign` resource — redundant/dead route registrations. `SalesController@index`, `salesActivities.index/create`, `TenantContractController@create`, and `LandlordStageController/LandlordContractController@create` return unstyled `sales::index`/`sales::create` "Hello World" stub views — effectively dead/unfinished routes, not real screens.

---

### Tenant Sales

#### Screen: Add / Edit Enquiry (Tenant, Landlord, Maintenance tabs)
- **Route:** `GET enquiry/create` (`enquiry.create`), `GET enquiry/{enquiry}/edit` (`enquiry.edit`), `GET enquiry` (`enquiry.index`)
- **View file:** `Modules/Sales/Resources/views/add_sales_enquiry.blade.php` (includes `sales::enquiry_form`, which includes `maintenance::complaint_form` for the Maintenance tab)
- **Purpose:** Capture a new sales enquiry or edit an existing one, with tabs for Tenant, Landlord, and Maintenance/Complaint enquiries.
- **Buttons/Actions:**
  - Tab links "Tenant" / "Landlord" / "Maintenance" — switch form panel
  - "Submit" — posts to `enquiry.store` or `enquiry.update`
- **Form fields (Tenant tab):** Enquiry No/Date (readonly), Mobile No*, Alternative Mobile No, Customer Name*, Region* (Local/International), Customer Email, Unit Type* (multi-select), Price Range* (multi-select), No. Of Units*, Predefined Areas* (multi-select), Square Meter, Tenant Type, Source*, Referred By, Activities (Retail/Office/Warehouse/Restaurant/Coffee Shop/Others), Move In Date* (Year/Month), Remark
- **Form fields (Landlord tab):** Enquiry No/Date (readonly), Mobile No*, Alternative No, Landlord Name*, Email, Company Name, Property Type, Building Name, Referred By, Source, Square Meter, Location, Region, Price Range, Customer Address, Remark

#### Screen: Tenant Enquiry List
- **Route:** `GET enquiry/tenant` (`tenantEnquiries`)
- **View file:** `Modules/Sales/Resources/views/enquiry_list.blade.php`
- **Purpose:** List/search all tenant enquiries with sortable columns and stage-based status; entry point into the sales pipeline.
- **Buttons/Actions:** "Add" — `enquiry.create` (type=tenant); row View/Edit (edit only while stage "New"); row Reminder (bell, call_center/super_admin); Advance Search toggle
- **Filters/Search:** Inline column filters (Enquiry No, Enquiry Date, First Call Attended, Customer Name, Mobile No, Unit Type, Location, Stage incl. "Referred Back", Last Stage Note, Created By) + Advance Search builder
- **Table columns:** Enq No, Enq Dt, Fc Att, Customer, Mob, Unit, Loc, Status, Last Stage Note, Created By, Action

#### Screen: Landlord Enquiry List
- **Route:** `GET enquiry/landlord` (`landlordEnquiries`)
- **View file:** `Modules/Sales/Resources/views/enquiry_list.blade.php` (same view, type=landlord)
- **Buttons/Actions:** Same pattern as Tenant Enquiry List
- **Table columns:** Enq No, Enq Dt, Customer, Mob, Status, Last Stage Note, Created By, Action

#### Screen: Enquiry Details (View)
- **Route:** `GET enquiry/{enquiry}` (`enquiry.show`)
- **View file:** `Modules/Sales/Resources/views/view_sales_enquiry.blade.php`
- **Purpose:** Read-only detail view of an enquiry, its progression notes, and (once converted) tenant/contract data.
- **Table columns:** Sales Note (Progress, Date & Time); Enquiry Stage Note (Stage, Note, Created By, Created At)

#### Screen: Edit Enquiry (Popup Modal)
- **Route:** `GET enquiryEditPopup/{id}` (`enquiry.enquiryEditPopup`)
- **View file:** `Modules/Sales/Resources/views/edit_sale_enquiry_modal.blade.php` (ajax-loaded modal)
- **Form fields:** Enquiry No/Date (readonly), Mobile No*, Alternative Mobile No, Customer Name*, Region*, Customer Email, Unit Type*, Price Range*, No. Of Units*, Predefined Areas*, Square Meter, Referred By, Move In Date* (Year/Month), Remark

#### Screen: Tenants List
- **Route:** `GET tenants` (`tenants.index`)
- **View file:** `Modules/Masters/Resources/views/Tenant/list.blade.php`
- **Buttons/Actions:** "Add"; row View/Edit; Advance Search toggle
- **Table columns:** Sl No., Code, Name, Mobile No, Status (Normal/VIP), Action

#### Screen: Tenant Create / Edit
- **Route:** `GET tenants/create` (`tenants.create`), `GET tenants/{tenant}/edit` (`tenants.edit`)
- **View file:** `Modules/Masters/Resources/views/Tenant/add_edit.blade.php`
- **Buttons/Actions:** "Save"; "Add" doc row; per-doc "Delete" (AJAX)
- **Form fields:** Tenant Type, Name*; (Individual) Residence ID No*/Exp Date, Nationality*, Passport No, Gender, DOB, Employer Name, Office Location*, Designation, Status; (Company) Company Name, Contact Person, Commercial Reg. No*; Contact block; Bank block; Docs Upload (repeatable)

#### Screen: Tenant Details (View)
- **Route:** `GET tenants/{tenant}` (`tenants.show`)
- **View file:** `Modules/Masters/Resources/views/Tenant/view.blade.php`
- **Buttons/Actions:** "Edit"
- **Table columns (Tenant Agreement):** Sl No., Contract No, Municipal Agr No, Agreement Amt, Building, Unit, Status

#### Screen: Unassigned Leads
- **Route:** `GET /leadAssign` (`leadAssign.index`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_unassigned_list.blade.php`
- **Purpose:** Lists newly-created tenant enquiries (stage 101) not yet assigned to a sales person.
- **Buttons/Actions:** checkbox + header Group Assign; Assign (single row, modal); Close (Reject/Close note modal); Edit (pencil); "Next Stage" view icon
- **Table columns:** checkbox, Enq No, Enq Dt, Customer, Mob No, Unit Type, Location, Last Notes, Action
- **Modals used:** Assign Employee modal; Group Assign Employee modal; Close/Reject note modal

#### Screen: Assigned List
- **Route:** `GET /leadAssignedList` (`leadAssign.assignedList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_assigned_list.blade.php`
- **Purpose:** Lists stage-102 enquiries just assigned, pending first call/action.
- **Buttons/Actions:** Group-Reassign; Close; Edit; "Inprogress" view icon; Re-Assign; Reminder bell (creator only)
- **Table columns:** checkbox, Enq No, Enq Dt, Customer, Mob No, Unit Type, Location, Assigned No, Last Notes, Action

#### Screen: In Progress List
- **Route:** `GET /leadAssign/inprogressList` (`inprogressList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_inprogress_list.blade.php`
- **Purpose:** Lists stage-103 enquiries — first contact made, actively progressing.
- **Table columns:** checkbox, Enq No, Enq Dt, Prog Days, Customer, Mob, Unit, Loc, Assigned, Last Notes, Action

#### Screen: Documentation List
- **Route:** `GET /leadAssign/documentationList` (`documentationList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_documentation_list.blade.php`
- **Purpose:** Lists stage-104 enquiries ("Preliminary Documentation") where a unit is selected and preliminary contract details entered.
- **Table columns:** checkbox, Enq No, Enq Dt, Customer, Building, Unit, Duration (Months), Start Dt, Rent, Assigned, Action

#### Screen: Preliminary Approval List
- **Route:** `GET /leadAssign/preliminaryApprovalList` (`preliminaryApprovalList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_priliminary_approval_list.blade.php`
- **Purpose:** Lists stage-105 enquiries awaiting Accept/Refer-back approval before final documentation.
- **Table columns:** Enq No, Enq Dt, Customer, Building, Unit, Duration (Months), Start Dt, Rent, Assigned, Action

#### Screen: Final Documentation List
- **Route:** `GET /leadAssign/finalDocumentationList` (`finalDocumentationList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_final_document_list.blade.php`
- **Purpose:** Lists stage-106 enquiries — final contract details/documents entered per unit ahead of final approval.
- **Table columns:** Enq No, Enq Dt, Customer, Building, Unit, Start Dt, Rent, Action

#### Screen: Final Documentation Pending Approval List
- **Route:** `GET /leadAssign/finalDocPendingApprovalList` (`finalDocPendingApprovalList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_final_document_pending_list.blade.php`
- **Purpose:** View-only worklist of stage-107 enquiries still awaiting final documentation sign-off (indirect contracts only).
- **Buttons/Actions:** "Documentation" view icon only (no Close/Edit/Re-Assign)
- **Table columns:** Enq No, Enq Dt, Customer, Building, Unit, Duration (Months), Start Dt, Rent, Action

#### Screen: Final Doc Pending — Info Detail
- **Route:** `GET /finalDocPendingApprovalList/{id}/{stage}/tenantPendingStageInfo` (`leadAssign.tenantPendingStageInfo`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_pending_stage_info.blade.php`
- **Purpose:** Read-only detail of a stage-107 enquiry. No action buttons.
- **Table columns:** Stage Notes; Documentation/Tenant Contract (Building, Unit No, Unit Usage/Duration, Attachment, Action=View); Sales Notes

#### Screen: Final Approval List
- **Route:** `GET /leadAssign/finalApproval` (`finalApprovalList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_final_approval_list.blade.php`
- **Purpose:** Lists stage-107 direct-contract enquiries awaiting final Accept/Refer Back/Close before Won.
- **Table columns:** Enq No, Enq Dt, Customer, Building, Unit, Duration (Months), Start Dt, Rent, Action

#### Screen: Won List
- **Route:** `GET /leadAssign/wonList` (`wonList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_won_list.blade.php`
- **Purpose:** Lists stage-108 enquiries converted into signed/won direct tenant contracts. Terminal state, View only.
- **Table columns:** Tenant, Building, Unit No, Unit Type, Duration (Months), Unit Usage, Action

#### Screen: Closed / Lost List
- **Route:** `GET /leadAssign/closedList` (`closedList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_closed_lists.blade.php`
- **Purpose:** Lists stage-109 rejected/closed (lost) enquiries, with option to re-open.
- **Buttons/Actions:** Re-Open (modal); View icon → Closed Detail
- **Table columns:** Customer/Tenant, Mobile No, Closing Remarks, Action

#### Screen: Sales Co-ordinator Assigned List
- **Route:** `GET /leadAssign/AssignedList` (`salesAssignedList`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_sales_assigned_list.blade.php`
- **Purpose:** Personal worklist of stage-102 enquiries created by the logged-in sales coordinator.
- **Table columns:** Enquiry Name, Company, Email, Phone, Enquiry Source, Action

#### Screen: In Progress / First-Call / Documentation Detail
- **Route:** `GET /tenantNextstage/{id}/{stage}` (`leadAssign.nextStage`), stages 101–104
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_inprogress.blade.php`
- **Purpose:** Full-page action screen while an enquiry is Unassigned/Assigned/In-Progress/Documentation.
- **Buttons/Actions:** First Call (advance to 103); Move To Documentation (103→104); Move To Approval; Assign/Reassign; Edit (stage<104); Create Document (104); per-doc Edit/View/Delete; Save note; Add Activity; per-activity Edit/Close/View
- **Table columns:** Stage Note; Documentation (Building, Unit No, Unit Usage, Duration, Attachment, Action); Sales Note; Open/Closed Activity (Subject, Type, Status, Due Date, Time, Note, Created By, Action)
- **Modals used:** Assign/Re-assign Employee modal; Documentation modal; View Documentation modal; Add Activity modal; View Activity modal

#### Screen: Preliminary Approval / Final Documentation / Final Approval Detail
- **Route:** `GET /tenantNextstage/{id}/{stage}` (`leadAssign.nextStage`), stages 105–107
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_priliminary_approval.blade.php`
- **Buttons/Actions:** Accept (105/107); Refer Back/Reject (105/107, note modal); Reject/Close (→109); Reassign (106/107); Accept (106); Renegotiate (106); per-unit View/Edit/Accept/Reject toggle (106); Contract icon (106-107) → contractCreation
- **Table columns:** Documentation table (105-106) or Tenant Contract table (≥107: Contract No, Tenant Name, Municipality Agr No, Rent Amount, Action); Sales Note; Enquiry Stage Note

#### Screen: Won Detail
- **Route:** `GET /tenantNextstage/{id}/108`
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_won_detail.blade.php`
- **Purpose:** Read-mostly summary of a won tenant contract.
- **Table columns:** Enquiry Stage Note; Tenant Contract (Contract No, Tenant Name, Municipality Agr No, Rent Amount, Action)

#### Screen: Closed Detail
- **Route:** `GET /tenantNextstage/{id}/109`
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_close_details.blade.php`
- **Purpose:** Read-only summary of a closed/lost enquiry.
- **Table columns:** Enquiry Stage Note; Sales Note; Open/Closed Activity

#### Screen: Tenant Contract Documentation (Modal)
- **Route:** `GET tenant/documentation/{id}` (`contractDocumentation`), `GET tenantContract/{tenantContract}/edit` (`tenantContract.edit`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_documentation_modal.blade.php`
- **Form fields:** Property Section (Building Name*, Unit No*, Unit Type readonly, Unit Usage*); Tenant Section (Exist/Not Exist, search, Name*, Tenant Type, ID/Nationality/Designation or Company details, Mobile No*); Contract Section (Start/Effective/End Date*, Rent Per Month*, Contract Value auto, Duration readonly, Payment Method*, uploads, Marketing Executive*, Remark, PDC, Deposit)

#### Screen: Tenant Contract Creation
- **Route:** `GET tenantContract/contractCreation/{id}/{stage}` (`contractCreation`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_contract_create.blade.php` (full page)
- **Purpose:** Finalize the tenant agreement — full contract terms, occupant info, payment/deposit, document uploads.
- **Form fields:** Agreement No/Date; Building/Unit/Tenant confirmation; Occupant Name*/Mobile*/Email; Contract Details (Start/Effective/Valid To*, Rent*, Contract Value auto, Duration, Vacant Since, Rent Paid by Previous Tenant, Municipality Agr. No, Deposit Electric/Water, Contract Registered In*, Payment Term*, Remark, uploads, Marketing Executive*, PDC, Deposit); Payment Details (Deposit Rent Amount, Guarantee Cheque Amount, Receipt No/Date/Amount); Document Upload (repeatable, max 10)

#### Screen: Tenant Contract View (Modal)
- **Route:** `GET tenantContract/{tenantContract}` (`tenantContract.show`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_view_documentation_modal.blade.php`
- **Purpose:** Read-only display of a tenant contract.

#### Screen: Sales Activity (Add/Edit Modal)
- **Route:** `GET salesActivities/activities/{id}` (`salesActivity`), `GET salesActivities/{salesActivity}/edit` (`salesActivities.edit`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_add_activity_modal.blade.php`
- **Form fields:** Subject*, Activity Type* (Email/Phone/Task/Appointment), Due Date*, Time, Note*, Description

#### Screen: Sales Activity View (Modal)
- **Route:** `GET salesActivities/{salesActivity}` (`salesActivities.show`)
- **View file:** `Modules/Sales/Resources/views/TenantSales/tenant_view_activity_modal.blade.php`
- **Purpose:** Read-only display of a logged sales activity.

---

### Landlord Sales

*Note: `landloardLeadAssign` and `landlordLead` resource routes are duplicate registrations pointing to the same `LandlordStageController` as `landlordLeadAssign` — treat as one screen set in the redesign.*

#### Screen: Landlord Leads List
- **Route:** `GET landlordLeadAssign` (`landlordLeadAssign.index`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_lead_list.blade.php`
- **Purpose:** Lists landlord enquiries at stage 201 (newly created, not yet accepted/actioned).
- **Buttons/Actions:** Row Edit; row Close (modal); row View (accept icon) → Landlord Accept detail
- **Table columns:** Sl No, Enquiry No, Customer Name, Building Name, Email, Phone, Action

#### Screen: Landlord Lead Accept/Close Detail (stage 201)
- **Route:** `GET landlordNextstage/{id}/201` (`landlordLeadAssign.nextStage`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_accept.blade.php`
- **Purpose:** First-stage action screen — Accept (moves toward contract stage 202) or Close (reject), plus notes/activities.
- **Buttons/Actions:** Close (note modal); Accept; Save note

#### Screen: Landlord Contracts (Draft) List
- **Route:** `GET landlordContract` (`landlordContract.index`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_contract_list.blade.php`
- **Purpose:** Lists landlord enquiries at stage 202 (accepted, direct contract) needing a draft contract.
- **Buttons/Actions:** View; View Enquiry Details; Close; Edit Contract (if exists); Create Contract (if none)
- **Table columns:** Enquiry No, Enquiry Name, Building Name, Email, Phone, Action

#### Screen: Landlord Contract Generation (Create Draft Contract)
- **Route:** `GET contractGeneration/{id}` (`contractGeneration`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php` (full page)
- **Purpose:** Create a new draft landlord/management contract, tying it to a Building and Vendor (landlord).
- **Buttons/Actions:** Save/submit; "+ Add Landlord" popup; "+ Add Building" popup
- **Form fields:** Building (autocomplete)*, Vendor/Landlord Name (autocomplete)*, Contract No (auto), Duration Type, Management Fee/%, Contract Amount, Management Type, Free Lease Period, Marketing Executive, Payment Type, Valid From/To*, Management Method/Fee Type, Note, Close Activity, Start Date
- **Modals used:** Create Landlord popup; Create Building popup

#### Screen: Landlord Contract Info (View)
- **Route:** `GET landlordContract/{id}` (`landlordContract.show`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_contract_info.blade.php`
- **Purpose:** Read-only display of a landlord contract plus enquiry stage note history.

#### Screen: Landlord Contract Edit
- **Route:** `GET landlordContract/{landlordContract}/edit` (`landlordContract.edit`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php`
- **Form fields:** Same set as Contract Generation

#### Screen: Landlord Documentation Approval List
- **Route:** `GET landlordcontractApprovalList` (`contractApprovalList`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_approve_list.blade.php`
- **Purpose:** Lists stage-203 landlord contracts (documentation submitted) awaiting Approve/Close decision.
- **Buttons/Actions:** Edit; View; Close (note modal); Approve
- **Table columns:** Enquiry No, Agrmt No, Agrmt Dt, Landlord, Mgmt Type, Building, Duration, To, Action

#### Screen: Landlord Documentation Pending Approval List
- **Route:** `GET landlordPendingApproval` (`landlordPendingApproval`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_approve_list.blade.php` (pending variant)
- **Purpose:** Variant scoped to contracts still pending/in-draft (stage 203).

#### Screen: Landlord Contract Approval Info (Detail)
- **Route:** `GET landlordcontractApprovalInfo/{id}/{stage}` (`contractApprovalListInfo`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_info.blade.php`
- **Purpose:** Read/action detail page for a landlord contract in the Documentation Approval List.

#### Screen: Landlord Contract Approval Pending Info (Detail)
- **Route:** `GET landlordPendingApproval/{id}/{stage}/contractApprovalListPendingInfo` (`contractApprovalListPendingInfo`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_pending_info.blade.php`
- **Purpose:** Read-only variant of the Approval Info detail page for the Pending Approval list.

#### Screen: Landlord Won List
- **Route:** `GET landlordContractWon` (`contractApprovalWon`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_won_list.blade.php`
- **Purpose:** Lists stage-204 approved/won landlord contracts. View only (terminal state).
- **Table columns:** Sl No, Enquiry No, Customer Name, Building Name, Email, Phone, Action

#### Screen: Landlord Loss List
- **Route:** `GET landlordContractLoss` (`contractApprovalLoss`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_loss_list.blade.php`
- **Purpose:** Lists stage-205 rejected/closed (lost) landlord contracts/enquiries, with option to re-open.
- **Buttons/Actions:** Re-Open (modal); View
- **Table columns:** Sl No, Enquiry No, Enquiry Name, Building Name, Email, Phone, Action
- **Modals used:** Re-assign Employee modal ("Re Open") — select Role, "SAVE" reactivates enquiry at previous stage

#### Screen: Landlord Loss Detail (stage 205)
- **Route:** `GET landlordNextstage/{id}/205`
- **View file:** `Modules/Sales/Resources/views/LandlordSales/landlord_accept.blade.php` (shared with stage 201)
- **Purpose:** Detail view of a closed/lost landlord contract — enquiry info, contract details, notes, activities.

#### Screen: Landlord Enquiry View
- **Route:** `GET enquiry/landlordContract/{id}` (`landlordEnquiryView`)
- **View file:** `Modules/Sales/Resources/views/LandlordSales/view_sales_enquiry.blade.php`
- **Purpose:** Read-only view of the original landlord enquiry, with stage/sales note history.

---

### General/Dashboard/Reports

#### Screen: Sales Home (Placeholder)
- **Route:** `GET sales/` (no name)
- **View file:** `Modules/Sales/Resources/views/index.blade.php`
- **Purpose:** Placeholder landing page ("Hello World" stub) — unfinished/unused scaffold.

#### Screen: Call Center Dashboard
- **Route:** `GET call-center` (`callCenter`)
- **View file:** `Modules/Sales/Resources/views/call_center_dashboard.blade.php`
- **Purpose:** Landing dashboard for call-center staff — today's/this-week's enquiry call counts, with a quick "add enquiry" form below.
- **Buttons/Actions:** "Submit" (embedded enquiry form)
- **Table columns:** Two stat tiles: "Calls received today!", "Calls received this week!"

#### Screen: Sales Enquiries Report
- **Route:** `GET showSalesEnquiries` (`showSalesEnquiries`)
- **View file:** `Modules/Sales/Resources/views/Reports/sales_enquiry_report.blade.php`
- **Purpose:** Generate a downloadable (PDF/Excel via JasperReports) enquiry report filtered by date range, unit type, location, source, and assigned salesperson.
- **Buttons/Actions:** "Generate" — submits to `salesEnquiryReportPdf` (POST, opens new tab)
- **Form fields:** Start Date*, Valid To, Unit Type, Location, Enquiry Source, Assigned To, Download Type* (PDF/Excel)

---

## Maintenance Module

### Complaints / Enquiries

#### Screen: Complaint List
- **Route:** `GET /complaint` (`complaint.index`)
- **View file:** `Modules/Maintenance/Resources/views/complaint_enquiry_list.blade.php`
- **Buttons/Actions:** Add; Advance Search toggle; View; Edit (coordinator/super_admin, only while status==0); Delete (disabled); Reminder (call_center role, when tickets exist)
- **Filters/Search:** Inline column filters (Complaint No, Created date, Complainer Name, Mobile No, Building, Unit, Location, Priority, Status) + Advance Search builder
- **Table columns:** Comp No, Time Since, Comp Name, Comp Mob No, Building, Unit, Location, Priority, Status, Action

#### Screen: Add / Edit Complaint
- **Routes:** `GET /complaint/create` (`complaint.create`), `GET /complaint/{id}/edit` (`complaint.edit`)
- **View file:** `Modules/Maintenance/Resources/views/add_complaint_enquiry.blade.php`
- **Form fields:** Category radio (Occupied/Vacant/Other), Complaint No/Date, Registered Mobile No, Resident Card ID, Complainer Name/Mobile, Building/Unit (autocomplete), Tenant/Occupant Name (occupied), Location, Way No, Priority, Preferred Time, Ticket List (checkbox grid with Description per item)

#### Screen: View Complaint
- **Route:** `GET /complaint/{id}` (`complaint.show`)
- **View file:** `Modules/Maintenance/Resources/views/complaint_enquiry_view.blade.php`
- **Buttons/Actions:** Edit; per-ticket Edit (modal, ticket_status<1); "View Service Report" (AJAX modal)
- **Table columns (Tickets):** Ticket, Category, Description, Supervisor, Technician, Work Order Des, Ticket Status, Action

---

### Ticket Assignment

#### Screen: Unassigned Complaint List / View
- **Routes:** `GET complaintStage` (`complaintStage.index`); `GET /complaintStage/{id}/UnAssignedList` (`complaintUnassigned.view`)
- **View files:** `ComplaintStages/complaint_unassigned_list.blade.php` / `_view.blade.php`
- **Buttons/Actions:** Add; View; Edit (0 tickets only); Close (modal); Reminder; Group Assign (checked tickets); per-ticket Assign
- **Table columns:** Cmp No, Cmp Date, Cmp Name, Cmp Mob No, Building, Unit, Location, Priority, Status, Action
- **Modals used:** `complaint_close_modal.blade.php`, `complaint_assign_modal.blade.php`

#### Screen: Assigned Complaint List / View
- **Routes:** `GET/POST complaintStage/AssignedList/` (`complaintAssignedList`); `GET /AssignedList/{id}` (`complaintAssigned.view`)
- **Buttons/Actions:** Assign (bulk sub-assign); Group-Reassign; View; Edit (coordinator, status==0); Close; SubAssign; Reassign; Service Report; Reminder
- **Table columns:** Checkbox, Tkt No, Cmp Date, Cmp Mob No, Building, Unit, Category, Assigned To, Status, Action
- **Modals used:** `complaint_subassign_modal.blade.php`, `complaint_assign_modal.blade.php`, `ticket_close_modal.blade.php`

---

### Sub-Contractor / Technician Assignment

#### Screen: Sub-Assigned Complaints List / View
- **Routes:** `GET/POST complaintStage/SubAssignedList` (`complaintSubAssignedList`); `GET /SubAssignedList/{id}/{sub_assign}` (`complaintSubAssigned.view`)
- **Buttons/Actions:** Group-Reassign; View; Edit; Close; SubReAssign; Service Report (view or edit depending on permission); Check In
- **Table columns:** Checkbox, Cmp No, Cmp Date, Cmp Name, Mob No, Building, Unit, Supervisor, Technician, Status, Action

---

### Service Reports & Check-in

#### Screen: Contractor Service Report
- **Route:** `GET AssignedList/{id}/serviceReport/` (`contractorServiceReport`)
- **Buttons/Actions:** Attended/Completed/Closed status buttons; edit status (modal); Save note; Add item; Edit/Delete item; Upload images
- **Table columns:** Notes; Inventory items (Item Name, Quantity, Action); Image gallery

#### Screen: Technician Service Report / Closed / View
- **Routes:** `GET SubAssignedList/{id}/{sub_assign}/serviceReportTechnician/`; `GET ClosedList/{id}/{sub_assign?}/serviceReportTechnician/`; `GET SubAssignedList/{id}/{sub_assign}/serviceReportView/`
- **Purpose:** Technician working screens (same note/item/image/status workflow); Closed variant adds Material/Labour Charge fields; View variant is read-only.
- **Modals used:** `service_report_update_status_modal.blade.php`, `service_report_item_edit_modal.blade.php`

#### Screen: Tenant Signature Capture
- **Route:** `GET complaintStage/signature` (`signature`)
- **Purpose:** Canvas signature pad embedded via iframe; finalizes ticket closure and notifies tenant.

---

### Review & Landlord Approval

#### Screen: Complaint Review List / View
- **Routes:** `GET/POST complaintStage/ReviewList` (`complaintReviewList`); `GET /ReviewList/{id}` (`complaintReview.view`)
- **Purpose:** Completed service reports pending landlord review/approval.
- **Buttons/Actions:** Send For Landlord Approval; Approve/Approve on behalf; Reject/Reject on behalf (modal)

---

### Closure

#### Screen: Closed / Completely Closed Complaints List / View
- **Routes:** `GET/POST complaintStage/ClosedList`, `/CompletelyClosedList`; `GET /ClosedList/{id}`, `/CompletelyClosedList/{id}`
- **Buttons/Actions:** View; Close (coordinator/super_admin); per-ticket View Service Report / Edit Service Report

---

### AMC Contracts

#### Screen: AMC Contract List / Add-Edit / View
- **Routes:** `GET amcContract` (`amcContract.index`), `/create`, `/{id}/edit`, `/{id}`
- **Buttons/Actions:** Add; View; Edit (active + no active schedule); Cancel (AJAX, warns if schedule exists)
- **Form fields:** Contract No (auto), Contractor (autocomplete), Building (autocomplete), Start/End Date (min 30-day gap), Frequency, Cost, Amenity picker + grid

### AMC Schedules

#### Screen: AMC Schedule List / Add-Edit / View
- **Routes:** `GET amcSchedule` (`amcSchedule.index`), `/create`, `/{id}/edit`, `/{id}`
- **Purpose:** Create/edit schedule against a sub-contractor AMC Contract or as in-house technician assignment; builds amenity task plan.
- **Buttons/Actions:** Contractor Type toggle; Add amenity; Process (auto-generate task schedule); Add/Edit/Remove task
- **Modals used:** Add Task, Edit Task, Process Result modals (sub-contractor and technician variants)

### AMC Tasks

#### Screen: AMC Task List / View / Edit
- **Routes:** `GET amcTask` (`amcTask.index`), `/{id}`, `/{id}/edit`
- **Buttons/Actions:** View; Close (validated then Remark modal); Reminder
- **Note:** `AmcTaskController@create` is dead — tasks actually created via modals from Schedule flows.

---

### Maintenance Invoices

#### Screen: Maintenance Invoice List / Approval List
- **Routes:** `GET /maintenanceInvoice` (`maintenanceInvoice.index`); `GET /maintenanceInvoiceApproval`
- **Buttons/Actions:** Add; View; Post (to AX); Edit; Delete; Send For Approval/Unapproval; Approve/Unapprove; Reject
- **Table columns:** Sl No., Invoice No, Invoice Date, Vendor Name, Vendor Code, Amount, Status/Purpose, Action

#### Screen: Add / Edit Maintenance Invoice
- **Routes:** `GET /maintenanceInvoice/create`, `/{id}/edit`
- **Form fields:** Invoice No (auto), Invoice Date, Vendor Name/Code, Description, Ref No, Amount (auto), Comments; repeating Dimension Details rows (Account Code, Building, Unit, Charges, Debit/Credit, Recovery, Dim1/2)

#### Screen: Maintenance Invoice View
- **Routes:** `GET /maintenanceInvoice/{id}`, `/maintenanceInvoiceApproval/{id}`
- **Buttons/Actions:** Approve/Unapprove; Reject; Delete (draft only); Send for Approval/Unapproval; Post; Edit (draft/rejected only)

#### Screen: Generate Maintenance Invoice (Group Invoice Generation)
- **Route:** `GET/POST /groupInvoiceGeneration` (`groupInvoiceGeneration`)
- **Purpose:** Search closed/approved service reports within a date range and bulk-generate invoices grouped by vendor/technician.
- **Form fields:** Start Date, End Date
- **Table columns:** Checkbox, Sl No., Vendor, Building, Unit No, Service Report, Material Charge, Labour Charge, Amount, Tech

---

### Maintenance Reports

Criteria-form → Jasper-generated PDF/Excel report screens:
- **Maintenance Report (Category Wise)** — `showMaintenanceReport`/`maintenanceReportPdf` — Start/End Date
- **Complaint Status Report** — `showComplaintStatusReport`/`complaintStatusReportPdf` — From/To Date
- **Complaint Ticket Report** — `showComplaintTicketReport`/`complaintTicketReportPdf` — 16 predefined variants (category/building/contractor/technician-wise, VIP, unit-wise, etc.)
- **Service Report** — `showServiceReport`/`serviceReportPdf` — Building Name / Complaint No filter

**Dead/duplicate files noted:** `ComplaintStages/complaint_reassign_modal.blade.php` (unreachable — no matching controller method), `ComplaintStages/old_sub_assign.blade.php`, `Http/Controllers/bkup2422021/*` backup controllers, `Amc/contract_filter.blade.php`/`schedule_filter.blade.php`/`task_filter.blade.php` (duplicated instead of shared), `Invoice/bkup2422021/*` backups.

---

## Masters Module

Most listing screens follow a shared pattern: server-side pagination, sortable headers, a collapsible "Advance Search" panel, row-level permission-gated View/Edit/Delete icons, and a Status pill that POSTs to a `changeStatus` route after a confirm().

### Building Types
#### Screen: Building Type List / Create / Edit / View
- **Routes:** `GET /buildingType` (`buildingType.index`), `/create`, `/{id}/edit`, `/{id}`
- **View files:** `Modules/Masters/Resources/views/BuildingType/{list,add_edit,view}.blade.php`
- **Buttons/Actions:** Add; View/Edit/Delete icons; Status toggle
- **Table columns:** Sl No., Name, Status, Action
- **Form fields:** Name (required, unique, max 25), Description

### Unit Types
#### Screen: Unit Type List / Create / Edit / View
- **Routes:** `GET /unitType` (`unitType.index`), `/create`, `/{id}/edit`, `/{id}`
- **View files:** `Modules/Masters/Resources/views/UnitType/{list,add_edit,view}.blade.php`
- **Form fields:** Name (required, unique, max 25), Description

### Tenant Statuses
#### Screen: Tenant Status List / Create / Edit / View
- **Routes:** `GET /tenantStatus` (`tenantStatus.index`), `/create`, `/{id}/edit`, `/{id}`
- **View files:** `Modules/Masters/Resources/views/TenantStatus/{list,add_edit,view}.blade.php`
- **Form fields:** Name (required, unique, max 25), Description
- **Note:** Edit view's default breadcrumb fallback route is `building-amentity.index` — copy/paste bug.

### Tenant Types
#### Screen: Tenant Type List / Create / Edit / View
- **Routes:** `GET /tenantType` (`tenantType.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Name (required, unique, max 25), Description

### Amenity Types
#### Screen: Amenity Type List / Create / Edit / View
- **Routes:** `GET /amentityType` (`amentityType.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Name (required, max 25), Description

### Works
#### Screen: Work List / Create / Edit / View
- **Routes:** `GET /work` (`work.index`), `/create`, `/{id}/edit`, `/{id}`
- **Purpose:** "Work" codes for maintenance/AMC job classification, linked to an accounting code.
- **Form fields:** Code (required, unique, max 20), Work Type (Both/Maintenance/AMC), Acc Code (select2), Description
- **Table columns:** Sl No., Code, Type, Status, Action

### Reasons
#### Screen: Reason List / Create / Edit / View
- **Routes:** `GET /reason` (`reason.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Code (required, unique, max 10), Description

### Locations
#### Screen: Location List / Create / Edit / View
- **Routes:** `GET /location` (`location.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Region (required select), Code (required, max 25), Name (required, max 25)
- **Note:** `LocationController::store()` is entirely commented out — create form is non-functional; only edit works. Flag for redesign.
- **Non-screen endpoints:** `locationAutocomplete`, `locationsAutocomplete` (typeahead JSON)

### Countries
#### Screen: Country List / Create / Edit / View
- **Routes:** `GET /country` (`country.index`), `/create`, `/{id}/edit`, `/{id}`
- **Buttons/Actions:** Delete suppressed for protected record id==6
- **Form fields:** Country Code (required, unique, max 4), Country Name (required, max 25)

### Job Categories
#### Screen: Job Category List / Create / Edit / View
- **Routes:** `GET /jobCategory` (`jobCategory.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Code (required, unique, max 8), Name (required, max 25)
- **Note:** Controller middleware checks `add_currency`-family permissions — copy/paste bug from CurrencyController.

### Currencies
#### Screen: Currency List / Create / Edit
- **Routes:** `GET /currency` (`currency.index`), `/create`, `/{id}/edit`
- **Buttons/Actions:** Delete hidden for protected id=1
- **Form fields:** Currency Code (required, max 4, unique), Currency Name (required, max 25)

### Contractor Work Links
#### Screen: Contractor Work Link List / Create / Edit
- **Routes:** `GET /workLink` (`workLink.index`), `/create`, `/{id}/edit`
- **Form fields:** Vendor (select, required), Work (select, required)

### Banks
#### Screen: Bank List / Create / Edit
- **Routes:** `GET /bank` (`bank.index`), `/create`, `/{id}/edit`
- **Form fields:** Code (required, unique, max 25), Name (required, max 25), Branch (required, max 25), Bank Cheque-book Id, Division (PLM/HO), Accounts (Yes/No — markup bug: duplicate `name="dim1Value"` shared with Division), Remark

### Home Amenities (Home Utilities)
#### Screen: Home Amenities List / Create / Edit
- **Routes:** `GET /homeUtility` (`homeUtility.index`), `/create`, `/{id}/edit`
- **Form fields:** Utility Code (required, unique, max 25), Category (Asset/Amenity, required), Make, Model, Serial No, Description

### Management Types
#### Screen: Management Type List / Create / Edit
- **Routes:** `GET /managementType` (`managementType.index`), `/create`, `/{id}/edit`
- **Buttons/Actions:** Add/Delete restricted to Auth::id()==1
- **Form fields:** Name (required, unique, max 25), Description

### Invoice Types
#### Screen: Invoice Type List / Create / Edit
- **Routes:** `GET /invoiceType` (`invoiceType.index`), `/create`, `/{id}/edit`
- **Form fields:** Name (required, unique, max 25), Description

### Payment Methods
#### Screen: Payment Method List / Create / Edit
- **Routes:** `GET /paymentMethod` (`paymentMethod.index`), `/create`, `/{id}/edit`
- **Form fields:** Code (required, unique, max 25), Description

### Enquiry Sources
#### Screen: Enquiry Source List / Create / Edit
- **Routes:** `GET /enquirySource` (`enquirySource.index`), `/create`, `/{id}/edit`
- **Form fields:** Enquiry Source Name (required, unique, max 25)
- **Note:** Row Show-link bug — falls back to `reason.show`.

### Vendor Types
#### Screen: Vendor Type List / Create / Edit
- **Routes:** `GET /vendorType` (`vendorType.index`), `/create`, `/{id}/edit`
- **Form fields:** Vendor Type name (required, unique, max 25)

### Inventory
#### Screen: Inventory List / Create / Edit
- **Routes:** `GET /inventory` (`inventory.index`), `/create`, `/{id}/edit`
- **Purpose:** Inventory item master (name/brand). No status toggle exists.
- **Form fields:** Name (required, unique, max 25), Brand Name (max 50), Description (bug: bound to `inventories_name` instead of `inventories_desc`)

### Price Ranges
#### Screen: Price Range List / Create / Edit
- **Routes:** `GET /priceRange` (`priceRange.index`), `/create`, `/{id}/edit`
- **Form fields:** From (numeric, required), To (numeric, required, ≥ From); Name auto-derived server-side as "From-To"

### Complaint Reasons
#### Screen: Complaint Reason List / Create / Edit
- **Routes:** `GET /complaintReason` (`complaintReason.index`), `/create`, `/{id}/edit`
- **Form fields:** Complaint Name (required, unique, max 25), Complaint Description

### Designations
#### Screen: Designation List / Create / Edit
- **Routes:** `GET /designation` (`designation.index`), `/create`, `/{id}/edit`
- **Form fields:** Designation Code (required, unique, max 10), Designation Name (required, max 25)

### Sub Works
#### Screen: Sub Work List / Create / Edit
- **Routes:** `GET /subWork` (`subWork.index`), `/create`, `/{id}/edit`
- **Purpose:** Sub-work linked to a parent Work. No status column/permission gating (controller lacks it entirely — inconsistent with siblings).
- **Form fields:** Work (select, required), Sub Work (text, required, max 50)

### Employees
#### Screen: Employee List
- **Route:** `GET /employee` (`employee.index`)
- **Buttons/Actions:** Add; Status toggle; View; Edit; Reset Password (key icon); Delete present but disabled
- **Table columns:** Sl No., Employee Code, Employee Name, Employee Designation, Employee Mobile, Status, Action

#### Screen: Employee Create / Edit / View
- **Routes:** `GET /employee/create`, `/{id}/edit`, `/{id}`
- **Purpose:** Create/edit employee + linked user account; sends welcome email; optionally syncs to Microsoft Dynamics AX.
- **Form fields:** Employee Code (auto, editable, unique), Username (required, unique), Name, Email (required, unique), Contact No, Secondary Contact No, Addresses, DOB, Designation, Role (multi-select), Head Role/User (AJAX), Default Role, Job Category (required for technical_head/technician), Profile Upload (image, max 2MB, resized to 100x100)
- **Buttons/Actions:** Save; Delete image (AJAX)
- **Dead files:** `Employee/add_edit.blade.php.bak`, `list.blade--.php`, `list.blade-9-01-2019.php`

### Vendors
#### Screen: Vendor List
- **Route:** `GET /vendors` (`vendors.index`)
- **Buttons/Actions:** Add; Status toggle; View; Edit
- **Note:** `VendorController::destroy()` is an empty stub — no working Delete.
- **Table columns:** Sl No., Name, Vendor Code, Vendor Type, Status, Action

#### Screen: Vendor Create / Edit / View
- **Routes:** `GET /vendors/create`, `/{id}/edit`, `/{id}`
- **Purpose:** Create vendor (contractor or landlord); syncs contractor-type vendors to AX.
- **Form fields:** Vendor Type, Vendor Code (live uniqueness AJAX), Name, Contact Person, Contact Number (default "00968"), Contact Email, Fax No., Status, Addresses, PostCode, Location, Bank, Account No.

### Buildings
#### Screen: Building List
- **Route:** `GET /building` (`building.index`)
- **Buttons/Actions:** Add; Advance Search toggle; Grid/Picture toggle; View; Edit
- **Filters/Search:** Building Name, Landlord Name, Management Type, Status — AJAX
- **Table columns:** Sl No., Building Name, Landlord Name, Management Type, Status, Action

#### Screen: Building Create / Edit
- **Routes:** `GET /building/create`, `/{id}/edit`
- **Purpose:** Large multi-section form — core details, landlord/management, repeatable meter readings/images/documents.
- **Form fields:** Building Code (auto), Building Name*, Building Prefix*, Building Year, Landlord Name* (autocomplete), Building No.*, Way No*, Block Number, Plot No, Landmark, Location*, Building Type*, Number Of Floors*, Watchman No, Management Type*, Management Date, Build-up Area, Google Location*, Building Address*, Tel DB No., Note*, Division*; repeatable Meter Details/Images/Docs

#### Screen: Building View (Detail / Tabs)
- **Route:** `GET /building/{id}` (`building.show`)
- **Purpose:** Read-only with embedded map, gallery, tabs: Unit Detail, Amenity, Insurance, Documents, Meter Details, Units
- **Buttons/Actions:** Edit; Unit Detail "Update"; Insurance Add/Edit/View/Delete; Amenity Add/Edit/View/Delete

### Units
#### Screen: Unit List
- **Route:** `GET /unit` (`unit.index`)
- **Buttons/Actions:** Add; View; Edit; QR Code download; Status toggle (only when vacant)
- **Table columns:** Sl No., Unit Code, Unit No, Building, Unit Type, Vacant, Status, Action

#### Screen: Unit Create / Edit
- **Routes:** `GET /unit/create`, `/{id}/edit`
- **Form fields:** Building Name* (autocomplete), Building Prefix* (readonly), Unit No*, Unit Code* (auto), Unit Type*, Base Rent, Floor No* (dynamic), Floor Area, No. Of Toilets, Electric/Water A/c+Meter No, Service, Vacant/Occupied?*, Furnished?*, Status*, Unit Note
- **Modals used:** "Existing Units" modal

#### Screen: Unit View (Detail / Tabs)
- **Route:** `GET /unit/{id}` (`unit.show`)
- **Purpose:** Read-only + Assets/Amenities tabs (checkbox+count, Update)

### Unit Utilities
#### Screen: Unit Utility List / Create / Edit / View
- **Routes:** `GET /unit-utility` (`unit-utility.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Home Utility* (select), Unit* (select), AMC Contract No.* (text), Remark

### Building Amenities
#### Screen: Building Amenity List / Create / Edit / View
- **Routes:** `GET /building-amentity` (`building-amentity.index`), `/create`, `/{id}/edit`, `/{id}`
- **Buttons/Actions:** Delete shown only when no AMC contract number set
- **Form fields:** Amenity Type* (select), Building* (select), AMC Contract No, Remark

### Building Insurance
#### Screen: Building Insurance List / Create / Edit / View
- **Routes:** `GET /building-insurance` (`building-insurance.index`), `/create`, `/{id}/edit`, `/{id}`
- **Form fields:** Insurance Company* (unique on create), Building* (select), Insurance Policy Type*, Insurance Premium*, Valid From/To* (To after From), Insured By* (Landlord/Al-habib), Insurance Building Value*, Debit Acc, Upload (jpeg/jpg)
- **Note:** View template bug — Insured By field displays `insurance_policy_type` again instead of `insurance_insured_by`.

### ARE Building Assign
#### Screen: ARE Building Assign List / Create / Edit / View
- **Routes:** `GET /areBuildingAssign` (`areBuildingAssign.index`), `/create`, `/{id}/edit`, `/{id}`
- **Purpose:** Which buildings each ARE (Area Relationship Executive) is responsible for.
- **Form fields:** ARE (autocomplete, locked on edit), From Date, Building Name (dual drag-and-drop list widget)
- **Note:** Delete form in markup but `destroy()` is an empty stub.

#### Screen: ARE Team Lead Group List / View
- **Routes:** `GET|POST /groupAreList` (`group_are_list`); `GET /groupView/groupAreList/{id}` (`groupView`)
- **Purpose:** For `are_team_lead` role — shows assignments for the lead plus subordinate ARE staff.

### Tenancy Details
#### Screen: Tenancy Details List (Occupancy Report)
- **Route:** `GET /tenancyDetails` (`tenancyDetails.index`)
- **Purpose:** Read-only occupancy/tenancy report off Unit records; `create`/`store`/`show`/`edit`/`update`/`destroy` are unused stubs — not a true CRUD resource.
- **Table columns:** Unit No, Unit Type, Tenant Name (or Vacant), Contract Number, Contract Period From/To, Rent, Payment Mode, Rent Received Till + totals row
- **Note:** Live Tenant master CRUD actually lives in `Modules/Masters/Resources/views/Tenant/` served by `Modules/Sales/Http/Controllers/TenantController.php` (see Sales module doc).

### Legal Case Management
#### Screen: Legal Case List / View
- **Routes:** `GET /legalCase` (`legalCase.index`); `GET /legalCase/{id}` (`legalCase.show`)
- **Purpose:** Master list of legal cases; workflow entry point routing to PLMS/Lawyer approval.
- **Buttons/Actions:** Verification (opens `are_stage_model` modal, at flow 804)
- **Table columns:** Agreement No, Name, Building, Unit No, To, Status, Rent, Legal Status, Action
- **Note:** `legalCase.create`/`edit` are dead scaffolding; cases created via `areStageStore` workflow action.

#### Screen: PLMS Approval List / Detail
- **Routes:** `GET /legalCase/plmsApproval` (`plmsApproval`); `GET /plmsApproval/{id}` (`plmsApprovalShow`)
- **Buttons/Actions:** Approve/Refer Back (note modal); Close; View

#### Screen: Lawyer Approval List / Detail
- **Routes:** `GET /legalCase/lawyerApproval` (`lawyerApproval`); `GET /lawyerApproval/{id}` (`lawyerApprovalShow`)
- **Purpose:** Cases approved by PLMS pending lawyer review; detail computes rent-arrears figures.
- **Buttons/Actions:** Close; Refer Back (to PLMS); Approve (marks closed/won)

#### Screen: Active Cases List / Detail
- **Routes:** `GET /legalCase/activeCases` (`activeCases`); `GET /activeCases/{id}` (`activeCasesShow`)
- **Purpose:** Working case file — rent-arrears calc, old contracts, notes, documents.
- **Buttons/Actions:** Add Note (Start/Middle/End — End closes case); Document Upload Add/Delete

#### Screen: Closed / Refer-Back Legal Cases List / Detail
- **Routes:** `GET /closedLegalCases`, `/{id}`; `GET /referBackCases`, `/{id}`
- **Purpose:** Legally concluded cases / cases referred back-rejected.

#### Screen: PDC Detail / Legal Tenant Contract View
- **Routes:** `GET /legalPdcShow/{id}`; `GET /legaltenantContractShow/{id}`
- **Purpose:** Read-only PDC transactions / old tenant contract snapshot, linked from case detail screens.
- **Dead files:** `legal_case_list.blade.php.bak`, `lawyer_approval_list.blade.php.bak`, `activa_cases_view.blade.php` (misspelled duplicate)

### Master Reports
Criteria-form → generated PDF/Excel report screens (JasperReports, each with a "Search/Generate" button):
- **Building Details Report** — `showBuildingDetails`/`showBuildingDetailsReport` — filter by Landlord/Location/Management
- **Building Unit Details Report** — `showBuildingUnitDetails`/`...Report` — filter by Building Name/Code/Management
- **Furnished Unit Report** — `showFurnishedUnitReport`/`furnishedUnitReportPdf` — Building Name required
- **Vacancy Loss Report** — `showVacancyLossReport`/`vacancyLossReportPdf` — From/To Date, Management Type
- **Vacant Unit Report** — `showVacantUnitReport`/`vacantUnitReportPdf` — As On Date, Management Type
- **Tenant Details Report (Building Wise)** — `showTenantDetailsReport`/`tenantDetailsReportPdf` — filter by Building Name/ARE/Building Code (ARE/Building Name autocompletes reference routes not defined — likely dead bindings)
- **Dead file:** `Reports-Masters.rar` — stray archive

---

## General Module

#### Screen: Workflow List

- **Route:** `GET /workFlow` (`workFlow.index`)
- **View file:** `Modules/General/Resources/views/WorkFlow/list.blade.php`
- **Purpose:** Lists all defined workflows (e.g. approval/process definitions) with status and CRUD actions.
- **Buttons/Actions:**
  - "Add New" — links to `workFlow.create` (visible if `add_workflow` permission and user id 1)
  - Status toggle button (Active/Inactive) — POSTs to `workFlow.changeStatus` via hidden form, confirm dialog (permission `change_status_workflow`, user id 1 only)
  - View icon — links to `workFlow.show`
  - Edit icon — links to `workFlow.edit`
  - Delete icon — DELETEs via `workFlow.destroy`, confirm dialog (permission `delete_workflow`, user id 1 only)
- **Filters/Search:** None (client-side DataTable initialized with paging/searching/info disabled); column sort links (`@sortablelink`) on Workflow Name and Status.
- **Table columns:** Sl No., Workflow Name, Status (conditional), Action

#### Screen: Create/Edit Workflow

- **Route:** `GET /workFlow/create` (`workFlow.create`); `GET /workFlow/{workFlow}/edit` (`workFlow.edit`)
- **View file:** `Modules/General/Resources/views/WorkFlow/add_edit.blade.php`
- **Purpose:** Create or edit a workflow definition, including its default role/user.
- **Buttons/Actions:**
  - "SAVE" — submits form to `workFlow.store` (create) or `workFlow.update` (edit, via PUT)
  - Default Role select triggers AJAX call to `processAssign/usersByRoleId` to repopulate Default User dropdown
- **Filters/Search:** N/A
- **Form fields:** Workflow Name (text), Default Role (select, from Roles), Default User (select, populated via AJAX based on role)

#### Screen: View Workflow

- **Route:** `GET /workFlow/{workFlow}` (`workFlow.show`)
- **View file:** `Modules/General/Resources/views/WorkFlow/view.blade.php`
- **Purpose:** Read-only detail view of a single workflow's name, default role, default user, and status.
- **Buttons/Actions:** None (read-only display form)
- **Filters/Search:** N/A
- **Form fields (display only):** Workflow Name, Default Role, Default User, Status

#### Screen: Workflow Stage (Process) List

- **Route:** `GET /workFlowProcess` (`workFlowProcess.index`)
- **View file:** `Modules/General/Resources/views/WorkFlowProcess/list.blade.php`
- **Purpose:** Lists all workflow stages/processes belonging to workflows, with status and CRUD actions.
- **Buttons/Actions:**
  - "Add New" — links to `workFlowProcess.create` (permission `add_workflow_process`, user id 1)
  - Status toggle — POSTs to `workFlowProcess.changeStatus`, confirm dialog (permission `change_status_workflow_process`, user id 1)
  - View icon — links to `workFlowProcess.show`
  - Edit icon — links to `workFlowProcess.edit`
  - Delete icon — DELETEs via `workFlowProcess.destroy`, confirm dialog (permission `delete_workflow_process`, user id 1)
- **Filters/Search:** Sortable column headers (Stage Name, Workflow Code, Workflow Name, Status)
- **Table columns:** Sl No., Stage Name, Workflow Code, Workflow Name, Status (conditional), Action

#### Screen: Create/Edit Workflow Stage

- **Route:** `GET /workFlowProcess/create` (`workFlowProcess.create`); `GET /workFlowProcess/{workFlowProcess}/edit` (`workFlowProcess.edit`)
- **View file:** `Modules/General/Resources/views/WorkFlowProcess/add_edit.blade.php`
- **Purpose:** Create or edit a stage/process within a workflow, including its sequencing status.
- **Buttons/Actions:**
  - "SAVE" — submits to `workFlowProcess.store` or `workFlowProcess.update` (PUT)
- **Filters/Search:** N/A
- **Form fields:** Workflow Stage Name (text), Workflow Code (text), Workflow Name (select, active workflows only), Workflow Status (select: In-Progress / Completed / Reopen — maps to `process_assign_level`)

#### Screen: View Workflow Stage

- **Route:** `GET /workFlowProcess/{workFlowProcess}` (`workFlowProcess.show`)
- **View file:** `Modules/General/Resources/views/WorkFlowProcess/view.blade.php`
- **Purpose:** Read-only detail view of a workflow stage (name, code, parent workflow, status).
- **Buttons/Actions:** None
- **Filters/Search:** N/A
- **Form fields (display only):** Workflow Stage Name, Workflow Process Code, Workflow Process Name (parent workflow), Status

#### Screen: Action List

- **Route:** `GET /action` (`action.index`)
- **View file:** `Modules/General/Resources/views/Action/list.blade.php`
- **Purpose:** Lists all "Action" definitions (transition triggers used in process/action links) with status and CRUD actions.
- **Buttons/Actions:**
  - "Add Action" — links to `action.create` (permission `add_action`, user id 1)
  - Status toggle — POSTs to `action.changeStatus`, confirm dialog (permission `change_status_action`, user id 1)
  - View icon — links to `action.show`
  - Edit icon — links to `action.edit`
  - Delete icon — DELETEs via `action.destroy`, confirm dialog (permission `delete_action`, user id 1)
- **Filters/Search:** None (DataTable init with paging/searching/info disabled)
- **Table columns:** Sl No., Action Name, Action Key, Status (conditional), Action

#### Screen: Create/Edit Action

- **Route:** `GET /action/create` (`action.create`); `GET /action/{action}/edit` (`action.edit`)
- **View file:** `Modules/General/Resources/views/Action/add_edit.blade.php`
- **Purpose:** Create or edit an Action (name + unique key) used to drive workflow transitions.
- **Buttons/Actions:**
  - "SAVE" — submits to `action.store` or `action.update` (PUT)
- **Filters/Search:** N/A
- **Form fields:** Action Name (text), Action Key (text)

#### Screen: View Action

- **Route:** `GET /action/{action}` (`action.show`)
- **View file:** `Modules/General/Resources/views/Action/view.blade.php`
- **Purpose:** Read-only detail view of a single Action's name, key, and status.
- **Buttons/Actions:** None
- **Filters/Search:** N/A
- **Form fields (display only):** Action Name, Action Key, Status

#### Screen: Process Action Link List

- **Route:** `GET /processActionLink` (`processActionLink.index`)
- **View file:** `Modules/General/Resources/views/ProcessActionLink/list.blade.php`
- **Purpose:** Lists the mappings that define, for a given workflow stage + action, what the next stage is (the workflow transition table), paginated.
- **Buttons/Actions:**
  - "Add New" — links to `processActionLink.create` (permission `add_process_action_link`, user id 1)
  - View icon — links to `processActionLink.show`
  - Edit icon — links to `processActionLink.edit`
  - Delete icon — DELETEs via `processActionLink.destroy`, confirm dialog (permission `delete_process_action_link`, user id 1)
- **Filters/Search:** Sortable column headers (Process Name, Action, Next Process Name); pagination info partial (`includes.pagination_info`) and Laravel paginator links (20/page)
- **Table columns:** Sl No., Process Name, Action, Next Process Name, Action(buttons)

#### Screen: Create/Edit Process Action Link

- **Route:** `GET /processActionLink/create` (`processActionLink.create`); `GET /processActionLink/{processActionLink}/edit` (`processActionLink.edit`)
- **View file:** `Modules/General/Resources/views/ProcessActionLink/add_edit.blade.php`
- **Purpose:** Define/edit a transition rule: given a workflow stage and an action, specify the resulting next stage.
- **Buttons/Actions:**
  - "SAVE" — submits to `processActionLink.store` or `processActionLink.update` (PUT)
- **Filters/Search:** N/A
- **Form fields:** Process (select of workflow stages, "Process Action Link" label), Action Name (select of Actions), Next Stage Name (select of workflow stages)

#### Screen: View Process Action Link

- **Route:** `GET /processActionLink/{processActionLink}` (`processActionLink.show`)
- **View file:** `Modules/General/Resources/views/ProcessActionLink/view.blade.php`
- **Purpose:** Read-only detail view of a single process→action→next-process transition rule.
- **Buttons/Actions:** None
- **Filters/Search:** N/A
- **Form fields (display only):** Stage Name, Action Name (mislabeled "Stage Name" in the view but shows action), Next Stage Name

#### Screen: Process Assign List

- **Route:** `GET /processAssign` (`processAssign.index`)
- **View file:** `Modules/General/Resources/views/ProcessAssign/list.blade.php`
- **Purpose:** Lists role/user assignments per workflow stage (optionally scoped by location/price range), i.e. who is responsible for a stage.
- **Buttons/Actions:**
  - "Add New" — links to `processAssign.create` (permission `add_workflow_assign`, user id 1)
  - View icon — links to `processAssign.show`
  - Edit icon — links to `processAssign.edit`
  - Delete icon — DELETEs via `processAssign.destroy`, confirm dialog (permission `delete_workflow_assign`, user id 1)
- **Filters/Search:** Sortable column headers (Work-Flow Name, Work-Flow Process, Location, Range)
- **Table columns:** Sl No., Work-Flow Name, Work-Flow Process, Location, Range, Action

#### Screen: Create/Edit Process Assign

- **Route:** `GET /processAssign/create` (`processAssign.create`); `GET /processAssign/{processAssign}/edit` (`processAssign.edit`)
- **View file:** `Modules/General/Resources/views/ProcessAssign/add_edit.blade.php`
- **Purpose:** Assign one or more Role+User(s) combinations to a specific workflow stage, optionally scoped by location/price range/tenant status; complex dynamic multi-row form.
- **Buttons/Actions:**
  - "SAVE" — submits to `processAssign.store` or `processAssign.update` (PUT)
  - "Add Row" — dynamically appends another Role/Users row (JS, client-side; enforces role limit against available roles)
  - "Remove" (per row, shown when more than one row exists) — removes that role/user row via `removeRoleRow()` JS and renumbers remaining rows
  - Work-Flow Name select — triggers AJAX to `processAssign/ajaxStages` to repopulate the Work Flow Stage dropdown
  - Role select (per row) — triggers AJAX to `processAssign/usersByRoleId` to repopulate that row's Users multi-select
  - Work-Flow Name select (value 4) toggles visibility between ".Sales" fields (Location/Price Range) and ".Maintenance" fields (Status) via JS
- **Filters/Search:** N/A
- **Form fields:** Work-Flow Name (select), Work Flow Stage (select, AJAX-populated), Location (select), Price Range (select), Status (select: On Hold/No Maintenance/Maintenance By Landlord/VIP/Blacklisted/Legal), repeatable rows of: Roles (select, required), Users (multi-select, AJAX-populated by role), hidden `row_count`

#### Screen: View Process Assign

- **Route:** `GET /processAssign/{processAssign}` (`processAssign.show`)
- **View file:** `Modules/General/Resources/views/ProcessAssign/view.blade.php`
- **Purpose:** Read-only detail view of a stage's role/user assignment, including location, price range, and all assigned role/user pairs.
- **Buttons/Actions:** None
- **Filters/Search:** N/A
- **Form fields (display only):** Work-Flow Name, Process Name, Location, Range, and repeated Role N / Users N pairs for each assigned role

#### Screen: Workflow Categories List

- **Route:** `GET /workFlowCategory` (`workFlowCategory.index`)
- **View file:** `Modules/General/Resources/views/WorkFlowCategory/list.blade.php`
- **Purpose:** Lists "assign field" categories (grouping by price range + location) used to scope process assignments, paginated, with status and CRUD actions.
- **Buttons/Actions:**
  - "Add New" — links to `workFlowCategory.create` (visible only if Auth::id() == 1)
  - Status toggle — POSTs to `workFlowCategory.changeStatus`, confirm dialog
  - View icon — links to `workFlowCategory.show`
  - Edit icon — links to `workFlowCategory.edit`
  - Delete icon — DELETEs via `workFlowCategory.destroy`, confirm dialog (visible only if Auth::id() == 1)
- **Filters/Search:** Sortable column headers (Name, Price Range, Location, Status); Laravel paginator links (10/page)
- **Table columns:** Sl No., Name, Price Range, Location, Status, Action

#### Screen: Create/Edit Workflow Category

- **Route:** `GET /workFlowCategory/create` (`workFlowCategory.create`); `GET /workFlowCategory/{workFlowCategory}/edit` (`workFlowCategory.edit`)
- **View file:** `Modules/General/Resources/views/WorkFlowCategory/add_edit.blade.php`
- **Purpose:** Create or edit a workflow category (a named combination of price range + location) used for scoping process assignment rules.
- **Buttons/Actions:**
  - "SAVE" — submits to `workFlowCategory.store` or `workFlowCategory.update` (PUT)
- **Filters/Search:** N/A
- **Form fields:** Name (text, `assign_field_name`), Price Range (select, active only), Location (select, active only)

#### Screen: View Workflow Category

- **Route:** `GET /workFlowCategory/{workFlowCategory}` (`workFlowCategory.show`)
- **View file:** `Modules/General/Resources/views/WorkFlowCategory/view.blade.php`
- **Purpose:** Read-only detail view of a workflow category's name, price range, location, and status.
- **Buttons/Actions:** None
- **Filters/Search:** N/A
- **Form fields (display only):** Code (name), Price Range, Location, Status

---

## Menu Module

#### Screen: Menu List (Top Level)
- **Route:** `GET menu` (`menu.index`)
- **View file:** `Modules/Menu/Resources/views/menu_list.blade.php`
- **Purpose:** Lists top-level menu groups/menus in order, allowing admins to reorder, edit, view submenus, manage permission keys, or delete them.
- **Buttons/Actions:**
  - "Add" (top-right, visible only to user id 1) — links to `menu.create`, opens the Add Menu form.
  - Up arrow (▲) icon per row (hidden on first row) — AJAX call to `menuOrder` route with `order_type=up`, swaps menu_order with the row above, then reloads page.
  - Down arrow (▼) icon per row (hidden on last row) — AJAX call to `menuOrder` route with `order_type=down`, swaps menu_order with the row below, then reloads page.
  - Edit (pencil icon) — links to `menu.edit` for that menu row.
  - Delete (trash icon, visible only to user id 1) — submits hidden `#delete-form` (DELETE `menu.destroy`) after a JS `confirm()` dialog; deletes the menu.
  - Permission Key icon (puzzle piece, shown when `menutype == 2`, i.e. a leaf "Menu") — links to `menu.permission.index` for that menu's permission keys.
  - Menu/View icon (eye, shown when `menutype == 1`, i.e. a "MenuGroup") — links to `menu.menus` (submenu list) for that group.
  - Menu name (for `menutype == 1` rows) — clickable link to `menu.menus` (drills into submenu list).
- **Filters/Search:** None (DataTables initialized with paging/searching/info all disabled; server-side pagination via `$menu->links()` instead).
- **Table columns:** Sl No., Menu (name, linked if group), Menu Order (up/down reorder buttons), Action (Edit / Delete / Permission or View icons).
- **Form fields:** N/A (listing screen only; contains a hidden delete form with CSRF + `_method=DELETE`).
- **Modals used:** None (uses native `confirm()` JS dialogs, not Bootstrap modals).

#### Screen: Submenu List
- **Route:** `GET submenu/{menu}` (`menu.menus`)
- **View file:** `Modules/Menu/Resources/views/menu_list.blade.php` (same view as Menu List, reused with a `$parentMenu` variable)
- **Purpose:** Shows the child menu items belonging to a selected parent menu group, with the same reorder/edit/delete/permission actions as the top-level list.
- **Buttons/Actions:** Identical to "Menu List (Top Level)" above — Add, reorder up/down, Edit, Delete (user id 1 only), Permission Key / View icon depending on `menutype`. Page title/breadcrumb shows the parent menu's name.
- **Filters/Search:** None.
- **Table columns:** Sl No., Menu, Menu Order, Action.

#### Screen: Add / Edit Menu
- **Route:** `GET menu/create` (`menu.create`) and `GET menu/{menu}/edit` (`menu.edit`)
- **View file:** `Modules/Menu/Resources/views/add_menu.blade.php`
- **Purpose:** Create or edit a menu entry, which can be either a top-level "MenuGroup" or a leaf "Menu" item pointing to a route.
- **Buttons/Actions:**
  - Radio toggle "MenuGroup" / "Menu" — client-side JS shows/hides one of two forms (`#menugroup-form` for groups, `#menu-form` for leaf menus); does not submit anything itself.
  - "Submit" (on MenuGroup form) — POSTs to `menu.store` (create) or PUTs to `menu.update` (edit) with `menutype=1`.
  - "Submit" (on Menu form) — POSTs to `menu.store` (create) or PUTs to `menu.update` (edit) with `menutype=2`.
- **Form fields:**
  - MenuGroup form: Menu Name (text, required), Menu Icon (text, required), hidden `menutype=1`.
  - Menu form: Menu Group (select of existing groups, `parent_menu`), Menu Name (text, required), Menu Icon (text, required), Route Name (text, required if menutype=2), Url Key (text, required if menutype=2), hidden `menutype=2`.

#### Screen: Permission Key List
- **Route:** `GET menu/{menu}/permission` (`menu.permission.index`)
- **View file:** `Modules/Menu/Resources/views/permission_list.blade.php`
- **Purpose:** Lists all permission keys (Spatie `Permission` records) defined for a specific leaf menu item.
- **Buttons/Actions:**
  - "Add Permission" — links to `menu.permission.create` for the current menu, opens the Add Permission Key form.
  - Edit (pencil icon) per row — links to `menu.permission.edit` for that permission.
  - Delete (trash icon) — present in markup but commented out/disabled (no active delete action on this screen).
- **Filters/Search:** None (DataTables paging/search disabled).
- **Table columns:** Sl No., Permission Key (name), Action (Edit).

#### Screen: Add / Edit Permission Key
- **Route:** `GET menu/{menu}/permission/create` (`menu.permission.create`) and `GET menu/{menu}/permission/{permission}/edit` (`menu.permission.edit`)
- **View file:** `Modules/Menu/Resources/views/add_permission.blade.php`
- **Purpose:** Create or rename a permission key associated with a specific menu item.
- **Buttons/Actions:**
  - "Submit" — POSTs to `menu.permission.store` (create) or PUTs to `menu.permission.update` (edit); server validates name uniqueness (create) and slugifies the value.
- **Form fields:** Permission Key (text, required).

#### Screen: Role List
- **Route:** `GET role` (`role.index`)
- **View file:** `Modules/Menu/Resources/views/role_list.blade.php`
- **Purpose:** Lists all Spatie roles defined in the system with actions to edit, delete, or manage each role's permissions.
- **Buttons/Actions:**
  - "Add Role" (visible only to user id 1) — links to `role.create`.
  - Edit (pencil icon) — links to `role.edit` for that role.
  - Delete (trash icon, visible only to user id 1) — submits hidden `#delete-form` (DELETE `role.destroy`) after JS `confirm()`.
  - "Set Permissions" (users icon) — links to `setPermission` route for that role, opens the Assign Permission screen.
- **Filters/Search:** None (DataTables paging/search disabled).
- **Table columns:** Sl No., Role (title-cased name), Action (Edit / Delete / Set Permissions).

#### Screen: Add / Edit Role
- **Route:** `GET role/create` (`role.create`) and `GET role/{role}/edit` (`role.edit`)
- **View file:** `Modules/Menu/Resources/views/add_role.blade.php`
- **Purpose:** Create a new role or rename an existing role.
- **Buttons/Actions:**
  - "Submit" — POSTs to `role.store` (create) or PUTs to `role.update` (edit); server slugifies the name.
- **Form fields:** Role (text, required).

#### Screen: Assign Permission (Role Permission Matrix)
- **Route:** `GET setPermission/{role}` (`setPermission`)
- **View file:** `Modules/Menu/Resources/views/set_permission.blade.php`
- **Purpose:** Lets an admin pick a menu group from a dropdown and check/uncheck permission keys to grant or revoke them for the given role.
- **Buttons/Actions:**
  - Menu select (`#menuoption`) — on change, triggers an AJAX POST to `menuPermissions/{menu}` which loads a permissions checklist partial into the `#permissions` container.
  - Checkboxes (rendered by AJAX partial `permissionAssign.blade.php`) — checking a parent-menu checkbox auto-checks/unchecks all its nested child permission checkboxes via JS.
  - "Save" button (rendered inside the AJAX-loaded partial) — submits the whole form (POST `storePermissions/{role}`), which revokes all previous permissions under the selected menu tree and grants the checked ones.
- **Filters/Search:** Menu select dropdown acts as the primary filter to load the relevant permission checklist.
- **Table columns:** N/A (checkbox grid, not a table).
- **Form fields:** Menu (select, drives AJAX load of permission checkboxes), dynamically injected `permissions[]` checkboxes.

### Notes on non-screen / dead routes (excluded)
- `GET menuOrder` — AJAX-only endpoint for reorder buttons.
- `POST storePermission/{role}` and `POST menuPermissions/{menu}` — POST-only endpoints, not full-page screens.
- `POST sidebarSetting` — AJAX-only endpoint to persist sidebar open/close preference.
- `GET menu/{menu}` (`menu.show`) and `GET role/{role}` (`role.show`) — unused leftover Route::resource `show()` scaffolding; not real navigable screens (would error or show blank data).
- `GET menu/{menu}/permission/{permission}` (`menu.permission.show`) — same dead-`show()` pattern, unused.
