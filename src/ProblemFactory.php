<?php

declare(strict_types = 1);

namespace src;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use src\calculation_problems\GreatestCommonDivisor;
use src\calculation_problems\SumProblem;
use src\interfaces\ProblemInterface;

class ProblemFactory
{
    /**
     * Get all available problem classes
     * @return array Array of problem class names
     */
    public static function getAvailableProblems(): array
    {
        return [
          SumProblem::class,
          GreatestCommonDivisor::class,
        ];
    }

    /**
     * Create a problem instance without parameters
     * @param string $className
     * @return ProblemInterface
     * @throws ReflectionException
     */
    public static function createProblem(string $className): ProblemInterface
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Problem class '$className' not found");
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->implementsInterface(ProblemInterface::class)) {
            throw new InvalidArgumentException("Class '$className' must implement ProblemInterface");
        }

        // Create an instance without constructor parameters
        return $reflection->newInstanceWithoutConstructor();
    }
}