<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Doctrine\DBAL\Exception\SyntaxErrorException;
use Faker\Factory;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Spatie\BinaryUuid\Test\Benchmark\Result\FileResult;
use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

abstract class Benchmark
{
    protected $recordsInTable = 100;
    protected $flushAmount = 1000;
    protected $benchmarkRounds = 100;

    protected $randomTexts = [];

    /** @var \Doctrine\DBAL\Driver\Connection */
    protected $connection;

    /** @var \Faker\Generator */
    protected $faker;

    protected $result = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $this->faker = Factory::create();

        $this->randomTexts = [
            $this->faker->text(1000),
            $this->faker->text(500),
            $this->faker->text(100),
            $this->faker->text(750),
        ];
    }

    public function result(): array
    {
        return $this->result;
    }

    public function recordsInTable(): int
    {
        return $this->recordsInTable;
    }

    abstract public function name(): string;

    abstract public function createTable();

    abstract public function seedTable();

    abstract public function run(): InlineResult;

    protected function runQueryBenchmark(array $queries): InlineResult
    {
        $stack = new DebugStack();
        $this->connection->getConfiguration()->setSQLLogger($stack);

        foreach ($queries as $query) {
            $this->connection->fetchAll($query);
        }

        $this->result = [];

        foreach ($stack->queries as $stat) {
            $this->result[] = $stat['executionMS'];
        }

        FileResult::save($this);

        return new InlineResult($this);
    }

    public function withRecordsInTable(int $recordsInTable): self
    {
        $this->recordsInTable = $recordsInTable;

        return $this;
    }

    public function withFlushAmount(int $flushAmount): self
    {
        $this->flushAmount = $flushAmount;

        return $this;
    }

    public function withBenchmarkRounds(int $benchmarkRounds): self
    {
        $this->benchmarkRounds = $benchmarkRounds;

        return $this;
    }
}
