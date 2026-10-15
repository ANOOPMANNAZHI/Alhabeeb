<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackOffice\Services\RentRevisal;
use Modules\Sales\Entities\TenantContract;

/** Due for Renewal > Rent revisal tab, its popup and save (spec 2026-10-15). */
class RentRevisalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:rent_revisal']);
    }

    public function index(Request $request)
    {
        $user = \Auth::user();
        $q = RentRevisal::windowQuery($user)->with(['tenant', 'building', 'unit', 'rentRevisal.createdBy.employee'])
            ->orderBy('tenant_contracts.tenant_contract_valid_to_date');
        if (trim((string) $request->search) !== '') {
            $term = '%' . trim($request->search) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('tenant_contracts.tenant_contract_no', 'ilike', $term)
                  ->orWhereHas('tenant', function ($t) use ($term) { $t->where('tenant_name', 'ilike', $term); })
                  ->orWhereHas('building', function ($b) use ($term) { $b->where('building_name', 'ilike', $term); });
            });
        }
        if ($request->revisal === 'pending') {
            $q->doesntHave('rentRevisal');
        } elseif ($request->revisal === 'done') {
            $q->has('rentRevisal');
        }
        $contracts = $q->paginate(25)->appends($request->only('search', 'revisal'));
        return view('backoffice::RentRevisal.index', compact('contracts', 'request'));
    }

    public function form(TenantContract $tenant_contract)
    {
        $user = \Auth::user();
        $revisal = $tenant_contract->rentRevisal;
        if ($revisal ? !RentRevisal::inScope($tenant_contract, $user) : !RentRevisal::canRevise($tenant_contract, $user)) {
            return response('This contract cannot be revised.', 422);
        }
        return view('backoffice::RentRevisal.form', ['contract' => $tenant_contract, 'revisal' => $revisal]);
    }

    public function store(Request $request, TenantContract $tenant_contract)
    {
        $rent = str_replace(',', '', trim((string) $request->revised_rent));
        if (!is_numeric($rent) || (float) $rent <= 0) {
            return response()->json(['ok' => false, 'message' => 'Enter the new monthly rent as a number above 0.'], 422);
        }
        if ((float) $rent >= 100000000000) {
            return response()->json(['ok' => false, 'message' => 'The rent is too large. Check the amount.'], 422);
        }
        // A rent more than double or less than half the current rent is usually a typo: ask once before sending.
        $current = (float) $tenant_contract->tenant_contract_rent;
        if ($current > 0 && ((float) $rent > 2 * $current || (float) $rent < $current / 2) && !$request->confirm_large) {
            return response()->json(['ok' => false, 'needs_confirm' => true,
                'message' => 'OMR ' . number_format((float) $rent, 3) . ' is very different from the current rent of OMR ' . number_format($current, 3)
                    . '. Check the amount and press Send again to confirm.'], 422);
        }
        if (mb_strlen((string) $request->note) > 500) {
            return response()->json(['ok' => false, 'message' => 'The note can be at most 500 characters.'], 422);
        }
        list($revisal, $message) = RentRevisal::send($tenant_contract, \Auth::user(), round((float) $rent, 3), $request->note);
        if (!$revisal) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }
        return response()->json(['ok' => true, 'message' => $message, 'email_status' => $revisal->email_status]);
    }
}
