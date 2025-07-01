<?php

declare(strict_types = 1);

namespace src\calculation_problems;

use function count;

class FindLightestConstructionWorker extends AbstractCalculationProblem
{
    /**
     * @var array|null
     */
    private ?array $list = null;

    public function getProblemName(): string
    {
        return "Find the lightest construction worker";
    }

    public function getProblemDescription(): string
    {
        if ($this->list === null) {
            return "Find the lightest construction worker (list not set yet)";
        }
        return "Find the lightest construction worker from list with length: " . count($this->list);
    }

    public function getAvailableMethods(): array
    {
        return [
          'bruteForce' => 'Check every combination of workers and find the lightest one',
        ];
    }

    public function setupParameters(): void
    {
        $this->setupHeader();
        $this->list = $this->getListInput('Enter a value for the list length: ', 'list');
    }

    public function isReady(): bool
    {
        return $this->isValidList($this->list);
    }

    public function reset(): void
    {
        $this->list = null;
    }

    /**
     * Helper method to check if worker at given index is the lightest
     */
    private function isLightest(array $workerList, int &$comparisons, int $index): bool
    {
        for ($j = 0; $j < count($workerList); $j++) {
            if ($j === $index) {
                continue; // Skip comparing with itself
            }

            $comparisons++;

            if ($workerList[$index] > $workerList[$j]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find the lightest worker in a list
     * Only shows the first one with the lightest weight
     *
     * Pseudocode
     *
     * ```
     * Algo (list of n construction workers)
     *   for i ← 0, ..., n
     *     if isLightest(i) then
     *       return i
     *
     *   isLightest(index i)
     *     for j ← 1, ..., n
     *       if worker(i) is heavier than worker(j), then
     *         return false
     *     return true
     *
     * @return void
     * @api
     */
    public function bruteForce(): void {
        $this->executeWithReadyCheck(function() {
            echo "Start finding lightest worker in list: " . $this->formatter->formatList($this->list, 5) . " ...\n";

            $result = $this->measurePerformance(function() {
                $comparisons = 0;

                for ($i = 0; $i < count($this->list); $i++) {
                    if ($this->isLightest($this->list, $comparisons, $i)) {
                        return ['index' => $i, 'weight' => $this->list[$i], 'comparisons' => $comparisons];
                    }
                }

                return ['index' => -1, 'weight' => 0, 'comparisons' => $comparisons];
            });

            $this->printResults($result['result'], null, $result['duration']);
        });
    }
}