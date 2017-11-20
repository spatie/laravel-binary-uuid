<?php

namespace Spatie\BinaryUuid;

use Illuminate\Support\ServiceProvider;

class UuidServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = app('db')->connection();

        $connection->setSchemaGrammar(new MySqlGrammar());
    }
}
