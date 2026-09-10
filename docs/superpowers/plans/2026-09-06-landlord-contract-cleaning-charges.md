# Landlord Contract Cleaning Charges Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "Cleaning Charges" field (Percentage or Amount, mirroring the existing "Management Fee" field exactly) to the `landlord_contract` table, and surface it in every form/view that currently reads or writes Management Fee — so it's available per landlord contract for whoever builds the manual monthly landlord tax invoice (Management Fee + Cleaning Charges + Repair & Maintenance) later.

**Architecture:** Two new nullable columns on `landlord_contract` (`cleaning_charge_method`, `landlord_contract_cleaning_charge`), added via a new migration. A parallel "Cleaning Charges" UI block (radio Percentage/Amount + value input, identical JS show/hide-and-required behavior to Management Fee) is added to every editable contract form, shown for **all** management types (unlike Management Fee, which is hidden for Comprehensive contracts) since the user confirmed Cleaning Charges should apply everywhere. Every controller method that currently writes the Management Fee fields gets two more array keys. Every read-only view that currently displays Management Fee gets a matching Cleaning Charges line.

**Tech Stack:** Laravel 5.x modules (Sales, BackOffice), Blade views, PostgreSQL, jQuery form-toggle JS already used by the Management Fee fields.

**Spec:** No separate spec doc — the design was approved directly in chat during brainstorming (see conversation). This plan is self-contained.

## Global Constraints

- Do not touch the existing Management Fee fields' behavior, values, or visibility rules (they must keep behaving exactly as today).
- Cleaning Charges is shown for **all** management types (Comprehensive, Normal, Commission) — it is **not** subject to the `#comprehensive_field` / `.normal_commission_field` show/hide rule that Management Fee uses.
- Cleaning Charges does **not** get a "Percentage Type" (Rent Income / Rent Collection) selector — only Percentage/Amount + value, per the approved design.
- Column names: `cleaning_charge_method` (integer, nullable — 1 = Percentage, 2 = Amount) and `landlord_contract_cleaning_charge` (double precision, nullable — the % or OMR value).
- `LandlordContract` model (`Modules/Sales/Entities/LandlordContract.php`) already has `protected $guarded = []`, so no model changes are needed for the new columns to be mass-assignable.
- One file, `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php`, posts to a route name (`landlordRenewalContractStore`) that does not currently exist in `Modules/BackOffice/Routes/web.php` — it may be dead/orphaned code. Add the field to it anyway for consistency, but do not spend time debugging its broken route; flag it in the task's completion notes.
- Every currency amount in this codebase is formatted with 3 decimals via the global `numberFormat()` helper (`config/function.php`) — use it in any new display markup exactly like the existing Management Fee display lines do (some already omit it inconsistently; match whichever the specific file you're editing already does for Management Fee, for visual consistency within that file).

---

## Task 1: Migration — add the two new columns

**Files:**
- Create: `Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php`
- Create (mirror for deployment): `server_upload_files/Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php`

**Interfaces:**
- Produces: `landlord_contract.cleaning_charge_method` (integer, nullable), `landlord_contract.landlord_contract_cleaning_charge` (double precision, nullable). Every later task's Blade/controller code references these two exact column names.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCleaningChargeToLandlordContract extends Migration
{
    public function up()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->unsignedInteger('cleaning_charge_method')->nullable()->after('management_method');
            $table->double('landlord_contract_cleaning_charge')->nullable()->after('cleaning_charge_method');
        });
    }

    public function down()
    {
        Schema::table('landlord_contract', function (Blueprint $table) {
            $table->dropColumn(['cleaning_charge_method', 'landlord_contract_cleaning_charge']);
        });
    }
}
```

- [ ] **Step 2: Run the migration locally**

Run (use the project's PHP 7.4 binary, not the system PHP 8.4):
```
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe artisan migrate --path=Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php --force
```
Expected: `Migrated: 2026_09_06_000000_add_cleaning_charge_to_landlord_contract`

- [ ] **Step 3: Verify the columns exist**

Run a one-off script (or `psql`) to confirm:
```sql
SELECT column_name, data_type FROM information_schema.columns
WHERE table_name = 'landlord_contract' AND column_name IN ('cleaning_charge_method', 'landlord_contract_cleaning_charge');
```
Expected: 2 rows.

- [ ] **Step 4: Mirror the migration file to `server_upload_files/` and commit**

```bash
cp "Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php" "server_upload_files/Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php"
git add Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php server_upload_files/Modules/Sales/Database/Migrations/2026_09_06_000000_add_cleaning_charge_to_landlord_contract.php
git commit -m "feat: add cleaning_charge_method and landlord_contract_cleaning_charge columns to landlord_contract"
```

---

## Task 2: Define the canonical Cleaning Charges form block and JS (reference only, no file changes)

This task defines the exact HTML and JS block that Tasks 3–10 insert verbatim (with only the `{ID_SUFFIX}` placeholder text below substituted per-file where noted, and in the one case — the BackOffice Direct **create** form — where the `disabled`/`old()`-prefill attributes differ, per the note in that task). No files are edited in this task; it exists so later tasks can say "insert the Cleaning Charges Form Block" unambiguously.

**Cleaning Charges Form Block** (HTML, insert as a new `<div class="w-100"></div>` + two `<div class="col-sm-6">` blocks, placed immediately after the existing Management Fee block in each form — i.e. after the closing `</div>` of the `percentage_type` div, or after the `percentage_field_value` div in forms that have no `percentage_type` div):

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cleaning_charge_method">Cleaning Charges</label>
                 <div class="p-relative">
                 <label for="cleaning_charge_method_one">
                        <input type="radio" name="cleaning_charge_method" id="cleaning_charge_method_one" value="1" class="cleaning_charge_method" checked> Percentage
                </label>
                <label for="cleaning_charge_method_two">
                    <input type="radio" name="cleaning_charge_method" id="cleaning_charge_method_two" value="2" class="cleaning_charge_method"> Amount
                </label>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                 <label for="landlord_contract_cleaning_charge_label" id="landlord_contract_cleaning_charge_label">Cleaning Value</label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" placeholder="Enter Cleaning Value" min="0" max="999999999">
            </div>
            </div>
        </div>
```

