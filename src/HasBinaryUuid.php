<?php

namespace Spatie\BinaryUuid;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public static function scopeWithUuid(Builder $builder, $uuid, ?string $field = null): Builder
    {
        if ($field) {
            return static::scopeWithUuidRelation($builder, $uuid, $field);
        }

        if ($uuid instanceof Uuid) {
            $uuid = (string) $uuid;
        }

        $uuid = (array) $uuid;

        return $builder->whereKey(array_map(function (string $modelUuid) {
            return static::encodeUuid($modelUuid);
        }, $uuid));
    }

    public static function scopeWithUuidRelation(Builder $builder, $uuid, string $field): Builder
    {
        if ($uuid instanceof Uuid) {
            $uuid = (string) $uuid;
        }

        $uuid = (array) $uuid;

        return $builder->whereIn($field, array_map(function (string $modelUuid) {
            return static::encodeUuid($modelUuid);
        }, $uuid));
    }

    public static function encodeUuid(string $uuid): string
    {
        if (!Uuid::isValid($uuid)) {
            return $uuid;
        }

        $uuid = str_replace('-', '', (string) $uuid);

        return
            substr(hex2bin($uuid), 6, 2)
            .substr(hex2bin($uuid), 4, 2)
            .substr(hex2bin($uuid), 0, 4)
            .substr(hex2bin($uuid), 8, 8);
    }

    public static function decodeUuid(string $binaryUuid): string
    {
        if (Uuid::isValid($binaryUuid)) {
            return $binaryUuid;
        }

        $uuidWithoutDashes = bin2hex(
            substr($binaryUuid, 4, 4)
            .substr($binaryUuid, 2, 2)
            .substr($binaryUuid, 0, 2)
            .substr($binaryUuid, 8, 8)
        );

        $uuidWithDashes = collect([8, 13, 18, 23])->reduce(function ($uuid, $position) {
            return substr_replace($uuid, '-', $position, 0);
        }, $uuidWithoutDashes);

        return $uuidWithDashes;
    }

    public function getUuidTextAttribute(): string
    {
        return static::decodeUuid($this->{$this->getKeyName()});
    }

    public function setUuidTextAttribute(string $uuid)
    {
        $this->{$this->getKeyName()} = static::encodeUuid($uuid);
    }

    public function getQueueableId()
    {
        return base64_encode($this->{$this->getKeyName()});
    }

    public function newQueryForRestoration($id)
    {
        return $this->newQueryWithoutScopes()->whereKey(base64_decode($id));
    }
}
