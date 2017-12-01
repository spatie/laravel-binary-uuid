<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

class OptimisedUuid extends Benchmark
{
    public function name(): string
    {
        return 'Optimised UUID';
    }

    public function createTable()
    {
        $this->connection->exec(<<<'SQL'
DROP TABLE IF EXISTS `optimised_uuid`;

CREATE TABLE `optimised_uuid` (
    `uuid` BINARY(16) NOT NULL,
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
            $uuid = Uuid::uuid1();

            $encodedUuid = $this->encodeBinary($uuid);

            $text = $this->randomTexts[array_rand($this->randomTexts)];

            $queries[] = <<<SQL
INSERT INTO `optimised_uuid` (`uuid`, `text`) VALUES ('$encodedUuid', '$text');
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
        $uuids = $this->connection->fetchAll('SELECT `uuid` FROM `optimised_uuid`');

        for ($i = 0; $i < $this->benchmarkRounds; $i++) {
            $uuid = $uuids[array_rand($uuids)]['uuid'];

            $queries[] = <<<SQL
SELECT * FROM `optimised_uuid` 
WHERE `uuid` = '$uuid';
SQL;
        }

        return $this->runQueryBenchmark($queries);
    }

    protected function encodeBinary(UuidInterface $uuid): string
    {
        $fields = $uuid->getFieldsHex();

        $optimized = [
            $fields['time_hi_and_version'],
            $fields['time_mid'],
            $fields['time_low'],
            $fields['clock_seq_hi_and_reserved'],
            $fields['clock_seq_low'],
            $fields['node'],
        ];

        return hex2bin(implode('', $optimized));
    }
}
