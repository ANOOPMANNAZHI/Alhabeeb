<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** Rent revisal templates (insert-only) and the rent_revisal permission. Run after the code is uploaded. */
class SeedRentRevisalTemplatesAndPermission extends Migration
{
    const KEYS = ['rent_revisal_due_are', 'rent_revision_tenant'];
    const ROLES = ['are', 'are_team_lead', 'backoffice_manager', 'super_admin'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
        if (!DB::table('permissions')->where('name', 'rent_revisal')->exists()) {
            // permissions.menu_id is NOT NULL: hang it on the Due for Renewal menu row.
            $menuId = DB::table('menu')->where('route_name', 'tenantRenewal.index')->value('id');
            $pid = DB::table('permissions')->insertGetId(['name' => 'rent_revisal', 'guard_name' => 'web', 'menu_id' => $menuId,
                'created_at' => now(), 'updated_at' => now()]);
            foreach (DB::table('roles')->whereIn('name', self::ROLES)->pluck('id') as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roleId]);
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
        foreach (DB::table('permissions')->where('name', 'rent_revisal')->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
}
