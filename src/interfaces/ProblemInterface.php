<?php
declare(strict_types = 1);

namespace src\interfaces;

interface ProblemInterface
{
    /**
     * Get problem name
     * @return string
     */
    public function getProblemName(): string;

    /**
     * Get problem description
     * @return string
     */
    public function getProblemDescription(): string;

    /**
     * Get all available algorithm methods
     * @return array Array of method names with descriptions
     */
    public function getAvailableMethods(): array;

    /**
     * Set up problem parameters by asking the user for input
     * This method should handle all user input required for the problem
     * @return void
     */
    public function setupParameters(): void;

    /**
     * Check if a problem is ready to run (parameters are set)
     * @return bool
     */
    public function isReady(): bool;

    /**
     * Reset problem to initial state
     * @return void
     */
    public function reset(): void;
}