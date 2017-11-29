<?php

namespace Spatie\BinaryUuid\Test;

use Spatie\BinaryUuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\BinaryUuid\HasDefaultUuidFields;

class TestModel extends Model
{
    use HasBinaryUuid;
    use HasDefaultUuidFields;

    protected $table = 'test';
}
