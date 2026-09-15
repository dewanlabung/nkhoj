<?php

namespace App\Domains\Blog\Services;

use App\Models\Bookmark;
use App\Models\BookmarkCollection;
use App\Models\Post;
use App\Models\SocialPage;
use Illuminate\Database\Eloquent\Model;

class BookmarkService
{
    private array $typeMap = [
        'post' => Post::class,
        'page' => SocialPage::class,
    ];

    public function getTypeMap(): array
    {
        return $this->typeMap;
    }

    public function resolveModel(string $type): string
    {
        return $this->typeMap[$type];
    }

    public function toggle(string $type, int $id, int $userId, ?int $collectionId = null): bool
    {
        $modelClass = $this->resolveModel($type);
        $model      = $modelClass::findOrFail($id);

        $existing = Bookmark::where('user_id', $userId)
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $model->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        Bookmark::create([
            'user_id'           => $userId,
            'bookmarkable_type' => $modelClass,
            'bookmarkable_id'   => $model->id,
            'collection_id'     => $collectionId,
        ]);

        return true;
    }

    public function togglePost(int $postId, int $userId): array
    {
        $post     = Post::findOrFail($postId);
        $existing = Bookmark::where('user_id', $userId)
            ->where('bookmarkable_type', Post::class)
            ->where('bookmarkable_id', $postId)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            Bookmark::create([
                'user_id'           => $userId,
                'bookmarkable_type' => Post::class,
                'bookmarkable_id'   => $postId,
            ]);
            $action = 'saved';
        }

        $count = Bookmark::where('bookmarkable_type', Post::class)
            ->where('bookmarkable_id', $postId)
            ->count();

        return ['action' => $action, 'count' => $count];
    }

    public function createCollection(int $userId, string $name): BookmarkCollection
    {
        return BookmarkCollection::create([
            'user_id' => $userId,
            'name'    => $name,
            'slug'    => \Str::slug($name . '-' . $userId),
        ]);
    }
}
