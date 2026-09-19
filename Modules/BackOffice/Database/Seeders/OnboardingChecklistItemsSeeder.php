<?php

namespace Modules\BackOffice\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BackOffice\Entities\OnboardingChecklistItem;

/** Default move-in checklist. Idempotent on title; admins edit the rest on the office screen. */
class OnboardingChecklistItemsSeeder extends Seeder
{
    public const ITEMS = [
        // group, title, input_type, required, needs_photo, sort
        ['Contract', 'Contract copy handed over and house rules explained', 'check', 1, 0, 10],
        ['Documents', 'Tenant ID / passport / visa collected (note expiry)', 'text', 1, 1, 20],
        ['Keys', 'Keys and access cards handed over (count)', 'count', 1, 1, 30],
        ['Utilities', 'Electricity account no. and opening reading', 'meter', 1, 1, 40],
        ['Utilities', 'Water account no. and opening reading', 'meter', 1, 1, 50],
        ['Unit condition', 'Living room', 'check', 1, 1, 60],
        ['Unit condition', 'Bedrooms', 'check', 1, 1, 61],
        ['Unit condition', 'Kitchen', 'check', 1, 1, 62],
        ['Unit condition', 'Bathrooms', 'check', 1, 1, 63],
        ['Unit condition', 'Balcony / other', 'check', 0, 1, 64],
        ['Payment', 'Payment instructions given / PDC set received', 'check', 1, 0, 70],
    ];

    public function run(): void
    {
        foreach (self::ITEMS as [$group, $title, $type, $required, $photo, $sort]) {
            OnboardingChecklistItem::firstOrCreate(['title' => $title], [
                'group_name' => $group, 'input_type' => $type, 'required' => (bool) $required,
                'needs_photo' => (bool) $photo, 'sort_order' => $sort, 'is_active' => true,
            ]);
        }
    }
}
