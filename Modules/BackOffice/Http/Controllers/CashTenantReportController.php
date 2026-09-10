<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
        $rows  = $this->baseQuery()->paginate(self::PER_PAGE);
        $total = $rows->total();

        if ($request->ajax()) {
            return view('backoffice::Reports.cash_tenant_report_ajax', compact('rows', 'total'));
        }

        return view('backoffice::Reports.cash_tenant_report', compact('rows', 'total'));
    }

    /**
     * Excel / PDF of the FULL result set.
     * download_type=pdf gives the PDF, anything else the spreadsheet.
     */
    public function download(Request $request)
    {
        $rows  = $this->baseQuery()->get();
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
    private function baseQuery()
    {
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
     * Payment term label for a contract's tenant_contract_payment_type.
     * Mirrors TenantContract::getTenantContractPaymentNameAttribute(), which is
     * not available here because the report reads through the query builder.
     */
    public static function paymentTermLabel($paymentType)
    {
        switch ((string) $paymentType) {
            case '1': return 'Monthly';
            case '2': return 'Bi-Monthly';
            case '3': return 'Quarterly';
            case '4': return 'Half Yearly';
            case '5': return 'Yearly';
        }

        return 'NA';
    }
}
