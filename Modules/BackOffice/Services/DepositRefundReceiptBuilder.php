<?php

namespace Modules\BackOffice\Services;

/**
 * Turns a deposit refund's accounting rows into the figures a customer
 * receipt shows: the deposit held, what was withheld from it, any balance
 * retained, and the amount actually handed back.
 *
 * Pure function - no models, no database, no settings lookup - so the rule
 * can be tested on its own. The caller supplies the payout account list.
 *
 * The rule, in words:
 *   deposit held  = every debit line (the deposit liability being released)
 *   handed back   = credit lines on a payout account (cash, cheque, bank)
 *   retained      = credit lines back to the deposit account itself
 *   deduction     = every other credit line (electricity, water, tax, rent...)
 *
 * Deposit held always equals handed back + deductions + retained.
 */
class DepositRefundReceiptBuilder
{
    /** Account the tenant deposit liability sits on. */
    const DEPOSIT_ACCOUNT = '22311';

    const DECIMALS = 3;

    /**
     * @param array $rows        each with description, account_code, debit_amount, credit_amount
     * @param array $payoutCodes account codes that mean "money handed back"
     * @return array {deposit_amount, deduction_total, retained_amount, net_refund, has_deductions, lines[]}
     */
    public static function build(array $rows, array $payoutCodes)
    {
        $payout = array_map('strval', $payoutCodes);

        $depositAmount = 0.0;
        $deductionTotal = 0.0;
        $retainedAmount = 0.0;
        $netRefund = 0.0;

        $deductionLines = [];
        $retainedLines = [];

        foreach ($rows as $row) {
            $debit  = isset($row['debit_amount']) ? (float) $row['debit_amount'] : 0.0;
            $credit = isset($row['credit_amount']) ? (float) $row['credit_amount'] : 0.0;
            $code   = isset($row['account_code']) ? (string) $row['account_code'] : '';
            $desc   = isset($row['description']) ? trim((string) $row['description']) : '';

            if ($debit > 0) {
                $depositAmount += $debit;
                continue;
            }

            if ($credit <= 0) {
                continue;
            }

            if (in_array($code, $payout, true)) {
                $netRefund += $credit;
                continue;
            }

            if ($code === self::DEPOSIT_ACCOUNT) {
                $retainedAmount += $credit;
                $retainedLines[] = [
                    'description'  => $desc !== '' ? $desc : 'Deposit balance retained',
                    'account_code' => $code,
                    'amount'       => round($credit, self::DECIMALS),
                    'line_type'    => 'retained',
                ];
                continue;
            }

            $deductionTotal += $credit;
            $deductionLines[] = [
                'description'  => $desc !== '' ? $desc : 'Deduction',
                'account_code' => $code,
                'amount'       => round($credit, self::DECIMALS),
                'line_type'    => 'deduction',
            ];
        }

        // deductions first, then any retained balance
        $lines = array_merge($deductionLines, $retainedLines);
        foreach ($lines as $i => $line) {
            $lines[$i]['line_order'] = $i + 1;
        }

        return [
            'deposit_amount'  => round($depositAmount, self::DECIMALS),
            'deduction_total' => round($deductionTotal, self::DECIMALS),
            'retained_amount' => round($retainedAmount, self::DECIMALS),
            'net_refund'      => round($netRefund, self::DECIMALS),
            'has_deductions'  => count($lines) > 0,
            'lines'           => $lines,
        ];
    }

    /**
     * Parse the configured payout account list ("12601,22461,...") into codes.
     *
     * @param string|null $setting
     * @return array
     */
    public static function payoutCodesFromSetting($setting)
    {
        $codes = array_filter(array_map('trim', explode(',', (string) $setting)), function ($c) {
            return $c !== '';
        });

        return array_values($codes);
    }
}
