<?php
/**
 * Created by PhpStorm.
 * User: liam
 * Date: 24/02/19
 * Time: 18:17
 */

namespace LiamWiltshire\LaravelJitLoader\Tests;


use Illuminate\Database\Capsule\Manager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->configureDatabase();
        $this->migrateIdentitiesTable();
    }
    protected function configureDatabase()
    {
        $db = new Manager();
        $db->addConnection(array(
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ));
        $db->bootEloquent();
        $db->setAsGlobal();
    }

    public function migrateIdentitiesTable()
    {
        Manager::schema()->create('dummy_models', function($table) {
            $table->increments('id');
            $table->integer('dummy_model_id');
            $table->timestamps();
        });
        DummyModel::create(array('dummy_model_id' => 5));
        DummyModel::create(array('dummy_model_id' => 4));
        DummyModel::create(array('dummy_model_id' => 3));
        DummyModel::create(array('dummy_model_id' => 2));
        DummyModel::create(array('dummy_model_id' => 1));

    }
}