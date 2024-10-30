<?php

namespace Karabin\Commentable\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Karabin\Commentable\Data\CommentData;

class EmailMentionedDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public mixed $comment,
        public string $email = ''
    ) {}

    public function broadcastAs(): string
    {
        return 'comment.email-mentioned';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('comments');
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => CommentData::from($this->comment),
            'email' => $this->email,
        ];
    }
}
