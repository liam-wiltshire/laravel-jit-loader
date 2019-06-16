<?php

namespace LiamWiltshire\LaravelJitLoader\Tests;

use Illuminate\Database\Eloquent\Model;
use Psr\Log\LoggerInterface;

class TraitlessModel extends Model
{
    protected $fillable = ['traitless_model_id'];

    public function myRelationship()
    {
        return $this->hasOne(TraitlessModel::class);
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
