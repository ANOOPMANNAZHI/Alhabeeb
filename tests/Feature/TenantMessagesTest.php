<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\Key;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\BackOffice\Services\TenantMessages;
use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\Tenant;
use Modules\Sales\Entities\TenantContract;

class TenantMessagesTest extends TestCase
{
    use DatabaseTransactions;

    public $sms = [];

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        MessageTemplate::where('kind', 'system')->where('key', '<>', 'termination_details')->update(['is_active' => true]);
        $this->actingAs(User::find(1));
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function contract($withRenewalForm = false)
    {
        $q = TenantContract::whereNotNull('unit_id')->whereNotNull('tenant_contract_valid_to_date')->whereHas('tenant')->whereHas('building');
        if ($withRenewalForm) {
            $q->whereHas('tenantRenewalFormLatest');
        }
        $c = $q->latest('id')->first();
        if (!$c) {
            $this->markTestSkipped('No suitable contract');
        }
        $c->tenant->update(['tenant_contact_email' => 'contact@example.com', 'tenant_personal_email' => 'personal@example.com', 'tenant_contact_no' => '99112233']);
        return TenantContract::find($c->id);
    }

    // --- key accepted ---

    public function test_key_accepted_from_tenant()
    {
        $c = $this->contract();
        $key = Key::create(['tenant_id' => $c->tenant_id, 'building_id' => $c->building_id, 'unit_id' => $c->unit_id, 'created_by' => 1]);
        $r = TenantMessages::keyAccepted($key);
        $this->assertSame('sent', $r['email']);
        $building = $c->building->building_name;
        Mail::assertSent(TemplatedMail::class, function ($m) use ($building) {
            return $m->hasTo('contact@example.com') && strpos($m->subject, $building) !== false;
        });
    }

    public function test_key_accepted_from_landlord()
    {
        $c = $this->contract();
        $vendor = Vendor::latest('id')->first();
        $vendor->update(['vendor_contact_email' => 'landlord@example.com']);
        $key = Key::create(['landlord_id' => $vendor->id, 'building_id' => $c->building_id, 'unit_id' => $c->unit_id, 'created_by' => 1]);
        TenantMessages::keyAccepted($key);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($vendor) {
            return $m->hasTo('landlord@example.com') && strpos($m->bodyHtml, e($vendor->vendor_name)) !== false;
        });
    }

    // --- inspection / termination details ---

    private function termination($withChecklist = true)
    {
        $q = \Modules\BackOffice\Entities\Termination::whereHas('tenantContract', function ($c) { $c->whereHas('tenant'); });
        $ids = \DB::table('termination_checklists')->whereNotNull('work_id')->distinct()->pluck('termination_contract_id')->all();
        $withChecklist ? $q->whereIn('contract_id', $ids) : $q->whereNotIn('contract_id', $ids);
        $t = $q->latest('id')->first();
        if (!$t) {
            $this->markTestSkipped('No suitable termination');
        }
        TenantContract::find($t->contract_id)->tenant->update(['tenant_contact_email' => 'contact@example.com', 'tenant_personal_email' => 'personal@example.com']);
        return $t;
    }

    public function test_inspection_email_carries_the_tables()
    {
        $t = $this->termination();
        $contract = TenantContract::find($t->contract_id);
        $status = TenantMessages::inspection($t, $contract);
        $this->assertSame('sent', $status);
        $log = MessageLog::where('template_key', 'inspection_details')->where('related_id', $t->id)->first();
        $this->assertContains('<table', $log->body);
        $this->assertContains($contract->tenant_contract_no, $log->body);
        $this->assertNotContains('{inspection_details}', $log->body);
        Mail::assertSent(TemplatedMail::class, function ($m) { return $m->hasTo('contact@example.com'); });
    }

    public function test_inspection_without_checklist_rows_still_sends()
    {
        $t = $this->termination(false);
        $this->assertSame('sent', TenantMessages::inspection($t, TenantContract::find($t->contract_id)));
    }

    public function test_termination_details_off_by_default_then_named_when_on()
    {
        $t = $this->termination();
        $contract = TenantContract::find($t->contract_id);
        MessageTemplate::where('key', 'termination_details')->update(['is_active' => false]);
        $this->assertSame('skipped_off', TenantMessages::terminationDetails($t, $contract));
        MessageTemplate::where('key', 'termination_details')->update(['is_active' => true]);
        $this->assertSame('sent', TenantMessages::terminationDetails($t, $contract));
        $log = MessageLog::where('template_key', 'termination_details')->where('status', 'sent')->latest('id')->first();
        $this->assertContains('Dear ' . e($contract->tenant->tenant_name), $log->body);
    }

    public function test_inspection_route_redirects_with_success()
    {
        $t = $this->termination();
        $this->get(route('inspectionSendMail', $t->id))->assertRedirect(route('handoverAssigned'))->assertSessionHas('success');
    }

    // --- renewal form ---

    public function test_renewal_button_emails_the_pdf_with_cc_and_counts_it()
    {
        $c = $this->contract(true);
        $before = (int) $c->email_count;
        // Local libpng warns about an sRGB profile in a PDF image (dompdf); that is machine-specific, not what is tested here.
        $level = error_reporting(E_ALL & ~E_WARNING);
        $response = $this->get(route('generatePdfEmail', $c->id));
        error_reporting($level);
        $response->assertRedirect(route('tenantRenewal.index'));
        Mail::assertSent(TemplatedMail::class, function ($m) {
            $m->build();
            return $m->hasTo('contact@example.com') && $m->hasCc(User::find(1)->email) && count($m->rawAttachments) === 1
                && $m->rawAttachments[0]['options']['mime'] === 'application/pdf';
        });
        $this->assertSame($before + 1, (int) TenantContract::find($c->id)->email_count);
    }

    public function test_renewal_button_downloads_the_pdf_when_template_is_off()
    {
        $c = $this->contract(true);
        MessageTemplate::where('key', 'renewal_form')->update(['is_active' => false]);
        $before = (int) $c->email_count;
        // Local libpng warns about an sRGB profile in a PDF image (dompdf); that is machine-specific, not what is tested here.
        $level = error_reporting(E_ALL & ~E_WARNING);
        $response = $this->get(route('generatePdfEmail', $c->id));
        error_reporting($level);
        $this->assertContains('application/pdf', $response->headers->get('content-type'));
        Mail::assertNothingSent();
        $this->assertSame($before, (int) TenantContract::find($c->id)->email_count);
    }
}
