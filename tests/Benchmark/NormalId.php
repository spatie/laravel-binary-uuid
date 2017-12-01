<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class NormalId extends Benchmark
{
    public function name(): string
    {
        return 'Normal ID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `normal_id`;

CREATE TABLE `normal_id` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `text` TEXT NOT NULL,
    
    KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
        );
    }

    public function seedTable()
    {
        $queries = [];

        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $queries[] = <<<SQL
INSERT INTO `normal_id` (`text`) VALUES ('$text');
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
        $ids = $this->connection->fetchAll('SELECT `id` FROM `normal_id`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $id = $ids[array_rand($ids)]['id'];

            $queries[] = "SELECT * FROM `normal_id` WHERE `id` = {$id};";
        }

        return $this->runQueryBenchmark($queries);
    }
}
