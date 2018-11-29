<?php

namespace Spatie\BinaryUuid;

use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar as IlluminateMySqlGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar as IlluminateSQLiteGrammar;

class UuidServiceProvider extends ServiceProvider
{
    public function boot()
    {
        
        $this->optimizeUuids();
    }

    protected function optimizeUuids()
    {
        $factory = new UuidFactory();

        $codec = new OrderedTimeCodec($factory->getUuidBuilder());

        $factory->setCodec($codec);

        Uuid::setFactory($factory);
    }
}
