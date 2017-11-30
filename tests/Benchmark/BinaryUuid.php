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
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `normal_uuid`;

CREATE TABLE `normal_uuid` (
    `uuid` BINARY(16) NOT NULL,
    `uuid_text` char(36) generated always as
        (insert(
            insert(
                insert(
                    insert(hex(uuid),9,0,'-'),
                14,0,'-'),
            19,0,'-'),
            24,0,'-')
        ) virtual,
    `text` TEXT NOT NULL,

    PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seedTable()
    {
        $queries = [];

        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = Uuid::uuid1()->toString();

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $queries[] = <<<SQL
INSERT INTO `normal_uuid` (`uuid`, `text`) VALUES (UNHEX(REPLACE("$uuid", "-","")), '$text');
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
        $uuids = $this->connection->fetchAll('SELECT `uuid_text` FROM `normal_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid_text'];

            $queries[] = "SELECT * FROM `normal_uuid` WHERE `uuid` = UNHEX(REPLACE('$uuid', '-', ''));";
        }

        return $this->runQueryBenchmark($queries);
    }
}
