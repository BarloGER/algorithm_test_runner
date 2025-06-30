<?php
declare(strict_types = 1);

namespace src\utilities;

use Exception;

class PerformanceMeasurement
{
    private string $startTime;

    public function start(): void
    {
        // Force garbage collection for a clean state
        gc_collect_cycles();

        // Small delay for system stabilization
        usleep(1000);

        $this->startTime = (string)hrtime(true);
    }

    public function stop(): array
    {
        $endTime = (string)hrtime(true);

        // Convert nanoseconds to seconds with bcmath (8 decimal places)
        $duration = bcdiv(bcsub($endTime, $this->startTime, 0), '1000000000', 8);

        return [
          'duration' => $duration
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
        $totalDuration = '0';

        for ($i = 1; $i <= $iterations; $i++) {
            // Pre-run cleanup
            gc_collect_cycles();

            $this->start();

            // Execute the callable
            $result = $callable();

            $stats = $this->stop();
            $allStats[] = $stats;

            $totalDuration = bcadd($totalDuration, $stats['duration'], 8);

            if ($showDetails) {
                echo "Run $i: " . $this->formatDuration($stats['duration']) . "\n";
            }

            // Cleanup between runs
            unset($result);
            gc_collect_cycles();
        }

        // Calculate statistics with bcmath
        $durations = array_column($allStats, 'duration');
        $avgDuration = bcdiv($totalDuration, (string)$iterations, 8);

        // Find min and max using bccomp
        $minDuration = $durations[0];
        $maxDuration = $durations[0];

        foreach ($durations as $duration) {
            if (bccomp($duration, $minDuration, 8) < 0) {
                $minDuration = $duration;
            }
            if (bccomp($duration, $maxDuration, 8) > 0) {
                $maxDuration = $duration;
            }
        }

        $avgStats = [
          'iterations' => $iterations,
          'avg_duration' => $avgDuration,
          'min_duration' => $minDuration,
          'max_duration' => $maxDuration,
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

    public function formatDuration(string $seconds): string
    {
        // Convert to microseconds for comparison
        $microseconds = bcmul($seconds, '1000000', 8);
        $milliseconds = bcmul($seconds, '1000', 8);

        if (bccomp($seconds, '0.001', 8) < 0) {
            return bcadd($microseconds, '0', 2) . ' µs (microseconds)';
        } elseif (bccomp($seconds, '1', 8) < 0) {
            return bcadd($milliseconds, '0', 3) . ' ms (milliseconds)';
        }
        return bcadd($seconds, '0', 8) . ' s (seconds)';
    }
}