<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * Default wording for every system template (project C). Seeds the
 * message_templates rows, stands in when a row is missing, powers
 * "Reset to default" and registers each key's placeholders.
 */
class SystemTemplateDefaults
{
    // Placeholder sets. A system template offers {today}, {company_name} and exactly
    // the sets listed in its 'placeholders' — the values its send site fills in.

    private static function tenantPlaceholders()
    {
        return [
            'tenant_name' => ['label' => 'Tenant name', 'sample' => 'Mohammed Al Balushi'],
            'mobile'      => ['label' => 'Tenant mobile', 'sample' => '99123456'],
        ];
    }

    /** MessageValues::contract() fills these (and tenant_name, mobile). */
    private static function contractPlaceholders()
    {
        return [
            'contract_no'    => ['label' => 'Contract no.', 'sample' => 'TAG2601977'],
            'building'       => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'           => ['label' => 'Unit', 'sample' => '84'],
            'location'       => ['label' => 'Location', 'sample' => 'Al Khuwair'],
            'rent'           => ['label' => 'Monthly rent', 'sample' => '250.000'],
            'contract_start' => ['label' => 'Contract start', 'sample' => '15/09/2026'],
            'contract_end'   => ['label' => 'Contract end', 'sample' => '30/09/2027'],
        ];
    }

    /** MessageValues::cheque() fills these. */
    private static function chequePlaceholders()
    {
        return [
            'cheque_no'     => ['label' => 'Cheque no.', 'sample' => '82079797'],
            'amount'        => ['label' => 'Cheque amount', 'sample' => '250.000'],
            'cheque_date'   => ['label' => 'Cheque date', 'sample' => '15/09/2026'],
            'bounce_reason' => ['label' => 'Bounce reason', 'sample' => 'Insufficient Funds'],
            'remarks'       => ['label' => 'Remarks', 'sample' => 'FUNDS NOT SUFFICIENT'],
        ];
    }

    private static function employeePlaceholders()
    {
        return ['employee_name' => ['label' => 'Employee name', 'sample' => 'Rashid Al Harthy']];
    }

    private static function accountPlaceholders($linkKey)
    {
        return [
            'username' => ['label' => 'Login username', 'sample' => 'rashid.h'],
            $linkKey   => ['label' => 'Password link (valid 24 hours)', 'sample' => 'https://plms.alhabib.om/password/reset/sample-token?email=rashid%40alhabib.om'],
        ];
    }

    public static function all()
    {
        return [
            'pdc_bounce_are' => [
                'name' => 'Cheque bounced (to ARE)', 'audience' => 'staff', 'stage' => 'C1',
                'email_subject' => 'Cheque bounced - {tenant_name} ({cheque_no})',
                'email_body' => '<p>Dear {employee_name},</p>'
                    . '<p>This is to inform you that cheque <strong>{cheque_no}</strong> for OMR {amount} dated {cheque_date} has bounced.</p>'
                    . '<p>Tenant: {tenant_name}<br>Contract: {contract_no}<br>Building: {building}, unit {unit}</p>'
                    . '<p>Bounce reason: {bounce_reason}<br>Remarks: {remarks}</p>'
                    . '<p>Regards,<br>Al Habib Team</p>',
                'sms_body' => 'Cheque {cheque_no} of {tenant_name} ({building} {unit}) bounced: {bounce_reason}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => self::employeePlaceholders() + self::tenantPlaceholders() + self::contractPlaceholders() + self::chequePlaceholders(),
            ],
            'pdc_bounce_tenant' => [
                'name' => 'Cheque bounced (to tenant)', 'audience' => 'tenant', 'stage' => 'C1',
                'email_subject' => 'Your cheque {cheque_no} has been returned by the bank',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>We regret to inform you that your cheque no. <strong>{cheque_no}</strong> for OMR {amount} dated {cheque_date} '
                    . 'for {building}, unit {unit} (contract {contract_no}) was returned by the bank.</p>'
                    . '<p>Reason: {bounce_reason}</p>'
                    . '<p>Please contact our office at the earliest to replace the cheque and avoid penalty charges.</p>'
                    . '<p>Regards,<br>Al Habib Team</p>',
                'sms_body' => 'Dear {tenant_name}, your cheque {cheque_no} for OMR {amount} was returned by the bank ({bounce_reason}). Please contact Al Habib to replace it.',
                'is_active' => false, 'sms_enabled' => false,
                'placeholders' => self::tenantPlaceholders() + self::contractPlaceholders() + self::chequePlaceholders(),
            ],
        ] + self::c2() + self::c3() + self::rentRevisal();
    }

