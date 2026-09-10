-- ============================================================================
-- Adds "Tenant's Payment History - Agreement Wise (V2)" under
-- PLM Module -> Reports, with its view permission granted to every role.
--
-- Equivalent of database/migrations/2026_09_10_000003_add_payment_history_v2_menu.php
-- Safe to re-run: does nothing if the menu already exists.
--
-- NOTE permissions.menu_id is NOT NULL with a cascade delete from menu, so the
-- menu row is always created before the permission.
-- ============================================================================

DO $$
DECLARE
  v_plm_id  INT;
  v_rep_id  INT;
  v_order   INT;
  v_menu_id INT;
  v_perm_id INT;
  v_role_id INT;
BEGIN
  IF EXISTS (SELECT 1 FROM menu WHERE route_name = 'showPaymentHistoryReportV2') THEN
    RAISE NOTICE 'menu showPaymentHistoryReportV2 already exists, nothing to do';
    RETURN;
  END IF;

  SELECT id INTO v_plm_id FROM menu WHERE menu_name = 'PLM Module' AND parent_menu = 0 LIMIT 1;
  SELECT id INTO v_rep_id FROM menu WHERE menu_name = 'Reports' AND menutype = 1 AND parent_menu = v_plm_id LIMIT 1;

  IF v_rep_id IS NULL THEN
    RAISE EXCEPTION 'Could not find PLM Module -> Reports. Run: SELECT id, menu_name, parent_menu, menutype FROM menu WHERE menutype = 1 ORDER BY parent_menu;';
  END IF;

  SELECT COUNT(*) + 1 INTO v_order FROM menu WHERE parent_menu = v_rep_id;

  INSERT INTO menu (menu_name, menu_icon, route_name, url_key, parent_menu, menutype, menu_order, status, created_at, updated_at)
  VALUES ('Tenant''s Payment History - Agreement Wise (V2)', 'fa-history',
          'showPaymentHistoryReportV2', 'showPaymentHistoryReportV2',
          v_rep_id, 2, v_order, 1, now(), now())
  RETURNING id INTO v_menu_id;

  INSERT INTO permissions (name, guard_name, menu_id, created_at, updated_at)
  VALUES ('view_payment_history_v2', 'web', v_menu_id, now(), now())
  RETURNING id INTO v_perm_id;

  FOR v_role_id IN SELECT id FROM roles LOOP
    INSERT INTO role_has_permissions (permission_id, role_id) VALUES (v_perm_id, v_role_id);
  END LOOP;

  RAISE NOTICE 'Created menu id=%, permission id=%', v_menu_id, v_perm_id;
END $$;

INSERT INTO migrations (migration, batch)
SELECT '2026_09_10_000003_add_payment_history_v2_menu', COALESCE(MAX(batch), 0) + 1 FROM migrations
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_09_10_000003_add_payment_history_v2_menu');

-- Verify
SELECT m.menu_name, m.route_name, m.status,
       (SELECT p.menu_name FROM menu p WHERE p.id = m.parent_menu) AS parent,
       (SELECT pe.name FROM permissions pe WHERE pe.menu_id = m.id LIMIT 1) AS permission,
       (SELECT COUNT(*) FROM permissions pe JOIN role_has_permissions r ON r.permission_id = pe.id WHERE pe.menu_id = m.id) AS roles_granted,
       (SELECT COUNT(*) FROM roles) AS roles_total
FROM menu m WHERE m.route_name = 'showPaymentHistoryReportV2';

-- ============================================================================
-- THEN:  php artisan permission:cache-reset   (or delete storage/framework/cache/data/*)
--        and log out / log back in.
-- ============================================================================
