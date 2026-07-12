<?php

/**
 * Calendar class
 */

namespace App\Helpers\NepaliDate;

/**
 * NepaliCalendar class.
 *
 * @since 1.0.0
 */
class NepaliCalendar
{
    /**
     * Nepali date details.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $bs = [
        0 => [2000, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        1 => [2001, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        2 => [2002, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        3 => [2003, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        4 => [2004, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        5 => [2005, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        6 => [2006, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        7 => [2007, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        8 => [2008, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        9 => [2009, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        10 => [2010, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        11 => [2011, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        12 => [2012, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        13 => [2013, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        14 => [2014, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        15 => [2015, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        16 => [2016, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        17 => [2017, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        18 => [2018, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        19 => [2019, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        20 => [2020, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        21 => [2021, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        22 => [2022, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        23 => [2023, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        24 => [2024, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        25 => [2025, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        26 => [2026, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        27 => [2027, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        28 => [2028, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        29 => [2029, 31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30],
        30 => [2030, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        31 => [2031, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        32 => [2032, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        33 => [2033, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        34 => [2034, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        35 => [2035, 30, 32, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        36 => [2036, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        37 => [2037, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        38 => [2038, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        39 => [2039, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        40 => [2040, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        41 => [2041, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        42 => [2042, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        43 => [2043, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        44 => [2044, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        45 => [2045, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        46 => [2046, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        47 => [2047, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        48 => [2048, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        49 => [2049, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        50 => [2050, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        51 => [2051, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        52 => [2052, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        53 => [2053, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        54 => [2054, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        55 => [2055, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        56 => [2056, 31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30],
        57 => [2057, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        58 => [2058, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        59 => [2059, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        60 => [2060, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        61 => [2061, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        62 => [2062, 30, 32, 31, 32, 31, 31, 29, 30, 29, 30, 29, 31],
        63 => [2063, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        64 => [2064, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        65 => [2065, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        66 => [2066, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        67 => [2067, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        68 => [2068, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        69 => [2069, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        70 => [2070, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        71 => [2071, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        72 => [2072, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        73 => [2073, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        74 => [2074, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        75 => [2075, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        76 => [2076, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        77 => [2077, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        78 => [2078, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        79 => [2079, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        80 => [2080, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        81 => [2081, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        82 => [2082, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        83 => [2083, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        84 => [2084, 31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        85 => [2085, 31, 32, 31, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        86 => [2086, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        87 => [2087, 31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30],
        88 => [2088, 30, 31, 32, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        89 => [2089, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        90 => [2090, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
    ];

    /**
     * Calculates whether English year is leap year or not.
     *
     * @since 1.0.0
     *
     * @param  int  $year  Year.
     * @return bool True if leap year.
     */
    private function isLeapYear($year)
    {
        $a = $year;

        if ($a % 100 === 0) {
            if ($a % 400 === 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($a % 4 === 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check if given date is in range.
     *
     * @since 1.0.0
     *
     * @param  int  $y  Year.
     * @param  int  $m  Month.
     * @param  int  $d  Day.
     * @param  string  $type  Type.
     * @return bool True if date is in range.
     */
    public function isDateInRange($y, $m, $d, $type = 'ad')
    {
        $output = true;

        $year_start = 1944;
        $year_end = 2033;
        $month_start = 1;
        $month_end = 12;
        $day_start = 1;
        $day_end = 31;

        if ($type === 'bs') {
            $year_start = 2000;
            $year_end = 2089;
            $month_start = 1;
            $month_end = 12;
            $day_start = 1;
            $day_end = 32;
        }

        if ($this->checkNumberInRange($y, $year_start, $year_end) !== true) {
            $output = false;
        }

        if ($this->checkNumberInRange($m, $month_start, $month_end) !== true) {
            $output = false;
        }

        if ($this->checkNumberInRange($d, $day_start, $day_end) !== true) {
            $output = false;
        }

        return $output;
    }

    /**
     * Check if given number is in range.
     *
     * @since 1.0.0
     *
     * @param  int  $value  Value.
     * @param  int  $min  Minimum number.
     * @param  int  $max  Maximum number.
     * @return bool True if number is in range.
     */
    private function checkNumberInRange($value, $min, $max)
    {
        return ($min <= $value) && ($value <= $max);
    }

    /**
     * Convert English date to Nepali.
     *
     * @since 1.0.0
     *
     * @param  int  $yy  Year.
     * @param  int  $mm  Month.
     * @param  int  $dd  Day.
     * @return array Converted date.
     */
    public function convertEnglishToNepali($yy, $mm, $dd)
    {
        if ($this->isDateInRange($yy, $mm, $dd, 'ad') !== true) {
            return;
        } else {
            // English month data.
            $month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            $lmonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            // Spear head English date.
            $def_eyy = 1944;
            $def_nyy = 2000;
            $def_nmm = 9;
            // Spear head nepali date.
            $def_ndd = 17 - 1;
            $total_edays = 0;
            $total_ndays = 0;
            $a = 0;
            // All the initializations.
            $day = 7 - 1;
            $m = 0;
            $y = 0;
            $i = 0;
            $j = 0;
            $num_day = 0;

            // Count total no. of days in terms of year.
            for ($i = 0; $i < ($yy - $def_eyy); $i++) {
                if ($this->isLeapYear($def_eyy + $i) === true) {
                    for ($j = 0; $j < 12; $j++) {
                        $total_edays += $lmonth[$j];
                    }
                } else {
                    for ($j = 0; $j < 12; $j++) {
                        $total_edays += $month[$j];
                    }
                }
            }

            // Count total no. of days in terms of month.
            for ($i = 0; $i < ($mm - 1); $i++) {
                if ($this->isLeapYear($yy) === true) {
                    $total_edays += $lmonth[$i];
                } else {
                    $total_edays += $month[$i];
                }
            }

            // Count total no. of days in terms of day.
            $total_edays += $dd;

            $i = 0;
            $j = $def_nmm;
            $total_ndays = $def_ndd;
            $m = $def_nmm;
            $y = $def_nyy;

            // Count Nepali date from array.
            while ($total_edays !== 0) {
                $a = $this->bs[$i][$j];
                // Count days.
                $total_ndays++;
                // Count the days in terms of 7 days.
                $day++;

                if ($total_ndays > $a) {
                    $m++;
                    $total_ndays = 1;
                    $j++;
                }

                if ($day > 7) {
                    $day = 1;
                }

                if ($m > 12) {
                    $y++;
                    $m = 1;
                }

                if ($j > 12) {
                    $j = 1;
                    $i++;
                }

                $total_edays--;
            }

            $num_day = $day;

            $output = [
                'year' => $y,
                'month' => $m,
                'day' => $total_ndays,
                'weekday' => $num_day,
            ];

            return $output;
        }
    }

    /**
     * Convert Nepali date to English.
     *
     * @since 1.0.0
     *
     * @param  int  $yy  Year.
     * @param  int  $mm  Month.
     * @param  int  $dd  Day.
     * @return array Converted date.
     */
    public function convertNepaliToEnglish($yy, $mm, $dd)
    {
        $def_eyy = 1943;
        $def_emm = 4;
        // Init English date.
        $def_edd = 14 - 1;
        $def_nyy = 2000;
        $def_nmm = 1;
        // Equivalent Nepali date.
        $def_ndd = 1;
        $total_edays = 0;
        $total_ndays = 0;
        $a = 0;
        // Initializations.
        $day = 4 - 1;
        $m = 0;
        $y = 0;
        $i = 0;
        $k = 0;
        $num_day = 0;

        $month = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $lmonth = [0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        if ($this->isDateInRange($yy, $mm, $dd, 'bs') !== true) {
            return;
        } else {
            // Count total days in terms of year.
            for ($i = 0; $i < ($yy - $def_nyy); $i++) {
                for ($j = 1; $j <= 12; $j++) {
                    $total_ndays += $this->bs[$k][$j];
                }

                $k++;
            }

            // Count total days in terms of month.
            for ($j = 1; $j < $mm; $j++) {
                $total_ndays += $this->bs[$k][$j];
            }

            // Count total days in terms of day.
            $total_ndays += $dd;

            // Calculation of equivalent English date.
            $total_edays = $def_edd;
            $m = $def_emm;
            $y = $def_eyy;

            while ($total_ndays !== 0) {
                if ($this->isLeapYear($y)) {
                    $a = $lmonth[$m];
                } else {
                    $a = $month[$m];
                }

                $total_edays++;
                $day++;

                if ($total_edays > $a) {
                    $m++;
                    $total_edays = 1;
                    if ($m > 12) {
                        $y++;
                        $m = 1;
                    }
                }

                if ($day > 7) {
                    $day = 1;
                }

                $total_ndays--;
            }

            $num_day = $day;

            $output = [
                'year' => $y,
                'month' => $m,
                'day' => $total_edays,
                'weekday' => $num_day,
            ];

            return $output;
        }
    }
}