**Cleaning Charges JS Block** (insert inside the same `$(document).ready(...)` / bottom-of-file `<script>` block that already contains the `.management_method` click handler — add this as a new, separate handler, not merged into the existing one):

```javascript
    $(".cleaning_charge_method").on('click', function(e) {
        var cleaning_charge_method = $(this).val();
        if (cleaning_charge_method == 1) {
            $("#landlord_contract_cleaning_charge_label").html('Cleaning Value');
            $("#landlord_contract_cleaning_charge").attr('placeholder', 'Enter Cleaning Value');
            $("#landlord_contract_cleaning_charge").attr("max", 100);
        } else {
            $("#landlord_contract_cleaning_charge_label").html('Cleaning Amount');
            $("#landlord_contract_cleaning_charge").attr('placeholder', 'Enter Cleaning Amount');
            $("#landlord_contract_cleaning_charge").attr("max", 999999999);
        }
    });
```

**Cleaning Charges Display Block** (read-only views — insert immediately after the existing Management Fee display block, matching whatever HTML tag style — `<h5 class="details">` or the `@if(...) ... @endif` card-row style — the specific file already uses for Management Fee):

```blade
@if(isset({{VAR}}->landlord_contract_cleaning_charge) && {{VAR}}->landlord_contract_cleaning_charge)
<h5 class="details"><b>Cleaning Charges :  </b>
<span>{{ ({{VAR}}->cleaning_charge_method == 1) ? numberFormat({{VAR}}->landlord_contract_cleaning_charge).'%' : numberFormat({{VAR}}->landlord_contract_cleaning_charge).' OMR' }}</span>
</h5>
@endif
```
`{{VAR}}` is the specific view's variable name for the contract (differs per file — see each task).

- [ ] **Step 1: No code to write.** Read and understand the three blocks above before starting Task 3.

---

## Task 3: Sales — `contract_in_draft.blade.php` (initial contract creation form)

**Files:**
- Modify: `Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php`

**Interfaces:**
- Consumes: the Cleaning Charges Form Block + JS Block from Task 2.
- Produces: `cleaning_charge_method`, `landlord_contract_cleaning_charge` POST fields, consumed by Task 11 (`landlordContractAction()`).

- [ ] **Step 1: Insert the Cleaning Charges Form Block**

