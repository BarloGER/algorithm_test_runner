<?php

namespace src\utilities;

use function array_slice;
use function count;
use function implode;
use function intval;

class CustomFormatter {
    public function formatLargeNumber($number): string
    {
        if (is_string($number)) {
            // Check if it's a bcmath string with decimals
            if (str_contains($number, '.')) {
                // Remove trailing zeros and format with 8 decimal places max
                $number = bcadd($number, '0', 8);
                $number = rtrim($number, '0');
                $number = rtrim($number, '.');
            }

            // Check if the number is very large (scientific notation threshold)
            if (bccomp($number, '1000000000000000', 0) > 0) {
                return sprintf('%.3e', (float)$number) . ' (scientific notation)';
            }

            return $number;
        }

        if ($number > 1e15) {
            return sprintf('%.3e', $number) . ' (scientific notation)';
        }

        return number_format($number);
    }

    /**
     * Format list for display (with smart truncation for large lists)
     */
    public function formatList(array $list, int $maxElements = 10): string
    {
        if (count($list) <= $maxElements) {
            return '[' . implode(', ', $list) . ']';
        }

        $half = intval($maxElements / 2);
        $start = array_slice($list, 0, $half);
        $end = array_slice($list, -$half);

        return '[' . implode(', ', $start) . ', ... (' . (count($list) - $maxElements) . ' more), ' . implode(', ', $end) . ']';
    }
}