    /** Rent revisal before renewal (spec 2026-10-15). */
    private static function rentRevisal()
    {
        $contract = ['tenant_name' => self::tenantPlaceholders()['tenant_name']] + self::contractPlaceholders();
        $revisal = [
            'current_rent' => ['label' => 'Current monthly rent', 'sample' => '300.000'],
            'revised_rent' => ['label' => 'Revised monthly rent', 'sample' => '330.000'],
            'revisal_note' => ['label' => 'Note from the ARE', 'sample' => '<p>The revision follows the market rate for the area.</p>', 'block' => true],
            'are_name'     => ['label' => 'ARE name', 'sample' => 'Rashid Al Balushi'],
            'are_email'    => ['label' => 'ARE email', 'sample' => 'rashid@alhabib.om'],
            'are_mobile'   => ['label' => 'ARE mobile', 'sample' => '99123456'],
        ];
        return [
            'rent_revisal_due_are' => [
                'name' => 'Rent revisal due (ARE)', 'audience' => 'staff', 'stage' => 'RR',
                'email_subject' => 'Contract expiring soon - start rent revisal if needed - {building} {unit}',
                'email_body' => '<p>Dear {employee_name},</p>'
                    . '<p>The contract below ends on <strong>{contract_end}</strong>. If the rent is to be revised on renewal, '
                    . 'please enter the revised rent in PLMS under Due for Renewal &gt; Rent revisal before it is 90 days from expiry.</p>'
                    . '<p>Tenant: {tenant_name}<br>Contract: {contract_no}<br>Building: {building}, unit {unit}<br>Current rent: OMR {rent}</p>'
                    . '<p><a href="{revisal_link}">Open Rent revisal</a></p>' . self::regards(),
                'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
                'placeholders' => self::employeePlaceholders() + $contract
                    + ['revisal_link' => ['label' => 'Link to the Rent revisal tab', 'sample' => 'https://plms.alhabib.om/tenantRenewal/rentRevisal']],
            ],
            'rent_revision_tenant' => [
                'name' => 'Rent revision notice (tenant)', 'audience' => 'tenant', 'stage' => 'RR',
                'email_subject' => 'Rent revision on renewal - {building} {unit}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Your tenancy contract {contract_no} for {building}, unit {unit} ends on {contract_end}.</p>'
                    . '<p>On renewal, the monthly rent will be <strong>OMR {revised_rent}</strong> (currently OMR {current_rent}).</p>'
                    . '{revisal_note}'
                    . '<p>For any questions please reply to this email or contact {are_name} on {are_mobile}.</p>' . self::regards(),
                'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
                'placeholders' => $contract + $revisal,
            ],
        ];
    }

