<?php declare(strict_types=1);

namespace LiamWiltshire\LaravelJitLoader;

use LiamWiltshire\LaravelJitLoader\Concerns\AutoloadsRelationships;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Class Model
 * @package LiamWiltshire\LaravelJitLoader
 */
class Model extends EloquentModel
{
    use AutoloadsRelationships;
}
