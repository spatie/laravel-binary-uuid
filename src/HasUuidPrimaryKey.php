<?php

namespace Spatie\BinaryUuid;

trait HasUuidPrimaryKey
{
    public function getIdAttribute()
    {
        return $this->uuid_text;
    }
    
    public function getKeyName()
    {
        return 'uuid';
    }

    public function getIncrementing()
    {
        return false;
    }
}
