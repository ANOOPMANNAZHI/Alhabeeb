<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Keeps contracts that are not approved out of public.tenantrentreceivable()
 * and public.tenantrentreceivablecompo() (wrapped by the *_v2 functions behind
 * the Rent Receivable V2 and Tenant Aging reports).
 *
 * Bug: a renewal saved but never sent for approval is stored with
 * tenant_contract_status 0 and no work_flow_processes_code. The "inactive, not
 * terminated" branch (status 0, no 505 termination) took it for an expired
 * contract and showed its full contract value as due: Merbat-1 Tower S 14
 * TAG2601339 (6,500) and TAG2601340 (7,800) next to the real renewal
 * TAG2601341. Contracts stuck mid-approval (104, 106) showed the same way.
 *
 * Fix: every row must belong to an approved contract
 * (work_flow_processes_code = 108), the rule the Receivables Follow-up
 * already uses.
 *
 * Only the final WHERE line is changed, on the definition currently in the
 * database; the migration stops if the line is not found exactly once.
 */
class SkipDraftRenewalsInTenantReceivable extends Migration
{
    private $line = 'where (A.startdate::date <= Date2)';

    private $extra = "\nand A.id in (select id from tenant_contracts where work_flow_processes_code = 108)";

    private $functions = ['tenantrentreceivable', 'tenantrentreceivablecompo'];

    public function up()
    {
        foreach ($this->functions as $function) {
            $this->rewrite($function, $this->line, $this->line . $this->extra);
        }
    }

    public function down()
    {
        foreach ($this->functions as $function) {
            $this->rewrite($function, $this->line . $this->extra, $this->line);
        }
    }

    private function rewrite($function, $from, $to)
    {
        $sql = DB::selectOne("SELECT pg_get_functiondef(?::regproc) AS def", ['public.' . $function])->def;
        if (strpos($to, (string) $from) !== false && strpos($sql, (string) $to) !== false) {
            throw new RuntimeException("$function: already changed: $to");
        }
        $count = substr_count($sql, $from);
        if ($count !== 1) {
            throw new RuntimeException("$function: expected the line once, found it $count times: $from");
        }
        DB::unprepared(str_replace($from, $to, $sql));
    }
}
