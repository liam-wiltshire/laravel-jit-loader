<?php declare(strict_types=1);

namespace LiamWiltshire\LaravelJitLoader;

use LogicException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Class Model
 * @package LiamWiltshire\LaravelJitLoader
 */
class Model extends EloquentModel
{
    /**
     * The maximum collection size that we will autoload for
     * @var int
     */
    protected $autoloadThreshold = 6000;

    /**
     * @var ?Collection
     */
    protected $parentCollection = null;


    private function shouldAutoLoad()
    {
        return ($this->parentCollection
            && count($this->parentCollection) > 1
            && count($this->parentCollection) <= $this->autoloadThreshold);
    }

    /**
     * Load the relationship for the given method, and then get a relationship value from a method.
     * @param string $method
     * @return mixed
     *
     * @throws LogicException
     */
    public function getRelationshipFromMethod($method)
    {

        $relations = $this->$method();
        if (!$relations instanceof Relation) {
            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }

        if ($this->shouldAutoLoad())  {
            $this->parentCollection->loadMissing($method);
        }
        return $this->$method()->getResults();
    }

    /**
     * Create a new Eloquent collection, and assign all models as a member of this collection
     *
     * @param array $models
     * @return Collection
     */
    public function newCollection(array $models = []): Collection
    {
        $collection = new Collection($models);
        unset($models);

        foreach ($collection as &$model) {
            $model->parentCollection = &$collection;
        }

        return $collection;
    }

    /**
     * Disable autoloading of relationships via this model
     * @return Model
     */
    public function disableAutoload(): Model
    {
        $this->autoloadThreshold = 0;
        return $this;
    }
}