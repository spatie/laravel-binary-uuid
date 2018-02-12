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

    public function resolveRouteBinding($value)
    {
        return $this->withUuid($value)->first();
    }
}
