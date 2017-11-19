<?php

namespace Spatie\Uuid\Test;

use Spatie\Uuid\HasBinaryUuid;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasBinaryUuid;

    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $table = 'test';
}
