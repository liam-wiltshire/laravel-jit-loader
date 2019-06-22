<?php

namespace LiamWiltshire\LaravelJitLoader\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\LogManager;
use LogicException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

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

    /**
     * @var ?LoggerInterface
     */
    protected $logDriver;


    /**
     * Check to see if we should autoload
     * @return bool
     */
    private function shouldAutoLoad(): bool
    {
        return ($this->parentCollection
            && count($this->parentCollection) > 1
            && count($this->parentCollection) <= $this->autoloadThreshold);
    }

    /**
     * @codeCoverageIgnore
     */
    private function getLogDriver()
    {
        if (!$this->logDriver) {
            /**
             * @var LogManager $logManager
             */
            $logManager = app(LogManager::class);
            $this->logDriver = $logManager->channel($this->logChannel);
        }
    }

    /**
     * @param string $file
     * @return bool|string
     * @codeCoverageIgnore
     */
    private function getBlade(string $file)
    {
        if (strpos($file, "framework/views/") === false) {
            return false;
        }

        $blade = file($file)[0];
        return trim(str_replace(["<?php /* ", " */ ?>"], "", $blade));
    }

    /**
     * Log the fact we have used the JIT loader, if required
     *
     * @param string $relationship
     * @param string $file
     * @param int $lineNo
     * @return bool
     */
    private function logAutoload(string $relationship)
    {
        if (!isset($this->logChannel)) {
            return false;
        }

        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[4];

        $this->getLogDriver();

        $file = $stack['file'];
        
        $this->logDriver->info(
            '[LARAVEL-JIT-LOADER] Relationship '.static::class.'::'.$relationship.' was JIT-loaded.',
            [
                'relationship' => static::class.'::'.$relationship,
                'file' => $file,
                'line' => $stack['line'],
                'view' => $this->getBlade($file),
            ]
        );
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
            if (!$this->relationLoaded($method)) {
                $this->logAutoload($method);
                $this->parentCollection->load($method);

                return $this->relations[$method];
            }
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

        foreach ($collection as $model) {
            $model->parentCollection = $collection;
        }

        return $collection;
    }

    /**
     * Disable autoloading of relationships on this model
     * @return Model
     */
    public function disableAutoload(): Model
    {
        $this->autoloadThreshold = 0;
        return $this;
    }
}
