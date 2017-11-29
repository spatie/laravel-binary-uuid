<?php

namespace Spatie\BinaryUuid;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar as IlluminateMySqlGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar as IlluminateSQLiteGrammar;
use Ramsey\Uuid\Codec\OrderedTimeCodec;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;

class UuidServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = app('db')->connection();

        $connection->setSchemaGrammar($this->createGrammarFromConnection($connection));

        $this->optimizeUuids();
    }

    protected function createGrammarFromConnection(Connection $connection): Grammar
    {
        $queryGrammar = $connection->getQueryGrammar();

        $queryGrammarClass = get_class($queryGrammar);

        if (! in_array($queryGrammarClass, [
            IlluminateMySqlGrammar::class,
            IlluminateSQLiteGrammar::class,
        ])) {
            throw new Exception("There current grammar `$queryGrammarClass` doesn't support binary uuids. Only  MySql and SQLite connections are supported.");
        }

        if ($queryGrammar instanceof IlluminateSQLiteGrammar) {
            return new SQLiteGrammar();
        }

        return new MySqlGrammar();
    }

    protected function optimizeUuids()
    {
        $factory = new UuidFactory();

        $codec = new OrderedTimeCodec($factory->getUuidBuilder());

        $factory->setCodec($codec);

        Uuid::setFactory($factory);
    }
}
