<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class OptimisedUuidFromText extends Benchmark
{
    public function name(): string
    {
        return 'Optimised UUID from text';
    }

    public function createTable()
    {
    }

    public function seedTable()
    {
    }

    public function run(): InlineResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `generated_optimised_uuid_text` FROM `optimised_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuidAsText = $uuids[array_rand($uuids)]['generated_optimised_uuid_text'];
            $uuidWithoutDash = str_replace('-', '', $uuidAsText);

            $queries[] = <<<SQL
SELECT * FROM `optimised_uuid` 
WHERE `optimised_uuid_binary` = UNHEX('$uuidWithoutDash');
SQL;
        }

        return $this->runQueryBenchmark($queries);
    }
}
