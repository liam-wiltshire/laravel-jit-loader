<?php

namespace LiamWiltshire\LaravelJitLoader\Tests;

use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelJitLoader\Concerns\AutoloadsRelationships;
use Psr\Log\LoggerInterface;

class TraitModel extends Model
{
    use AutoloadsRelationships;

    protected $fillable = ['trait_model_id'];

    public function myRelationship()
    {
        return $this->hasOne(TraitModel::class);
    }

    public function nonExistentRelationship()
    {
        return false;
    }

    public function setAutoloadThreshold(int $autoloadThreshold)
    {
        $this->autoloadThreshold = $autoloadThreshold;
    }

    public function setLogging(string $channel, LoggerInterface $logger)
    {
        $this->logChannel = $channel;
        $this->logDriver = $logger;
    }
}
