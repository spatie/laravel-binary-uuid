<?php

namespace Spatie\BinaryUuid\Test;

use Spatie\BinaryUuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasBinaryUuid;

    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $table = 'test';
}
