<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 28/02/19
 * Time: 10:48
 */

namespace LiamWiltshire\LaravelJitLoader\Tests\Concerns;

use Illuminate\Log\LogManager;
use LiamWiltshire\LaravelJitLoader\Tests\TestCase;
use LiamWiltshire\LaravelJitLoader\Tests\TraitModel;
use Psr\Log\LoggerInterface;

class AutoloadsRelationshipsTest extends TestCase
{
    public function testGetRelationshipFromMethodWithNonExistentRelationshipThrowsException()
    {
        $model = new TraitModel();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('TraitModel::nonExistentRelationship must return a relationship instance.');
        $model->getRelationshipFromMethod('nonExistentRelationship');
    }

    public function testGetRelationshipFromMethodAfterDisableAutoLoadCalledDoesntAutoLoad()
    {
        $models = TraitModel::all();
        $related = $models[0]->disableAutoload()->myRelationship;

        $this->assertFalse($models[1]->relationLoaded('myRelationship'));
    }

    public function testGetRelationshipFromMethodOverThresholdDoesntAutoLoad()
    {
        $models = TraitModel::all();
        $models[0]->setAutoloadThreshold(2);
        $related = $models[0]->myRelationship;

        $this->assertFalse($models[1]->relationLoaded('myRelationship'));
    }


    public function testGetRelationshipFromMethodUnderThresholdDoesAutoLoad()
    {
        $models = TraitModel::all();
        $related = $models[0]->myRelationship;

        $this->assertTrue($models[1]->relationLoaded('myRelationship'));
    }

    public function testGetRelationshipFromMethodUnderThresholdDoesAutoLoadWithLogging()
    {

        $driver = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $driver->expects($this->atLeastOnce())->method('info')->willReturn(true);

        $models = TraitModel::all();
        $models[0]->setLogging('jitLogger', $driver);

        $related = $models[0]->myRelationship;

        $this->assertTrue($models[1]->relationLoaded('myRelationship'));
    }
}