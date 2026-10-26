<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\SystemMessenger;

class ForgotPasswordTemplateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'password_forgot')->update(['is_active' => true]);
    }

    protected function tearDown(): void
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    public function test_forgot_password_sends_the_template_not_the_laravel_notification()
    {
        $user = User::whereHas('employee')->orderBy('id')->skip(3)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => 'forgot.me@example.com']);

        $this->post(route('password.email'), ['email' => 'forgot.me@example.com'])->assertSessionHas('status');

        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('forgot.me@example.com') && $m->subject === 'Reset your PLMS password'
                && strpos($m->bodyHtml, '/password/reset/') !== false;
        });
        Notification::assertNothingSent();
        $this->assertSame(1, DB::table('password_resets')->where('email', 'forgot.me@example.com')->count());
        $log = MessageLog::where('template_key', 'password_forgot')->latest('id')->first();
        $this->assertStringContainsString('[link hidden]', $log->body);
    }

    public function test_unknown_email_sends_nothing()
    {
        $this->post(route('password.email'), ['email' => 'nobody-here@example.com'])->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }

    private function userWithEmail($email, $active)
    {
        $user = User::whereHas('employee')->orderBy('id')->skip(3)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => $email, 'user_type_status' => $active]);
        return User::find($user->id);
    }

    public function test_inactive_user_gets_no_forgot_link()
    {
        $this->userWithEmail('inactive.me@example.com', false);
        $this->post(route('password.email'), ['email' => 'inactive.me@example.com'])->assertSessionHasErrors('email');
        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('password_resets')->where('email', 'inactive.me@example.com')->count());
    }

    public function test_inactive_user_cannot_reset_with_a_valid_token()
    {
        $user = $this->userWithEmail('inactive.reset@example.com', false);
        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
        $hash = $user->password;
        $this->post(route('password.update'), ['token' => $token, 'email' => 'inactive.reset@example.com',
            'password' => 'NewSecret#2026', 'password_confirmation' => 'NewSecret#2026']);
        $this->assertSame($hash, DB::table('users')->where('id', $user->id)->value('password'));
        $this->assertGuest();
    }

    public function test_active_user_can_reset_with_a_valid_token()
    {
        $user = $this->userWithEmail('active.reset@example.com', true);
        $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
        $hash = $user->password;
        $this->post(route('password.update'), ['token' => $token, 'email' => 'active.reset@example.com',
            'password' => 'NewSecret#2026', 'password_confirmation' => 'NewSecret#2026']);
        $this->assertNotSame($hash, DB::table('users')->where('id', $user->id)->value('password'));
    }

    public function test_reset_page_prefills_the_email_from_the_link()
    {
        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'x.y@example.com']))
            ->assertStatus(200)
            ->assertSee('value="x.y@example.com"', false);
    }
}
