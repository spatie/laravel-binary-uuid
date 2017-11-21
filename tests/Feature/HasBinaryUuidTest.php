<?php

namespace Spatie\BinaryUuid\Test\Unit;

use Ramsey\Uuid\Uuid;
use Spatie\BinaryUuid\Test\TestCase;
use Spatie\BinaryUuid\Test\TestModel;

class HasBinaryUuidTest extends TestCase
{
    /** @test */
    public function it_generates_the_uuid_on_save()
    {
        $model = new TestModel();

        $this->assertNull($model->uuid);

        $model->save();

        $this->assertNotNull($model->uuid);
    }

    /** @test */
    public function it_generates_the_uuid_when_set_via_uuid_text()
    {
        $uuid = Uuid::uuid1();

        $model = new TestModel();

        $model->uuid_text = $uuid;

        $model->save();

        $this->assertNotNull($model->uuid);
        $this->assertEquals((string) $uuid, $model->uuid_text);
    }

    /** @test */
    public function it_finds_a_model_with_uuid_scope()
    {
        $uuid = Uuid::uuid1();
        $this->createModel($uuid);

        $model = TestModel::withUuid($uuid)->first();

        $this->assertEquals((string) $uuid, $model->uuid_text);
    }

    /** @test */
    public function it_finds_multiple_models_with_uuid_scope()
    {
        $uuid1 = Uuid::uuid1();
        $this->createModel($uuid1);

        $uuid2 = Uuid::uuid1();
        $this->createModel($uuid2);

        $uuid3 = Uuid::uuid1();
        $this->createModel($uuid3);

        $models = TestModel::withUuid([$uuid1, $uuid2])->get();

        $this->assertCount(2, $models);
        $this->assertEquals((string) $uuid1, $models[0]->uuid_text);
        $this->assertEquals((string) $uuid2, $models[1]->uuid_text);
    }

    private function createModel(string $uuid): TestModel
    {
        $model = new TestModel();

        $model->uuid_text = $uuid;

        $model->save();

        return $model;
    }
}
