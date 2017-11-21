<?php

namespace Spatie\BinaryUuid;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
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
    }

    protected function createGrammarFromConnection(Connection $connection): Grammar
    {
        $queryGrammar = $connection->getQueryGrammar();

        if (! in_array(
            get_class($queryGrammar),
            [IlluminateMySqlGrammar::class, IlluminateSQLiteGrammar::class]
        )) {
            throw new Exception("spatie/laravel-binary-uuid only supports MySql or SQLite connections. The current connection doesn't match those criteria.");
        }

        if ($queryGrammar instanceof IlluminateSQLiteGrammar) {
            return new SQLiteGrammar();
        }

        return new MySqlGrammar();
    }
}
