<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\PdcBounceNotifier;

/** Grid saves re-post bounced rows; only a change to "bounced" may notify (final review I1). */
class PdcBounceNotifierRuleTest extends TestCase
{
    public function test_only_a_change_to_bounced_notifies()
    {
        $this->assertTrue(PdcBounceNotifier::isNewBounce(0, '1'));
        $this->assertTrue(PdcBounceNotifier::isNewBounce(null, 1));
        $this->assertTrue(PdcBounceNotifier::isNewBounce(2, 1));   // exchanged, then bounced
        $this->assertFalse(PdcBounceNotifier::isNewBounce(1, 1));  // already bounced, grid saved again
        $this->assertFalse(PdcBounceNotifier::isNewBounce('1', '1'));
        $this->assertFalse(PdcBounceNotifier::isNewBounce(0, 0));
        $this->assertFalse(PdcBounceNotifier::isNewBounce(0, null));
    }
}
