<?php
require_once 'vendor/autoload.php';

use src\ProblemFactory;
use src\utilities\PerformanceMeasurement;
use src\interfaces\ProblemInterface;

class AlgorithmTestRunner
{
    private array $problems = [];
    private PerformanceMeasurement $performance;

    public function __construct()
    {
        $this->performance = new PerformanceMeasurement();
        $this->loadAvailableProblems();
        $this->setupMemoryAndLimits();
    }

    private function setupMemoryAndLimits(): void
    {
        // Check the current memory limit
        $currentLimit = ini_get('memory_limit');
        $currentBytes = self::parseMemoryLimit($currentLimit);

        echo "🚀 Algorithm Runner\n";
        echo "📊 Current memory limit: $currentLimit\n";

        // Check if the limit is too small
        if ($currentBytes < 512 * 1024 * 1024) { // < 512MB
            echo "\n⚠️  LOW MEMORY WARNING ⚠️\n";
            echo "💡 For large algorithms, i recommend at least 512MB\n";
            echo "🔧 Would you like to increase the memory limit?\n\n";
            echo "Available options:\n";
            echo "1. 512M (recommended for most algorithms)\n";
            echo "2. 1G (for large datasets)\n";
            echo "3. 2G (for very large datasets)\n";
            echo "4. 4G (for extreme testing)\n";
            echo "5. Keep current ($currentLimit)\n";
            echo "Choice (1-5): ";

            $choice = trim(fgets(STDIN));

            switch($choice) {
                case '1':
                    ini_set('memory_limit', '512M');
                    echo "✅ Memory limit set to 512M\n";
                    break;
                case '2':
                    ini_set('memory_limit', '1G');
                    echo "✅ Memory limit set to 1G\n";
                    break;
                case '3':
                    ini_set('memory_limit', '2G');
                    echo "✅ Memory limit set to 2G\n";
                    break;
                case '4':
                    ini_set('memory_limit', '4G');
                    echo "✅ Memory limit set to 4G\n";
                    break;
                default:
                    echo "✅ Keeping current limit ($currentLimit)\n";
                    break;
            }
        } else {
            //
            ini_set('memory_limit', '1G');
            echo "✅ Memory limit optimized to 1G\n";
        }

        // Execution time for complex algorithms
        ini_set('max_execution_time', 300);

        // Error reporting for development
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        echo "⏱️ Execution time: " . ini_get('max_execution_time') . "s\n";
        echo "📊 Final memory limit: " . ini_get('memory_limit') . "\n\n";
    }

    /**
     * Get memory info for warnings
     */
    public static function getMemoryInfo(): array
    {
        $memoryLimit = self::parseMemoryLimit(ini_get('memory_limit'));
        $memoryUsage = memory_get_usage(true);

        return [
          'limit' => $memoryLimit,
          'usage' => $memoryUsage,
          'available' => $memoryLimit - $memoryUsage,
          'limit_formatted' => self::formatBytes($memoryLimit),
          'usage_formatted' => self::formatBytes($memoryUsage),
          'available_formatted' => self::formatBytes($memoryLimit - $memoryUsage)
        ];
    }

