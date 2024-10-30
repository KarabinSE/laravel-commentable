# Easily add a commentable trait to a Eloquent model

[![Latest Version on Packagist](https://img.shields.io/packagist/v/karabinse/laravel-commentable.svg?style=flat-square)](https://packagist.org/packages/karabinse/laravel-commentable)


Easily add a commentable trait to an Eloquent model

```php

use Karabin\Commentable\Concerns\Commentable;

class Product extends Model
{
    use Commentable;

    protected $guarded = ['location'];

```

## Installation

You can install the package via composer:

```bash
composer require karabinse/laravel-commentable
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-commentable-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-commentable-config"
```

This is the contents of the published config file:

```php
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

```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-commentable-views"
```

## Usage

Add the trait to the model you want to add comments to.
```php
class Customer extends Model
{
    use Commentable, HasFactory;
```

Configure the model map. You can also provide a data transformer for the comment itself.
```php

use App\Data\CommentData;
use App\Models\Customer;
use App\Models\Product;

return [
    'models' => [
        'comment' => \Karabin\Commentable\Models\Comment::class,
    ],

    // Add models that have comments here
    'model_map' => [
        'customers' => Customer::class,
        'products' => Product::class,
    ],

    'data' => [
        'comment' => CommentData::class,
    ],
];
```

Saving a new comment:
```php
use Karabin\Commentable\Models\Comment;

$commentModel = new Comment;
$comment = $commentModel->storeComment(
    commenter: $request->user(),
    modelName: $modelName,
    modelId: $modelId,
    comment: $request->comment,
    parentId: $request->parent_id ?? null
);

return CommentData::from($comment)->wrap('data');

```

Getting comments:

```php
$commentModel = new Comment;
$comments = $commentModel->getCommentsForModel($modelName, $modelId);

return response()->json(['data' => $comments]);
```

## Testing

```bash
composer test
```


## Credits

- [Albin N](https://github.com/nivv)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
