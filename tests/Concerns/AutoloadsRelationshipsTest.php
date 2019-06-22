<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 28/02/19
 * Time: 10:48
 */

namespace LiamWiltshire\LaravelJitLoader\Tests\Concerns;

use LiamWiltshire\LaravelJitLoader\Tests\TestCase;
use LiamWiltshire\LaravelJitLoader\Tests\TraitlessModel;
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
        $models[0]->disableAutoload()->myRelationship;

        $this->assertFalse($models[1]->relationLoaded('myRelationship'));
    }

    public function testGetRelationshipFromMethodOverThresholdDoesntAutoLoad()
    {
        $models = TraitModel::all();
        $models[0]->setAutoloadThreshold(2);
        $models[0]->myRelationship;

        $this->assertFalse($models[1]->relationLoaded('myRelationship'));
    }


    public function testGetRelationshipFromMethodUnderThresholdDoesAutoLoad()
    {
        $models = TraitModel::all();
        $models[0]->myRelationship;

        $this->assertTrue($models[1]->relationLoaded('myRelationship'));
    }

    public function testGetRelationshipFromMethodUnderThresholdDoesAutoLoadWithLogging()
    {
        $message = "[LARAVEL-JIT-LOADER] Relationship " . TraitModel::class . "::myRelationship was JIT-loaded.";
        $context = [
            'relationship' => TraitModel::class.'::myRelationship',
            'file' => __FILE__,
            'line' => __LINE__ + 14,
            'view' => false,
        ];

        $driver = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $driver
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with($message, $context)
            ->willReturn(true);

        $models = TraitModel::all();
        $models[0]->setLogging('jitLogger', $driver);

        $models[0]->myRelationship;

        $this->assertTrue($models[1]->relationLoaded('myRelationship'));
    }

    public function testPerformance()
    {
        $startTime = microtime(true);
        $this->db->getConnection()->flushQueryLog();
        $models = TraitModel::all();

        foreach ($models as $model) {
            $model->myRelationship;
        }

        $traitedCount = count($this->db->getConnection()->getQueryLog());
        $traitedTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        $this->db->getConnection()->flushQueryLog();

        $models = TraitlessModel::all();

        foreach ($models as $model) {
            $model->myRelationship;
        }

        $traitlessCount = count($this->db->getConnection()->getQueryLog());
        $traitlessTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        $this->db->getConnection()->flushQueryLog();

        $models = TraitlessModel::with('myRelationship')->get();

        foreach ($models as $model) {
            $model->myRelationship;
        }

        $eagerCount = count($this->db->getConnection()->getQueryLog());
        $eagerTime = microtime(true) - $startTime;



        $this->messages[] = "Using Trait: {$traitedCount} queries in {$traitedTime}s";
        $this->messages[] = "Lazy Loading: {$traitlessCount} queries in {$traitlessTime}s";
        $this->messages[] = "Eager Loading: {$eagerCount} queries in {$eagerTime}s";

        $this->assertTrue($traitedCount < $traitlessCount);
        $this->assertTrue($traitedTime < $traitlessTime);
    }
}
