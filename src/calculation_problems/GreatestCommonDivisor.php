<?php

namespace src\calculation_problems;

use function number_format;

class GreatestCommonDivisor extends AbstractCalculationProblem
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
        return 'Find the greatest common divisor of n: ' . number_format($this->n) . ' and m: ' . number_format($this->m);
    }

    public function getAvailableMethods(): array
    {
        return [
          'simpleCheck' => 'Simply iterate all numbers from 1 to min n and min m and check if they are divisors of each other',
        ];
    }

    public function setupParameters(): void
    {
        $this->setupHeader();
        $this->n = $this->getIntegerInput('Enter the value for n: ', 'n');
        $this->m = $this->getIntegerInput('Enter the value for m: ', 'm');
    }

    public function isReady(): bool
    {
        return $this->isPositiveInteger($this->n) && $this->isPositiveInteger($this->m);
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
     *   d ← 1
     *   for i ← min(n,m), ..., 1
     *     if i divides n and i divides m
     *       d ← i
     *       break
     *   return d
     * ```
     *
     * @return void
     * @api
     */
    public function simpleCheck(): void
    {
        $this->executeWithReadyCheck(function () {
            echo 'Start finding greatest common divisor with n = ' . number_format($this->n) . ' and m = ' . number_format($this->m) . " ...\n";

            $result = $this->measurePerformance(function () {
                $divisor = 1;
                $counter = 0;

                for ($i = min($this->n, $this->m); $i >= 1; $i--) {
                    $counter++;
                    if (bcmod((string)$this->n, (string)$i) === '0' &&
                      bcmod((string)$this->m, (string)$i) === '0') {
                        $divisor = $i;
                        break;
                    }
                }

                return ['divisor' => $divisor, 'iterations' => $counter];
            });

            $this->printResults(
              (string)$result['result']['divisor'],
              $result['result']['iterations'],
              $result['duration']
            );
        });
    }
}

