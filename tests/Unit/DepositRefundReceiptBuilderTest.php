<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\DepositRefundReceiptBuilder;

/**
 * The builder turns a deposit refund's accounting rows into the figures a
 * customer receipt shows. Pure: no models, no database.
 */
class DepositRefundReceiptBuilderTest extends TestCase
{
    /** Real shape from refund AJV2600537. */
    private function rows()
    {
        return [
            ['description' => 'TENANTS DEPOSIT',     'account_code' => '22311', 'debit_amount' => 700.0, 'credit_amount' => 0.0],
            ['description' => 'ELECTRICITY & WATER', 'account_code' => '41105', 'debit_amount' => 0.0,   'credit_amount' => 100.25],
            ['description' => 'OMAN ARAB BANK - 500','account_code' => '22461', 'debit_amount' => 0.0,   'credit_amount' => 599.75],
        ];
    }

    private function payout()
    {
        return ['12601', '22461', '22401', '22301', '12651'];
    }

    public function test_splits_deposit_deduction_and_payout()
    {
        $r = DepositRefundReceiptBuilder::build($this->rows(), $this->payout());

        $this->assertSame(700.0, $r['deposit_amount']);
        $this->assertSame(100.25, $r['deduction_total']);
        $this->assertSame(0.0, $r['retained_amount']);
        $this->assertSame(599.75, $r['net_refund']);
        $this->assertTrue($r['has_deductions']);
    }

    public function test_deduction_lines_carry_description_and_account()
    {
        $r = DepositRefundReceiptBuilder::build($this->rows(), $this->payout());

        $this->assertCount(1, $r['lines']);
        $this->assertSame('ELECTRICITY & WATER', $r['lines'][0]['description']);
        $this->assertSame('41105', $r['lines'][0]['account_code']);
        $this->assertSame(100.25, $r['lines'][0]['amount']);
        $this->assertSame('deduction', $r['lines'][0]['line_type']);
        $this->assertSame(1, $r['lines'][0]['line_order']);
    }

    public function test_refund_with_no_deduction_is_flagged_so_no_receipt_is_issued()
    {
        $rows = [
            ['description' => 'TENANTS DEPOSIT', 'account_code' => '22311', 'debit_amount' => 200.0, 'credit_amount' => 0.0],
            ['description' => 'PETTY CASH',      'account_code' => '12601', 'debit_amount' => 0.0,   'credit_amount' => 200.0],
        ];

        $r = DepositRefundReceiptBuilder::build($rows, $this->payout());

        $this->assertFalse($r['has_deductions']);
        $this->assertSame([], $r['lines']);
        $this->assertSame(200.0, $r['net_refund']);
        $this->assertSame(0.0, $r['deduction_total']);
    }

    public function test_credit_to_the_deposit_account_is_retained_not_a_deduction()
    {
        $rows = [
            ['description' => 'TENANTS DEPOSIT',     'account_code' => '22311', 'debit_amount' => 500.0, 'credit_amount' => 0.0],
            ['description' => 'TENANTS DEPOSIT',     'account_code' => '22311', 'debit_amount' => 0.0,   'credit_amount' => 150.0],
            ['description' => 'ELECTRICITY & WATER', 'account_code' => '41105', 'debit_amount' => 0.0,   'credit_amount' => 50.0],
            ['description' => 'PETTY CASH',          'account_code' => '12601', 'debit_amount' => 0.0,   'credit_amount' => 300.0],
        ];

        $r = DepositRefundReceiptBuilder::build($rows, $this->payout());

        $this->assertSame(150.0, $r['retained_amount']);
        $this->assertSame(50.0, $r['deduction_total']);
        $this->assertSame(300.0, $r['net_refund']);
        $this->assertTrue($r['has_deductions']);

        $types = array_column($r['lines'], 'line_type');
        $this->assertSame(['deduction', 'retained'], $types, 'deductions print first, retained balance last');
    }

    public function test_several_deductions_are_ordered_and_totalled()
    {
        $rows = [
            ['description' => 'TENANTS DEPOSIT',     'account_code' => '22311', 'debit_amount' => 1000.0, 'credit_amount' => 0.0],
            ['description' => 'ELECTRICITY & WATER', 'account_code' => '41105', 'debit_amount' => 0.0,    'credit_amount' => 120.5],
            ['description' => 'MUNICIPAL TAX',       'account_code' => '41102', 'debit_amount' => 0.0,    'credit_amount' => 30.25],
            ['description' => 'RENT RECEIVABLE',     'account_code' => '12211', 'debit_amount' => 0.0,    'credit_amount' => 49.25],
            ['description' => 'AHLI BANK',           'account_code' => '22401', 'debit_amount' => 0.0,    'credit_amount' => 800.0],
        ];

        $r = DepositRefundReceiptBuilder::build($rows, $this->payout());

        $this->assertCount(3, $r['lines']);
        $this->assertSame(200.0, $r['deduction_total']);
        $this->assertSame(800.0, $r['net_refund']);
        $this->assertSame([1, 2, 3], array_column($r['lines'], 'line_order'));
    }

    public function test_amounts_are_rounded_to_three_decimals()
    {
        $rows = [
            ['description' => 'TENANTS DEPOSIT', 'account_code' => '22311', 'debit_amount' => 100.0,    'credit_amount' => 0.0],
            ['description' => 'ELECTRICITY',     'account_code' => '41105', 'debit_amount' => 0.0,      'credit_amount' => 33.3333333],
            ['description' => 'PETTY CASH',      'account_code' => '12601', 'debit_amount' => 0.0,      'credit_amount' => 66.6666667],
        ];

        $r = DepositRefundReceiptBuilder::build($rows, $this->payout());

        $this->assertSame(33.333, $r['deduction_total']);
        $this->assertSame(66.667, $r['net_refund']);
    }

    public function test_payout_account_list_is_compared_as_text_not_numbers()
    {
        $rows = [
            ['description' => 'TENANTS DEPOSIT', 'account_code' => 22311, 'debit_amount' => 100.0, 'credit_amount' => 0.0],
            ['description' => 'PETTY CASH',      'account_code' => 12601, 'debit_amount' => 0.0,   'credit_amount' => 100.0],
        ];

        $r = DepositRefundReceiptBuilder::build($rows, $this->payout());

        $this->assertFalse($r['has_deductions'], 'an integer account code must still match the payout list');
        $this->assertSame(100.0, $r['net_refund']);
    }

    public function test_empty_rows_produce_an_empty_receipt()
    {
        $r = DepositRefundReceiptBuilder::build([], $this->payout());

        $this->assertFalse($r['has_deductions']);
        $this->assertSame(0.0, $r['deposit_amount']);
        $this->assertSame(0.0, $r['net_refund']);
    }
}
