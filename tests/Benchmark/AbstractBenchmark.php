<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Faker\Factory;
use Spatie\BinaryUuid\Test\Benchmark\Result\FileResult;
use Spatie\BinaryUuid\Test\Benchmark\Result\InlineResult;

abstract class AbstractBenchmark
{
    protected $recordsInTable = 100;
    protected $flushAmount = 1000;
    protected $benchmarkRounds = 100;

    protected $randomTexts = [];
    protected $connection;
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

    abstract public function table();

    abstract public function seed();

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

    public function withRecordsInTable(int $recordsInTable): AbstractBenchmark
    {
        $this->recordsInTable = $recordsInTable;

        return $this;
    }

    public function withFlushAmount(int $flushAmount): AbstractBenchmark
    {
        $this->flushAmount = $flushAmount;

        return $this;
    }

    public function withBenchmarkRounds(int $benchmarkRounds): AbstractBenchmark
    {
        $this->benchmarkRounds = $benchmarkRounds;

        return $this;
    }
}
