<?php

namespace Spatie\BinaryUuid;

trait HasDefaultUuidFields
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
