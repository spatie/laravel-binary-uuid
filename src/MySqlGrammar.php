<?php

namespace Spatie\BinaryUuid;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as IlluminateMySqlGrammar;

class MySqlGrammar extends IlluminateMySqlGrammar
{
    protected function typeUuid(Fluent $column)
    {
        return 'binary(16)';
    }

    protected function typeUuidText(Fluent $column)
    {
        return 'char(36)';
    }
}
