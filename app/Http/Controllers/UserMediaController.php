<?php

namespace App\Http\Controllers;

use App\Traits\SavesOptimizedThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UserMediaController extends Controller
{
    use SavesOptimizedThumbnail;

    // Content-type → upload subfolder mapping
    private const FOLDERS = [
        'posts'     => 'posts',
        'events'    => 'events',
        'pages'     => 'pages',
        'questions' => 'questions',
        'recipes'   => 'recipes',
        'profiles'  => 'profiles',
        'stories'   => 'stories',
    ];

    /**
     * Return the current user's uploaded images as JSON (for TinyMCE file_picker_callback).
     * GET /user/media?type=posts
     */
    public function index(Request $request)
    {
        $type   = $request->query('type', '');
        $folder = self::FOLDERS[$type] ?? '';
        $userId = auth()->id();

        // User-scoped subfolder: uploads/{type}/{user_id}/
        $dir  = public_path('uploads' . ($folder ? "/{$folder}" : '') . "/{$userId}");
        $items = [];

        if (File::exists($dir)) {
            foreach (File::files($dir) as $f) {
                $ext = strtolower($f->getExtension());
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    continue;
                }
                $relPath = ($folder ? "{$folder}/" : '') . "{$userId}/" . $f->getFilename();
                $items[] = [
                    'title' => $f->getFilename(),
                    'value' => '/uploads/' . $relPath,
                    'size'  => $f->getSize(),
                    'mtime' => $f->getMTime(),
                ];
            }
        }

        usort($items, fn($a, $b) => $b['mtime'] - $a['mtime']);

        return response()->json(['images' => array_values($items)]);
    }

    /**
     * Upload an image via TinyMCE's images_upload_handler.
     * POST /user/media/upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:8192',
            'type' => 'nullable|string|in:posts,events,pages,questions,recipes,profiles,stories',
        ]);

        $type   = $request->input('type', 'posts');
        $folder = self::FOLDERS[$type] ?? 'posts';
        $userId = auth()->id();
        $subdir = "{$folder}/{$userId}";

        $url = $this->saveOptimizedThumbnail($request->file('file'), $subdir);

        return response()->json(['location' => $url]);
    }
}
