-- Equivalent of migration 2026_09_14_000001_add_acc_codes_id_to_landlord_invoice_v2_lines
-- Adds the expense head (acc_codes) reference to landlord_invoice_v2_lines.
-- Safe to run once; re-running fails on the duplicate column / FK.

ALTER TABLE `landlord_invoice_v2_lines`
    ADD COLUMN `acc_codes_id` INT UNSIGNED NULL AFTER `description`,
    ADD CONSTRAINT `landlord_invoice_v2_lines_acc_codes_id_foreign`
        FOREIGN KEY (`acc_codes_id`) REFERENCES `acc_codes` (`id`) ON DELETE SET NULL;

-- Mark the migration as applied so a future `php artisan migrate` skips it.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_14_000001_add_acc_codes_id_to_landlord_invoice_v2_lines',
       COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` m
    WHERE m.`migration` = '2026_09_14_000001_add_acc_codes_id_to_landlord_invoice_v2_lines'
);

-- Verify:
-- SHOW COLUMNS FROM `landlord_invoice_v2_lines` LIKE 'acc_codes_id';
