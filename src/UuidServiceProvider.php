<?php

namespace Spatie\Uuid;

use Illuminate\Support\ServiceProvider;
use Spatie\Uuid\MySqlGrammar;

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
