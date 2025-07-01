<?php
declare(strict_types=1);

namespace src\calculation_problems;

use src\interfaces\ProblemInterface;
use src\utilities\CustomFormatter;
use src\utilities\PerformanceMeasurement;

use function fgets;
use function is_int;
use function number_format;
use function str_repeat;
use function trim;

use const STDIN;

abstract class AbstractCalculationProblem implements ProblemInterface
{
    protected CustomFormatter $formatter;
    protected PerformanceMeasurement $timer;

    public function __construct()
    {
        $this->formatter = new CustomFormatter();
        $this->timer = new PerformanceMeasurement();
    }

    protected function setupHeader(): void
    {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "SETUP: " . $this->getProblemName() . "\n";
        echo str_repeat('=', 50) . "\n";
    }

    protected function getIntegerInput(string $prompt, string $paramName): int
    {
        echo $prompt;
        $input = trim(fgets(STDIN));
        $value = (int)$input;

        echo "✓ Parameter set: $paramName = " . number_format($value) . "\n";

        if ($value > 100000000) {
            echo "⚠ WARNING: Big Number!\n";
        }

        return $value;
    }

    protected function isPositiveInteger(?int $value): bool
    {
        return is_int($value) && $value > 0;
    }

    protected function executeWithReadyCheck(callable $method): void
    {
        if (!$this->isReady()) {
            echo "Problem not configured! Run Setup first.\n";
            return;
        }

        $method();
    }

    protected function printResults(string $result, ?int $iterations = null, ?string $duration = null): void
    {
        echo "\n--- Results ---\n";
        echo "Result: " . $this->formatter->formatLargeNumber($result) . "\n";

        if ($iterations !== null) {
            echo "Iterations: " . $this->formatter->formatLargeNumber($iterations) . "\n";
        }

        if ($duration !== null) {
            echo "Time: " . $this->timer->formatDuration($duration) . "\n";
        }
    }

    protected function measurePerformance(callable $algorithm): array
    {
        $this->timer->start();
        $result = $algorithm();
        $stats = $this->timer->stop();

        return [
          'result' => $result,
          'duration' => $stats['duration']
        ];
    }
}