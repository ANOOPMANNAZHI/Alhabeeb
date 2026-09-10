# Cleaning Charges Amount-Only Refactor — Report

Date: 2026-09-06

## Summary

Removed the Percentage/Amount radio toggle for "Cleaning Charges" on 3 landlord renewal contract blade views, leaving `landlord_contract_cleaning_charge` (OMR amount) as the sole field. Also removed the now-dead `.cleaning_charge_method` JS click handler from each file's script block.

## Files changed (one commit each)

### File 1: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php`

**HTML block — before (lines ~235-257):**
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

**HTML block — after:**
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

**JS handler removed (was at ~line 647, standard location after another `.on('click', ...)` handler):**
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

Lint: `No syntax errors detected in Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_add.blade.php`

Commit: `798063f0` — refactor: simplify Cleaning Charges to amount-only on landlord renewal contract add form (1 file changed, 4 insertions, 30 deletions)

---

### File 2: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php`

Identical before/after HTML block as File 1 (found at lines ~233-255).

**JS handler removed (was at ~line 525):**
Same handler body as File 1.

Lint: `No syntax errors detected in Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renewal_contract_update.blade.php`

Commit: `f838fdcc` — refactor: simplify Cleaning Charges to amount-only on landlord renewal new-contract update form (1 file changed, 4 insertions, 30 deletions)

---

### File 3: `Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php`

Identical before/after HTML block as File 1 (found at lines ~196-218).

**JS handler removed** — confirmed via `grep -n "cleaning_charge_method"` at line 457. As noted in the task, this file's handler was standalone at the end of the `$(document).ready()` block, directly preceded by the closing `});` of an unrelated ajax-based click handler (`#sales_id` button), and followed only by the `});` closing `document.ready`. Removed cleanly:
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

Lint: `No syntax errors detected in Modules/BackOffice/Resources/views/RenewalOrTermination/landlord_renew_contract_create.blade.php`

Commit: `b27fc294` — refactor: simplify Cleaning Charges to amount-only on landlord renewal contract create form (1 file changed, 4 insertions, 30 deletions)

## Verification

- Post-edit `grep -n "cleaning_charge_method"` on all 3 files returned no matches.
- All 3 files passed `php -l` with no syntax errors.
- Each commit touched exactly one file (verified via `git show --stat`).
- Management Fee's own radio toggle (`management_fee_type`) was left untouched in all 3 files.
- No other files were staged or modified; the many pre-existing unrelated uncommitted changes in the repo were left untouched (`git add` was scoped to each individual file, never `-A` or `.`).

## Commit hashes

1. `798063f0` — File 1 (landlord_renew_contract_add.blade.php)
2. `f838fdcc` — File 2 (landlord_renewal_contract_update.blade.php)
3. `b27fc294` — File 3 (landlord_renew_contract_create.blade.php)
