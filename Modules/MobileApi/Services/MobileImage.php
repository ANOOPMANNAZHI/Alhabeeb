<?php

namespace Modules\MobileApi\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Photos and signatures uploaded from the app: resized copies under
 * storage/app/mobile/{dir}/ on the `local` disk. Paths are disk-relative, the
 * same convention the desktop uses (`public/MaintenanceServiceReportImages/…`),
 * so the existing office views render them unchanged.
 */
class MobileImage
{
    /** @return array{path:string,thumb:string} paths on the `local` disk */
    public static function store(UploadedFile $file, string $dir): array
    {
        $dir = 'mobile/' . trim($dir, '/');
        Storage::disk('local')->makeDirectory($dir);
        $base = Storage::disk('local')->path($dir);
        $name = now()->format('YmdHis') . '_' . Str::random(6);
        $max = (int) config('mobileapi.image_max_px', 1600);
        $thumb = (int) config('mobileapi.thumb_px', 300);

        Image::make($file->getRealPath())->orientate()
            ->resize($max, $max, fn ($c) => $c->aspectRatio()->upsize())
            ->save("$base/$name.jpg", 85);
        Image::make($file->getRealPath())->orientate()
            ->resize($thumb, $thumb, fn ($c) => $c->aspectRatio()->upsize())
            ->save("$base/{$name}_t.jpg", 80);

        return ['path' => "$dir/$name.jpg", 'thumb' => "$dir/{$name}_t.jpg"];
    }

    public static function storeSignature(string $dataUrl, string $dir): string
    {
        if (!preg_match('#^data:image/png;base64,(.+)$#s', $dataUrl, $m)) {
            throw new \InvalidArgumentException('Signature must be a PNG data URL.');
        }
        $dir = 'mobile/' . trim($dir, '/');
        Storage::disk('local')->makeDirectory($dir);
        $path = "$dir/signature_" . now()->format('YmdHis') . '_' . Str::random(4) . '.png';
        Storage::disk('local')->put($path, base64_decode($m[1]));
        return $path;
    }
}
