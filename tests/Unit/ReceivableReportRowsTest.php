<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\ReceivableReportRows as R;

class ReceivableReportRowsTest extends TestCase
{
    private function due($id, $bld, $unit, $balance, array $o = [])
    {
        return (object) array_merge([
            'id' => $id, 'contract_no' => 'TAG' . $id, 'tenant_name' => 'Tenant ' . $id, 'building_name' => $bld, 'building_no' => 'B1',
            'unit_code' => $unit, 'eff' => '2026-01-16', 'vto' => '2027-01-15', 'rent' => 200.0, 'value' => '2400.000', 'pdc' => 'Full',
            'term_date' => null, 'paid_till' => '2026-06-15', 'last_received' => '2026-06-01 10:00:00',
            'ledger' => ['due' => 1800.0, 'received' => 1800.0 - $balance, 'balance' => $balance, 'not_paid_from' => null, 'lines' => []],
        ], $o);
    }

    private function detail($paymode = 'Monthly')
    {
        return (object) ['paymentmode' => $paymode, 'contact_person' => 'Ali', 'contact_no' => '9999', 'management_type' => 'Normal',
            'pdc_closed' => 2, 'employee_name' => 'ARE One'];
    }

    public function test_rows_carry_the_report_fields_from_the_ledger_and_details()
    {
        $rows = R::build([7 => $this->due(7, 'A Bldg', '1', 400.0)], [7 => $this->detail()]);
        $r = $rows[0];
        $this->assertSame('TAG7', $r->contract_no);
        $this->assertSame('A Bldg', $r->buildingname);
        $this->assertSame('2026-01-16', $r->start_date);
        $this->assertSame('2027-01-15', $r->end_date);
        $this->assertSame(1800.0, $r->totaldue);
        $this->assertSame(1400.0, $r->amount_received);
        $this->assertSame(400.0, $r->netamtdue);
        $this->assertSame(200.0, $r->rentper_month);
        $this->assertSame(2400.0, $r->contract_value);
        $this->assertSame('2026-06-15', $r->lastpaid_till);
        $this->assertSame('2026-06-01', $r->receipt_date);
        $this->assertSame('Monthly', $r->paymentmode);
        $this->assertSame('Ali', $r->contact_person);
        $this->assertSame('9999', $r->contact_no);
        $this->assertSame('Normal', $r->management_type);
        $this->assertSame(2, $r->pdc_closed);
        $this->assertSame('ARE One', $r->employee_name);
        $this->assertSame('Full', $r->pdc);
        $this->assertNull($r->termination_date);
    }

    public function test_only_owing_contracts_sorted_by_building_and_unit()
    {
        $dues = [
            1 => $this->due(1, 'B Bldg', '2', 50.0),
            2 => $this->due(2, 'A Bldg', '10', 20.0),
            3 => $this->due(3, 'A Bldg', '2', 30.0),
            4 => $this->due(4, 'A Bldg', '1', 0.0),
        ];
        $rows = R::build($dues, []);
        $this->assertSame(['TAG3', 'TAG2', 'TAG1'], array_column($rows, 'contract_no'));
        $this->assertSame('', $rows[0]->paymentmode);   // no details row: blank, not an error
    }

    public function test_no_receipts_shows_nothing_received()
    {
        $rows = R::build([5 => $this->due(5, 'A', '1', 1800.0, ['paid_till' => null, 'last_received' => null])], []);
        $this->assertSame(0.0, $rows[0]->amount_received);
        $this->assertNull($rows[0]->lastpaid_till);
        $this->assertNull($rows[0]->receipt_date);
    }
}
