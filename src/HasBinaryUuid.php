<?php

namespace Spatie\BinaryUuid;

use Exception;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

trait HasBinaryUuid
{
    protected static function bootHasBinaryUuid()
    {
        static::creating(function (Model $model) {
            $uuidAttributes = $model->getUuidAttributes();

            foreach ($uuidAttributes as $key) {
                if ($model->{$key}) {
                    continue;
                }

                $model->{$key} = static::encodeUuid(static::generateUuid());
            }
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

    public static function generateUuid() : string
    {
        return Uuid::uuid1();
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

    public function isBinary($uuidStr)
    {
        return preg_match('~[^\x20-\x7E\t\r\n]~', $uuidStr) > 0;
    }

    public function getUuidKeys()
    {
        return (property_exists($this, 'uuids') && is_array($this->uuids)) ? $this->uuids : [];
    }

    public function toArray()
    {
        $uuidAttributes = $this->getUuidAttributes();

        $array = parent::toArray();
        $pivotUuids = [];

        if (! $this->exists || ! is_array($uuidAttributes)) {
            return $array;
        }

        foreach ($uuidAttributes as $attributeKey) {
            if (! array_key_exists($attributeKey, $array)) {
                continue;
            }
            $uuidKey = $this->getRelatedBinaryKeyName($attributeKey);
            $array[$attributeKey] = $this->{$uuidKey};
        }

        if (isset($array['pivot'])) {
            $pivotUuids = $array['pivot'];

            if (! is_array($pivotUuids)) {
                $pivotUuids = get_object_vars($pivotUuids);
            }

            foreach ($pivotUuids as $key => $uuid) {
                if ($this->isBinary($uuid)) {
                    $pivotUuids[$key] = $this->decodeUuid($uuid);
                }
            }

            $array['pivot'] = $pivotUuids;
        }

        return $array;
    }

    public function getRelatedBinaryKeyName($attribute): string
    {
        $suffix = $this->getUuidSuffix();

        return preg_match('/(?:[a-zA-Z_]+)?id/i', $attribute) ? "{$attribute}{$suffix}" : $attribute;
    }

    public function getAttribute($key)
    {
        $uuidKey = $this->uuidTextAttribute($key);

        if ($uuidKey && $this->{$uuidKey} !== null) {
            return static::decodeUuid($this->{$uuidKey});
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->uuidTextAttribute($key)) {
            $value = static::encodeUuid($value);
        }

        return parent::setAttribute($key, $value);
    }

    protected function getUuidSuffix()
    {
        return (property_exists($this, 'uuidSuffix')) ? $this->uuidSuffix : '_text';
    }

    protected function uuidTextAttribute($key)
    {
        $uuidAttributes = $this->getUuidAttributes();
        $suffix = $this->getUuidSuffix();
        $offset = -(strlen($suffix));

        if (substr($key, $offset) == $suffix && in_array(($uuidKey = substr($key, 0, $offset)), $uuidAttributes)) {
            return $uuidKey;
        }

        return false;
    }

    public function getUuidAttributes()
    {
        $uuidKeys = $this->getUuidKeys();

        // non composite primary keys will return a string so casting required
        $key = (array) $this->getKeyName();

        $uuidAttributes = array_unique(array_merge($uuidKeys, $key));

        return $uuidAttributes;
    }

    public function getUuidTextAttribute(): ?string
    {
        $key = $this->getKeyName();

        if (! $this->exists || is_array($key)) {
            return null;
        }

        return static::decodeUuid($this->{$key});
    }

    public function setUuidTextAttribute(string $uuid)
    {
        $key = $this->getKeyName();

        if (! is_string($key)) {
            throw new Exception('composite keys not allowed for attribute mutation');
        }

        $this->{$key} = static::encodeUuid($uuid);
    }

    public function getQueueableId()
    {
        return base64_encode($this->{$this->getKeyName()});
    }

    public function newQueryForRestoration($id)
    {
        return $this->newQueryWithoutScopes()->whereKey(base64_decode($id));
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function getRouteKeyName()
    {
        $key = $this->primaryKey;

        $keyName = is_string($key) ? $this->strUuidSuffix($key) : array_map([&$this, 'strUuidSuffix'], $key);

        return $keyName;
    }

    public function resolveRouteBinding($value)
    {
        $keyName = $this->getRouteKeyName();

        if (is_array($keyName)) {
            $value = explode(':', strval($value), count($keyName));

            return $this->where(array_combine($keyName, $value))->frist();
        }

        return $this->where($keyName, $value)->first();
    }

    public function getKeyName()
    {
        return (! property_exists($this, 'primaryKey') || $this->primaryKey === 'id') ? 'uuid' : $this->primaryKey;
    }

    public function getIncrementing()
    {
        return false;
    }

    public function resolveRouteBinding($value)
    {
        return $this->withUuid($value)->first();
    }

    public function strUuidSuffix($str)
    {
        $suffix = $this->getUuidSuffix();

        return "{$str}{$suffix}";
    }

    private function decodeIdArray($ids)
    {
        foreach ($ids as $key => $id) {
            $ids[$key] = base64_decode($id);
        }

        return $ids;
    }
}
