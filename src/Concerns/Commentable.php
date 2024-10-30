<?php

namespace Karabin\Commentable\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Karabin\Commentable\Models\Comment;

trait Commentable
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'DESC');
    }

    public function comment(string $comment): Model
    {
        return $this->newComment(['comment' => $comment]);
    }

    /**
     * Comment as a user.
     *
     * @param  null|int  $parentId
     */
    public function commentAs(Model $user, string $comment, $parentId = null): Model
    {
        return $this->newComment([
            'comment' => $comment,
            'user_id' => $user->getKey(),
            'parent_id' => $parentId,
        ]);
    }

    private function newComment(array $data): Model
    {
        return $this->comments()->create(array_merge(
            $data,
            [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        ));
    }
}
