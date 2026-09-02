-- Run this once on the production database (adds the "Tenancy Details MERA" menu item + permission).
-- Equivalent of database/migrations/2026_08_15_000001_add_tenancy_details_mera_report_menu.php

DO $$
DECLARE
  v_plm_module_id INT;
  v_parent_menu_id INT;
  v_order_next INT;
  v_menu_id INT;
  v_permission_id INT;
  v_role_id INT;
BEGIN
  -- Find "PLM Module" (top-level menu) then its "Reports" child. There are
  -- several "Reports" menus in the system (one per module), so matching on
  -- menu_name alone is ambiguous and can attach this under the wrong module.
  SELECT id INTO v_plm_module_id
  FROM menu
  WHERE menu_name = 'PLM Module' AND parent_menu = 0
  LIMIT 1;

  SELECT id INTO v_parent_menu_id
  FROM menu
  WHERE menu_name = 'Reports' AND menutype = 1 AND parent_menu = v_plm_module_id
  LIMIT 1;

  IF v_parent_menu_id IS NULL THEN
    v_parent_menu_id := 0;
  END IF;

  SELECT COUNT(*) + 1 INTO v_order_next
  FROM menu
  WHERE parent_menu = v_parent_menu_id;

  INSERT INTO menu (menu_name, menu_icon, route_name, url_key, parent_menu, menutype, menu_order, status, created_at, updated_at)
  VALUES ('Tenancy Details MERA', 'fa-file-text', 'showtenancyDetailsMeraReport', 'showtenancyDetailsMeraReport', v_parent_menu_id, 2, v_order_next, 1, now(), now())
  RETURNING id INTO v_menu_id;

  INSERT INTO permissions (name, guard_name, menu_id, created_at, updated_at)
  VALUES ('view_tenancy_details_mera_report', 'web', v_menu_id, now(), now())
  RETURNING id INTO v_permission_id;

  FOR v_role_id IN SELECT id FROM roles LOOP
    INSERT INTO role_has_permissions (permission_id, role_id) VALUES (v_permission_id, v_role_id);
  END LOOP;

  RAISE NOTICE 'Created menu id=%, permission id=%', v_menu_id, v_permission_id;
END $$;

-- After running this, clear the Spatie permission cache one of these ways:
--   1) If you have any artisan access on the server: php artisan cache:forget spatie.permission.cache
--   2) Otherwise, restart php-fpm / the app, or just wait for the cache TTL to expire.
