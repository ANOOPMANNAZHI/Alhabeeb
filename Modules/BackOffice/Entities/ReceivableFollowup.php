<?php

namespace Modules\BackOffice\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * One receivables follow-up on a tenant contract (append-only history).
 * PHP < 7.4 in production: no arrow functions.
 */
class ReceivableFollowup extends Model
{
    const KEPT = 'kept';
    const BROKEN = 'broken';
    const UPCOMING = 'upcoming';

    protected $table = 'receivable_followups';
    protected $guarded = [];
    protected $casts = ['promised_date' => 'datetime'];

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    /** Employee name of the user who followed up, else the username. */
    public function creatorName()
    {
        $u = $this->creator;
        if (!$u) {
            return '';
        }
        return optional($u->employee)->employee_name ?: (string) $u->username;
    }

    /** Whole OMR for the screen: decimals cut off, never rounded (351.750 -> 351), user 04/10/2026. */
    public static function amount($v)
    {
        $v = round((float) $v, 3);   // float noise first, so 351.9999999 stays 352
        return number_format($v < 0 ? ceil($v) : floor($v));
    }

    /**
     * Kept: a rent receipt dated on/after the follow-up day and on/before the promised date.
     * Broken: promised date before today and no such receipt. Upcoming: otherwise.
     * $receiptDates are 'Y-m-d' strings; null when no promised date.
     */
    public static function promiseStatus($promisedDate, $followedUpAt, array $receiptDates, $today)
    {
        if (!$promisedDate) {
            return null;
        }
        $from = Carbon::parse($followedUpAt)->format('Y-m-d');
        $to = Carbon::parse($promisedDate)->format('Y-m-d');
        foreach ($receiptDates as $d) {
            $d = Carbon::parse($d)->format('Y-m-d');
            if ($d >= $from && $d <= $to) {
                return self::KEPT;
            }
        }
        return $to < Carbon::parse($today)->format('Y-m-d') ? self::BROKEN : self::UPCOMING;
    }
}
