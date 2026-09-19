<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Two menus for the tenant onboarding (move-in checklist) feature and the
 * permissions hung off them:
 *   PLM Module -> Masters    -> "Onboarding Checklist Items"  (onboarding_items_manage)
 *   PLM Module -> Operations -> "Tenant Onboarding"           (onboarding_view, onboarding_verify)
 * Parents are resolved by exact name under PLM Module, as the other menu migrations do.
 */
return new class extends Migration
{
    const MENUS = [
        // route, name, icon, url_key, parent group, permissions => roles
        ['onboarding-items.index', 'Onboarding Checklist Items', 'fa-list-ol', 'onboardingItems', 'Masters', [
            'onboarding_items_manage' => ['super_admin', 'backoffice_manager'],
        ]],
        ['tenant-onboarding.index', 'Tenant Onboarding', 'fa-clipboard-check', 'tenantOnboarding', 'Operations', [
            'onboarding_view' => ['super_admin', 'backoffice_manager', 'backoffice_executive', 'are', 'are_team_lead'],
            'onboarding_verify' => ['super_admin', 'backoffice_manager', 'backoffice_executive'],
        ]],
    ];

    public function up(): void
    {
        $plmModule = DB::table('menu')->where('menu_name', 'PLM Module')->where('parent_menu', 0)->first();

        foreach (self::MENUS as [$route, $name, $icon, $urlKey, $group, $perms]) {
            $parent = $plmModule
                ? DB::table('menu')->where('menu_name', $group)->where('parent_menu', $plmModule->id)->where('menutype', 1)->first()
                : null;
            $parentMenuId = $parent ? $parent->id : ($plmModule ? $plmModule->id : 0);

            $menuId = DB::table('menu')->where('route_name', $route)->value('id')
                ?? DB::table('menu')->insertGetId([
                    'menu_name'   => $name,
                    'menu_icon'   => $icon,
                    'route_name'  => $route,
                    'url_key'     => $urlKey,
                    'parent_menu' => $parentMenuId,
                    'menutype'    => 2,
                    'menu_order'  => DB::table('menu')->where('parent_menu', $parentMenuId)->count() + 1,
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

            foreach ($perms as $permName => $roles) {
                $pid = DB::table('permissions')->where('name', $permName)->value('id')
                    ?? DB::table('permissions')->insertGetId([
                        'name' => $permName, 'guard_name' => 'web', 'menu_id' => $menuId, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                foreach (DB::table('roles')->whereIn('name', $roles)->pluck('id') as $rid) {
                    DB::table('role_has_permissions')->updateOrInsert(['permission_id' => $pid, 'role_id' => $rid]);
                }
            }
        }
        app('cache')->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        foreach (self::MENUS as [$route, , , , , $perms]) {
            $ids = DB::table('permissions')->whereIn('name', array_keys($perms))->pluck('id');
            DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
            DB::table('menu')->where('route_name', $route)->delete();
        }
        app('cache')->forget('spatie.permission.cache');
    }
};
