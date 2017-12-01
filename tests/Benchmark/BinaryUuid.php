<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Ramsey\Uuid\Uuid;
use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class BinaryUuid extends Benchmark
{
    public function name(): string
    {
        return 'Binary UUID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<SQL
DROP TABLE IF EXISTS `binary_uuid`;

CREATE TABLE `binary_uuid` (
    `uuid` BINARY(16) NOT NULL,
    `text` TEXT NOT NULL,
    
    KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
    }

    public function seedTable()
    {
        $queries = [];

        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = str_replace('-', '', Uuid::uuid1()->toString());

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $queries[] = <<<SQL
INSERT INTO `binary_uuid` (`uuid`, `text`) VALUES (UNHEX('$uuid'), "$i $text");
SQL;

            if (count($queries) > $this->flushAmount) {
                $this->connection->exec(implode('', $queries));
                $queries = [];
            }
        }

        if (count($queries)) {
            $this->connection->exec(implode('', $queries));
        }
    }

    public function run(): InlineResult
    {
        $queries = [];
        $uuids = $this->connection->fetchAll('SELECT `uuid` FROM `binary_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid'];

            $queries[] = 'SELECT * FROM `binary_uuid` WHERE `uuid` = "$uuid";';
        }

        return $this->runQueryBenchmark($queries);
    }
}
