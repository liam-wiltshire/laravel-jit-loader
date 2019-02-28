<?php

namespace LiamWiltshire\LaravelJitLoader\Concerns;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait AutoloadsRelationships
 * @package LiamWiltshire\LaravelJitLoader\Concerns
 */
trait AutoloadsRelationships
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

    private function shouldAutoLoad(): bool
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
        $relation = $this->$method();

        if (!$relation instanceof Relation) {
            throw new LogicException(
                sprintf('%s::%s must return a relationship instance.', static::class, $method)
            );
        }

        if ($this->shouldAutoLoad()) {
            $this->parentCollection->loadMissing($method);
        }

        return tap($relation->getResults(), function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
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
