<?php

namespace Spatie\Uuid\Test;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestTable extends Migration
{
    private $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->uuid('uuid');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
