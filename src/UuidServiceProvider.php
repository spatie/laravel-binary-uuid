<?php

namespace Spatie\BinaryUuid;

use Illuminate\Support\ServiceProvider;
use Spatie\BinaryUuid\MySqlGrammar;

class UuidServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var \Illuminate\Database\Connection $connection */
        $connection = app('db')->connection();

        $connection->setSchemaGrammar(new MySqlGrammar());
    }

    public function register()
    {
        //
    }
}
