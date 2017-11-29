<?php

namespace Spatie\BinaryUuid\Test\Benchmark;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class BenchmarkTest extends TestCase
{
    protected $connection;

    protected function setUp()
    {
        parent::setUp();

        $config = new Configuration();

        $parameters = [
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'host' => getenv('DB_HOST'),
            'driver' => 'pdo_mysql',
        ];

        $this->connection = DriverManager::getConnection($parameters, $config);
    }

    /** @test */
    public function run_benchmarks()
    {
        $benchmarks = collect([
            new NormalId($this->connection),
            new BinaryUuid($this->connection),
            new OptimisedUuid($this->connection),
            new OptimisedUuidFromText($this->connection),
            (new TextualUuid($this->connection))
                ->withBenchmarkRoundsTextualUuid(getenv('BENCHMARK_ROUNDS_TEXTUAL_UUID')),
        ]);

        $this->writeln('Starting benchmarks...');

        $iterations = $this->determineIterations();

        foreach ($iterations as $iteration => $recordsInTable) {
            $this->writeln("\nStarting iteration {$iteration} with {$recordsInTable} records in table");

            $this->runIteration($benchmarks, $recordsInTable);
        }

        $this->writeln("\nDone");

        $this->assertTrue(true);
    }

    protected function determineIterations(): array
    {
        $max = getenv('RECORDS_IN_TABLE');

        return array_filter([100, 1000, 50000, 500000], function ($recordsInTable) use ($max) {
            return $recordsInTable <= $max;
        });
    }

    protected function runIteration(Collection $benchmarks, int $recordsInTable)
    {
        $benchmarks
            ->each(function ($benchmark) use ($recordsInTable) {
                $benchmark
                    ->withRecordsInTable($recordsInTable)
                    ->withBenchmarkRounds(getenv('BENCHMARK_ROUNDS'))
                    ->withFlushAmount(getenv('FLUSH_QUERY_AMOUNT'));
            });

        $this->writeln("\nCreating tables");

        $benchmarks->each(function (Benchmark $benchmark) {
            $benchmark->createTable();

            $this->writeln("\t- {$benchmark->name()}");
        });

        $this->writeln("\nSeeding tables");

        $benchmarks->each(function (Benchmark $benchmark) {
            $benchmark->seedTable();

            $this->writeln("\t- {$benchmark->name()}");
        });

        $this->writeln("\nRunning benchmarks");

        $benchmarks->each(function (Benchmark $benchmark) {
            $this->writeln("\t- {$benchmark->name()}: ");

            $result = $benchmark->run();

            $this->writeln("\t\tAvarage of {$result->getAverageInMilliSeconds()}ms over {$result->getIterations()} iterations.");
        });
    }

    protected function writeln(string $message)
    {
        fwrite(STDOUT, "{$message}\n");
    }
}
