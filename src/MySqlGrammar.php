<?php

namespace Spatie\BinaryUuid;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;

class MySqlGrammar extends IlluminateMySqlGrammar
{
    protected function typeBinaryUuid(Fluent $column)
    {
        return 'binary(16)';
    }
}