Find the existing Management Fee block, which ends with the `percentage_type` div (around line 213–223, containing `<select ... id="landlord_contract_percentage" ...>` with `<option value=1 >Rent Income</option>` / `<option value=2 >Rent Collection</option>`). Insert the Cleaning Charges Form Block (Task 2) immediately after that div's closing `</div>`.

- [ ] **Step 2: Insert the Cleaning Charges JS Block**

Find the existing `$(".management_method").on('click', function(e) {` handler (around line 473). Insert the Cleaning Charges JS Block (Task 2) immediately after that handler's closing `});`.

- [ ] **Step 3: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php"
```
Expected: `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
git add Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php
git commit -m "feat: add Cleaning Charges fields to landlord contract draft-creation form"
```

---

## Task 4: Sales — `edit_contract.blade.php` (edit an existing contract)

**Files:**
- Modify: `Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php`

**Interfaces:**
- Consumes: Task 2 blocks, plus this file must **prefill** the radio/input from `$landlordContract->cleaning_charge_method` / `$landlordContract->landlord_contract_cleaning_charge` (this file, unlike the create-only `contract_in_draft.blade.php`, always has `$landlordContract` in scope).
- Produces: same two POST fields, consumed by Task 12 (`update()`).

- [ ] **Step 1: Insert the Cleaning Charges Form Block, with prefill**

Find the existing Management Fee block's `percentage_type` div (around line 198–210, guarded by `@if($landlordContract->management_method==2 || $landlordContract->management_id==1) style="display:none;" @endif`). Insert immediately after its closing `</div>` — but use this prefill-aware version instead of the bare Task 2 block:

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cleaning_charge_method">Cleaning Charges</label>
                 <div class="p-relative">
                 <label for="cleaning_charge_method_one">
                        <input type="radio" name="cleaning_charge_method" value="1" {{ isset($landlordContract->cleaning_charge_method)? (($landlordContract->cleaning_charge_method==1)?'CHECKED':''):'CHECKED' }} class="cleaning_charge_method"> Percentage
                </label>
                <label for="cleaning_charge_method_two">
                    <input type="radio" name="cleaning_charge_method" value="2" {{ isset($landlordContract->cleaning_charge_method)? (($landlordContract->cleaning_charge_method==2)?'CHECKED':''):'' }} class="cleaning_charge_method"> Amount
                </label>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                 <label for="landlord_contract_cleaning_charge_label" id="landlord_contract_cleaning_charge_label">Cleaning Value</label>
                 <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" placeholder="Enter Cleaning Value" min="0" max="999999999">
            </div>
            </div>
        </div>
```

- [ ] **Step 2: Insert the Cleaning Charges JS Block**

Find `$(".management_method").on('click', function(e) {` (around line 520). Insert the Task 2 JS Block immediately after its closing `});`.

- [ ] **Step 3: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php"
```

- [ ] **Step 4: Commit**

```bash
git add Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php
git commit -m "feat: add Cleaning Charges fields to Sales landlord contract edit form"
```

---

## Task 5: BackOffice Direct — `add_contract_direct.blade.php` (create)

**Files:**
- Modify: `Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php`

**Interfaces:**
- Consumes: Task 2 blocks (bare, no prefill — this is a create-only form).
- Produces: consumed by Task 13 (`LandlordContractDirectController::store()`).

- [ ] **Step 1: Insert the Cleaning Charges Form Block**

Find the existing Management Fee block's `percentage_type` div (around line 197–209). Insert the Task 2 Form Block immediately after its closing `</div>`.

- [ ] **Step 2: Insert the Cleaning Charges JS Block**

Find `$(".management_method").on('click', function(e) {` (around line 455). Insert the Task 2 JS Block immediately after its closing `});`.

- [ ] **Step 3: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php"
git add Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php
git commit -m "feat: add Cleaning Charges fields to BackOffice direct contract creation form"
```

---

## Task 6: BackOffice Direct — `edit_contract.blade.php` (edit)

**Files:**
- Modify: `Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php`

**Interfaces:**
- Consumes: Task 2 blocks with prefill (same pattern as Task 4 — use `$landlordContract`).
- Produces: consumed by Task 14 (`LandlordContractDirectController::update()`).

- [ ] **Step 1: Insert the Cleaning Charges Form Block, with prefill**

