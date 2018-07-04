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

            $model->{$model->getKeyName()} = static::generateUuid();
        });
    }

    public static function scopeWithUuid(Builder $builder, $uuid, $field = null): Builder
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

    public static function generateUuid(bool $asText = false) : string
    {
        $uuid = Uuid::uuid1();
        return $asText ? (string) $uuid : static::encodeUuid($uuid);
    }

    public static function encodeUuid($uuid): string
    {
        if (! Uuid::isValid($uuid)) {
            return $uuid;
        }

        if (! $uuid instanceof Uuid) {
            $uuid = Uuid::fromString($uuid);
        }

        return $uuid->getBytes();
    }

    public static function decodeUuid(string $binaryUuid): string
    {
        if (Uuid::isValid($binaryUuid)) {
            return $binaryUuid;
        }

        return Uuid::fromBytes($binaryUuid)->toString();
    }

    public function toArray()
    {
        if (! $this->exists) {
            return parent::toArray();
        }

        return array_merge(parent::toArray(), [$this->getKeyName() => $this->uuid_text]);
    }

    public function getUuidTextAttribute(): ?string
    {
        if (! $this->exists) {
            return null;
        }

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

    public function getRouteKeyName()
    {
        return 'uuid_text';
    }

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
