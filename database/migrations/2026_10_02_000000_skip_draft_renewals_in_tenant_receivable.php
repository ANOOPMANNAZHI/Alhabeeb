<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Keeps unapproved renewal drafts out of public.tenantrentreceivable() and
 * public.tenantrentreceivablecompo() (wrapped by the *_v2 functions behind the
 * Rent Receivable V2 and Tenant Aging reports).
 *
 * Bug: a renewal saved but never sent for approval is stored with
 * tenant_contract_status 0 and no work_flow_processes_code. The "inactive, not
 * terminated" branch (status 0, no 505 termination) took it for an expired
 * contract and showed its full contract value as due: Merbat-1 Tower S 14
 * TAG2601339 (6,500) and TAG2601340 (7,800) next to the real renewal
 * TAG2601341.
 *
 * Fix: that branch also requires work_flow_processes_code IS NOT NULL. The
 * Receivables Follow-up already counts only approved (108) contracts.
 *
 * Only that one WHERE line is changed, on the definition currently in the
 * database; the migration stops if the line is not found exactly once.
 */
class SkipDraftRenewalsInTenantReceivable extends Migration
{
    private $lines = [
        'tenantrentreceivable'      => "where tc.tenant_contract_status = '0' and b.building_status = '1' and ter.termination_date is null",
        'tenantrentreceivablecompo' => "where tc.tenant_contract_status = '0' and ter.termination_date is null",
    ];

    private $extra = ' and tc.work_flow_processes_code is not null';

    public function up()
    {
        foreach ($this->lines as $function => $line) {
            $this->rewrite($function, $line, $line . $this->extra);
        }
    }

    public function down()
    {
        foreach ($this->lines as $function => $line) {
            $this->rewrite($function, $line . $this->extra, $line);
        }
    }

    private function rewrite($function, $from, $to)
    {
        $sql = DB::selectOne("SELECT pg_get_functiondef(?::regproc) AS def", ['public.' . $function])->def;
        if (strpos($to, $from) !== false && strpos($sql, $to) !== false) {
            throw new RuntimeException("$function: already changed: $to");
        }
        $count = substr_count($sql, $from);
        if ($count !== 1) {
            throw new RuntimeException("$function: expected the line once, found it $count times: $from");
        }
        DB::unprepared(str_replace($from, $to, $sql));
    }
}
