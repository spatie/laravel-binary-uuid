<?php

namespace Spatie\BinaryUuid\Test\Feature;

use Ramsey\Uuid\Uuid;
use Spatie\BinaryUuid\Test\TestCase;
use Spatie\BinaryUuid\Test\TestModel;
use Spatie\BinaryUuid\Test\TestModelComposite;

class HasBinaryUuidTest extends TestCase
{
    use CreatesModel;

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

    /** @test */
    public function it_can_query_a_relation_with_scope()
    {
        $uuid = Uuid::uuid1();
        $relationUuid = Uuid::uuid1();

        $this->createModel($uuid, $relationUuid);

        $model = TestModel::withUuid($relationUuid, 'relation_uuid')->first();

        $this->assertNotNull($model);
    }

    /** @test */
    public function it_decodes_columns_besides_primary_uuid_when_turned_to_an_array()
    {
        $uuid = Uuid::uuid1();
        $relationUuid = Uuid::uuid1();

        $model = $this->createModel($uuid, $relationUuid);

        $modelArray = $model->toArray();

        $this->assertNotNull($model);
        $this->assertCount(4, $modelArray);
        $this->assertTrue(array_key_exists('relation_uuid', $modelArray));
        $this->assertEquals($modelArray['relation_uuid'], $model->relation_uuid_text);
    }

    /** @test */
    public function it_should_use_custom_suffix_when_specified()
    {
        $uuid = Uuid::uuid1();

        $model = $this->createModel($uuid);

        $model->setUuidSuffix('_str');

        $modelArray = $model->toArray();

        $this->assertNotNull($model);
        $this->assertTrue(array_key_exists('uuid', $modelArray));
        $this->assertEquals($modelArray['uuid'], $model->uuid_str);
    }

    /** @test */
    public function it_can_query_multiple_relations_with_scope()
    {
        $relationUuid1 = Uuid::uuid1();
        $relationUuid2 = Uuid::uuid1();

        $uuid1 = Uuid::uuid1();
        $this->createModel($uuid1, $relationUuid1);

        $uuid2 = Uuid::uuid1();
        $this->createModel($uuid2, $relationUuid1);

        $uuid3 = Uuid::uuid1();
        $this->createModel($uuid3, $relationUuid2);

        $models = TestModel::withUuid($relationUuid1, 'relation_uuid')->get();

        $this->assertCount(2, $models);
    }

    /** @test */
    public function it_prevents_double_decoding()
    {
        $uuid = Uuid::uuid1();

        $decodedUuid = TestModel::decodeUuid($uuid);

        $this->assertEquals($uuid, $decodedUuid);
    }

    /** @test */
    public function it_prevents_double_encoding()
    {
        $uuid = Uuid::uuid1();

        $encodeUuid = TestModel::encodeUuid($uuid);

        $decodedUuid = TestModel::encodeUuid($encodeUuid);

        $this->assertEquals($encodeUuid, $decodedUuid);
    }

    /** @test */
    public function it_generates_valid_routes()
    {
        $uuid = Uuid::uuid1();

        $model = $this->createModel($uuid);

        app('router')->get('uuid-test/{model}')->name('uuid-test');

        $this->assertContains("/uuid-test/{$uuid}", route('uuid-test', $model));
    }

    /** @test */
    public function it_serialises_the_model_correctly()
    {
        $model = TestModel::create();

        $json = $model->toJson();

        $this->assertContains($model->uuid_text, $json);
        $this->assertNotContains($model->uuid, $json);
    }

    /** @test */
    public function it_serialises_the_model_correctly_with_json_encode()
    {
        $model = TestModel::create();

        $json = json_encode($model);

        $this->assertContains($model->uuid_text, $json);
        $this->assertNotContains($model->uuid, $json);
    }

    /** @test */
    public function it_generates_the_uuids_on_save_for_composite_keys()
    {
        $model = new TestModelComposite();

        $this->assertNull($model->first_id);
        $this->assertNull($model->second_id);

        $model->prop_val = 'somevalue';

        $model->save();

        $this->assertNotNull($model->first_id);
        $this->assertNotNull($model->second_id);
    }

    /** @test */
    public function it_decodes_the_uuids_when_attributes_are_retrieved_for_composite_keys()
    {
        $model = new TestModelComposite();

        $model->prop_val = 'anothervalue';

        $model->save();

        $model->setUuidSuffix('_str');

        $this->assertTrue(Uuid::isValid($model->first_id_str));
        $this->assertTrue(Uuid::isValid($model->second_id_str));
    }

    /** @test */
    public function it_prevents_decoding_the_uuid_when_the_model_does_not_exist()
    {
        $model = new TestModel;

        $this->assertEmpty($model->toArray());
        $this->assertNull($model->uuid_text);
    }

    /** @test */
    public function it_generates_uuids_in_binary_form()
    {
        $binaryUuid = TestModel::generateUuid();

        $this->assertTrue(Uuid::isValid(TestModel::decodeUuid($binaryUuid)));
    }

    /** @test */
    public function it_prevents_decoding_model_key_when_it_is_not_included_in_attributes()
    {
        $model = TestModel::create();
        $model->setRawAttributes(['test' => 'test']);
        $array = $model->toArray();

        $this->assertFalse(isset($array[$model->getKeyName()]));
    }

    /** @test */
    /*public function restoration_query_supports_arrays_and_returns_models()
    {
        $model1 = TestModel::create();
        $model2 = TestModel::create();

        $ids = [$model1->uuid, $model2->uuid];

        $testModel = new TestModel;
        $query = $testModel->newQueryForRestoration($ids);

        $this->assertNotNull($query);

        $models = $query->get()->toArray();

        $this->assertTrue(in_array($model1, $models));
        $this->assertTrue(in_array($model2, $models));
    }*/

    /** @test */
    public function it_finds_a_model_from_its_textual_uuid_too()
    {
        $model = TestModel::create();

        $this->assertTrue($model->is(TestModel::find($model->uuid)));
        $this->assertTrue($model->is(TestModel::find($model->uuid_text)));
    }

    /** @test */
    public function it_finds_many_models_from_their_textual_uuids()
    {
        $model1 = TestModel::create();
        $model2 = TestModel::create();
        $model3 = TestModel::create();

        $this->assertCount(2, TestModel::find([$model1->uuid, $model3->uuid]));
        $this->assertCount(2, TestModel::findMany([$model1->uuid, $model3->uuid]));

        $this->assertCount(2, TestModel::find([$model1->uuid_text, $model3->uuid_text]));
        $this->assertCount(2, TestModel::findMany([$model1->uuid_text, $model3->uuid_text]));
    }

    /** @test */
    public function it_finds_or_fails_a_model_from_its_textual_uuid()
    {
        $model = TestModel::create();

        $this->assertTrue($model->is(TestModel::findOrFail($model->uuid)));
        $this->assertTrue($model->is(TestModel::findOrFail($model->uuid_text)));
    }
}
