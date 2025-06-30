<?php

namespace src\utilities;

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
}