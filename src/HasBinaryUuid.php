<?php

namespace Spatie\Uuid;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait HasBinaryUuid
{
    protected static function bootHasBinaryUuid()
    {
        static::creating(function (Model $model) {
            if ($model->{$model->getKeyName()}) {
                return;
            }

            $model->{$model->getKeyName()} = static::encodeUuid(Uuid::uuid1());
        });
    }

    public static function scopeWithUuid(Builder $builder, $uuid): Builder
    {
        if (is_array($uuid)) {
            return $builder->whereIn('uuid', array_map(function(string $modelUuid) {
                return static::encodeUuid($modelUuid);
            }, $uuid));
        }

        return $builder->where('uuid', static::encodeUuid($uuid));
    }

    public static function encodeUuid(string $uuid): string
    {
        $uuid = str_replace('-', '', (string) $uuid);

        return
            substr(hex2bin($uuid), 6, 2)
            . substr(hex2bin($uuid), 4, 2)
            . substr(hex2bin($uuid), 0, 4)
            . substr(hex2bin($uuid), 8, 8);
    }

    public static function decodeUuid(string $binary): string
    {
        $uuid = bin2hex(
            substr($binary, 4, 4)
            . substr($binary, 2, 2)
            . substr($binary, 0, 2)
            . substr($binary, 8, 8)
        );

        collect([8, 13, 18, 23])->each(function ($position) use (&$uuid) {
            $uuid = substr_replace($uuid, '-', $position, 0);
        });

        return $uuid;
    }

    public function getUuidTextAttribute(): string
    {
        return static::decodeUuid($this->{$this->getKeyName()});
    }

    public function setUuidTextAttribute(string $uuid)
    {
        $this->{$this->getKeyName()} = static::encodeUuid($uuid);
    }
}