    /**
     * Parse memory limit string to bytes
     */
    public static function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit)-1]);
        $value = (int) $memoryLimit;

        switch($last) {
            case 'g':                           // 1G → 1.073.741.824 ✓
            case 'm':                           // 1M → 1.048.576 ✓
            case 'k': $value *= (1024); break;  // 1K → 1.024 ✓
        }

        return $value;
    }

    /**
     * Format bytes to human-readable format
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Check if the array size would fit in memory
     */
    public static function checkMemoryForArray(int $size): array
    {
        $memoryInfo = self::getMemoryInfo();
        $estimatedMemory = $size * 24; // ~24 bytes per PHP array element (overhead)

        return [
          'size' => $size,
          'estimated_memory' => $estimatedMemory,
          'estimated_formatted' => self::formatBytes($estimatedMemory),
          'fits' => $estimatedMemory < $memoryInfo['available'] * 0.8, // 80% safety margin
          'memory_info' => $memoryInfo
        ];
    }

    private function loadAvailableProblems(): void
    {
        foreach (ProblemFactory::getAvailableProblems() as $className) {
            try {
                $problem = ProblemFactory::createProblem($className);
                $this->problems[] = $problem;
            } catch (Exception $e) {
                echo "Error loading problem '$className': " . $e->getMessage() . "\n";
                continue;
            }
        }
    }

    private function getUserInput(string $prompt): string
    {
        echo $prompt;
        return trim(fgets(STDIN));
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        echo "Algorithm test runner started!\n";
        echo 'Found problems: ' . count($this->problems) . "\n\n";

        while (true) {
            $this->showMainMenu();
            $choice = $this->getUserInput('Your choice: ');

            if ($choice === 'q') {
                echo "Goodbye!\n";
                break;
            }

            $problemIndex = (int)$choice - 1;
            if (isset($this->problems[$problemIndex])) {
                $this->runProblem($this->problems[$problemIndex]);
            } else {
                echo "Invalid Choice!\n\n";
            }
        }
    }

    private function showMainMenu(): void
    {
        echo str_repeat('=', 60) . "\n";
        echo "ALGORITHMUS TEST RUNNER - MAIN MENU\n";
        echo str_repeat('=', 60) . "\n";
        echo "Available problems:\n\n";

        foreach ($this->problems as $index => $problem) {
            $status = $problem->isReady() ? "✓" : "○";
            echo ($index + 1) . ". $status " . $problem->getProblemName() . "\n";
            echo "   " . $problem->getProblemDescription() . "\n\n";
        }

        echo "q. Cancel\n";
        echo str_repeat('-', 60) . "\n";
        echo "Legend: ✓ = configured, ○ = setup required\n";
    }

    /**
     * @throws Exception
     */
    private function runProblem(ProblemInterface $problem): void
    {
        while (true) {
            $this->showProblemMenu($problem);
            $choice = $this->getUserInput("Your choice: ");

            if ($choice === 'b') {
                break;
            }

            switch ($choice) {
                case 's':
                    $problem->setupParameters();
                    $this->getUserInput("\nPress Enter to continue...");
                    break;
                case 'r':
                    $problem->reset();
                    echo "Problem reset.\n";
                    $this->getUserInput("Press Enter to continue...");
                    break;
                case '0':
                    if ($problem->isReady()) {
                        $this->compareAllMethods($problem);
                    } else {
                        echo "Run the setup first!\n";
                    }
                    $this->getUserInput("\nPress Enter to continue...");
                    break;
                default:
                    $methods = array_keys($problem->getAvailableMethods());
                    $methodIndex = (int)$choice - 1;

                    if (isset($methods[$methodIndex])) {
                        if ($problem->isReady()) {
                            $this->runMethod($problem, $methods[$methodIndex]);
                        } else {
                            echo "Run the setup first!\n";
                        }
                        $this->getUserInput("\nPress Enter to continue...");
                    } else {
                        echo "Invalid Choice!\n\n";
                    }
                    break;
            }
        }
    }

    private function showProblemMenu(ProblemInterface $problem): void
    {
        $status = $problem->isReady() ? '✓ READY' : '○ SETUP REQUIRED';

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "PROBLEM: " . $problem->getProblemName() . " [$status]\n";
        echo $problem->getProblemDescription() . "\n";
        echo str_repeat('=', 60) . "\n";

        echo "s. Problem with setup/configuration\n";
        echo "r. Reset problem\n\n";

        if ($problem->isReady()) {
            echo "Available algorithms:\n\n";
            $methods = $problem->getAvailableMethods();
            $index = 1;
            foreach ($methods as $methodName => $description) {
                echo "$index. $methodName\n";
                echo "   $description\n\n";
                $index++;
            }
            echo "0. Compare all algorithms\n";
        } else {
            echo "⚠ First run the setup to test algorithms.\n";
        }

        echo "b. Back to main menu\n";
        echo str_repeat('-', 60) . "\n";
    }

    /**
     * @throws Exception
     */
    private function runMethod(ProblemInterface $problem, string $methodName): void
    {
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "EXECUTION: $methodName\n";
        echo str_repeat('=', 50) . "\n";

        $mode = $this->getUserInput('Mode: (1) Run once, (2) Benchmark: ');

        if ($mode === '2') {
            $iterations = (int)$this->getUserInput('Number of repetitions (default 5): ') ?: 5;
            $showDetails = $this->getUserInput('Show details? (y/n): ') === 'y';

            $this->performance->benchmark(
              callable: fn() => $problem->$methodName(),
              iterations: $iterations,
              showDetails: $showDetails,
              description: "$methodName"
            );
        } else {
            echo "\n--- SINGLE EXECUTION ---\n";
            $problem->$methodName();
        }
    }

    /**
     * @throws Exception
     */
    private function compareAllMethods(ProblemInterface $problem): void
    {
        $methods = $problem->getAvailableMethods();
        if (count($methods) < 2) {
            echo "At least 2 methods required for comparison!\n";
            return;
        }

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "COMPARISON OF ALL ALGORITHMS\n";
        echo str_repeat('=', 70) . "\n";

        $iterations = (int)$this->getUserInput('Number of repetitions per algorithm (default 3): ') ?: 3;
        $results = [];

        foreach ($methods as $methodName => $description) {
            echo "\nTest: $methodName...\n";

            $stats = $this->performance->benchmark(
              callable: function() use ($problem, $methodName) {
                  ob_start();
                  $problem->$methodName();
                  ob_get_clean();
              },
              iterations: $iterations,
              showDetails: true,
              description: $methodName
            );

            $results[] = [
              'name' => $methodName,
              'description' => $description,
              'avg_duration' => $stats['avg_duration']
            ];
        }

        // Sort by execution time
        usort($results, fn($a, $b) => $a['avg_duration'] <=> $b['avg_duration']);

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "RESULTS - RANKING BY SPEED\n";
        echo str_repeat('=', 70) . "\n";

        foreach ($results as $rank => $result) {
            echo ($rank + 1) . '. ' . $result['name'] . "\n";
            echo '   ' . $result['description'] . "\n";
            echo '   Time: ' . $this->performance->formatDuration($result['avg_duration']) . "\n\n";
        }
    }
}

// === MAIN PROGRAMM ===
$runner = new AlgorithmTestRunner();
try {
    $runner->run();
} catch (Exception $e) {
    echo $e;
}