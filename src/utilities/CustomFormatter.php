<?php

namespace src\utilities;

class CustomFormatter {
    public function formatLargeNumber($number): string
    {
        if (is_string($number)) {
            return $number;
        }

        if ($number > 1e15) {
            return sprintf('%.3e', $number) . ' (scientific notation)';
        }

        return number_format($number);
    }
}