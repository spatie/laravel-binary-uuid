<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class TextualUuid extends Benchmark
{
    private $benchmarkRoundsTextualUuid;

    public function name(): string
    {
        return 'Textual UUID';
    }

    public function table()
    {
    }

    public function seed()
    {
    }

    public function withBenchmarkRoundsTextualUuid($benchmarkRoundsTextualUuid): self
    {
        $this->benchmarkRoundsTextualUuid = $benchmarkRoundsTextualUuid;

        return $this;
    }

    public function run(): InlineResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `normal_uuid_text` FROM `optimised_uuid`');

        for ($i = 0; $i < $this->benchmarkRoundsTextualUuid; $i++) {
            $uuid = $uuids[array_rand($uuids)]['normal_uuid_text'];

            $queries[] = "SELECT * FROM `optimised_uuid` WHERE `normal_uuid_text` = '$uuid';";
        }

        return $this->runQueryBenchmark($queries);
    }
}
