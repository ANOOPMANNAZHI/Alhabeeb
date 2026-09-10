# Cleaning Charges Amount-Only Refactor — Report

Task: Remove the Percentage/Amount radio toggle for Cleaning Charges (the
`cleaning_charge_method` column has already been dropped) from 4 landlord
contract blade views, keeping `landlord_contract_cleaning_charge` unchanged.
Each file committed separately.

## File 1: `Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php`

**Before (HTML block, lines ~226-248):**
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

**After:**
```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" placeholder="Enter Cleaning Amount" min="0" max="999999999">
                </div>
            </div>
        </div>
```

**JS block removed (lines ~522-533):**
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

Lint: `No syntax errors detected in Modules/Sales/Resources/views/LandlordSales/contract_in_draft.blade.php`
Commit: `94fd9a29` — "refactor: simplify Cleaning Charges to amount-only on Sales draft-creation form"

## File 2: `Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php`

**Before (HTML block, lines ~211-233):**
```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cleaning_charge_method">Cleaning Charges</label>
                 <div class="p-relative">
                 <label for="cleaning_charge_method_one">
                        <input type="radio" name="cleaning_charge_method" id="cleaning_charge_method_one" value="1" {{ isset($landlordContract->cleaning_charge_method)? (($landlordContract->cleaning_charge_method==1)?'CHECKED':''):'CHECKED' }} class="cleaning_charge_method"> Percentage
                </label>
                <label for="cleaning_charge_method_two">
                    <input type="radio" name="cleaning_charge_method" id="cleaning_charge_method_two" value="2" {{ isset($landlordContract->cleaning_charge_method)? (($landlordContract->cleaning_charge_method==2)?'CHECKED':''):'' }} class="cleaning_charge_method"> Amount
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

**After:**
```html
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="landlord_contract_cleaning_charge">Cleaning Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number" class="form-control" id="landlord_contract_cleaning_charge" name="landlord_contract_cleaning_charge" value="{{ isset($landlordContract->landlord_contract_cleaning_charge)?$landlordContract->landlord_contract_cleaning_charge:'' }}" placeholder="Enter Cleaning Amount" min="0" max="999999999">
                </div>
            </div>
        </div>
```

**JS block removed (lines ~570-581):** same pattern as File 1.

Lint: `No syntax errors detected in Modules/Sales/Resources/views/LandlordSales/edit_contract.blade.php`
Commit: `0b6ede93` — "refactor: simplify Cleaning Charges to amount-only on Sales edit form"

## File 3: `Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php`

**Before (HTML block, lines ~210-232):** identical structure to File 1 (create-only, `checked` on percentage radio, "Cleaning Value" label/placeholder).

**After:** same single-field pattern as File 1.

**JS block removed (lines ~505-516):** same pattern as File 1.

Lint: `No syntax errors detected in Modules/BackOffice/Resources/views/LandlordContract/add_contract_direct.blade.php`
Commit: `7abc1d7b` — "refactor: simplify Cleaning Charges to amount-only on BackOffice direct creation form"

## File 4: `Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php`

**Before (HTML block, lines ~211-233):** identical structure to File 2 (edit form with `isset($landlordContract->cleaning_charge_method)` prefill logic and `landlord_contract_cleaning_charge` value prefill).

**After:** same single-field pattern as File 2 (value prefill retained).

**JS block removed (lines ~612-623):** same pattern as File 1.

Lint: `No syntax errors detected in Modules/BackOffice/Resources/views/LandlordContract/edit_contract.blade.php`
Commit: `485de33d` — "refactor: simplify Cleaning Charges to amount-only on BackOffice direct edit form"

## Verification

- `grep -rn "cleaning_charge_method"` across all 4 files after edits: no matches (fully removed).
- `landlord_contract_cleaning_charge` field/value untouched apart from label/placeholder text ("Cleaning Value" -> "Cleaning Amount") and removal of the surrounding radio markup.
- Management Fee radio toggle (separate field/JS) was not touched in any file.
- Each file committed individually; no `git add -A`/`git add .` used.

## Commit hashes

| File | Commit |
|---|---|
| 1. Sales `contract_in_draft.blade.php` | `94fd9a29` |
| 2. Sales `edit_contract.blade.php` | `0b6ede93` |
| 3. BackOffice `add_contract_direct.blade.php` | `7abc1d7b` |
| 4. BackOffice `edit_contract.blade.php` | `485de33d` |
