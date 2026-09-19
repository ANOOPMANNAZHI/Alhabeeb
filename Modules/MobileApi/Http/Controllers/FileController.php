<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FileController extends ApiController
{
    public static function signedUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return URL::temporarySignedRoute('api.files.show', now()->addMinutes((int) config('mobileapi.signed_url_minutes', 15)), ['path' => $path]);
    }

    /** Signed, unauthenticated download (the app opens PDFs/images in a viewer that cannot send headers). */
    public function show(Request $request)
    {
        $path = (string) $request->query('path', '');
        abort_unless($request->hasValidSignature() && $path !== '' && !str_contains($path, '..'), 403);
        // App uploads live on the local disk under mobile/; desktop uploads on the default disk under public/
        $disk = str_starts_with($path, 'mobile/') ? 'local' : config('filesystems.default', 'local');
        abort_unless(Storage::disk($disk)->exists($path), 404);
        return Storage::disk($disk)->response($path);
    }
}
