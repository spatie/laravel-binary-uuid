<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Ramsey\Uuid\Uuid;
use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class OptimisedUuid extends Benchmark
{
    public function name(): string
    {
        return 'Optimised UUID';
    }

    public function table()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `optimised_uuid`;

CREATE TABLE `optimised_uuid` (
    `optimised_uuid_binary` BINARY(16) NOT NULL,

    `generated_optimised_uuid_text` varchar(36) generated always as
        (insert(
            insert(
                insert(
                    insert(
                        hex(
                            optimised_uuid_binary
                        ), 9,0,'-'),
                    14,0,'-'),
                19,0,'-'),
            24,0,'-')
        ) virtual,
  
    `generated_normal_uuid_from_optimised_uuid` varchar(36) generated always as
        (insert(
            insert(
                insert(
                    insert(
                        hex(
                            concat(substr(optimised_uuid_binary,5,4),substr(optimised_uuid_binary,3,2),
                            substr(optimised_uuid_binary,1,2),substr(optimised_uuid_binary,9,8))
                        ), 9,0,'-'),
                    14,0,'-'),
                19,0,'-'),
            24,0,'-')
        ) virtual,
    
    `normal_uuid_text` varchar(36),

    PRIMARY KEY (`optimised_uuid_binary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `optimised_uuid` ADD unique(`optimised_uuid_binary`);
SQL
        );
    }

    public function seed()
    {
        $queries = [];

        for ($i = 0; $i < $this->recordsInTable; $i++) {
            $uuid = Uuid::uuid1()->toString();
            $uuidWithoutDash = str_replace('-', '', $uuid);

            $queries[] = <<<SQL
INSERT INTO `optimised_uuid` (`optimised_uuid_binary`, `normal_uuid_text`) VALUES (
  concat(substr(unhex('$uuidWithoutDash'), 7, 2), substr(unhex('$uuidWithoutDash'), 5, 2),
        substr(unhex('$uuidWithoutDash'), 1, 4), substr(unhex('$uuidWithoutDash'), 9, 8)),
  '$uuid'
);
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
        $uuids = $this->connection->fetchAll('SELECT HEX(`optimised_uuid_binary`) AS optimised_uuid_binary FROM `optimised_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['optimised_uuid_binary'];

            $queries[] = <<<SQL
SELECT * FROM `optimised_uuid` 
WHERE `optimised_uuid_binary` = UNHEX('$uuid');
SQL;
        }

        return $this->runQueryBenchmark($queries);
    }
}