Find the existing Management Fee block's `percentage_type` div (around line 198–210, same structure as Task 4's file — this BackOffice edit form is near-identical to the Sales one). Insert immediately after its closing `</div>`, using the **same prefill-aware HTML block given in full in Task 4, Step 1** (identical field names, identical `$landlordContract` variable).

- [ ] **Step 2: Insert the Cleaning Charges JS Block**

Find `$(".management_method").on('click', function(e) {`. Insert the Task 2 JS Block immediately after its closing `});`.

- [ ] **Step 3: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php"
git add Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php
git commit -m "feat: add Cleaning Charges fields to BackOffice direct contract edit form"
```

---

## Task 7: BackOffice Renewal — `landlord_renew_contract_add.blade.php`

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php`

**Interfaces:**
- Consumes: Task 2 blocks with prefill from `$landlordContract` (this file already prefills Management Fee from `$landlordContract`, e.g. `value="{{ isset($landlordContract->landlord_contract_management_fee)?$landlordContract->landlord_contract_management_fee:'' }}"`).
- Produces: consumed by Task 15 (`LandlordRenewalController::renewalContractAdd()`).

- [ ] **Step 1: Insert the Cleaning Charges Form Block, with prefill**

Find the existing `management_fee_type` select block (around line 220–235). Insert the same prefill-aware HTML block from Task 4, Step 1 immediately after that block's closing `</div>`.

- [ ] **Step 2: Insert the Cleaning Charges JS Block**

Find the `.management_method` click handler in this file's `<script>` section. Insert the Task 2 JS Block immediately after it.

- [ ] **Step 3: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php
git commit -m "feat: add Cleaning Charges fields to landlord renewal contract add form"
```

---

## Task 8: BackOffice Renewal — `landlord_renew_contract_edit.blade.php`

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php`

