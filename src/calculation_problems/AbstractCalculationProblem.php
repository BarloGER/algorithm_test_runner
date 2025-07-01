<?php
declare(strict_types=1);

namespace src\calculation_problems;

use AlgorithmTestRunner;
use src\interfaces\ProblemInterface;
use src\utilities\CustomFormatter;
use src\utilities\PerformanceMeasurement;

use function fgets;
use function is_int;
use function number_format;
use function str_repeat;
use function trim;
use function explode;
use function array_map;
use function count;
use function implode;

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

    /**
     * Get a list from a user with flexible content generation
     *
     * @param string $prompt The prompt to show the user
     * @param string $paramName The parameter name for logging
     * @return array The generated list
     */
    protected function getListInput(string $prompt, string $paramName): array
    {
        echo $prompt;
        $size = (int)trim(fgets(STDIN));

        if ($size <= 0) {
            echo "Invalid size! Using size 1.\n";
            $size = 1;
        }

        // Memory check with dynamic upgrade prompt
        $memoryCheck = AlgorithmTestRunner::checkMemoryForArray($size);

        if (!$memoryCheck['fits']) {
            echo "\n⚠️  MEMORY WARNING ⚠️\n";
            echo "📊 Array size: " . number_format($size) . " elements\n";
            echo "💾 Estimated memory: " . $memoryCheck['estimated_formatted'] . "\n";
            echo "🔧 Available memory: " . $memoryCheck['memory_info']['available_formatted'] . "\n";
            echo "💡 Current limit: " . $memoryCheck['memory_info']['limit_formatted'] . "\n\n";

            // To big for the current limit
            if ($memoryCheck['estimated_memory'] > $memoryCheck['memory_info']['limit']) {
                echo "❌ Array too large for current memory limit!\n\n";
                echo "🔧 SOLUTION OPTIONS:\n";
                echo "1. Increase memory limit to " . $this->suggestMemoryLimit($memoryCheck['estimated_memory']) . "\n";
                echo "2. Use smaller array size\n";
                echo "3. Cancel and return to menu\n";
                echo "Choice (1-3): ";

                $choice = trim(fgets(STDIN));

                switch($choice) {
                    case '1':
                        $newLimit = $this->suggestMemoryLimit($memoryCheck['estimated_memory']);
                        ini_set('memory_limit', $newLimit);
                        echo "✅ Memory limit increased to $newLimit\n";
                        echo "🔄 Continuing with array generation...\n\n";
                        break;
                    case '2':
                        echo "📝 Enter new smaller size:\n";
                        return $this->getListInput($prompt, $paramName);
                    case '3':
                    default:
                        echo "❌ Cancelled.\n";
                        return [];
                }
            } else {
                // Should fit, but warn anyway
                echo "⚠️  This might use a lot of memory and be slow!\n";
                echo "Continue anyway? (y/n): ";
                $confirm = trim(fgets(STDIN));
                if (strtolower($confirm) !== 'y') {
                    return $this->getListInput($prompt, $paramName);
                }
            }
        }

        echo "\nChoose list content type:\n";
        echo "1. Random integers (1-100)\n";
        echo "2. Random integers (custom range)\n";
        echo "3. Sequential numbers (1, 2, 3, ...)\n";
        echo "4. Sequential numbers (custom start)\n";
        echo "5. Same value repeated\n";
        echo "6. Manual input (space-separated)\n";
        echo "7. Reverse sequential (n, n-1, n-2, ...)\n";
        echo "Choice: ";

        $choice = trim(fgets(STDIN));
        $startMemory = 0;

        // Show memory usage during generation for large arrays
        if ($size > 100000) {
            echo "\n🔄 Generating large array... ";
            $startMemory = memory_get_usage(true);
        }

        switch ($choice) {
            case '1':
                $list = $this->generateRandomList($size, 1, 100);
                break;
            case '2':
                echo "Enter min value: ";
                $min = (int)trim(fgets(STDIN));
                echo "Enter max value: ";
                $max = (int)trim(fgets(STDIN));
                $list = $this->generateRandomList($size, $min, $max);
                break;
            case '3':
                $list = $this->generateSequentialList($size, 1);
                break;
            case '4':
                echo "Enter start value: ";
                $start = (int)trim(fgets(STDIN));
                $list = $this->generateSequentialList($size, $start);
                break;
            case '5':
                echo "Enter value to repeat: ";
                $value = (int)trim(fgets(STDIN));
                $list = $this->generateRepeatedList($size, $value);
                break;
            case '6':
                $list = $this->getManualList($size);
                break;
            case '7':
                echo "Enter start value: ";
                $start = (int)trim(fgets(STDIN));
                $list = $this->generateReverseSequentialList($size, $start);
                break;
            default:
                echo "Invalid choice! Using random integers 1-100.\n";
                $list = $this->generateRandomList($size, 1, 100);
        }

        // Show actual memory usage for large arrays
        if ($size > 100000) {
            $endMemory = memory_get_usage(true);

            if ($startMemory === 0) {
                echo "❌ Error while calculating memory usage.\n";
            } else {
                $actualUsed = AlgorithmTestRunner::formatBytes($endMemory - $startMemory);
                echo "✅ Done! (Used: $actualUsed)\n";
            }
        }

        // Show a preview for large lists
        $preview = count($list) > 10
          ? '[' . implode(', ', array_slice($list, 0, 5)) . ', ..., ' . implode(', ', array_slice($list, -5)) . ']'
          : '[' . implode(', ', $list) . ']';

        echo "✓ Parameter set: $paramName = $preview (size: " . count($list) . ")\n";

        // Performance warnings
        if (count($list) > 1000000) {
            echo "🐌 WARNING: Very large list! Algorithms will be slow.\n";
        } elseif (count($list) > 100000) {
            echo "⚠️  WARNING: Large list! This might take a while.\n";
        }

        return $list;
    }

    /**
     * Suggest the appropriate memory limit based on required memory
     */
    private function suggestMemoryLimit(int $requiredBytes): string
    {
        // Add 50% safety margin
        $safeBytes = $requiredBytes * 1.5;

        if ($safeBytes < 512 * 1024 * 1024) {
            return '512M';
        } elseif ($safeBytes < 1024 * 1024 * 1024) {
            return '1G';
        } elseif ($safeBytes < 2048 * 1024 * 1024) {
            return '2G';
        } elseif ($safeBytes < 4096 * 1024 * 1024) {
            return '4G';
        } else {
            return '8G';
        }
    }

    private function generateRandomList(int $size, int $min, int $max): array
    {
        $list = [];
        for ($i = 0; $i < $size; $i++) {
            $list[] = rand($min, $max);
        }
        return $list;
    }

    private function generateSequentialList(int $size, int $start): array
    {
        $list = [];
        for ($i = 0; $i < $size; $i++) {
            $list[] = $start + $i;
        }
        return $list;
    }

    private function generateReverseSequentialList(int $size, int $start): array
    {
        $list = [];
        for ($i = 0; $i < $size; $i++) {
            $list[] = $start - $i;
        }
        return $list;
    }

    private function generateRepeatedList(int $size, int $value): array
    {
        return array_fill(0, $size, $value);
    }

    private function getManualList(int $expectedSize): array
    {
        echo "Enter $expectedSize numbers separated by spaces: ";
        $input = trim(fgets(STDIN));
        $numbers = array_map('intval', explode(' ', $input));

        if (count($numbers) !== $expectedSize) {
            echo "⚠ Expected $expectedSize numbers, got " . count($numbers) . ". Using what was provided.\n";
        }

        return $numbers;
    }

    protected function isPositiveInteger(?int $value): bool
    {
        return is_int($value) && $value > 0;
    }

    protected function isValidList(?array $list): bool
    {
        return is_array($list) && count($list) > 0;
    }

    protected function executeWithReadyCheck(callable $method): void
    {
        if (!$this->isReady()) {
            echo "Problem not configured! Run Setup first.\n";
            return;
        }

        $method();
    }

    /**
     * Flexible result printer that handles strings, integers, arrays, and complex results
     *
     * @param mixed $result The result to display (string, int, array, or complex structure)
     * @param int|null $iterations Number of iterations/comparisons performed
     * @param string|null $duration Execution duration
     * @param string|null $label Custom label for the result (default: "Result")
     */
    protected function printResults(mixed $result, ?int $iterations = null, ?string $duration = null, ?string $label = null): void
    {
        echo "\n--- Results ---\n";
        $resultLabel = $label ?? "Result";

        // Handle different result types
        if (is_array($result)) {
            echo "$resultLabel: " . $this->formatter->formatList($result) . "\n";
        } elseif (is_int($result)) {
            echo "$resultLabel: " . $this->formatter->formatLargeNumber((string)$result) . "\n";
        } elseif (is_string($result)) {
            echo "$resultLabel: " . $this->formatter->formatLargeNumber($result) . "\n";
        } elseif (is_bool($result)) {
            echo "$resultLabel: " . ($result ? 'true' : 'false') . "\n";
        } elseif (is_float($result)) {
            echo "$resultLabel: " . number_format($result, 2) . "\n";
        } else {
            // For complex objects/arrays, try to format nicely
            echo "$resultLabel: " . $this->formatComplexResult($result) . "\n";
        }

        if ($iterations !== null) {
            echo "Iterations: " . $this->formatter->formatLargeNumber((string)$iterations) . "\n";
        }

        if ($duration !== null) {
            echo "Time: " . $this->timer->formatDuration($duration) . "\n";
        }
    }

    /**
     * Format complex results (objects, associative arrays, etc.)
     */
    private function formatComplexResult($result): string
    {
        if (is_object($result) && method_exists($result, '__toString')) {
            return (string)$result;
        }

        if (is_array($result)) {
            // Handle associative arrays with meaningful keys
            if ($this->isAssociativeArray($result)) {
                $parts = [];
                foreach ($result as $key => $value) {
                    if (is_array($value)) {
                        $parts[] = "$key: " . $this->formatter->formatList($value);
                    } elseif (is_numeric($value)) {
                        $parts[] = "$key: " . $this->formatter->formatLargeNumber((string)$value);
                    } else {
                        $parts[] = "$key: $value";
                    }
                }
                return implode(', ', $parts);
            }
            return $this->formatter->formatList($result);
        }

        return print_r($result, true);
    }

    /**
     * Check if an array is associative (has string keys)
     */
    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
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