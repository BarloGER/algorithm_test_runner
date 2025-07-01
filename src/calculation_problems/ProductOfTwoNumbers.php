<?php

namespace src\calculation_problems;

use function bcsub;
use function number_format;

class ProductOfTwoNumbers extends AbstractCalculationProblem
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
        return 'Find the product of two numbers without using multiplication.';
    }

    public function getProblemDescription(): string
    {
        if ($this->n === null || $this->m === null) {
            return 'Find the product of n and m (n, m not set yet)';
        }
        return 'Find the product of n: ' . number_format($this->n) . ' and m: ' . number_format($this->m);
    }

    public function getAvailableMethods(): array
    {
        return [
          'ancientEgyptianMultiplikation' => 'Using the old egyptian multiplication algorithm to find the product of two numbers.',
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
     * Find the product of two integers using the ancient Egyptian method
     *
     *
     * **Correctness Proof by Induction:**
     *
     * **Invariant:** n * m = x * y + p
     *
     * Pseudocode
     *
     * ```
     * Algo(n, m)
     *   x ← n
     *   y ← m
     *   p ← 0
     *
     *   while x ≧ 1
     *     if x is even
     *       x ← (x / 2)
     *     else
     *       p ← p + y
     *       x ← (x - 1) / 2
     *     y ← 2 * y
     *
     *   return p
     * ```
     *
     * @return void
     * @api
     */
    public function ancientEgyptianMultiplikation(): void
    {
        $this->executeWithReadyCheck(function() {
            echo 'Start finding the product with n = ' . number_format($this->n) . ' and m = ' . number_format($this->m) . " ...\n";

            $result = $this->measurePerformance(function() {
                $counter = 0;
                $product = '0';
                $num1 = (string)$this->n;
                $num2 = (string)$this->m;

                while ((int)$num1 >= 1) {
                    $counter++;

                    if ((int)$num1 % 2 === 0) {
                        $num1 = bcdiv($num1, '2');
                    } else {
                        $product = bcadd($product, $num2);
                        if ((int)$num1 === 1) break;
                        $num1 = bcdiv(bcsub($num1, '1', 0), '2');
                    }

                    $num2 = bcmul('2', $num2, 0);
                }

                return ['product' => $product, 'iterations' => $counter];
            });

            $this->printResults(
              $result['result']['product'],
              $result['result']['iterations'],
              $result['duration']
            );
        });
    }
}

