<?php

namespace Spatie\BinaryUuid\Test;

use Spatie\BinaryUuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\Model;

class TestModelComposite extends Model
{
    use HasBinaryUuid;

    protected $uuids = [
        'another_uuid',
    ];

    protected $primaryKey = [
        'first_id', 
        'second_id',
    ];

    protected $uuidSuffix = '_text';

    protected $table = 'test_composite';

    public function setUuidSuffix($suffix = '_text')
    {
        $this->uuidSuffix = $suffix;
    }
}
