<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Events\ChequeBounceAre;
use Modules\BackOffice\Services\PdcBounceNotifier;
use Modules\BackOffice\Services\SystemMessenger;

class PdcBounceNotifierTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        Event::fake([ChequeBounceAre::class]);
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => true, 'sms_enabled' => false]);
        MessageTemplate::where('key', 'pdc_bounce_tenant')->update(['is_active' => false]);
        \Auth::loginUsingId(1);
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    /** A PDC whose building has at least two distinct ARE users with an email, plus those users' ids. */
    private function pdcWithTwoAres()
    {
        $building = DB::selectOne("select pb.building_id from preferred_buildings pb
            join are_buildings ab on ab.id = pb.are_building_id join users u on u.id = ab.user_id
            where coalesce(u.email, '') <> '' group by pb.building_id having count(distinct u.id) >= 2 limit 1");
        if (!$building) {
            $this->markTestSkipped('No building with two AREs locally');
        }
        $pdc = Pdc::whereHas('tenantContractInfo', function ($q) use ($building) { $q->where('building_id', $building->building_id); })
            ->latest('id')->first();
        if (!$pdc) {
            $this->markTestSkipped('No PDC in that building');
        }
        $ids = DB::table('preferred_buildings as pb')->join('are_buildings as ab', 'ab.id', '=', 'pb.are_building_id')
            ->join('users as u', 'u.id', '=', 'ab.user_id')->where('pb.building_id', $building->building_id)
            ->distinct()->orderBy('u.id')->pluck('u.id')->all();
        $emails = DB::table('users')->whereIn('id', $ids)->where('email', '<>', '')->whereNotNull('email')->pluck('email')->all();
        return [$pdc, $ids, $emails, $building->building_id];
    }

    public function test_every_are_gets_one_email_and_the_in_app_alert()
    {
        list($pdc, $ids, $emails, $buildingId) = $this->pdcWithTwoAres();
        // duplicate assignment of an already-counted ARE must not produce a second email
        $dupe = DB::table('preferred_buildings')->where('building_id', $buildingId)->first();
        $row = (array) $dupe;
        unset($row['id']);
        DB::table('preferred_buildings')->insert($row);

        $result = PdcBounceNotifier::notify($pdc, 'pdc/1/edit');

        $this->assertSame(count($ids), $result['ares']);
        Mail::assertSent(TemplatedMail::class, count(array_unique($emails)));
        foreach ($emails as $email) {
            Mail::assertSent(TemplatedMail::class, function ($m) use ($email) { return $m->hasTo($email); });
        }
        Event::assertDispatched(ChequeBounceAre::class, function ($e) use ($ids) {
            $got = $e->users->pluck('id')->sort()->values()->all();
            return $got == $ids;
        });
        $this->assertSame(count(array_unique($emails)), MessageLog::where('template_key', 'pdc_bounce_are')->where('status', 'sent')
            ->where('related_type', 'pdc')->where('related_id', $pdc->id)->count());
        $this->assertSame(1, MessageLog::where('template_key', 'pdc_bounce_tenant')->where('status', 'skipped_off')->where('related_id', $pdc->id)->count());
    }

    public function test_empty_remarks_render_as_dash()
    {
        list($pdc) = $this->pdcWithTwoAres();
        $pdc->bounce_remark = null;
        PdcBounceNotifier::notify($pdc, 'pdc/1/edit');
        $log = MessageLog::where('template_key', 'pdc_bounce_are')->where('related_id', $pdc->id)->first();
        $this->assertContains('Remarks: -', $log->body);
        $this->assertNotContains('{', $log->body);
    }

    public function test_mail_failure_does_not_throw()
    {
        list($pdc) = $this->pdcWithTwoAres();
        Mail::shouldReceive('to')->andThrow(new \Exception('boom'));
        PdcBounceNotifier::notify($pdc, 'pdc/1/edit');
        $this->assertGreaterThan(0, MessageLog::where('template_key', 'pdc_bounce_are')->where('status', 'failed')->where('related_id', $pdc->id)->count());
    }

    public function test_bounce_endpoint_saves_and_notifies()
    {
        list($pdc, $ids, $emails) = $this->pdcWithTwoAres();
        $open = Pdc::whereHas('tenantContractInfo', function ($q) use ($pdc) { $q->where('building_id', $pdc->tenantContractInfo->building_id); })
            ->where(function ($q) { $q->whereNull('pdc_cancel_reason')->orWhere('pdc_cancel_reason', 0); })->latest('id')->first();
        if (!$open) {
            $this->markTestSkipped('No unbounced PDC in that building');
        }
        // Merge decision (2026-09-30): the bounce endpoint keeps master's own sending (ARE email,
        // Cheque Bounce Copy Emails, tenant SMS via sendSms) instead of PdcBounceNotifier.
        // Clear the tenant's mobile so the real SMS gateway is never called from a test.
        DB::table('tenant')->where('id', $open->tenantContractInfo->tenant_id)->update(['tenant_contact_no' => '']);

        $response = $this->post(route('tenantAjaxPdcBounceSearch'), ['id' => $open->id, 'pdc_bounce_reason' => 1, 'bounce_remark' => 'FUNDS NOT SUFFICIENT']);
        $response->assertJson(['success' => 'Data is successfully added']);
        $bounced = Pdc::find($open->id);
        $this->assertEquals(1, $bounced->pdc_cancel_reason);
        $this->assertContains('Bounced: FUNDS NOT SUFFICIENT', (string) $bounced->pdc_remark);
        Mail::assertSent(\Modules\BackOffice\Emails\ChequeBounceAreEmail::class);
        Mail::assertNotSent(TemplatedMail::class);
    }
}
