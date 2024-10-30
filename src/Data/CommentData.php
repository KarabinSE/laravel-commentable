<?php

namespace Karabin\Commentable\Data;

use DateTime;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class CommentData extends Data
{
    public function __construct(
        public int $id,
        public ?int $parent_id,
        public string $commentable_type,
        public int $commentable_id,
        public int $user_id,
        public string $comment,
        public ?string $ip_address,
        public ?string $user_agent,
        public ?string $edited_at,

        // public UserData|null|Optional $user,

        #[DataCollectionOf(self::class)]
        public DataCollection|Optional $children,

        public ?DateTime $anonymized_at,
        public ?DateTime $created_at,
        public ?DateTime $updated_at,
    ) {}

    public function defaultWrap(): string
    {
        return 'data';
    }
}
