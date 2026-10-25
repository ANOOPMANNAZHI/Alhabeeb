<?php

namespace Modules\Masters\Services;

use Illuminate\Support\Facades\DB;

/**
 * Legal waivers (plan 2026-10-13, phase 4). A waiver PLM verifies on a settlement is a credit on the
 * contract: every outstanding / receivable figure subtracts the live (not cancelled) waivers of the
 * contract. The waiver is cancelled when the deal falls through, and the amount is owed again.
 */
class LegalWaiver
{
    /** Live waiver total for one contract. */
    public static function forContract($contractId)
    {
        if (!$contractId) {
            return 0.0;
        }
        return round((float) DB::table('legal_waivers')->where('tenant_contract_id', $contractId)
            ->whereNull('cancelled_at')->sum('amount'), 3);
    }

    /** Live waiver totals for many contracts: [contract id => amount]. */
    public static function forContracts(array $contractIds)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $contractIds))));
        if (!$ids) {
            return [];
        }
        $out = [];
        foreach (array_chunk($ids, 5000) as $chunk) {
            $rows = DB::table('legal_waivers')->whereIn('tenant_contract_id', $chunk)->whereNull('cancelled_at')
                ->groupBy('tenant_contract_id')->select('tenant_contract_id', DB::raw('sum(amount) as amt'))->get();
            foreach ($rows as $r) {
                $out[(int) $r->tenant_contract_id] = round((float) $r->amt, 3);
            }
        }
        return $out;
    }

    /** SQL expression: live waiver total of the contract in column $contractColumn (for raw report SQL). */
    public static function sql($contractColumn)
    {
        return "(SELECT coalesce(sum(lw.amount), 0) FROM legal_waivers lw WHERE lw.tenant_contract_id = {$contractColumn} AND lw.cancelled_at IS NULL)";
    }

    /** Record the waiver of a verified settlement. */
    public static function record($settlement, $contractId, $userId)
    {
        if ((float) $settlement->waiver_amount <= 0) {
            return null;
        }
        $now = now();
        $id = DB::table('legal_waivers')->insertGetId(['legal_id' => $settlement->legal_id, 'settlement_id' => $settlement->id,
            'tenant_contract_id' => $contractId, 'amount' => $settlement->waiver_amount, 'reason' => $settlement->waiver_reason,
            'approved_by' => $userId, 'approved_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        self::touch($contractId);
        return $id;
    }

    /**
     * "Balance written off" on close: the unpaid balance becomes a waiver, so it leaves every receivable like the
     * settlement waiver does (user, 18/10/2026). Reopening the case can cancel it with the others.
     */
    public static function writeOff($settlement, $contractId, $amount, $note, $userId)
    {
        if ($amount <= 0) {
            return null;
        }
        $now = now();
        $id = DB::table('legal_waivers')->insertGetId(['legal_id' => $settlement->legal_id, 'settlement_id' => $settlement->id,
            'tenant_contract_id' => $contractId, 'amount' => round($amount, 3),
            'reason' => mb_substr('Balance written off on close' . ($note ? ': ' . $note : ''), 0, 500),
            'approved_by' => $userId, 'approved_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        self::touch($contractId);
        return $id;
    }

    /** Live (not cancelled) waiver total of a case. */
    public static function liveForCase($legalId)
    {
        return round((float) DB::table('legal_waivers')->where('legal_id', $legalId)->whereNull('cancelled_at')->sum('amount'), 3);
    }

    /** Cancel the live waivers of a settlement (or of every settlement of a case when $settlementId is null). */
    public static function cancel($legalId, $settlementId, $reason)
    {
        $q = DB::table('legal_waivers')->where('legal_id', $legalId)->whereNull('cancelled_at');
        if ($settlementId) {
            $q->where('settlement_id', $settlementId);
        }
        $contracts = (clone $q)->pluck('tenant_contract_id')->all();
        $n = $q->update(['cancelled_at' => now(), 'cancel_reason' => mb_substr($reason, 0, 200), 'updated_at' => now()]);
        DB::table('legal_settlements')->where('legal_id', $legalId)
            ->when($settlementId, function ($w) use ($settlementId) { $w->where('id', $settlementId); })
            ->whereNull('waiver_cancelled_at')->where('waiver_amount', '>', 0)->update(['waiver_cancelled_at' => now()]);
        foreach (array_unique($contracts) as $c) {
            self::touch($c);
        }
        return $n;
    }

    /** Stored figures that follow receipts (termination dues) are refreshed after a waiver change. */
    private static function touch($contractId)
    {
        \Modules\BackOffice\Services\TerminationDuesService::touchContract($contractId);
    }
}
