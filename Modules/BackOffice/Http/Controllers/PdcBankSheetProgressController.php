<?php

namespace Modules\BackOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

/**
 * Progress of a bank-sheet post (cheque n of total), polled by the review page's
 * overlay. Served without the session on purpose: a session request finishing
 * during the post would use up the result page's flash messages. Instead the
 * review page carries a key only it knows (HMAC of the sheet name with the app key).
 */
class PdcBankSheetProgressController extends Controller
{
    public static function key($stored)
    {
        return hash_hmac('sha256', 'pdc-bank-sheet-progress|' . $stored, (string) config('app.key'));
    }

    public static function cacheKey($stored)
    {
        return 'pdc_bank_sheet_progress:' . $stored;
    }

    public function __invoke($stored, $key)
    {
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $stored) || !hash_equals(self::key($stored), (string) $key)) {
            abort(404);
        }
        $p = Cache::get(self::cacheKey($stored));
        return response()->json($p ?: ['done' => 0, 'total' => 0, 'msg' => 'Checking the sheet…', 'finished' => false]);
    }
}
