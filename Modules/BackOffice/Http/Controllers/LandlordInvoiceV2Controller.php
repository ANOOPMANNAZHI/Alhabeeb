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
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
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
        $vendors = Vendor::active()->orderBy('vendor_name')->get();

        return view('backoffice::LandlordInvoiceV2.index', compact('landlordInvoicesV2', 'vendors'));
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

        $prefixKey = $request->invoice_type === 'tax_invoice'
            ? 'landlord_invoice_v2_tax_invoice_prefix'
            : 'landlord_invoice_v2_other_deductions_prefix';

        $setting = Setting::where('configuration_settings', $prefixKey)->first();
        abort_if(!$setting, 500, 'Invoice numbering is not configured for this invoice type.');

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
}
