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
        /** @var \Illuminate\Database\Connection $connection */
        $connection = app('db')->connection();

        $connection->setSchemaGrammar($this->createGrammarFromConnection($connection));

        $this->app->singleton(OrderedTimeCodec::class, function () {
            return new OrderedTimeCodec((new UuidFactory)->getUuidBuilder());
        });
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
            $grammar = new SQLiteGrammar();
        } else {
            $grammar = new MySqlGrammar();
        }

        $grammar->setTablePrefix($queryGrammar->getTablePrefix());

        return $grammar;
    }
}
