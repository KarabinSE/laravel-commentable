<?php

namespace Karabin\Commentable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Karabin\Commentable\Commentable
 */
class Commentable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Karabin\Commentable\Commentable::class;
    }
}