    /** C3: staff messages (complaint assignment, enquiries) and legal alerts. */
    private static function c3()
    {
        $employee = self::employeePlaceholders();
        $assignment = $employee + [
            'works_code'       => ['label' => 'Work code', 'sample' => 'PLB'],
            'work_desc'        => ['label' => 'Work description', 'sample' => 'Kitchen sink leaking'],
            'complaint_no'     => ['label' => 'Complaint no.', 'sample' => 'CMP2609123'],
            'complaint_date'   => ['label' => 'Complaint date & time', 'sample' => '29/09/2026 10:15'],
            'complaint_mobile' => ['label' => 'Complainer GSM', 'sample' => '99123456'],
        ] + self::placePlaceholders();
        $enquiry = [
            'enquiry_no'    => ['label' => 'Enquiry no.', 'sample' => 'ENQ2609045'],
            'enquiry_type'  => ['label' => 'Enquiry type', 'sample' => 'Tenant'],
            'enquirer_name' => ['label' => 'Enquirer name', 'sample' => 'Salim Al Hinai'],
        ];
        $assignSms = 'Please attend {works_code} - {work_desc} Building Name: {building} Flat No: {unit} GSM Number: {complaint_mobile} Comp No- {complaint_no} Date & Time: {complaint_date}';
        $assignBody = function ($verb) {
            return '<p>Dear {employee_name},</p>'
                . '<p>Complaint no. <strong>{complaint_no}</strong> has been ' . $verb . ' to you. Please attend:</p>'
                . '<p>{works_code} - {work_desc}<br>Building: {building}, flat {unit}<br>GSM number: {complaint_mobile}<br>Date &amp; time: {complaint_date}</p>'
                . self::regards();
        };
        $enquiryStaff = function ($what) {
            return '<p>Dear {employee_name},</p><p>' . $what . '</p>' . self::regards();
        };

        $out = [
            'complaint_assigned' => [
                'name' => 'Complaint assigned (technician)', 'audience' => 'staff', 'stage' => 'C3',
                'email_subject' => 'Complaint {complaint_no} assigned to you', 'email_body' => $assignBody('assigned'),
                'sms_body' => $assignSms, 'is_active' => true, 'sms_enabled' => true, 'placeholders' => $assignment,
            ],
            'complaint_subassigned' => [
                'name' => 'Complaint ticket sub-assigned (technician)', 'audience' => 'staff', 'stage' => 'C3',
                'email_subject' => 'Complaint {complaint_no} ticket sub-assigned to you', 'email_body' => $assignBody('sub-assigned'),
                'sms_body' => $assignSms, 'is_active' => true, 'sms_enabled' => true, 'placeholders' => $assignment,
            ],
            'enquiry_new' => [
                'name' => 'New enquiry (to the customer)', 'audience' => 'any', 'stage' => 'C3',
                'email_subject' => 'Your enquiry {enquiry_no} has been received',
                'email_body' => '<p>Dear {enquirer_name},</p><p>Thank you for your interest. Your {enquiry_type} enquiry no. <strong>{enquiry_no}</strong> '
                    . 'has been registered and our team will contact you shortly.</p>' . self::regards(),
                'sms_body' => 'Dear {enquirer_name}, your enquiry {enquiry_no} has been received. Our team will contact you shortly. - Al Habib',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $enquiry,
            ],
            'enquiry_assigned' => [
                'name' => 'Enquiry assigned (staff)', 'audience' => 'staff', 'stage' => 'C3',
                'email_subject' => '{enquiry_type} enquiry {enquiry_no} assigned to you',
                'email_body' => $enquiryStaff('New {enquiry_type} enquiry <strong>{enquiry_no}</strong> ({enquirer_name}) has been assigned to you.'),
                'sms_body' => 'Enquiry {enquiry_no} ({enquirer_name}) has been assigned to you. - PLMS',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $employee + $enquiry,
            ],
            'enquiry_reassigned' => [
                'name' => 'Enquiry re-assigned (staff)', 'audience' => 'staff', 'stage' => 'C3',
                'email_subject' => '{enquiry_type} enquiry {enquiry_no} re-assigned',
                'email_body' => $enquiryStaff('{enquiry_type} enquiry <strong>{enquiry_no}</strong> ({enquirer_name}) has been re-assigned and needs your action.'),
                'sms_body' => 'Enquiry {enquiry_no} ({enquirer_name}) has been re-assigned. - PLMS',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $employee + $enquiry,
            ],
            'enquiry_rejected' => [
                'name' => 'Preliminary documentation rejected (staff)', 'audience' => 'staff', 'stage' => 'C3',
                'email_subject' => 'Preliminary documentation rejected - enquiry {enquiry_no}',
                'email_body' => $enquiryStaff('The preliminary documentation for enquiry <strong>{enquiry_no}</strong> ({enquirer_name}) has been rejected.'),
                'sms_body' => 'Preliminary documentation rejected for enquiry {enquiry_no}. - PLMS',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $employee + $enquiry,
            ],
        ];

        // Legal alerts: [name, subject, sentence, sms, extra placeholders, active]
        $legal = [
            'legal_deposit_refund_posted' => ['Legal: deposit refund posted', 'Deposit refund posted - contract {contract_no} (under legal)',
                'A deposit refund has been posted for contract {contract_no}, which is under legal.', 'Deposit refund posted for contract {contract_no} (under legal). - PLMS', [], true],
            'legal_rental_income_posted' => ['Legal: rental income posted', 'Rental income posted - contract {contract_no} (under legal)',
                'Rental income invoice no. {invoice_no} has been posted for contract {contract_no}, which is under legal.', 'Rental income {invoice_no} posted for contract {contract_no} (under legal). - PLMS',
                ['invoice_no' => ['label' => 'Invoice no.', 'sample' => 'INV2609001']], true],
            'legal_pdc_posted' => ['Legal: PDC posted', 'PDC posted - contract {contract_no} (under legal)',
                'Cheque no. {cheque_no} has been posted for contract {contract_no}, which is under legal.', 'PDC {cheque_no} posted for contract {contract_no} (under legal). - PLMS',
                ['cheque_no' => ['label' => 'Cheque no.', 'sample' => '82079797']], true],
            'legal_receipt_posted' => ['Legal: tenant receipt posted', 'Tenant receipt posted - contract {contract_no} (under legal)',
                'Receipt no. {receipt_no} has been posted for contract {contract_no}, which is under legal.', 'Receipt {receipt_no} posted for contract {contract_no} (under legal). - PLMS',
                ['receipt_no' => ['label' => 'Receipt no.', 'sample' => 'CPY2621842']], true],
            'legal_contract_revoked' => ['Legal: tenant contract revoked', 'Tenant contract revoked - {contract_no}',
                'Tenant contract {contract_no} ({tenant_name}), which is under legal, has been revoked.', 'Tenant contract {contract_no} revoked (under legal). - PLMS', [], true],
            'legal_case_received' => ['Legal: new legal case received', 'New legal case received - contract {contract_no}',
                'A new legal case has been received for contract {contract_no} ({tenant_name}).', 'New legal case received for contract {contract_no}. - PLMS', [], true],
            'legal_case_approved' => ['Legal: legal case approved', 'Legal case approved - contract {contract_no}',
                'The legal case for contract {contract_no} ({tenant_name}) has been approved.', 'Legal case approved for contract {contract_no}. - PLMS', [], true],
            'legal_case_referback' => ['Legal: legal case referred back', 'Legal case referred back - contract {contract_no}',
                'The legal case for contract {contract_no} ({tenant_name}) has been referred back.', 'Legal case referred back for contract {contract_no}. - PLMS', [], false],
            'legal_advisor_approved' => ['Legal: legal advisor approved', 'Legal advisor approved - contract {contract_no}',
                'The legal advisor has approved the legal case for contract {contract_no} ({tenant_name}).', 'Legal advisor approved the case for contract {contract_no}. - PLMS', [], false],
            'legal_advisor_referback' => ['Legal: legal advisor referred back', 'Legal advisor referred back - contract {contract_no}',
                'The legal advisor has referred back the legal case for contract {contract_no} ({tenant_name}).', 'Legal advisor referred back the case for contract {contract_no}. - PLMS', [], true],
            'legal_case_closed' => ['Legal: legal case closed', 'Legal case closed - contract {contract_no}',
                'The legal case for contract {contract_no} ({tenant_name}) has been closed.', 'Legal case closed for contract {contract_no}. - PLMS', [], true],
            'legal_advisor_closed' => ['Legal: legal advisor closed', 'Legal advisor closed - contract {contract_no}',
                'The legal advisor has closed the legal case for contract {contract_no} ({tenant_name}).', 'Legal advisor closed the case for contract {contract_no}. - PLMS', [], true],
            'legal_case_handed_over' => ['Legal: case handed over', 'Legal case handed over - contract {contract_no}',
                'The legal case for contract {contract_no} ({tenant_name}) has been handed over to {handed_to} and closed on our side.', 'Legal case for contract {contract_no} handed over to {handed_to}. - PLMS',
                ['handed_to' => ['label' => 'Handed over to', 'sample' => 'Government - Ministry of Housing']], true],
            'legal_lawyer_rejected' => ['Legal: lawyer rejected a case', 'Legal case rejected by lawyer - contract {contract_no}',
                'The lawyer rejected the legal case for contract {contract_no} ({tenant_name}). Reason: {reason}', 'Lawyer rejected the legal case for contract {contract_no}: {reason} - PLMS',
                ['reason' => ['label' => 'Reason', 'sample' => 'Notice period not completed']], true],
            'legal_stage_changed' => ['Legal: case stage updated', 'Legal case stage: {stage} - contract {contract_no}',
                'The legal case for contract {contract_no} ({tenant_name}) moved to stage "{stage}". {stage_note}', 'Legal case {contract_no} now at stage {stage}. - PLMS',
                ['stage' => ['label' => 'Stage', 'sample' => 'Case filed'], 'stage_note' => ['label' => 'Stage note', 'sample' => 'Filed at the Rental Dispute Committee, case no. 1234.']], true],
            'legal_settlement_entered' => ['Legal: settlement waiting for verification', 'Settlement to verify - contract {contract_no}',
                'The lawyer recorded a settlement of {amount} for contract {contract_no} ({tenant_name}). Please verify it.', 'Settlement of {amount} for contract {contract_no} waiting for your verification. - PLMS',
                ['amount' => ['label' => 'Amount', 'sample' => '1,200.000']], true],
            'legal_settlement_verified' => ['Legal: settlement verified', 'Settlement verified - contract {contract_no}',
                'PLM verified the settlement for contract {contract_no} ({tenant_name}): {amount} to collect.', 'Settlement for contract {contract_no} verified: {amount}. - PLMS',
                ['amount' => ['label' => 'Amount', 'sample' => '1,200.000']], true],
            'legal_settlement_sent_back' => ['Legal: settlement sent back', 'Settlement sent back - contract {contract_no}',
                'PLM sent back the settlement for contract {contract_no} ({tenant_name}). Reason: {reason}', 'Settlement for contract {contract_no} sent back: {reason} - PLMS',
                ['reason' => ['label' => 'Reason', 'sample' => 'Installment dates beyond contract end']], true],
            'legal_settlement_overdue' => ['Legal: settlement installment overdue', 'Settlement installment overdue - contract {contract_no}',
                'The settlement installment of {amount} due on {due_date} for contract {contract_no} ({tenant_name}) is not paid.', 'Settlement installment {amount} due {due_date} unpaid - contract {contract_no}. - PLMS',
                ['amount' => ['label' => 'Amount', 'sample' => '400.000'], 'due_date' => ['label' => 'Due date', 'sample' => '01/11/2026']], true],
            'legal_settlement_broken' => ['Legal: settlement broken', 'Settlement broken - contract {contract_no}',
                'The settlement for contract {contract_no} ({tenant_name}) is broken: an installment is overdue more than {days} days. The case is active again.', 'Settlement broken for contract {contract_no}; case active again. - PLMS',
                ['days' => ['label' => 'Days', 'sample' => '30']], true],
            'legal_settlement_completed' => ['Legal: settlement fully paid', 'Settlement fully paid - contract {contract_no}',
                'The settlement for contract {contract_no} ({tenant_name}) is fully collected. The case can now be closed.', 'Settlement for contract {contract_no} fully paid; case can be closed. - PLMS', [], true],
            'legal_settlement_shortfall' => ['Legal: settled case lost a payment', 'Settled legal case short - contract {contract_no}',
                'A payment for the settled legal case on contract {contract_no} ({tenant_name}) was cancelled (e.g. cheque bounced). {short} is now unpaid. Reopen the case if needed.', 'Settled legal case {contract_no}: {short} unpaid after a cancelled payment. - PLMS',
                ['short' => ['label' => 'Unpaid amount', 'sample' => '400.000']], true],
            'legal_case_reopened' => ['Legal: case reopened', 'Legal case reopened - contract {contract_no}',
                'The legal case for contract {contract_no} ({tenant_name}) was reopened as a new case. Reason: {reason}', 'Legal case {contract_no} reopened: {reason} - PLMS',
                ['reason' => ['label' => 'Reason', 'sample' => 'Settlement cheque bounced']], true],
            'legal_status_note' => ['Legal: advisor added a status note', 'Legal status note - contract {contract_no}',
                'The legal advisor added a status note for contract {contract_no} ({tenant_name}): {status_note}', 'Legal status note for contract {contract_no}: {status_note} - PLMS',
                ['status_note' => ['label' => 'Status note', 'sample' => 'Court hearing scheduled']], true],
        ];
        $legalBase = $employee + ['tenant_name' => self::tenantPlaceholders()['tenant_name']]
            + ['contract_no' => self::contractPlaceholders()['contract_no']] + self::placePlaceholders();
        foreach ($legal as $key => $l) {
            $out[$key] = [
                'name' => $l[0], 'audience' => 'staff', 'stage' => 'C3',
                'email_subject' => $l[1],
                'email_body' => '<p>Dear {employee_name},</p><p>' . $l[2] . '</p><p>Building: {building}, unit {unit}</p>' . self::regards(),
                'sms_body' => $l[3], 'is_active' => $l[5], 'sms_enabled' => false,
                'placeholders' => $legalBase + $l[4],
            ];
        }
        // C4: accounts and the rest. Links go by email only (never SMS).
        $out['employee_welcome'] = [
            'name' => 'New employee account (set password)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Your PLMS account is ready',
            'email_body' => '<p>Dear {employee_name},</p><p>An account has been created for you on PLMS.</p>'
                . '<p>Username: <strong>{username}</strong></p>'
                . '<p>Please set your password using this link: <a href="{set_password_link}">Set my password</a></p>'
                . '<p>The link is valid for 24 hours. If it expires, use "Forgot Password?" on the login page or ask the administrator for a new link.</p>'
                . self::regards(),
            'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders() + self::accountPlaceholders('set_password_link'),
        ];
        $out['employee_password_reset'] = [
            'name' => 'Employee password reset (set new password)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Set a new PLMS password',
            'email_body' => '<p>Dear {employee_name},</p><p>The administrator has sent you a link to set a new password for your PLMS account <strong>{username}</strong>.</p>'
                . '<p><a href="{set_password_link}">Set a new password</a></p>'
                . '<p>The link is valid for 24 hours. Your current password keeps working until you change it.</p>'
                . self::regards(),
            'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders() + self::accountPlaceholders('set_password_link'),
        ];
        $out['password_forgot'] = [
            'name' => 'Forgot password (login page)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Reset your PLMS password',
            'email_body' => '<p>Dear {employee_name},</p><p>We received a request to reset the password of your PLMS account <strong>{username}</strong>.</p>'
                . '<p><a href="{reset_link}">Reset my password</a></p>'
                . '<p>The link is valid for 24 hours. If you did not ask for this, ignore this email; your password stays the same.</p>'
                . self::regards(),
            'sms_body' => null, 'is_active' => true, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders() + self::accountPlaceholders('reset_link'),
        ];
        $out['are_residence_reminder'] = [
            'name' => 'Tenant Residence ID expired (ARE)', 'audience' => 'staff', 'stage' => 'C4',
            'email_subject' => 'Residence ID expired - {tenant_name} ({contract_no})',
            'email_body' => '<p>Dear {employee_name},</p><p>The Residence ID of tenant <strong>{tenant_name}</strong> '
                . '(contract {contract_no}, {building}) expired on {residence_expiry}.</p><p>Please follow up with the tenant for the renewed ID.</p>'
                . self::regards(),
            'sms_body' => 'Residence ID of {tenant_name} ({contract_no}, {building}) expired on {residence_expiry}. - PLMS',
            'is_active' => false, 'sms_enabled' => false,
            'placeholders' => self::employeePlaceholders()
                + ['tenant_name' => self::tenantPlaceholders()['tenant_name'], 'contract_no' => self::contractPlaceholders()['contract_no'],
                   'building' => self::contractPlaceholders()['building'],
                   'residence_expiry' => ['label' => 'Residence ID expiry', 'sample' => '15/09/2026']],
        ];
        return $out;
    }

