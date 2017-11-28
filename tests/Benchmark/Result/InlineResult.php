<?php

namespace Spatie\BinaryUuid\Test\Benchmark\Result;

use Spatie\BinaryUuid\Test\Benchmark\AbstractBenchmark;

class InlineResult
{
    private $results;
    private $iterations;
    protected $averageInSeconds;

    public function __construct(AbstractBenchmark $benchmark)
    {
        $result = $benchmark->result();

        $this->results = $result;
        $this->iterations = count($result);
        $this->averageInSeconds = (float) (array_sum($result) / count($result));
    }

    public function getAverageInSeconds(): float
    {
        return $this->averageInSeconds;
    }

    public function getAverageInMilliSeconds(): float
    {
        return round($this->averageInSeconds * 1000, 6);
    }

    public function getIterations(): int
    {
        return $this->iterations;
    }
}
