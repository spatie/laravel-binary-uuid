<?php

namespace Spatie\BinaryUuid;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as IlluminateSQLiteGrammar;

class SQLiteGrammar extends IlluminateSQLiteGrammar
{
    protected function typeUuid(Fluent $column)
    {
        return 'blob(256)';
    }
}
