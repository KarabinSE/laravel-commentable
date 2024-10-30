<?php

// config for Karabin/Commentable
return [
    'models' => [
        'comment' => \Karabin\Commentable\Models\Comment::class,
    ],

    // Add models that have comments here
    'model_map' => [
    ],

    'data' => [
        'comment' => \Karabin\Commentable\Data\CommentData::class,
    ],
];
