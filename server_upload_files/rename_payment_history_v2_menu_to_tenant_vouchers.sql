-- Rename PLM Module -> Reports menu "Tenant's Payment History - Agreement Wise (V2)" to "Tenant Vouchers"
UPDATE menu SET menu_name = 'Tenant Vouchers', updated_at = NOW()
WHERE menu_name = 'Tenant''s Payment History - Agreement Wise (V2)';
