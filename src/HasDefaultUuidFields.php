<?php

namespace Spatie\BinaryUuid;

trait HasUuidPrimaryKey
{
    public function getKeyName()
    {
        return 'uuid';
    }

    public function getIncrementing()
    {
        return false;
    }
}
