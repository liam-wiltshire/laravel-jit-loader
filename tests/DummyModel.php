<?php

namespace LiamWiltshire\LaravelJitLoader\Tests;

use LiamWiltshire\LaravelJitLoader\Model;

class DummyModel extends Model
{
    protected $fillable = ['dummy_model_id'];

    public function myRelationship()
    {
        return $this->hasOne(DummyModel::class);
    }

    public function nonExistentRelationship()
    {
        return false;
    }

    public function setAutoloadThreshold(int $autoloadThreshold)
    {
        $this->autoloadThreshold = $autoloadThreshold;
    }
}
