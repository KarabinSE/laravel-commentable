<?php

namespace Karabin\Commentable\Models;

use DateTimeInterface;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Karabin\Commentable\Events\CommentDeleted;
use Karabin\Commentable\Events\CommentPosted;
use Karabin\Commentable\Events\EmailMentioned;
use Karabin\Commentable\Events\EmailMentionedDeleted;
use Karabin\Commentable\Exceptions\ModelNotCommentableException;

class Comment extends Model
{
    use HasFactory;

    /**
     * Morph class.
     *
     * @var string
     */
    public $morphClass = 'comment';

    protected $casts = [
        'anonymized_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::boot();

        static::saved(function ($model) {
            $filtered = $model::getEmailsFromHtmlString($model->comment, $model);
            if ($filtered) {
                foreach ($filtered as $filter) {
                    $email = $filter->getAttribute('data-email');
                    EmailMentioned::dispatch($model, $email);
                }
            }
            CommentPosted::dispatch($model);
        });

        static::deleting(function ($model) {
            if ($model->children->count()) {
                $model->comment = 'Borttagen kommentar';
                $model->anonymized_at = now();
                $model->save();

                return false;
            }
        });

        // If the last child comment is deleted on a deleted
        // parent, the parent should be deleted
        static::deleted(function ($model) {
            CommentDeleted::dispatch($model);

            $filtered = $model::getEmailsFromHtmlString($model->comment, $model);
            if ($filtered) {
                foreach ($filtered as $filter) {
                    $email = $filter->getAttribute('data-email');
                    EmailMentionedDeleted::dispatch($model, $email);
                }
            }

            // Should be done in user land
            // DatabaseNotification::where('type', 'App\Notifications\UserMentionedInComment')
            //     ->where('data->comment_id', $model->id)
            //     ->get()->each(function ($item) {
            //         $item->delete();
            //     });

            if (! $model->parent_id) {
                return;
            }
            if ($model->parent->anonymized_at && ! $model->parent->children->count()) {
                $model->parent->delete();
            }

        });
    }

    public static function getEmailsFromHtmlString(string $string, $model)
    {
        $doc = new DOMDocument;
        $doc->loadHTML($string);
        $xpath = new DOMXPath($doc);
        $filtered = $xpath->query('//span[@data-email]');

        return $filtered;
    }

    protected $fillable = [
        'user_id',
        'comment',
        'parent_id',
        'ip_address',
        'user_agent',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model') ?? \Karabin\Commentable\Tests\TestModels\User::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->orderBy('created_at');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function getModelMap()
    {
        return config('commentable.model_map');
    }

    public function getCommentsForModel(string $modelName, int $modelId)
    {
        $modelMap = $this->getModelMap();
        if (! array_key_exists($modelName, $modelMap)) {
            throw new ModelNotCommentableException('The specified model is not commentable');
        }

        $modelClass = $modelMap[$modelName];
        $model = (new ($modelClass))->where('id', $modelId)
            ->select('id')
            ->with('comments', 'comments.user', 'comments.children', 'comments.children.user')
            ->firstOrFail();

        return config('commentable.data.comment')::collect($model->comments);
    }

    public function storeComment(Model $commenter, string $modelName, int $modelId, string $comment, ?int $parentId = null)
    {
        $modelMap = $this->getModelMap();
        if (! array_key_exists($modelName, $modelMap)) {
            throw new ModelNotCommentableException('The specified model is not commentable');
        }

        $modelClass = $modelMap[$modelName];
        $model = (new ($modelClass))->where('id', $modelId)
            ->select('id')
            ->firstOrFail();
        $comment = $model->commentAs($commenter, $comment, $parentId ?? null);

        return config('commentable.data.comment')::from($comment)->wrap('data');
    }
}
