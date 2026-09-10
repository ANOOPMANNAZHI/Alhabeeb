<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Masters\Entities\Unit;
use Modules\BackOffice\Exports\CashTenantReportExport;

/**
 * Cash Tenant Report (PLM Module -> Reports).
 *
 * Lists the ACTIVE tenant contracts that have no post dated cheque on record -
 * i.e. no row at all in the pdc table - which in practice means the tenant is
 * paying by cash.
 *
 * No filters by design: the screen is the list, with Excel and PDF downloads
 * covering the whole result set rather than the current page.
 *
 * NOTE a contract whose cheques have simply not been entered yet looks the same
 * as a genuine cash tenant, which is why the Start date is on the report.
 */
class CashTenantReportController extends Controller
{
    /** Rows per page on screen. Downloads are never paginated. */
    const PER_PAGE = 50;

    /**
     * tenant_contract_payment_type values, matching
     * TenantContract::getTenantContractPaymentNameAttribute().
     */
    const PAYMENT_TERMS = [
        '1' => 'Monthly',
        '2' => 'Bi-Monthly',
        '3' => 'Quarterly',
        '4' => 'Half Yearly',
        '5' => 'Yearly',
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_cash_tenant_report');
    }

    /**
     * The screen. Paginated; the ajax request returns just the table so sorting
     * and paging do not reload the page.
     */
    public function index(Request $request)
    {
        $rows  = $this->baseQuery($request)->paginate(self::PER_PAGE);
        $total = $rows->total();

        // Keep the filters on the pagination links, otherwise page 2 drops them.
        $rows->appends($request->except('page'));

        if ($request->ajax()) {
            return view('backoffice::Reports.cash_tenant_report_ajax', compact('rows', 'total'));
        }

        // Dropdown options, and the current filter values so the inputs keep
        // what was typed after the page reloads.
        $unitTypes    = DB::table('unit_types')->orderBy('unit_types_name')->get();
        $paymentTerms = self::PAYMENT_TERMS;
        $filters      = $request->only(
            array_merge(array_keys(self::TEXT_FILTERS), array_keys(self::EXACT_FILTERS))
        );

        return view('backoffice::Reports.cash_tenant_report',
            compact('rows', 'total', 'unitTypes', 'paymentTerms', 'filters'));
    }

    /**
     * Excel / PDF of the FULL result set.
     * download_type=pdf gives the PDF, anything else the spreadsheet.
     */
    public function download(Request $request)
    {
        // $request carries the same column filters as the screen, so the export
        // matches what the user is looking at rather than the whole report.
        $rows  = $this->baseQuery($request)->get();
        $total = $rows->count();
        $user  = \Auth::user();

        $data = compact('rows', 'total', 'user');

        if ($request->input('download_type') === 'pdf') {
            $logoPath = public_path('img/logo_pdf.jpg');
            if (is_file($logoPath)) {
                $data['logo'] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = \PDF::loadView('backoffice::Reports.cash_tenant_report_pdf', $data)
                       ->setPaper('a4', 'landscape');

            return $pdf->download('cash_tenant_report.pdf');
        }

        return \Excel::download(new CashTenantReportExport($data), 'cash_tenant_report.xlsx');
    }

    /**
     * Active tenant contracts with no PDC row.
     *
     * "No PDC" is NOT EXISTS against the pdc table rather than the contract's
     * pdc_check flag: pdc_check only ever holds Full (1) or Partial (2), so a
     * NULL there means "not recorded", not "pays cash".
     */
    /**
     * The column filters the screen offers, mapped to the column each one
     * searches. Text columns are matched with ILIKE, the two id/code columns
     * with "=" - ILIKE against an integer column makes Postgres abort with
     * "operator does not exist: integer ~~* unknown".
     */
    const TEXT_FILTERS = [
        'f_contract_no' => 'tc.tenant_contract_no',
        'f_tenant'      => 't.tenant_name',
        'f_building'    => 'b.building_name',
        'f_unit'        => 'u.unit_code',
        'f_mobile'      => 't.tenant_contact_no',
    ];

    const EXACT_FILTERS = [
        'f_unit_type'    => 'u.unit_type_id',
        'f_payment_term' => 'tc.tenant_contract_payment_type',
    ];

    private function baseQuery(Request $request = null)
    {
        $areBuildingIds = $this->areBuildingIds();

        return DB::table('tenant_contracts as tc')
            ->leftJoin('buildings as b', 'b.id', '=', 'tc.building_id')
            ->leftJoin('units as u', 'u.id', '=', 'tc.unit_id')
            ->leftJoin('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->leftJoin('unit_types as ut', 'ut.id', '=', 'u.unit_type_id')
            ->where('tc.tenant_contract_status', 1)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('pdc as p')
                  ->whereRaw('p.tenant_contract_id = tc.id');
            })
            // An ARE (or ARE team lead) sees only their own buildings; admin,
            // managers and anyone holding more than one role see everything.
            ->when($areBuildingIds !== null, function ($q) use ($areBuildingIds) {
                $q->whereIn('b.id', $areBuildingIds);
            })
            // Column filters. Applied here rather than in index() so the Excel
            // and PDF downloads export exactly what is on screen.
            ->when($request !== null, function ($q) use ($request) {
                foreach (self::TEXT_FILTERS as $input => $column) {
                    $value = trim((string) $request->input($input));
                    if ($value !== '') {
                        $q->where($column, 'ilike', '%' . $value . '%');
                    }
                }
                foreach (self::EXACT_FILTERS as $input => $column) {
                    $value = $request->input($input);
                    if ($value !== null && $value !== '') {
                        $q->where($column, $value);
                    }
                }
            })
            ->orderBy('b.building_name')
            ->orderBy('u.unit_code')
            ->select([
                'tc.id',
                'tc.tenant_contract_no',
                'tc.tenant_contract_start_date',
                'tc.tenant_contract_valid_to_date',
                'tc.tenant_contract_rent',
                'tc.tenant_contract_payment_type',
                't.tenant_name',
                't.tenant_contact_no',
                'b.building_name',
                'u.unit_code',
                'ut.unit_types_name',
            ]);
    }

    /**
     * Building ids this user is limited to, or NULL when they may see everything.
     *
     * Mirrors Unit::scopeAre(): only a user whose SINGLE role is are or
     * are_team_lead is restricted. Admin, every manager role, and anyone holding
     * more than one role fall through unrestricted.
     *
     * The building list itself comes from Unit::are() rather than being rebuilt
     * here, so the assignment rules - including the team lead's head_user walk -
     * stay defined in one place and this report follows any change to them.
     *
     * @return array|null
     */
    private function areBuildingIds()
    {
        $user = \Auth::user();

        if (!$user) {
            return null;
        }

        if (count($user->roles) !== 1) {
            return null;
        }

        if (!$user->hasRole('are') && !$user->hasRole('are_team_lead')) {
            return null;
        }

        return Unit::are()
            ->whereNotNull('building_id')
            ->distinct()
            ->pluck('building_id')
            ->all();
    }

    /**
     * Payment term label for a contract's tenant_contract_payment_type.
     * Mirrors TenantContract::getTenantContractPaymentNameAttribute(), which is
     * not available here because the report reads through the query builder.
     */
    public static function paymentTermLabel($paymentType)
    {
        $key = (string) $paymentType;

        return isset(self::PAYMENT_TERMS[$key]) ? self::PAYMENT_TERMS[$key] : 'NA';
    }
}
