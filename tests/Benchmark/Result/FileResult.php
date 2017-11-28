<?php

namespace Spatie\BinaryUuid\Test\Benchmark\Result;

use Spatie\BinaryUuid\Test\Benchmark\AbstractBenchmark;

class FileResult
{
    public static function save(AbstractBenchmark $benchmark)
    {
        $slug = str_replace(' ', '-', strtolower($benchmark->name())) . "_{$benchmark->recordsInTable()}";

        $path = __DIR__ . "/../../data/{$slug}.csv";

        $handle = fopen($path, 'w+');

        foreach ($benchmark->result() as $executionTime) {
            fputcsv($handle, ['executionTime' => number_format($executionTime, 23)]);
        }

        fclose($handle);
    }
}
