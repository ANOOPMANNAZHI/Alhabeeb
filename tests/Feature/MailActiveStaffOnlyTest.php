<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\Bulk\RecipientFinder;

/** Mail -> Compose (CC lookup) and Send (staff audience) list only active staff (user, 07/10/2026). */
class MailActiveStaffOnlyTest extends TestCase
{
    use DatabaseTransactions;

    /** A staff login + employee record; returns the user id. */
    private function staff($name, $loginActive, $employeeActive)
    {
        $empId = DB::table('employees')->insertGetId(['employee_code' => 'TMA' . substr(md5($name), 0, 5), 'employee_name' => $name,
            'employee_contact_no' => '90000000', 'employee_contact_address' => 'Test', 'employee_dob' => '1990-01-01',
            'designation_id' => DB::table('employees')->value('designation_id'), 'employee_status' => $employeeActive ? 1 : 0,
            'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return DB::table('users')->insertGetId(['username' => $name, 'email' => $name . '@mailtest.example', 'password' => bcrypt('x'),
            'user_type' => 'employee', 'user_type_id' => $empId, 'user_type_status' => $loginActive ? 1 : 0, 'default_sidebar' => 0,
            'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function people()
    {
        return [
            'active'       => $this->staff('mailtest_active', true, true),
            'login_off'    => $this->staff('mailtest_loginoff', false, false),
            'employee_off' => $this->staff('mailtest_empoff', true, false),
        ];
    }

    public function test_send_staff_audience_has_only_active_staff()
    {
        $ids = $this->people();
        $keys = array_keys(RecipientFinder::people('staff', []));

        $this->assertContains('staff:' . $ids['active'], $keys);
        $this->assertNotContains('staff:' . $ids['login_off'], $keys);
        $this->assertNotContains('staff:' . $ids['employee_off'], $keys, 'active login but inactive employee');
        $this->assertContains('staff:1', $keys, 'admin login (no employee record) stays');
    }

    public function test_compose_cc_lookup_has_only_active_staff()
    {
        $ids = $this->people();
        $this->actingAs(User::find(1));

        $found = array_column($this->get(route('emailCcAutocomplete', ['search' => 'mailtest']))->assertOk()->json(), 'value');

        $this->assertContains($ids['active'], $found);
        $this->assertNotContains($ids['login_off'], $found);
        $this->assertNotContains($ids['employee_off'], $found);
    }
}
