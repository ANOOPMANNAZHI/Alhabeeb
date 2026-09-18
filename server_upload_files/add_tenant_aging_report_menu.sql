-- Equivalent of migration 2026_09_16_000000_add_tenant_aging_report
-- Adds the "Tenant Aging Report" menu entry (under the same parent as Tenant Receivable v2, menu id 183)
-- and the view_tenant_aging_report permission granted to every role.
-- PostgreSQL. Safe to re-run: every step is guarded.
-- If you see "current transaction is aborted" (25P02): run ROLLBACK; and read the FIRST error above it.

BEGIN;

DO $$
DECLARE
    v_parent  integer;
    v_menu_id integer;
    v_perm_id integer;
    v_role    record;
BEGIN
    -- 1. Menu entry
    SELECT id INTO v_menu_id FROM menu WHERE route_name = 'showTenantAgingReport';
    IF v_menu_id IS NULL THEN
        SELECT COALESCE((SELECT id FROM menu WHERE id = 183), 0) INTO v_parent;

        INSERT INTO menu (menu_name, menu_icon, route_name, url_key, parent_menu, menutype, menu_order, status, created_at, updated_at)
        VALUES ('Tenant Aging Report', 'fa-hourglass-half', 'showTenantAgingReport', 'showTenantAgingReport',
                v_parent, 2,
                (SELECT COUNT(*) + 1 FROM menu WHERE parent_menu = v_parent),
                1, NOW(), NOW())
        RETURNING id INTO v_menu_id;
    END IF;

    -- 2. Permission
    SELECT id INTO v_perm_id FROM permissions WHERE name = 'view_tenant_aging_report';
    IF v_perm_id IS NULL THEN
        INSERT INTO permissions (name, guard_name, menu_id, created_at, updated_at)
        VALUES ('view_tenant_aging_report', 'web', v_menu_id, NOW(), NOW())
        RETURNING id INTO v_perm_id;
    END IF;

    -- 3. Grant to every role (skip roles that already have it)
    FOR v_role IN SELECT id FROM roles LOOP
        IF NOT EXISTS (
            SELECT 1 FROM role_has_permissions
            WHERE permission_id = v_perm_id AND role_id = v_role.id
        ) THEN
            INSERT INTO role_has_permissions (permission_id, role_id) VALUES (v_perm_id, v_role.id);
        END IF;
    END LOOP;
END $$;

-- 4. Mark the migration as applied so a future `php artisan migrate` skips it.
INSERT INTO migrations (migration, batch)
SELECT '2026_09_16_000000_add_tenant_aging_report',
       COALESCE(MAX(batch), 0) + 1
FROM migrations
WHERE NOT EXISTS (
    SELECT 1 FROM migrations m WHERE m.migration = '2026_09_16_000000_add_tenant_aging_report'
);

COMMIT;

-- After running: clear the permission cache on the server
--   php artisan cache:forget spatie.permission.cache
-- (or php artisan cache:clear)

-- Verify:
-- SELECT id, menu_name, route_name, parent_menu, menu_order FROM menu WHERE route_name = 'showTenantAgingReport';
-- SELECT p.id, p.name, COUNT(rhp.role_id) AS roles
--   FROM permissions p LEFT JOIN role_has_permissions rhp ON rhp.permission_id = p.id
--  WHERE p.name = 'view_tenant_aging_report' GROUP BY p.id, p.name;
