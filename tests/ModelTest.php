<?php

namespace LiamWiltshire\LaravelJitLoader\Tests;

class ModelTest extends TestCase
{
    public function testGetRelationshipFromMethodWithNonExistentRelationshipThrowsException()
    {
        $model = new DummyModel();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('DummyModel::nonExistentRelationship must return a relationship instance.');
        $model->getRelationshipFromMethod('nonExistentRelationship');
    }

    public function testGetRelationshipFromMethodAfterDisableAutoLoadCalledDoesntAutoLoad()
    {
        $models = DummyModel::all();
        $models[0]->disableAutoload()->myRelationship;

        $this->assertFalse($models[1]->relationLoaded('myRelationship'));
    }

    public function testGetRelationshipFromMethodOverThresholdDoesntAutoLoad()
    {
        $models = DummyModel::all();
        $models[0]->setAutoloadThreshold(2);
        $models[0]->myRelationship;

        $this->assertFalse($models[1]->relationLoaded('myRelationship'));
    }


    public function testGetRelationshipFromMethodUnderThresholdDoesAutoLoad()
    {
        $models = DummyModel::all();
        $models[0]->myRelationship;

        $this->assertTrue($models[1]->relationLoaded('myRelationship'));
    }
}
