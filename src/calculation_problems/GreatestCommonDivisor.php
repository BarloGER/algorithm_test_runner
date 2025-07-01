<?php

namespace src\calculation_problems;

use src\interfaces\ProblemInterface;

use src\utilities\CustomFormatter;
use src\utilities\PerformanceMeasurement;

use function fgets;
use function number_format;
use function str_repeat;
use function trim;

use const STDIN;

class GreatestCommonDivisor implements ProblemInterface
{
    /**
     * @var int|null
     */
    private ?int $n = null;

    /**
     * @var int|null
     */
    private ?int $m = null;

    public function getProblemName(): string
    {
        return 'Find the greatest common divisor of two numbers';
    }

    public function getProblemDescription(): string
    {
        if ($this->n === null || $this->m === null) {
            return 'Find the greatest common divisor of n and m (n, m not set yet)';
        }


        return 'Find the greatest common divisor of n: ' . number_format($this->n) . 'and m: ' . number_format($this->m);
    }

    public function getAvailableMethods(): array
    {
        return [
          'simpleCheck' => 'Simply iterate all numbers from 1 to min n and min m and check if they are divisors of each other',
        ];
    }

    public function setupParameters(): void
    {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo 'SETUP: ' . $this->getProblemName() . "\n";
        echo str_repeat('=', 50) . "\n";

        echo 'Enter the value for n: ';
        $input = trim(fgets(STDIN));
        $n = (int)$input;

        $this->n = $n;

        echo 'Enter the value for m: ';
        $input = trim(fgets(STDIN));
        $m = (int)$input;

        $this->m = $m;
        echo '✓ Parameters set: n = ' . number_format($this->n) . ' and m = ' . number_format($this->m) . "\n";

        if ($this->n > 100000000 || $this->m > 100000000) {
            echo "⚠ WARNING: Big Number!\n";
        }
    }

    public function isReady(): bool
    {
        if ($this->n === null) {
            return false;
        }

        if (!is_int($this->n) || !is_int($this->m)) {
            return false;
        }


        if ($this->n <= 0 || $this->m <= 0) {
            return false;
        }

        return true;
    }

    public function reset(): void
    {
        $this->n = null;
        $this->m = null;
    }

    /**
     * Finds the greatest common divisor for 2 nums by simply iterating and checking if the modulo is 0
     *
     * Pseudocode
     *
     * ```
     * Algo(n, m)
     * d ← 1
     * for i ← min(n,m), ..., 1
     *   if i divides n and i divides m
     *     d ← i
     *     break
     * return d
     * ```
     *
     * @return void
     * @api
     */
    public function simpleCheck(): void
    {
        if (!$this->isReady()) {
            echo "Problem not configured! Run Setup first.\n";
            return;
        }

        echo 'Start finding greatest common divisor with n = ' . number_format($this->n) . ' and m = ' . number_format($this->m) . " ...\n";

        $timer = new PerformanceMeasurement();
        $timer->start();

        $divisor = 1;
        $counter = 0;

        for ($i = min($this->n, $this->m); $i >= 1; $i--) {
            $counter += 1;

            if (bcmod((string)$this->n, (string)$i) === '0' && bcmod((string)$this->m, (string)$i) === '0') {
                $divisor = $i;
                break;
            }
        }

        $stats = $timer->stop();

        $customFormatter = new CustomFormatter();

        echo "\n--- Results ---\n";
        echo "Result: " . $customFormatter->formatLargeNumber($divisor) . "\n";
        echo "Iterations: " . $customFormatter->formatLargeNumber($counter) . "\n";
        echo "Time: " . $timer->formatDuration($stats['duration']) . "\n";
    }
}

