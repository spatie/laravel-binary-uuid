<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Ramsey\Uuid\Uuid;
use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class TextualUuid extends Benchmark
{
    public function name(): string
    {
        return 'Textual UUID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<SQL
DROP TABLE IF EXISTS `textual_uuid`;

CREATE TABLE `textual_uuid` (
    `uuid` CHAR(36) NOT NULL,
    `text` TEXT NOT NULL,
    
    KEY (`uuid`)
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
INSERT INTO `textual_uuid` (`uuid`, `text`) VALUES ('$uuid', '$text');
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
        $uuids = $this->connection->fetchAll('SELECT `uuid` FROM `textual_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid'];

            $queries[] = "SELECT * FROM `textual_uuid` WHERE `uuid` = '$uuid';";
        }

        return $this->runQueryBenchmark($queries);
    }
}
