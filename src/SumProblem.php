<?php
declare(strict_types = 1);

namespace src;

use src\utilities\CustomFormatter;
use src\interfaces\ProblemInterface;

use src\utilities\PerformanceMeasurement;

class SumProblem implements ProblemInterface
{
    private ?int $n = null;

    public function getProblemName(): string
    {
        return "Calculate the sum from 1 to n";
    }

    public function getProblemDescription(): string
    {
        if ($this->n === null) {
            return "Calculate the sum of all numbers from 1 to n (n not set yet)";
        }
        return "Calculate the sum of all numbers from 1 to " . number_format($this->n);
    }

    public function getAvailableMethods(): array
    {
        return [
          'iterativeSum' => 'Simply add all numbers together',
        ];
    }

    public function setupParameters(): void
    {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "SETUP: " . $this->getProblemName() . "\n";
        echo str_repeat('=', 50) . "\n";

        echo "Enter the value for n: ";
        $input = trim(fgets(STDIN));
        $n = (int)$input;

        $this->n = $n;
        echo "✓ Parameters set: n = " . number_format($this->n) . "\n";

        if ($this->n > 100000000) {
            echo "⚠ WARNING: Big Number!\n";
        }
    }

    public function isReady(): bool
    {
        return $this->n !== null && $this->n > 0;
    }

    public function reset(): void
    {
        $this->n = null;
    }

    public function iterativeSum(): void
    {
        if (!$this->isReady()) {
            echo "Problem not configured! Run Setup first.\n";
            return;
        }

        if ($this->n > 10000000000) {
            echo "⚠ WARNING: n too large for iterative solution!\n";
            return;
        }

        echo "Start iterative calculation for n = " . number_format($this->n) . "...\n";

        $timer = new PerformanceMeasurement();
        $timer->start();

        $sum = 0;
        $counter = 0;

        for ($i = 1; $i <= $this->n; $i++) {
            $sum += $i;
            $counter++;
        }

        $stats = $timer->stop();

        echo "\n--- Results ---\n";
        echo "Result: " . new CustomFormatter()->formatLargeNumber($sum) . "\n";
        echo "Iterations: " . number_format($counter) . "\n";
        echo "Time: " . $timer->formatDuration($stats['duration']) . "\n";
    }
}