    private static function complaintPlaceholders()
    {
        return [
            'complaint_no'   => ['label' => 'Complaint no.', 'sample' => 'CMP2609123'],
            'complaint_date' => ['label' => 'Complaint date', 'sample' => '29/09/2026'],
            'building'       => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'           => ['label' => 'Unit', 'sample' => '84'],
            'location'       => ['label' => 'Location', 'sample' => 'Al Khuwair'],
        ] + self::tenantPlaceholders();
    }

    private static function placePlaceholders()
    {
        return [
            'building' => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'     => ['label' => 'Unit', 'sample' => '84'],
        ];
    }

    private static function tableSample($title)
    {
        return '<table border="1" style="border-collapse:collapse"><tr><th>' . $title . '</th><th>Amount</th></tr>'
            . '<tr><td>Painting</td><td>45.000</td></tr><tr><td>Cleaning</td><td>15.000</td></tr></table>';
    }

    private static function regards()
    {
        return '<p>Regards,<br>Al Habib Team</p>';
    }

    /** C2: tenant and landlord messages. */
    private static function c2()
    {
        $complaint = self::complaintPlaceholders();
        $contract = self::tenantPlaceholders() + self::contractPlaceholders();
        return [
            'complaint_registered' => [
                'name' => 'Complaint registered', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Complaint {complaint_no} registered',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>We have received your complaint no. <strong>{complaint_no}</strong> for {building}, unit {unit}. '
                    . 'Our maintenance team will get back to you at the earliest.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your complaint {complaint_no} has been registered. Our maintenance team will contact you soon. - Al Habib',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $complaint,
            ],
            'complaint_completed' => [
                'name' => 'Complaint completed', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Complaint {complaint_no} completed',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Your complaint no. <strong>{complaint_no}</strong> has been successfully attended by the Al Habib Maintenance Team. '
                    . 'Thank you for your kind co-operation.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your complaint {complaint_no} has been completed by Al Habib Maintenance. Thank you for your co-operation.',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $complaint,
            ],
            'complaint_closed' => [
                'name' => 'Complaint closed', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Complaint {complaint_no} closed',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Our team has completed the work on your complaint no. <strong>{complaint_no}</strong>. '
                    . 'If you have any questions about the work or service, please do not hesitate to contact us.</p>'
                    . '<p>We look forward to serving you again.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your complaint {complaint_no} has been closed. Contact Al Habib if you have any questions.',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $complaint,
            ],
            'key_accepted_tenant' => [
                'name' => 'Keys received (from tenant)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Keys received - {building}, unit {unit}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>This is to confirm that we have received the keys of {building}, unit {unit} on {today}.</p>'
                    . '<p>Thank you.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, we have received the keys of {building}, unit {unit} on {today}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => ['tenant_name' => self::tenantPlaceholders()['tenant_name']] + self::placePlaceholders(),
            ],
            'key_accepted_landlord' => [
                'name' => 'Keys received (from landlord)', 'audience' => 'landlord', 'stage' => 'C2',
                'email_subject' => 'Keys received - {building}, unit {unit}',
                'email_body' => '<p>Dear {landlord_name},</p>'
                    . '<p>This is to confirm that we have received the keys of {building}, unit {unit} on {today}.</p>'
                    . '<p>Thank you.</p>' . self::regards(),
                'sms_body' => 'Dear {landlord_name}, we have received the keys of {building}, unit {unit} on {today}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => ['landlord_name' => ['label' => 'Landlord name', 'sample' => 'Ali Mallalah Habib']] + self::placePlaceholders(),
            ],
            'renewal_form' => [
                'name' => 'Tenancy renewal form (PDF attached)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Tenancy renewal form - {building}, unit {unit}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>This is an official notice to inform you that your current lease agreement for {building} / {unit} is due for renewal on {contract_end}. '
                    . 'Accordingly, we enclose herewith the Online Tenancy Contract Renewal Form for your signature.</p>'
                    . '<p>You are requested to submit the form duly signed, along with the rental cheques and copies of ID card / company documents (as applicable), '
                    . 'at our office, which will enable us to proceed with the online registration at Muscat Municipality.</p>'
                    . '<p>We request you to finish the renewal process within a week from the receipt of this mail. As you are aware, late registration attracts a penalty, '
                    . 'so please complete the formalities well in time. You may contact us if you need any clarification.</p>'
                    . '<p>Thank you for your co-operation.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your lease for {building} / {unit} is due for renewal on {contract_end}. The renewal form has been emailed to you. - Al Habib',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $contract,
            ],
            'inspection_details' => [
                'name' => 'Inspection report (termination)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Inspection report - contract {contract_no}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Please find below the inspection report for your contract no. <strong>{contract_no}</strong> ({building}, unit {unit}).</p>'
                    . '{inspection_details}' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, the inspection report for contract {contract_no} has been emailed to you. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => $contract + ['inspection_details' => ['label' => 'Inspection details (table)', 'sample' => self::tableSample('Work'), 'block' => true]],
            ],
            'termination_details' => [
                'name' => 'Termination inspection details', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Termination inspection details - contract {contract_no}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>This is your official notice of the inspection details for contract no. <strong>{contract_no}</strong> ({building}, unit {unit}).</p>'
                    . '{termination_details}' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, the termination inspection details for contract {contract_no} have been emailed to you. - Al Habib',
                'is_active' => false, 'sms_enabled' => false,
                'placeholders' => $contract + ['termination_details' => ['label' => 'Termination details (table)', 'sample' => self::tableSample('Item'), 'block' => true]],
            ],
            'tenant_birthday' => [
                'name' => 'Tenant birthday', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Happy Birthday',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Wish you a Happy Birthday! We hope that you have a great year and accomplish all the fabulous goals you have set. '
                    . 'May the coming years be filled with happiness, peace and love. Have a great day ahead.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, Al Habib wishes you a very Happy Birthday!',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => ['tenant_name' => self::tenantPlaceholders()['tenant_name']],
            ],
            'pdc_due_reminder' => [
                'name' => 'Cheque due reminder (SMS)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => null, 'email_body' => null,
                'sms_body' => 'Dear {tenant_name}, your cheque No. {cheque_no} of amount {amount} is due on {cheque_date}. '
                    . 'Please ensure sufficient funds are available in your account to avoid penalty charges. - Al Habib',
                'is_active' => true, 'sms_enabled' => true,
                'placeholders' => ['tenant_name' => self::tenantPlaceholders()['tenant_name'], 'contract_no' => self::contractPlaceholders()['contract_no']]
                    + self::placePlaceholders() + array_intersect_key(self::chequePlaceholders(), array_flip(['cheque_no', 'amount', 'cheque_date'])),
            ],
        ];
    }

