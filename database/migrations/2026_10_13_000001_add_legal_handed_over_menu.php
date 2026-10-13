<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/**
 * Legal -> Handed-over Cases (cases PLM handed to the landlord, the government or MERA) and the two
 * new legal message templates. Run after the code is uploaded: the sidebar calls route() on every
 * menu row.
 */
class AddLegalHandedOverMenu extends Migration
{
    const ROUTE = 'handedOverLegalCases';
    const PERMISSION = 'handed_over_cases_list';
    const ROLES = ['super_admin', 'md', 'backoffice_manager', 'are', 'legal_advisor'];
    const TEMPLATES = ['legal_case_handed_over', 'legal_lawyer_rejected'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::TEMPLATES);

        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $parent = DB::table('menu')->where('menu_name', 'Legal')->where('menutype', 1)->first();
        if (!$parent) {
            throw new \RuntimeException('Menu "Legal" not found.');
        }
        $now = now();
        $menuId = DB::table('menu')->insertGetId([
            'menu_name' => 'Handed-over Cases', 'menu_icon' => 'fa-share-square-o', 'route_name' => self::ROUTE,
            'url_key' => self::ROUTE, 'parent_menu' => $parent->id, 'menutype' => 2,
            'menu_order' => DB::table('menu')->where('parent_menu', $parent->id)->max('menu_order') + 1,
            'status' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $pid = DB::table('permissions')->insertGetId(['name' => self::PERMISSION, 'guard_name' => 'web', 'menu_id' => $menuId,
            'created_at' => $now, 'updated_at' => $now]);
        foreach (DB::table('roles')->whereIn('name', self::ROLES)->pluck('id') as $roleId) {
            DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roleId]);
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (DB::table('permissions')->where('name', self::PERMISSION)->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        DB::table('message_templates')->whereIn('key', self::TEMPLATES)->delete();
        app('cache')->forget('spatie.permission.cache');
    }
}
