<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\SystemMessenger;

class EmployeeAccountLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'employee_password_reset')->update(['is_active' => true]);
        $this->actingAs(User::find(1));
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function employeeUser($email)
    {
        $user = User::where('user_type', 'employee')->whereHas('employee')->orderBy('id')->skip(2)->first();
        DB::table('users')->where('id', $user->id)->update(['email' => $email]);
        return User::find($user->id);
    }

    public function test_admin_reset_sends_link_and_keeps_the_password()
    {
        $user = $this->employeeUser('staff.reset@example.com');
        $hashBefore = $user->password;
        $this->get(route('employee.resetPassword', $user->user_type_id))
            ->assertRedirect(route('employee.index'))
            ->assertSessionHas('success');
        $this->assertSame($hashBefore, User::find($user->id)->password, 'current password still works');
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('staff.reset@example.com') && $m->subject === 'Set a new PLMS password'
                && strpos($m->bodyHtml, '/password/reset/') !== false;
        });
    }

    public function test_admin_reset_without_email_explains_why()
    {
        $user = $this->employeeUser('');
        $this->get(route('employee.resetPassword', $user->user_type_id))
            ->assertRedirect(route('employee.index'))
            ->assertSessionHas('error');
        Mail::assertNothingSent();
    }

    public function test_admin_reset_with_template_off_explains_why()
    {
        MessageTemplate::where('key', 'employee_password_reset')->update(['is_active' => false]);
        $user = $this->employeeUser('off@example.com');
        $this->get(route('employee.resetPassword', $user->user_type_id))->assertSessionHas('error');
        Mail::assertNothingSent();
    }

    public function test_admin_reset_with_duplicate_email_explains_why()
    {
        $user = $this->employeeUser('shared@example.com');
        $other = User::where('id', '<>', $user->id)->orderBy('id')->first();
        DB::table('users')->where('id', $other->id)->update(['email' => 'SHARED@example.com']);
        $this->get(route('employee.resetPassword', $user->user_type_id))
            ->assertSessionHas('error', 'No link sent: another login user has the same email address. Give this employee a unique email and use Reset Password again.');
        Mail::assertNothingSent();
    }

    public function test_old_employee_mail_classes_are_gone()
    {
        $this->assertFileNotExists(base_path('Modules/Masters/Emails/EmployeeLoginEmail.php'));
        $this->assertFileNotExists(base_path('Modules/Masters/Emails/EmployeeResetEmail.php'));
    }
}