    public static function get($key)
    {
        $all = self::all();
        return isset($all[$key]) ? $all[$key] : null;
    }

    /**
     * Is the system template switched on? The saved row decides; when the row is
     * missing (code uploaded before its migration) the default's is_active does.
     */
    public static function isActive($key)
    {
        $row = \Modules\BackOffice\Entities\MessageTemplate::where('key', $key)
            ->where('kind', \Modules\BackOffice\Entities\MessageTemplate::KIND_SYSTEM)->first();
        if ($row) {
            return (bool) $row->is_active;
        }
        $d = self::get($key);
        return $d ? (bool) $d['is_active'] : false;
    }

    public static function registerPlaceholders()
    {
        foreach (self::all() as $key => $d) {
            MessagePlaceholders::registerSystem($key, $d['placeholders']);
        }
    }

    public static function seed(array $keys)
    {
        $inserted = 0;
        foreach ($keys as $key) {
            $d = self::get($key);
            if (!$d || DB::table('message_templates')->where('key', $key)->exists()) {
                continue;
            }
            DB::table('message_templates')->insert([
                'key' => $key, 'name' => $d['name'], 'kind' => 'system', 'audience' => $d['audience'],
                'email_subject' => $d['email_subject'], 'email_body' => $d['email_body'], 'sms_body' => $d['sms_body'],
                'is_active' => $d['is_active'], 'sms_enabled' => $d['sms_enabled'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $inserted++;
        }
        return $inserted;
    }
}
