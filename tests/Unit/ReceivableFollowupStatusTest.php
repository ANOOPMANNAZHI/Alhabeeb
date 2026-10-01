<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Entities\ReceivableFollowup as F;

class ReceivableFollowupStatusTest extends TestCase
{
    public function test_no_promise_has_no_status()
    {
        $this->assertNull(F::promiseStatus(null, '2026-09-01 10:00:00', ['2026-09-02'], '2026-10-01'));
    }

    public function test_receipt_between_followup_and_promise_is_kept()
    {
        $this->assertSame(F::KEPT, F::promiseStatus('2026-09-10', '2026-09-01 10:00:00', ['2026-09-10'], '2026-10-01'));
        // same day as the follow-up counts
        $this->assertSame(F::KEPT, F::promiseStatus('2026-09-10', '2026-09-01 10:00:00', ['2026-09-01'], '2026-10-01'));
    }

    public function test_receipt_before_followup_or_after_promise_does_not_count()
    {
        $this->assertSame(F::BROKEN, F::promiseStatus('2026-09-10', '2026-09-01 10:00:00', ['2026-08-31', '2026-09-11'], '2026-10-01'));
    }

    public function test_future_promise_without_receipt_is_upcoming()
    {
        $this->assertSame(F::UPCOMING, F::promiseStatus('2026-10-05', '2026-10-01 09:00:00', [], '2026-10-01'));
        // promise due today is still upcoming
        $this->assertSame(F::UPCOMING, F::promiseStatus('2026-10-01', '2026-09-28 09:00:00', [], '2026-10-01'));
    }
}
