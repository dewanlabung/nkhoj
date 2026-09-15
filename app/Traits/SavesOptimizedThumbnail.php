<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait SavesOptimizedThumbnail
{
    protected function saveOptimizedThumbnail(\Illuminate\Http\UploadedFile $file, string $subdir = ''): string
    {
        $filename = time() . '_' . Str::random(8) . '.webp';
        $dir      = 'uploads' . ($subdir ? '/' . trim($subdir, '/') : '');
        $destPath = public_path($dir . '/' . $filename);

        if (!is_dir(public_path($dir))) {
            mkdir(public_path($dir), 0755, true);
        }

        try {
            $manager = new ImageManager(new Driver());
            $manager->read($file->getRealPath())
                ->scaleDown(1200, 675)
                ->toWebp(80)
                ->save($destPath);
        } catch (\Throwable) {
            $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $destPath = public_path($dir . '/' . $filename);
            $file->move(public_path($dir), $filename);
        }

        return '/' . $dir . '/' . $filename;
    }
}
