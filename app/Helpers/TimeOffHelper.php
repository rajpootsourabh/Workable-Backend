<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeOffHelper
{
    public static function calculateTotalDays(Carbon $start, Carbon $end, string $firstDayType = 'full', string $lastDayType = 'full'): float
    {
        $dayCount = $start->diffInDays($end) + 1;

        if ($dayCount === 1) {
            return $firstDayType === 'half' ? 0.5 : 1;
        }

        $total = ($firstDayType === 'half') ? 0.5 : 1;

        if ($dayCount > 2) {
            $total += ($dayCount - 2); // full middle days
        }

        $total += ($lastDayType === 'half') ? 0.5 : 1;

        return $total;
    }
}
