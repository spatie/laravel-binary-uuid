<?php

namespace Spatie\BinaryUuid\Test;

use Spatie\BinaryUuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasBinaryUuid;

    protected $table = 'test';
}
