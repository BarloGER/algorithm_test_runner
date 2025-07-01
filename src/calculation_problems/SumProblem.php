<?php

declare(strict_types = 1);

namespace src\calculation_problems;

use function bcdiv;
use function bcmul;

class SumProblem extends AbstractCalculationProblem
{
    /**
     * @var int|null
     */
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
          'gaussianSumFormula' => 'Use the Gaussian Sum Formula',
        ];
    }

    public function setupParameters(): void
    {
        $this->setupHeader();
        $this->n = $this->getIntegerInput("Enter the value for n: ", "n");
    }

    public function isReady(): bool
    {
        return $this->isPositiveInteger($this->n);
    }

    public function reset(): void
    {
        $this->n = null;
    }

    /**
     * Calculate the sum from 1 to n iterative
     *
     * Pseudocode
     *
     * ```
     * Algo(n)
     *   s ← 0
     *   for i ← 1, ..., n
     *     s ← s + i
     *   return s
     * ```
     *
     * @return void
     * @api
     */
    public function iterativeSum(): void
    {
        $this->executeWithReadyCheck(function () {
            if ($this->n > 10000000000) {
                echo "⚠ WARNING: n too large for iterative solution!\n";
                return;
            }

            echo "Start iterative calculation for n = " . number_format($this->n) . " ...\n";

            $result = $this->measurePerformance(function () {
                $sum = 0;
                $counter = 0;

                for ($i = 1; $i <= $this->n; $i++) {
                    $sum += $i;
                    $counter++;
                }

                return ['sum' => $sum, 'iterations' => $counter];
            });

            $this->printResults(
              (string)$result['result']['sum'],
              $result['result']['iterations'],
              $result['duration']
            );
        });
    }

    /**
     * Calculates the sum of 1 to n using gaussian sum formula
     *
     * Pseudocode
     *
     * ```
     * Algo(n)
     *       n × (n + 1)
     *   s ← ───────────
     *           2
     *   returns s
     * ```
     *
     * @return void
     * @api
     */
    public function gaussianSumFormula(): void
    {
        $this->executeWithReadyCheck(function() {
            echo "Start calculation for n = " . number_format($this->n) . "...\n";

            $result = $this->measurePerformance(function() {
                return bcdiv(
                  bcmul((string)$this->n, bcadd((string)$this->n, '1', 8), 8),
                  '2',
                  8
                );
            });

            $this->printResults($result['result'], null, $result['duration']);
        });
    }
}