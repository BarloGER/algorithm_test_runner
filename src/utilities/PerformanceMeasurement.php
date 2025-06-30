<?php
declare(strict_types = 1);

namespace src\utilities;

use Exception;

class PerformanceMeasurement
{
    private float $startTime;

    public function start(): void
    {
        // Force garbage collection for a clean state
        gc_collect_cycles();

        // Small delay for system stabilization
        usleep(1000);

        $this->startTime = hrtime(true);
    }

    public function stop(): array
    {
        $endTime = hrtime(true);

        return [
          'duration' => ($endTime - $this->startTime) / 1e9
        ];
    }

    /**
     * @throws Exception
     */
    public function benchmark(callable $callable, int $iterations = 5, bool $showDetails = false, string $description = ''): array
    {
        if ($iterations < 1) {
            throw new Exception('Iterations must be at least 1');
        }

        echo str_repeat('=', 60) . "\n";
        echo $description ? "BENCHMARK: $description\n" : "BENCHMARK RESULTS\n";
        echo "Iterations: $iterations\n";
        echo str_repeat('-', 60) . "\n";

        $allStats = [];
        $totalDuration = 0;

        for ($i = 1; $i <= $iterations; $i++) {
            // Pre-run cleanup
            gc_collect_cycles();

            $this->start();

            // Execute the callable
            $result = $callable();

            $stats = $this->stop();
            $allStats[] = $stats;

            $totalDuration += $stats['duration'];

            if ($showDetails) {
                echo "Run $i: " . $this->formatDuration($stats['duration']) . "\n";
            }

            // Cleanup between runs
            unset($result);
            gc_collect_cycles();
        }

        // Calculate averages
        $avgStats = [
          'iterations' => $iterations,
          'avg_duration' => $totalDuration / $iterations,
          'min_duration' => min(array_column($allStats, 'duration')),
          'max_duration' => max(array_column($allStats, 'duration')),
          'total_duration' => $totalDuration,
          'all_runs' => $allStats
        ];

        // Display summary
        echo str_repeat('-', 60) . "\n";
        echo "SUMMARY:\n";
        echo "Average Duration: " . $this->formatDuration($avgStats['avg_duration']) . "\n";
        echo "Min Duration: " . $this->formatDuration($avgStats['min_duration']) . "\n";
        echo "Max Duration: " . $this->formatDuration($avgStats['max_duration']) . "\n";
        echo "Total Duration: " . $this->formatDuration($avgStats['total_duration']) . "\n";
        echo str_repeat('=', 60) . "\n\n";

        return $avgStats;
    }

    public function formatDuration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return number_format($seconds * 1000000, 2) . ' µs (microseconds)';
        } elseif ($seconds < 1) {
            return number_format($seconds * 1000, 3) . ' ms (milliseconds)';
        }
        return number_format($seconds, 6) . ' s (seconds)';
    }

}