<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Entities\LandlordInvoiceV2;

class LandlordInvoiceV2Controller extends Controller
{
    protected $calc;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_landlord_invoice_v2');
        $this->calc = app(BackOfficeReportController::class);
    }

    public function index(Request $request)
    {
        $query = LandlordInvoiceV2::with(['vendor', 'landlordContract.buildingInfo']);

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'ilike', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('building_name')) {
            $query->where('building_name', 'ilike', '%' . $request->building_name . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('invoice_date', '<=', $request->to_date);
        }

        $landlordInvoicesV2 = $query->orderBy('id', 'desc')->paginate(20)->appends($request->query());

        return view('backoffice::LandlordInvoiceV2.index', compact('landlordInvoicesV2'));
    }

    public function create()
    {
        $vendors = Vendor::active()->orderBy('vendor_name')->get();
        return view('backoffice::LandlordInvoiceV2.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_type'          => 'required|in:tax_invoice,other_deductions',
            'invoice_date'          => 'required|date',
            'vendor_id'             => 'required|exists:vendors,id',
            'landlord_contract_id'  => 'required|exists:landlord_contract,id',
            'period_month'          => 'required|integer|min:1|max:12',
            'period_year'           => 'required|integer|min:2000|max:2100',
            'lines'                 => 'required|array|min:1',
            'lines.*.description'   => 'required|string',
            'lines.*.amount'        => 'required|numeric',
        ]);

        $vendor   = Vendor::findOrFail($request->vendor_id);
        $contract = LandlordContract::with('buildingInfo')->findOrFail($request->landlord_contract_id);

        abort_if((int) $contract->vendor_id !== (int) $vendor->id, 422, 'The selected contract does not belong to the selected vendor.');

        $prefixKey = $request->invoice_type === 'tax_invoice'
            ? 'landlord_invoice_v2_tax_invoice_prefix'
            : 'landlord_invoice_v2_other_deductions_prefix';

        $setting = Setting::where('configuration_settings', $prefixKey)->first();
        abort_if(!$setting, 500, 'Invoice numbering is not configured for this invoice type.');

        // Year-rollover guard: mirrors the $isYearCorrect pattern used by
        // LandlordInvoiceController/add_invoice.blade.php, but since this
        // feature only generates the invoice number at store() time (no
        // create-page preview moment to alert the user beforehand), we
        // auto-roll-over here instead of just warning.
        if ((int) $setting->configuration_year !== (int) date('y')) {
            $setting->configuration_year = (int) date('y');
            $setting->configuration_increment_value = 1;
            $setting->save();
        }

        $nextCode = $setting->configuration_value . $setting->configuration_year
            . str_pad($setting->configuration_increment_value, 5, '0', STR_PAD_LEFT);

        $subtotal = 0.0;
        $vatTotal = 0.0;
        $lineRows = [];
        foreach (array_values($request->lines) as $i => $line) {
            $amount = round((float) $line['amount'], 3);
            $vat = $request->invoice_type === 'tax_invoice' ? round($amount * 0.05, 3) : 0.0;
            $subtotal += $amount;
            $vatTotal += $vat;
            $lineRows[] = [
                'description' => $line['description'],
                'amount'      => $amount,
                'vat_amount'  => $vat,
                'line_order'  => $i + 1,
            ];
        }

        $invoice = LandlordInvoiceV2::create([
            'invoice_type'         => $request->invoice_type,
            'invoice_no'           => $nextCode,
            'invoice_date'         => $request->invoice_date,
            'vendor_id'            => $vendor->id,
            'landlord_contract_id' => $contract->id,
            'period_month'         => $request->period_month,
            'period_year'          => $request->period_year,
            'vendor_name'          => $vendor->vendor_name,
            'building_name'        => optional($contract->buildingInfo)->building_name,
            'vendor_address'       => $vendor->vendor_contact_address,
            'vatin_no'             => $vendor->vatin_no,
            'status'               => 'active',
            'subtotal'             => round($subtotal, 3),
            'vat_total'            => round($vatTotal, 3),
            'grand_total'          => round($subtotal + $vatTotal, 3),
            'created_by'           => \Auth::user()->id,
        ]);

        $invoice->lines()->createMany($lineRows);

        Setting::where('configuration_settings', $prefixKey)
            ->update(['configuration_increment_value' => $setting->configuration_increment_value + 1]);

        session()->flash('success', 'Landlord Invoice Created: ' . $nextCode);
        return redirect()->route('landlord-invoice-v2.index');
    }

    public function edit(LandlordInvoiceV2 $landlordInvoiceV2)
    {
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'Voided invoices cannot be edited.');
        $landlordInvoiceV2->load('lines');
        return view('backoffice::LandlordInvoiceV2.edit', compact('landlordInvoiceV2'));
    }

    public function update(Request $request, LandlordInvoiceV2 $landlordInvoiceV2)
    {
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'Voided invoices cannot be edited.');

        $request->validate([
            'invoice_date'    => 'required|date',
            'lines'           => 'required|array|min:1',
            'lines.*.id'      => 'required|exists:landlord_invoice_v2_lines,id',
            'lines.*.amount'  => 'required|numeric',
        ]);

        $subtotal = 0.0;
        $vatTotal = 0.0;
        foreach ($request->lines as $line) {
            $lineModel = $landlordInvoiceV2->lines()->findOrFail($line['id']);
            $amount = round((float) $line['amount'], 3);
            $vat = $landlordInvoiceV2->invoice_type === 'tax_invoice' ? round($amount * 0.05, 3) : 0.0;
            $lineModel->update(['amount' => $amount, 'vat_amount' => $vat]);
            $subtotal += $amount;
            $vatTotal += $vat;
        }

        $landlordInvoiceV2->update([
            'invoice_date' => $request->invoice_date,
            'subtotal'     => round($subtotal, 3),
            'vat_total'    => round($vatTotal, 3),
            'grand_total'  => round($subtotal + $vatTotal, 3),
        ]);

        session()->flash('success', 'Landlord Invoice Updated');
        return redirect()->route('landlord-invoice-v2.index');
    }

    public function destroy(LandlordInvoiceV2 $landlordInvoiceV2)
    {
        abort_if($landlordInvoiceV2->status === 'voided', 403, 'This invoice is already voided.');

        $landlordInvoiceV2->update([
            'status'    => 'voided',
            'voided_at' => now(),
            'voided_by' => \Auth::user()->id,
        ]);

        session()->flash('success', 'Landlord Invoice Voided: ' . $landlordInvoiceV2->invoice_no);
        return redirect()->route('landlord-invoice-v2.index');
    }

    public function contractsByVendor(Request $request)
    {
        $contracts = LandlordContract::with('buildingInfo')
            ->where('vendor_id', $request->vendor_id)
            ->where('landlord_contract_status', 1)
            ->get()
            ->map(function ($c) {
                return [
                    'id'    => $c->id,
                    'label' => $c->landlord_contract_no . ' - ' . optional($c->buildingInfo)->building_name,
                ];
            });

        return response()->json($contracts);
    }

    public function contractDetails(LandlordContract $landlordContract)
    {
        $vendor = $landlordContract->vendorInfo;

        return response()->json([
            'vendor_name'    => optional($vendor)->vendor_name,
            'vendor_address' => optional($vendor)->vendor_contact_address,
            'vatin_no'       => optional($vendor)->vatin_no,
            'building_name'  => optional($landlordContract->buildingInfo)->building_name,
        ]);
    }

    public function calculationPreview(Request $request)
    {
        $request->validate([
            'landlord_contract_id' => 'required|exists:landlord_contract,id',
            'invoice_type'         => 'required|in:tax_invoice,other_deductions',
            'period_month'         => 'required|integer|min:1|max:12',
            'period_year'          => 'required|integer|min:2000|max:2100',
        ]);

        $contract = LandlordContract::with('buildingInfo')->findOrFail($request->landlord_contract_id);
        $building = $contract->buildingInfo;
        abort_if(!$building, 404, 'This contract has no building assigned.');

        $fromDate = sprintf('%04d-%02d-01', $request->period_year, $request->period_month);
        $toDate   = date('Y-m-t', strtotime($fromDate));

        if ($request->invoice_type === 'tax_invoice') {
            $amounts = $this->calc->landlordTaxInvoiceLineAmounts($building, $fromDate, $toDate, $contract->vendor_id);
            $lines = [
                ['description' => 'MANAGEMENT FEES FOR ' . $amounts['period_label'], 'amount' => $amounts['management_fee']],
                ['description' => 'CLEANING CHARGES FOR ' . $amounts['period_label'], 'amount' => $amounts['cleaning_charge']],
                ['description' => 'REPAIR AND MAINTENANCE CHARGES', 'amount' => $amounts['repair_maintenance']],
            ];
        } else {
            $amounts = $this->calc->landlordOtherDeductionsLineAmounts($building, $fromDate, $toDate);
            $subAmount = $this->calc->landlordSubcontractorRepairMaintenanceAmount($building->id, $fromDate, $toDate);
            $lines = [
                ['description' => 'MUN TAX CHARGES', 'amount' => $amounts['mun_tax_charges']],
                ['description' => 'ELECT. AND WATER CHARGES', 'amount' => $amounts['elect_water_charges']],
                ['description' => 'DEWATERING CHARGES', 'amount' => $amounts['dewatering_charges']],
                ['description' => 'AMC / REPAIR AND MAINTENANCE CHARGES FOR A/C UNITS', 'amount' => $amounts['ac_amc_repair_charges']],
                ['description' => 'AMC FOR F.A.S FOR THE PERIOD', 'amount' => $amounts['fas_amc_charges']],
                ['description' => 'REPAIR AND MAINTENANCE CHARGES (SUB-CONTRACTOR)', 'amount' => $subAmount],
            ];
        }

        return response()->json(['lines' => $lines]);
    }

    /**
     * Building/month overview for the create screen's "Overview" tab -
     * income, collection, expenses, transfer amount, and occupancy figures.
     * Reuses buildNormalManagementMonthData() and the exact same formulas
     * as Normal Management Report v2's consolidated sheet
     * (Modules/BackOffice/Exports/NormalManagementConsolidateSheet.php) so
     * the two screens never disagree on the same month's numbers.
     */
    public function overviewPreview(Request $request)
    {
        $request->validate([
            'landlord_contract_id' => 'required|exists:landlord_contract,id',
            'period_month'         => 'required|integer|min:1|max:12',
            'period_year'          => 'required|integer|min:2000|max:2100',
        ]);

        $contract = LandlordContract::with('buildingInfo')->findOrFail($request->landlord_contract_id);
        $building = $contract->buildingInfo;
        abort_if(!$building, 404, 'This contract has no building assigned.');

        $month = (int) $request->period_month;
        $year  = (int) $request->period_year;

        $monthData = $this->calc->buildNormalManagementMonthData($building, $year, [$month]);
        $data = $monthData[$month] ?? null;

        $income = 0.0;
        $collection = 0.0;
        $totalExpenses = 0.0;
        $occupancy = [
            'total_units' => 0, 'new_leased_residential' => 0, 'new_leased_commercial' => 0,
            'occupied_residential' => 0, 'occupied_commercial' => 0, 'vacant_residential' => 0,
            'vacant_commercial' => 0, 'evacuation_residential' => 0, 'evacuation_commercial' => 0,
        ];

        if ($data) {
            foreach ($data['units'] ?? [] as $u) {
                $income += (float) ($u->income_amount ?? 0);
                $collection += (float) ($u->collection_amount ?? 0);
            }
            foreach ($data['old_outstanding'] ?? [] as $u) {
                $collection += (float) ($u->collection_amount ?? 0);
            }

            foreach ($data['expenses'] ?? [] as $exp) {
                $totalExpenses += (float) $exp->expense_amount;
            }
            // Cleaning Charges, Facility Management Fee, Renewal Fee, and New
            // Leasing Fee are not transactional expenses but are folded into
            // Total Expenses here, matching NormalManagementConsolidateSheet.php.
            $totalExpenses += (float) ($data['cleaning_charge'] ?? 0);
            $totalExpenses += (float) ($data['facility_management_fee'] ?? 0);
            $totalExpenses += (float) ($data['renewal_fee'] ?? 0);
            $totalExpenses += (float) ($data['new_leasing_fee'] ?? 0);

            $occupancy = array_merge($occupancy, $data['occupancy'] ?? []);
        }

        $transfer = $collection - $totalExpenses;
        $occupiedTotal = $occupancy['occupied_residential'] + $occupancy['occupied_commercial'];
        $occupancyLevel = $occupancy['total_units'] > 0
            ? round(($occupiedTotal / $occupancy['total_units']) * 100, 1)
            : 0.0;

        return response()->json([
            'income'                  => round($income, 3),
            'collection'              => round($collection, 3),
            'total_expenses'          => round($totalExpenses, 3),
            'transfer_to_landlord'    => round($transfer, 3),
            'total_units'             => $occupancy['total_units'],
            'new_leased_residential'  => $occupancy['new_leased_residential'],
            'new_leased_commercial'   => $occupancy['new_leased_commercial'],
            'occupied_residential'    => $occupancy['occupied_residential'],
            'occupied_commercial'     => $occupancy['occupied_commercial'],
            'vacant_residential'      => $occupancy['vacant_residential'],
            'vacant_commercial'       => $occupancy['vacant_commercial'],
            'evacuation_residential'  => $occupancy['evacuation_residential'],
            'evacuation_commercial'   => $occupancy['evacuation_commercial'],
            'occupancy_level'         => $occupancyLevel,
        ]);
    }

    public function print(LandlordInvoiceV2 $landlordInvoiceV2)
    {
        $landlordInvoiceV2->load('lines');

        $lines = $landlordInvoiceV2->lines->map(function ($l) {
            return [
                'desc'       => $l->description,
                'qty'        => 1.000,
                'unit_price' => (float) $l->amount,
                'amount'     => (float) $l->amount,
                'vat'        => (float) $l->vat_amount,
                'total'      => round((float) $l->amount + (float) $l->vat_amount, 3),
            ];
        })->toArray();

        $data = [
            'invoice'       => $landlordInvoiceV2,
            'lines'         => $lines,
            'totalAmount'   => (float) $landlordInvoiceV2->subtotal,
            'totalVat'      => (float) $landlordInvoiceV2->vat_total,
            'totalDue'      => (float) $landlordInvoiceV2->grand_total,
            'amountInWords' => $this->calc->landlordTaxInvoiceAmountInWords((float) $landlordInvoiceV2->grand_total),
        ];

        $pdf = \PDF::loadView('backoffice::LandlordInvoiceV2.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream($landlordInvoiceV2->invoice_no . '.pdf');
    }
}
