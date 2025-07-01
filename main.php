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