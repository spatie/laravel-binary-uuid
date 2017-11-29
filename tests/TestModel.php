<?php

namespace Spatie\BinaryUuid\Test;

use Spatie\BinaryUuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\BinaryUuid\HasUuidPrimaryKey;

class TestModel extends Model
{
    use HasBinaryUuid;
    use HasUuidPrimaryKey;

    protected $table = 'test';
}
