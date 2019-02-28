<?php

namespace LiamWiltshire\LaravelJitLoader\Tests;


use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelJitLoader\Concerns\AutoloadsRelationships;

class TraitModel extends Model {

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
}