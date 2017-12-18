<?php

namespace Spatie\BinaryUuid;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as IlluminateSQLiteGrammar;

class SQLiteGrammar extends IlluminateSQLiteGrammar
{
    protected function typeBinaryUuid(Fluent $column)
    {
        return 'binary(16)';
    }
}
