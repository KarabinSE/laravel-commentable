<?php

namespace Karabin\Commentable\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The comment posted.
     *
     * @var mixed
     */
    public $comment;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $comment
     * @return void
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function broadcastAs(): string
    {
        return 'comment.deleted';
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
}
