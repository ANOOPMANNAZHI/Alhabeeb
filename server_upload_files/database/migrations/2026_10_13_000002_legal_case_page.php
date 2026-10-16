<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/**
 * Legal module phase 2 (plan 2026-10-13): the case page.
 * - legal_stages.code drives the extra fields of a stage (case no / court on "filed", amount on
 *   "judgment", unit back on "eviction"); stages added in the master have no code.
 * - legal: judgment_date, eviction_ordered. legal_documents.update_id links a file to a comment / stage.
 * - Legal -> Legal Stages menu; permissions to update stages, add comments and keep the stage master.
 * Run after the code is uploaded: the sidebar calls route() on every menu row.
 */
class LegalCasePage extends Migration
{
    const CODES = [
        'Legal notice sent' => 'notice', 'Case filed' => 'filed', 'Hearing' => 'hearing', 'Judgment' => 'judgment',
        'Appeal' => 'appeal', 'Execution / enforcement' => 'execution', 'Eviction completed' => 'eviction',
        'Withdrawn - settled out of court' => 'withdrawn', 'Closed' => 'closed',
    ];
    const ROUTE = 'legal.stages';
    // permission => [menu route it hangs on, roles]
    const PERMISSIONS = [
        'legal_stages_master' => ['legal.stages', ['super_admin', 'md', 'backoffice_manager']],
        'legal_stage_update' => ['activeCases', ['super_admin', 'legal_advisor']],
        'legal_comment_add' => ['activeCases', ['super_admin', 'md', 'backoffice_manager', 'legal_advisor', 'are']],
    ];
    const TEMPLATES = ['legal_stage_changed'];

    public function up()
    {
        Schema::table('legal_stages', function (Blueprint $table) {
            $table->string('code', 20)->nullable();
        });
        foreach (self::CODES as $name => $code) {
            DB::table('legal_stages')->where('name', $name)->update(['code' => $code]);
        }
        Schema::table('legal', function (Blueprint $table) {
            $table->date('judgment_date')->nullable();
            $table->boolean('eviction_ordered')->nullable();
        });
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->integer('update_id')->nullable();
        });

        SystemTemplateDefaults::seed(self::TEMPLATES);

        if (DB::table('menu')->where('route_name', self::ROUTE)->exists()) {
            return;
        }
        $parent = DB::table('menu')->where('menu_name', 'Legal')->where('menutype', 1)->first();
        if (!$parent) {
            throw new \RuntimeException('Menu "Legal" not found.');
        }
        $now = now();
        DB::table('menu')->insert([
            'menu_name' => 'Legal Stages', 'menu_icon' => 'fa-list-ol', 'route_name' => self::ROUTE, 'url_key' => 'legal-stages',
            'parent_menu' => $parent->id, 'menutype' => 2,
            'menu_order' => DB::table('menu')->where('parent_menu', $parent->id)->max('menu_order') + 1,
            'status' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $roles = DB::table('roles')->pluck('id', 'name');
        foreach (self::PERMISSIONS as $perm => $p) {
            $menuId = DB::table('menu')->where('route_name', $p[0])->value('id');
            $pid = DB::table('permissions')->insertGetId(['name' => $perm, 'guard_name' => 'web', 'menu_id' => $menuId,
                'created_at' => $now, 'updated_at' => $now]);
            foreach ($p[1] as $r) {
                if (isset($roles[$r])) {
                    DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $roles[$r]]);
                }
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down()
    {
        foreach (DB::table('permissions')->whereIn('name', array_keys(self::PERMISSIONS))->get() as $p) {
            DB::table('role_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $p->id)->delete();
            DB::table('permissions')->where('id', $p->id)->delete();
        }
        DB::table('menu')->where('route_name', self::ROUTE)->delete();
        DB::table('message_templates')->whereIn('key', self::TEMPLATES)->delete();
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn('update_id');
        });
        Schema::table('legal', function (Blueprint $table) {
            $table->dropColumn(['judgment_date', 'eviction_ordered']);
        });
        Schema::table('legal_stages', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        app('cache')->forget('spatie.permission.cache');
    }
}
