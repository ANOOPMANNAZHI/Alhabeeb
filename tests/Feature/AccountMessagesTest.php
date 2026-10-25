<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\AccountMessages;
use Modules\BackOffice\Services\SystemMessenger;

class AccountMessagesTest extends TestCase
{
    use DatabaseTransactions;

    public $sms = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        MessageTemplate::whereIn('key', ['employee_welcome', 'employee_password_reset', 'password_forgot'])->update(['is_active' => true]);
    }

    protected function tearDown(): void
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function staff($email)
    {
        $user = User::whereHas('employee')->orderBy('id')->skip(1)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => $email]);
        return User::find($user->id);
    }

    public function test_random_password_is_long_and_not_username_based()
    {
        $a = AccountMessages::randomPassword();
        $this->assertSame(40, strlen($a));
        $this->assertNotSame($a, AccountMessages::randomPassword());
    }

    public function test_welcome_sends_a_working_link_and_hides_it_in_the_log()
    {
        $user = $this->staff('new.staff@example.com');
        $r = AccountMessages::welcome($user);
        $this->assertSame('sent', $r['email']);

        $link = null;
        Mail::assertSent(TemplatedMail::class, function ($m) use (&$link, $user) {
            if (!$m->hasTo('new.staff@example.com') || $m->subject !== 'Your PLMS account is ready') {
                return false;
            }
            preg_match('#href="([^"]*/password/reset/[^"]+)"#', $m->bodyHtml, $x);
            $link = isset($x[1]) ? html_entity_decode($x[1]) : null;
            return $link && strpos($m->bodyHtml, $user->username) !== false;
        });
        preg_match('#/password/reset/([^?"]+)#', $link, $t);
        $this->assertTrue(Password::broker()->tokenExists($user, $t[1]), 'token in the link is valid');
        $this->assertStringContainsString('email=new.staff%40example.com', $link);

        $log = MessageLog::where('template_key', 'employee_welcome')->latest('id')->first();
        $this->assertStringNotContainsString($t[1], $log->body);
        $this->assertStringContainsString('[link hidden]', $log->body);
        $this->assertSame([], $this->sms);
    }

    public function test_welcome_without_email_creates_no_token_and_reports_it()
    {
        $user = $this->staff('');
        $before = DB::table('password_resets')->count();
        $r = AccountMessages::welcome($user);
        $this->assertSame('skipped_no_address', $r['email']);
        $this->assertSame($before, DB::table('password_resets')->count());
        Mail::assertNothingSent();
    }

    public function test_password_reset_uses_its_own_template()
    {
        $user = $this->staff('reset.me@example.com');
        $this->assertSame('sent', AccountMessages::passwordReset($user)['email']);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('reset.me@example.com') && $m->subject === 'Set a new PLMS password';
        });
    }

    public function test_forgot_uses_the_given_token()
    {
        $user = $this->staff('forgot@example.com');
        AccountMessages::forgot($user, 'tok-123');
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('forgot@example.com') && $m->subject === 'Reset your PLMS password'
                && strpos($m->bodyHtml, '/password/reset/tok-123') !== false;
        });
        $log = MessageLog::where('template_key', 'password_forgot')->latest('id')->first();
        $this->assertStringNotContainsString('tok-123', $log->body);
    }

    public function test_switched_off_template_sends_nothing()
    {
        MessageTemplate::where('key', 'employee_password_reset')->update(['is_active' => false]);
        $user = $this->staff('off@example.com');
        $before = DB::table('password_resets')->count();
        $this->assertSame('skipped_off', AccountMessages::passwordReset($user)['email']);
        Mail::assertNothingSent();
        $this->assertSame($before, DB::table('password_resets')->count());
    }

    public function test_duplicate_email_creates_no_token_and_sends_nothing()
    {
        $user = $this->staff('dup.staff@example.com');
        $other = User::where('id', '<>', $user->id)->orderBy('id')->first();
        DB::table('users')->where('id', $other->id)->update(['email' => ' Dup.Staff@Example.com ']);
        $before = DB::table('password_resets')->count();
        $logs = MessageLog::count();

        $this->assertSame(['email' => AccountMessages::DUPLICATE_EMAIL, 'sms' => null], AccountMessages::welcome($user));
        $this->assertSame('duplicate_email', AccountMessages::passwordReset($user)['email']);
        $this->assertSame('duplicate_email', AccountMessages::forgot($user, 'tok-dup')['email']);

        Mail::assertNothingSent();
        $this->assertSame([], $this->sms);
        $this->assertSame($before, DB::table('password_resets')->count());
        $this->assertSame($logs, MessageLog::count());
    }
}
