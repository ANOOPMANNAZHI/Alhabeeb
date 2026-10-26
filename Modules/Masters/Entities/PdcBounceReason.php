<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Masters -> Bounce Reasons. The id is the code stored in pdc.pdc_bounce_reason
 * (1-9 were built into the code before this master existed and keep their numbers).
 * Reasons are never deleted: switching one off hides it from the dropdowns while
 * PDCs that already carry it keep showing its name.
 */
class PdcBounceReason extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $table = 'pdc_bounce_reasons';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean', 'is_fallback' => 'boolean', 'sort_order' => 'integer'];

    /** Starting data (seeded by the migration). bank_keywords: words / codes in the bank's rejection text. */
    const DEFAULTS = [
        ['id' => 1, 'name' => 'Insufficient Funds', 'bank_keywords' => 'insufficient, not sufficient, nsf, funds', 'sort_order' => 1, 'is_active' => true, 'is_fallback' => false],
        ['id' => 2, 'name' => 'Signature Missing', 'bank_keywords' => 'signature missing, missing signature, signature absent, without signature, unsigned, not signed', 'sort_order' => 2, 'is_active' => true, 'is_fallback' => false],
        ['id' => 3, 'name' => 'Signature Mismatch', 'bank_keywords' => 'signature', 'sort_order' => 3, 'is_active' => true, 'is_fallback' => false],
        ['id' => 4, 'name' => 'Word in amount and figure differ', 'bank_keywords' => 'words and figures, figure', 'sort_order' => 4, 'is_active' => true, 'is_fallback' => false],
        ['id' => 5, 'name' => 'Stop Payment', 'bank_keywords' => 'stop', 'sort_order' => 5, 'is_active' => true, 'is_fallback' => false],
        ['id' => 6, 'name' => 'Refer to Drawer', 'bank_keywords' => 'refer', 'sort_order' => 6, 'is_active' => true, 'is_fallback' => false],
        ['id' => 7, 'name' => 'Correction', 'bank_keywords' => 'correction, alter', 'sort_order' => 7, 'is_active' => true, 'is_fallback' => false],
        ['id' => 8, 'name' => 'Stale Cheque (Beyond six months)', 'bank_keywords' => 'stale', 'sort_order' => 8, 'is_active' => true, 'is_fallback' => false],
        ['id' => 9, 'name' => 'Misc', 'bank_keywords' => null, 'sort_order' => 99, 'is_active' => false, 'is_fallback' => false],
        ['id' => 10, 'name' => 'Account Closed / Frozen / Dormant', 'bank_keywords' => 'account closed, frozen, dormant, 012', 'sort_order' => 9, 'is_active' => true, 'is_fallback' => false],
    ];

    /** @var \Illuminate\Support\Collection|null all reasons, loaded once per request */
    private static $cache;

    public static function flushCache()
    {
        self::$cache = null;
    }

    private static function cached()
    {
        if (self::$cache === null) {
            self::$cache = static::orderBy('sort_order')->orderBy('id')->get();
        }
        return self::$cache;
    }

    /** id => name for a dropdown: active reasons, plus $selected if it has been switched off. */
    public static function options($selected = null)
    {
        $out = [];
        foreach (self::cached() as $r) {
            if ($r->is_active || ((string) $selected !== '' && (int) $selected === (int) $r->id)) {
                $out[$r->id] = $r->name;
            }
        }
        return $out;
    }

    /** All reasons for the PDC grid's JavaScript row builders: [{id, name, active}] in dropdown order. */
    public static function jsList()
    {
        $out = [];
        foreach (self::cached() as $r) {
            $out[] = ['id' => (int) $r->id, 'name' => $r->name, 'active' => (bool) $r->is_active];
        }
        return $out;
    }

    /** Name for a stored code (switched-off reasons included); null when unknown or empty. */
    public static function nameFor($id)
    {
        if ($id === null || $id === '') {
            return null;
        }
        $r = self::cached()->firstWhere('id', (int) $id);
        return $r ? $r->name : null;
    }

    /** Active reasons as matchers for match(). */
    public static function matchers()
    {
        $out = [];
        foreach (self::cached() as $r) {
            if ($r->is_active) {
                $out[] = ['id' => (int) $r->id, 'keywords' => self::splitKeywords($r->bank_keywords), 'fallback' => (bool) $r->is_fallback, 'sort_order' => (int) $r->sort_order];
            }
        }
        return $out;
    }

    public static function splitKeywords($text)
    {
        $out = [];
        foreach (explode(',', strtolower((string) $text)) as $k) {
            $k = trim($k);
            if ($k !== '') {
                $out[] = $k;
            }
        }
        return $out;
    }

    /**
     * Reason for a bank's rejection text: the reason with the longest matching word
     * (so "signature missing" beats "signature"); ties go to the lower order. No match
     * gives the fallback reason if one is set, otherwise null (the user picks).
     */
    public static function match($bankText, array $matchers)
    {
        $t = strtolower(trim((string) $bankText));
        if ($t === '' || strpos($t, 'no clearing reason') !== false) {
            return null;
        }
        $best = null;
        $bestLen = 0;
        $bestOrder = PHP_INT_MAX;
        $fallback = null;
        foreach ($matchers as $m) {
            if (!empty($m['fallback']) && $fallback === null) {
                $fallback = (int) $m['id'];
            }
            foreach ($m['keywords'] as $k) {
                $len = strlen($k);
                if (strpos($t, (string) $k) !== false && ($len > $bestLen || ($len === $bestLen && $m['sort_order'] < $bestOrder))) {
                    $best = (int) $m['id'];
                    $bestLen = $len;
                    $bestOrder = $m['sort_order'];
                }
            }
        }
        return $best !== null ? $best : $fallback;
    }
}
