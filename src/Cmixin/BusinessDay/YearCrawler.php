<?php

namespace Cmixin\BusinessDay;

use Cmixin\BusinessDay\Calculator\HolidayCalculator;
use Exception;

class YearCrawler extends HolidaysList
{
    /**
     * Get the holidays dates for a the given year (current year if no parameter given).
     *
     * @return \Closure
     */
    public function getYearHolidays()
    {
        /**
         * Get the holidays dates for a given year (current year if no parameter given).
         *
         * @param int    $year input year, year of the current instance or context used if omitted, current year used if omitted and called statically
         * @param string $type can be 'string' (to return dates as string) or a class name to returns instances of this class
         *
         * @return array
         */
        return function ($year = null, $type = null, $self = null) {
            $carbonClass = @get_class() ?: Emulator::getClass(new Exception());
            $next = $carbonClass::getYearHolidaysNextFunction($year, $type, $self);
            $holidays = [];

            while ($data = $next()) {
                [$key, $holiday] = $data;

                $holidays[$key] = $holiday;
            }

            return $holidays;
        };
    }

    /**
     * Get the holidays of the given year (current year if no parameter given).
     *
     * @return \Closure
     */
    public function getYearHolidaysNextFunction()
    {
        $mixin = $this;

        /**
         * Get a next() callback to call to iterate over holidays of a year.
         *
         * @param int    $year input year, year of the current instance or context used if omitted, current year used if omitted and called statically
         * @param string $type can be 'string' (to return dates as string) or a class name to returns instances of this class
         *
         * @return callable
         */
        return function ($year = null, $type = null, $self = null) use ($mixin): callable {
            $carbonClass = @get_class() ?: Emulator::getClass(new Exception());
            $year = $year ?: $carbonClass::getThisOrToday($self, isset($this) && $this !== $mixin ? $this : null)->year;
            $holidays = $carbonClass::getHolidays();
            $outputClass = $type ? (is_string($type) && $type !== 'string' ? $type : 'DateTime') : $carbonClass;
            $holidaysList = [];
            $calculator = new HolidayCalculator((int) $year, $type, $holidays);
            $calculator->setOutputClass($outputClass);
            $calculator->setHolidaysList($holidaysList);

            return [$calculator, 'next'];
        };
    }
}
