<?php

namespace Spatie\Uuid\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;
use Illuminate\Support\Fluent;

class MySqlGrammar extends IlluminateMySqlGrammar
{
    protected function typeUuid(Fluent $column)
    {
        return 'binary(16)';
    }
}
