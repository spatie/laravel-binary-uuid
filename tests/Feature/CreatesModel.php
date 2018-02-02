<?php

namespace Spatie\BinaryUuid\Test\Feature;

use Spatie\BinaryUuid\Test\TestModel;

trait CreatesModel
{
    private function createModel(string $uuid, $relationUuid = null): TestModel
    {
        $model = new TestModel();

        $model->uuid_text = $uuid;

        if ($relationUuid) {
            $model->relation_uuid = TestModel::encodeUuid($relationUuid);
        }

        $model->save();

        return $model;
    }
    
}