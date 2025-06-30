<?php
declare(strict_types = 1);

namespace src;

use src\utilities\CustomFormatter;
use src\interfaces\ProblemInterface;
use src\utilities\PerformanceMeasurement;

class SumProblem implements ProblemInterface
{
    private ?string $n = null;

    public function getProblemName(): string
    {
        return "Calculate the sum from 1 to n";
    }

    public function getProblemDescription(): string
    {
        if ($this->n === null) {
            return "Calculate the sum of all numbers from 1 to n (n not set yet)";
        }
        return "Calculate the sum of all numbers from 1 to " . number_format((int)$this->n);
    }

    public function getAvailableMethods(): array
    {
        return [
          'iterativeSum' => 'Simply add all numbers together',
          'gaussianSumFormula' => 'Use the Gaussian Sum Formula'
        ];
    }

    public function setupParameters(): void
    {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "SETUP: " . $this->getProblemName() . "\n";
        echo str_repeat('=', 50) . "\n";

        echo "Enter the value for n: ";
        $input = trim(fgets(STDIN));
        $n = $input;

        $this->n = $n;
        echo "✓ Parameters set: n = " . number_format((int)$this->n) . "\n";

        if (bccomp($this->n, '100000000', 0) > 0) {
            echo "⚠ WARNING: Big Number!\n";
        }
    }

    public function isReady(): bool
    {
        return $this->n !== null && bccomp($this->n, '0', 0) > 0;
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

        if (bccomp($this->n, '10000000000', 0) > 0) {
            echo "⚠ WARNING: n too large for iterative solution!\n";
            return;
        }

        echo "Start iterative calculation for n = " . number_format((int)$this->n) . "...\n";

        $timer = new PerformanceMeasurement();
        $timer->start();

        $sum = '0';
        $counter = '0';
        $i = '1';

        while (bccomp($i, $this->n, 0) <= 0) {
            $sum = bcadd($sum, $i, 8);
            $counter = bcadd($counter, '1', 0);
            $i = bcadd($i, '1', 0);
        }

        $stats = $timer->stop();

        echo "\n--- Results ---\n";
        echo "Result: " . new CustomFormatter()->formatLargeNumber($sum) . "\n";
        echo "Iterations: " . $counter . "\n";
        echo "Time: " . $timer->formatDuration($stats['duration']) . "\n";
    }

    public function gaussianSumFormula(): void
    {
        if (!$this->isReady()) {
            echo "Problem not configured! Run Setup first.\n";
            return;
        }

        echo "Start calculation for n = " . number_format((int)$this->n) . "...\n";

        $timer = new PerformanceMeasurement();
        $timer->start();

        // Formula: n * (n + 1) / 2 with bcmath precision
        $nPlusOne = bcadd($this->n, '1', 8);
        $product = bcmul($this->n, $nPlusOne, 8);
        $sum = bcdiv($product, '2', 8);

        $stats = $timer->stop();

        echo "\n--- Results ---\n";
        echo "Result: " . new CustomFormatter()->formatLargeNumber($sum) . "\n";
        echo "Time: " . $timer->formatDuration($stats['duration']) . "\n";
    }
}