**Interfaces:**
- Consumes: a bare value field — this specific file is verified to have **no** `management_method` radio and **no** `management_fee_type` select for Management Fee (unlike every other form in this plan), just two plain inputs:
  ```html
  <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_management_fee)?$landlordContract->landlord_contract_management_fee:'' }}" id="landlord_contract_management_fee" name="landlord_contract_management_fee" placeholder="Enter fees type" min="1">
  ```
  (line 182) and a matching `landlord_contract_percentage` input (line 192). Cleaning Charges here must mirror that same bare-value simplicity — **do not** add a Percentage/Amount radio to this file, and no JS handler is needed (there's no toggle to react to).
- Produces: consumed by Task 16 (`LandlordRenewalController::getContractData()`), which — matching this file's existing asymmetry for Management Fee — only needs to read the value field, not a method field.

- [ ] **Step 1: Insert a bare Cleaning Charges value field (no radio)**

Immediately after line 192 (the `landlord_contract_percentage` input's closing `</div>`), insert:

```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Charges</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="number" class="form-control" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" placeholder="Enter Cleaning Charges" min="1">
                </div>
            </div>
        </div>
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_edit.blade.php
git commit -m "feat: add Cleaning Charges fields to landlord renewal contract edit form"
```

---

## Task 9: BackOffice Renewal — `landlord_renewal_contract_update.blade.php`

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php`

**Interfaces:**
- Consumes: Task 2 blocks with prefill.
- Produces: consumed by Task 17 (`LandlordRenewalController::renewalNewContractUpdate()`).

- [ ] **Step 1: Insert the Cleaning Charges Form Block, with prefill**

Find the existing `management_fee_type` select block (around line 223–238, same structure as Task 7's file). Insert the prefill-aware HTML block from Task 4, Step 1 immediately after it.

- [ ] **Step 2: Insert the Cleaning Charges JS Block**

Insert after the `.management_method` click handler in this file.

- [ ] **Step 3: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php
git commit -m "feat: add Cleaning Charges fields to landlord renewal new-contract update form"
```

---

## Task 10: BackOffice Renewal — `landlord_renew_contract_create.blade.php` (verify liveness first)

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php`

**Interfaces:**
- Consumes: Task 2 blocks with prefill.
- Produces: intended for a controller write site, but per Global Constraints this form's target route (`landlordRenewalContractStore`) does not currently exist in `Modules/BackOffice/Routes/web.php` — confirm this before spending time here.

- [ ] **Step 1: Confirm whether this form is reachable**

```bash
grep -rn "landlord_renew_contract_create" Modules/BackOffice/Http/Controllers/ Modules/BackOffice/Routes/
grep -n "landlordRenewalContractStore" Modules/BackOffice/Routes/web.php
```
If no route named `landlordRenewalContractStore` exists anywhere and no controller `return view(...)` targets this blade file, treat this file as dead code: still complete Steps 2–4 below for consistency (cheap insurance if it's wired up later), but note in the task's completion remarks that the route was unresolved and this file appears unused.

- [ ] **Step 2: Insert the Cleaning Charges Form Block, with prefill**

Find the existing Management Fee input (around line 192, same structure as Task 8's file). Insert the prefill-aware HTML block from Task 4, Step 1 immediately after that block.

- [ ] **Step 3: Insert the Cleaning Charges JS Block**

Insert after the `.management_method` click handler in this file.

- [ ] **Step 4: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php"
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php
git commit -m "feat: add Cleaning Charges fields to landlord renewal contract create form (route currently unresolved - see remarks)"
```

---

## Task 11: Controller — Sales `LandlordContractController::landlordContractAction()`

**Files:**
- Modify: `Modules/Sales/Http/Controllers/LandlordContractController.php`

**Interfaces:**
- Consumes: `cleaning_charge_method`, `landlord_contract_cleaning_charge` POST fields from Task 3's form.

- [ ] **Step 1: Add the two array keys**

In `landlordContractAction()` (around line 279–330), find this existing line (around line 310):
```php
          'landlord_contract_management_fee'    => $request['landlord_contract_management_fee'],
```
Immediately after the array entry that contains `'landlord_contract_percentage' => $request['landlord_contract_percentage'],` (a few lines below the management fee line, in the same array), add:
```php
          'cleaning_charge_method'               => $request['cleaning_charge_method'],
          'landlord_contract_cleaning_charge'    => $request['landlord_contract_cleaning_charge'],
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Http/Controllers/LandlordContractController.php"
git add Modules/Sales/Http/Controllers/LandlordContractController.php
git commit -m "feat: save Cleaning Charges fields when finalizing a landlord contract from draft"
```

---

## Task 12: Controller — Sales `LandlordContractController::update()`

**Files:**
- Modify: `Modules/Sales/Http/Controllers/LandlordContractController.php`

**Interfaces:**
- Consumes: the same two fields from Task 4's form.

- [ ] **Step 1: Add the two array keys**

In `update()` (around line 187–250), find the array entry `'landlord_contract_management_fee' => $request['landlord_contract_management_fee'],` (around line 206) and its sibling `landlord_contract_percentage` entry. Add the same two lines shown in Task 11, Step 1, immediately after them, within the same `update([...])` array.

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Http/Controllers/LandlordContractController.php"
git add Modules/Sales/Http/Controllers/LandlordContractController.php
git commit -m "feat: save Cleaning Charges fields when editing a landlord contract (Sales)"
```

*(Note: Tasks 11 and 12 touch the same file — commit them together as one commit if executed back-to-back in the same session, to avoid an intermediate broken-lint state; the two "commit" steps above are written separately only because they may be executed by different task-review cycles.)*

---

## Task 13: Controller — `LandlordContractDirectController::store()`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php`

**Interfaces:**
- Consumes: fields from Task 5's form.

- [ ] **Step 1: Add the two array keys**

In `store()` (around line 132–268), find `'landlord_contract_management_fee' => $request['landlord_contract_management_fee'],` (around line 224) and its sibling `landlord_contract_percentage` entry. Add the same two lines as Task 11, Step 1, immediately after them.

- [ ] **Step 2: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php"
```

---

## Task 14: Controller — `LandlordContractDirectController::update()`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php`

**Interfaces:**
- Consumes: fields from Task 6's form.

- [ ] **Step 1: Add the two array keys**

In `update()` (around line 332–385), find `'landlord_contract_management_fee' => $request['landlord_contract_management_fee'],` (around line 350) and its sibling. Add the same two lines immediately after them.

- [ ] **Step 2: Lint and commit** (commit Tasks 13+14 together, same file)

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php"
git add Modules/BackOffice/Http/Controllers/LandlordContractDirectController.php
git commit -m "feat: save Cleaning Charges fields in BackOffice direct contract create/update"
```

---

## Task 15: Controller — `LandlordRenewalController::renewalContractAdd()`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`

**Interfaces:**
- Consumes: fields from Task 7's form.

- [ ] **Step 1: Add the two array keys**

In `renewalContractAdd()` (around line 864–980), find `'landlord_contract_management_fee' => $request['landlord_contract_management_fee'],` (around line 902) and its sibling `landlord_contract_percentage` entry. Add the same two lines immediately after them.

- [ ] **Step 2: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordRenewalController.php"
```

---

## Task 16: Controller — `LandlordRenewalController::getContractData()`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`

**Interfaces:**
- Consumes: the field from Task 8's form (via `renewalContractUpdate()`, which calls this helper at line ~413). Task 8's form has no method radio, so only the value field needs reading here — **do not** add a `cleaning_charge_method` line, matching this method's existing asymmetry for Management Fee (it reads `landlord_contract_management_fee` and `landlord_contract_percentage`, but has no `management_method` line either).

- [ ] **Step 1: Add one array key**

In `getContractData()` (line 344–377), find:
```php
      $contractData['landlord_contract_management_fee'] = request('landlord_contract_management_fee');
      $contractData['landlord_contract_percentage'] = request('landlord_contract_percentage');
```
Immediately after, add:
```php
      $contractData['landlord_contract_cleaning_charge'] = request('landlord_contract_cleaning_charge');
```

- [ ] **Step 2: Lint**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordRenewalController.php"
```

---

## Task 17: Controller — `LandlordRenewalController::renewalNewContractUpdate()`

**Files:**
- Modify: `Modules/BackOffice/Http/Controllers/LandlordRenewalController.php`

**Interfaces:**
- Consumes: fields from Task 9's form.

- [ ] **Step 1: Add the two array keys**

In `renewalNewContractUpdate()` (line 1003–1290), find `'landlord_contract_management_fee' => $request['landlord_contract_management_fee'],` (around line 1018) and its sibling. Add the same two lines immediately after them.

- [ ] **Step 2: Lint and commit** (commit Tasks 15+16+17 together, same file)

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Http/Controllers/LandlordRenewalController.php"
git add Modules/BackOffice/Http/Controllers/LandlordRenewalController.php
git commit -m "feat: save Cleaning Charges fields in landlord renewal add/edit/update flows"
```

---

## Task 18: Display — `landlord_contract_info.blade.php` (Sales)

**Files:**
- Modify: `Modules/Sales/Resources/views/LandlordSales/landlord_contract_info.blade.php`

**Interfaces:**
- Consumes: `cleaning_charge_method`, `landlord_contract_cleaning_charge` columns via `$landlordContractInfo`.

- [ ] **Step 1: Insert the display block**

Find the existing Management Fee value display around line 306–311:
```php
         @if($landlordContractInfo->landlord_contract_management_fee)
               <h5 class="details"><b>{{($landlordContractInfo->management_method==1)?'Management Value':'Management Amount' }} :  </b>
               {{$landlordContractInfo->landlord_contract_management_fee??'0'}} OMR
               </h5>
          @endif
```
Immediately after its `@endif`, insert:
```php
         @if($landlordContractInfo->landlord_contract_cleaning_charge)
               <h5 class="details"><b>{{($landlordContractInfo->cleaning_charge_method==1)?'Cleaning Value':'Cleaning Amount' }} :  </b>
               {{ ($landlordContractInfo->cleaning_charge_method==1) ? $landlordContractInfo->landlord_contract_cleaning_charge.' %' : numberFormat($landlordContractInfo->landlord_contract_cleaning_charge).' OMR' }}
               </h5>
          @endif
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/landlord_contract_info.blade.php"
git add Modules/Sales/Resources/views/LandlordSales/landlord_contract_info.blade.php
git commit -m "feat: display Cleaning Charges on landlord contract info page"
```

---

## Task 19: Display — `landlord_contract_approve_info.blade.php` (Sales)

**Files:**
- Modify: `Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_info.blade.php`

- [ ] **Step 1: Insert the display block**

Find the Management Fee display around line 291–296 (same `@if($landlordContractInfo->landlord_contract_management_fee) ... {{numberFormat($landlordContractInfo->landlord_contract_management_fee)??'0'}} OMR` structure as Task 18). Insert the same display block shown in Task 18, Step 1 immediately after its `@endif`, adjusting to use `numberFormat(...)` for the amount case exactly as this file's Management Fee line already does:
```php
         @if($landlordContractInfo->landlord_contract_cleaning_charge)
               <h5 class="details"><b>{{($landlordContractInfo->cleaning_charge_method==1)?'Cleaning Value':'Cleaning Amount' }} :  </b>
               {{ ($landlordContractInfo->cleaning_charge_method==1) ? $landlordContractInfo->landlord_contract_cleaning_charge.' %' : numberFormat($landlordContractInfo->landlord_contract_cleaning_charge).' OMR' }}
               </h5>
          @endif
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_info.blade.php"
git add Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_info.blade.php
git commit -m "feat: display Cleaning Charges on landlord contract approval info page"
```

---

## Task 20: Display — `landlord_contract_approve_pending_info.blade.php` (Sales)

**Files:**
- Modify: `Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_pending_info.blade.php`

- [ ] **Step 1: Insert the display block**

Find the Management Fee display around line 226–231 (same structure as Task 19). Insert the identical block from Task 19, Step 1 immediately after its `@endif`.

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_pending_info.blade.php"
git add Modules/Sales/Resources/views/LandlordSales/landlord_contract_approve_pending_info.blade.php
git commit -m "feat: display Cleaning Charges on landlord contract pending-approval info page"
```

---

## Task 21: Display — `view.blade.php` (BackOffice LandlordContract)

**Files:**
- Modify: `Modules/BackOffice/Resources/views/LandlordContract/view.blade.php`

**Interfaces:**
- Consumes: columns via `$landlordContractInfo` (confirm this exact variable name in the file — it matches the Sales views' convention here too based on the earlier grep at lines 115–165).

- [ ] **Step 1: Insert the display block**

Find the existing Management Fee value display block around line 147–156 (guarded by `@if(isset($landlordContractInfo->landlord_contract_management_fee) && ($landlordContractInfo->management_id==3 ||$landlordContractInfo->management_id==2) )`). Insert immediately after its `@endif` — note this file's guard also checks `management_id`, which Cleaning Charges must **not** do (it shows for all management types per Global Constraints), so use:
```php
         @if(isset($landlordContractInfo->landlord_contract_cleaning_charge) && $landlordContractInfo->landlord_contract_cleaning_charge)
			   <h5 class="details"><b>{{($landlordContractInfo->cleaning_charge_method==1)?'Cleaning Value':'Cleaning Amount' }} :  </b>
			   <span>{{($landlordContractInfo->cleaning_charge_method==1)?  $landlordContractInfo->landlord_contract_cleaning_charge.' %' : numberFormat($landlordContractInfo->landlord_contract_cleaning_charge).' OMR' }}</span>
			   </h5>
		   @endif
```

- [ ] **Step 2: Lint and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/LandlordContract/view.blade.php"
git add Modules/BackOffice/Resources/views/LandlordContract/view.blade.php
git commit -m "feat: display Cleaning Charges on BackOffice landlord contract view page"
```

---

## Task 22: Display — three Renewal single-line views

**Files:**
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_view.blade.php` (line 105, variable `$landlordContract->newLandlordContract`)
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_approval_contract_view.blade.php` (line 102, variable `$landlordApprove->newLandlordContract`)
- Modify: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewed_contract_view.blade.php` (line 92, variable `$renewedContract->newLandlordContract`)

These three files each have a single-line Management Fee display in this exact form:
```blade
<h5 class="details"><b>Management Fee  :  </b><span>{{ {{VAR}}->landlord_contract_management_fee ?? '' }}</span></h5>
```

- [ ] **Step 1: `landlord_renewal_contract_view.blade.php`** — immediately after line 105, insert:
```blade
   <h5 class="details"><b>Cleaning Charges  :  </b><span>{{ ($landlordContract->newLandlordContract->cleaning_charge_method ?? null) == 1 ? ($landlordContract->newLandlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($landlordContract->newLandlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
```

- [ ] **Step 2: `landlord_approval_contract_view.blade.php`** — immediately after line 102, insert:
```blade
   <h5 class="details"><b>Cleaning Charges  :  </b><span>{{ ($landlordApprove->newLandlordContract->cleaning_charge_method ?? null) == 1 ? ($landlordApprove->newLandlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($landlordApprove->newLandlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
```

- [ ] **Step 3: `landlord_renewed_contract_view.blade.php`** — immediately after line 92, insert:
```blade
   <h5 class="details"><b>Cleaning Charges  :  </b><span>{{ ($renewedContract->newLandlordContract->cleaning_charge_method ?? null) == 1 ? ($renewedContract->newLandlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($renewedContract->newLandlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
```

- [ ] **Step 4: Lint all three and commit together**

```bash
for f in Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_view.blade.php \
         Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_approval_contract_view.blade.php \
         Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewed_contract_view.blade.php; do
  /c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "$f"
done
git add Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_view.blade.php \
        Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_approval_contract_view.blade.php \
        Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewed_contract_view.blade.php
git commit -m "feat: display Cleaning Charges on renewal contract view/approval/renewed pages"
```

---

## Task 23: Display — `early_landlord_termination_ajax.blade.php` and `pdc_acceptanace.blade.php`

**Files:**
- Modify: `Modules/BackOffice/Resources/views/Termination/early_landlord_termination_ajax.blade.php` (line 49, variable `$landlordContract`)
- Modify: `Modules/BackOffice/Resources/views/TenantContract/pdc_acceptanace.blade.php` (line 111, variable `$landlordContractInfo`, inside a `<li>` list, not an `<h5>`)

- [ ] **Step 1: `early_landlord_termination_ajax.blade.php`** — immediately after line 49, insert:
```blade
             <h5 class="details"><b>Cleaning Charges :  </b><span>{{ ($landlordContract->cleaning_charge_method ?? null) == 1 ? ($landlordContract->landlord_contract_cleaning_charge ?? '').' %' : numberFormat($landlordContract->landlord_contract_cleaning_charge ?? 0).' OMR' }}</span></h5>
```

- [ ] **Step 2: `pdc_acceptanace.blade.php`** — immediately after line 111 (`<li>{{$landlordContractInfo->landlord_contract_management_fee}}</li>`), insert:
```blade
                            <li>{{ $landlordContractInfo->landlord_contract_cleaning_charge }}</li>
```

- [ ] **Step 3: Lint both and commit**

```bash
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/Termination/early_landlord_termination_ajax.blade.php"
/c/laragon/bin/php/php-7.4.33-Win32-vc15-x64/php.exe -l "Modules/BackOffice/Resources/views/TenantContract/pdc_acceptanace.blade.php"
git add Modules/BackOffice/Resources/views/Termination/early_landlord_termination_ajax.blade.php Modules/BackOffice/Resources/views/TenantContract/pdc_acceptanace.blade.php
git commit -m "feat: display Cleaning Charges on early termination and PDC acceptance views"
```

---

## Task 24: End-to-end verification

**Files:** none (verification only)

- [ ] **Step 1: Verify a full create → display round trip via a standalone script**

Write a throwaway script (in the scratchpad, not committed) that:
1. Creates a test `landlord_contract` row directly via `LandlordContract::create([...])` with `cleaning_charge_method => 1` and `landlord_contract_cleaning_charge => 5.5`.
2. Reads it back and confirms both values persist correctly.
3. Renders `Modules/Sales/Resources/views/LandlordSales/landlord_contract_info.blade.php` with that record as `$landlordContractInfo` (plus whatever else the view requires) and confirms the rendered HTML contains "Cleaning Value" and "5.5 %".
4. Deletes the test row.

- [ ] **Step 2: Manually click through one full flow in the browser**

Using the `run` skill or dev server: open the BackOffice "Create Contract" (Direct) form, fill in Cleaning Charges as an Amount (not Percentage) this time, save, then open the contract's view page and confirm "Cleaning Amount : <value> OMR" displays correctly. Toggle between Percentage and Amount on the form first to confirm the JS label/placeholder swap works exactly like Management Fee's does.

- [ ] **Step 3: Report results, no commit needed for this task** (Step 1's script is scratch-only; delete it after running.)

---

## Sync to `server_upload_files/`

After all tasks pass review, mirror every edited file (not just the Task 1 migration) into `server_upload_files/` at the same relative path, `diff -q` each pair to confirm, then commit that sync as its own commit. This mirrors the deployment-staging convention already established elsewhere in this project (see `client_reports/tasks_data.json` entries for prior v2 reports).
