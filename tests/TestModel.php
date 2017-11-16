<?php

namespace Spatie\Uuid\Test;

use Illuminate\Database\Eloquent\Model;
use Spatie\Uuid\HasBinaryUuid;

class TestModel extends Model
{
    use HasBinaryUuid;

    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $table = 'test';
}
