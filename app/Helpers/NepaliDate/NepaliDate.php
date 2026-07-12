<?php

/**
 * Nepali Date class
 *
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @copyright 2020 Nilambar Sharma
 */

namespace App\Helpers\NepaliDate;

/**
 * NepaliDate class.
 *
 * @since 1.0.0
 */
class NepaliDate
{
    /**
     * NepaliCalendar object.
     *
     * @since 1.0.0
     *
     * @var NepaliCalendar
     */
    protected $calendar;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->calendar = new NepaliCalendar;
    }

    /**
     * Convert AD date to BS.
     *
     * @since 1.0.0
     *
     * @param  int  $y  Year.
     * @param  int  $m  Month.
     * @param  int  $d  Day.
     * @return array Converted date.
     */
    public function convertAdToBs($y, $m, $d)
    {
        $output = [];

        $date = $this->validateDate($y, $m, $d, 'ad');

        if (! empty($date)) {
            $output = $this->calendar->convertEnglishToNepali($y, $m, $d);
        }

        return $output;
    }

    /**
     * Convert BS date to AD.
     *
     * @since 1.0.0
     *
     * @param  int  $y  Year.
     * @param  int  $m  Month.
     * @param  int  $d  Day.
     * @return array Converted date.
     */
    public function convertBsToAd($y, $m, $d)
    {
        $output = [];

        $date = $this->validateDate($y, $m, $d, 'bs');

        if (! empty($date)) {
            $output = $this->calendar->convertNepaliToEnglish($y, $m, $d);
        }

        return $output;
    }

    /**
     * Get formatted date.
     *
     * @since 1.0.0
     *
     * @param  array  $date  Date details.
     * @param  string  $format  Format.
     * @return string Formatted date.
     */
    public function getFormattedDate($date, $format)
    {
        return strtr($format, $date);
    }

    /**
     * Get date details by given date.
     *
     * @since 1.0.0
     *
     * @param  int  $y  Year.
     * @param  int  $m  Month.
     * @param  int  $d  Day.
     * @param  string  $type  Type.
     * @param  string  $language  Language.
     * @return array Date details.
     */
    public function getDetails($y, $m, $d, $type, $language = 'en')
    {
        $output = [];

        if ($type === 'bs') {
            $date = $this->validateDate($y, $m, $d, 'bs');

            if ($date) {
                $output = $this->getDateDetails($date, $language);
            }
        } elseif ($type === 'ad') {
            $date = $this->validateDate($y, $m, $d, 'ad');

            if ($date) {
                $new_date = $this->calendar->convertEnglishToNepali($y, $m, $d);

                $output = $this->getDateDetails($new_date, $language);
            }
        }

        return $output;
    }

    /**
     * Validate date.
     *
     * @since 1.0.0
     *
     * @param  int  $y  Year.
     * @param  int  $m  Month.
     * @param  int  $d  Day.
     * @param  string  $type  Type.
     * @return array Date detail.
     */
    public function validateDate($y, $m, $d, $type)
    {
        $output = [];

        if ($type === 'bs') {
            $new_date = $this->calendar->convertNepaliToEnglish($y, $m, $d);

            if (is_array($new_date) && ! empty($new_date)) {
                $temp_date = $this->calendar->convertEnglishToNepali(
                    $new_date['year'],
                    $new_date['month'],
                    $new_date['day']
                );

                if (is_array($temp_date) && ! empty($temp_date)) {
                    if (
                        intval($y) === intval($temp_date['year'])
                        && intval($m) === intval($temp_date['month'])
                        && intval($d) === intval($temp_date['day'])
                    ) {
                        $output = $temp_date;
                    }
                }
            }
        } else {
            $new_date = $this->calendar->convertEnglishToNepali($y, $m, $d);

            if (is_array($new_date) && ! empty($new_date)) {
                $temp_date = $this->calendar->convertNepaliToEnglish(
                    $new_date['year'],
                    $new_date['month'],
                    $new_date['day']
                );

                if (is_array($temp_date) && ! empty($temp_date)) {
                    if (
                        intval($y) === intval($temp_date['year'])
                        && intval($m) === intval($temp_date['month'])
                        && intval($d) === intval($temp_date['day'])
                    ) {
                        $output = $temp_date;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Get BS date details.
     *
     * @since 1.0.0
     *
     * @param  array  $date  Date in BS.
     * @param  string  $language  Language.
     * @return array Date details.
     */
    private function getDateDetails($date, $language = 'en')
    {
        $output = [];

        // Year two digit.
        $year2 = substr($date['year'], -2);

        if ($language === 'np') {
            $output['Y'] = $this->getNepaliNumber($date['year']);
            $output['y'] = $this->getNepaliNumber($year2);
            $output['j'] = $this->getNepaliNumber($date['day']);
            $output['d'] = $this->getNepaliNumber($date['day'], true);
            $output['F'] = $this->getMonthText($date['month'], $language);
            $output['n'] = $this->getNepaliNumber($date['month']);
            $output['m'] = $this->getNepaliNumber($date['month'], true);
            $output['l'] = $this->getNepaliDayText($date['weekday']);
            $output['D'] = $this->getNepaliDayText($date['weekday'], 'D');
        } else {
            $output['Y'] = (string) $date['year'];
            $output['y'] = $year2;
            $output['j'] = (string) $date['day'];
            $output['d'] = str_pad($date['day'], 2, '0', STR_PAD_LEFT);
            $output['F'] = $this->getMonthText($date['month'], $language);
            $output['n'] = (string) $date['month'];
            $output['m'] = str_pad($date['month'], 2, '0', STR_PAD_LEFT);
            $output['l'] = $this->getEnglishDayText($date['weekday']);
            $output['D'] = $this->getEnglishDayText($date['weekday'], 'D');
        }

        return $output;
    }

    /**
     * Get Nepali number.
     *
     * @since 1.0.0
     *
     * @param  int  $number  Number.
     * @param  bool  $padding  Whether to do padding or not.
     * @param  int  $length  Padding length.
     * @return string Translated number.
     */
    private function getNepaliNumber($number, $padding = false, $length = 2)
    {
        $new_numbers = [];

        $digits = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

        $num_arr = str_split($number);

        $cnt = count($num_arr);

        for ($i = 0; $i < $cnt; $i++) {
            $new_numbers[$i] = $digits[$num_arr[$i]];
        }

        if ($padding === true) {
            $remaining = $length - $cnt;

            if ($remaining > 0) {
                for ($j = 0; $j < $remaining; $j++) {
                    array_unshift($new_numbers, $digits[0]);
                }
            }
        }

        return implode('', $new_numbers);
    }

    /**
     * Get Nepali day text.
     *
     * @since 1.0.0
     *
     * @param  int  $day  Day in number.
     * @param  string  $format  Format.
     * @return string Week text.
     */
    private function getNepaliDayText($day, $format = 'l')
    {
        $output = '';

        $details = $this->getNepaliWeekDetails();

        if (isset($details[$day][$format])) {
            $output = $details[$day][$format];
        }

        return $output;
    }

    /**
     * Get English day text.
     *
     * @since 1.0.0
     *
     * @param  int  $day  Day in number.
     * @param  string  $format  Format.
     * @return string Week text.
     */
    private function getEnglishDayText($day, $format = 'l')
    {
        $output = '';

        $details = $this->getEnglishWeekDetails();

        if (isset($details[$day][$format])) {
            $output = $details[$day][$format];
        }

        return $output;
    }

    /**
     * Get month text.
     *
     * @since 1.0.0
     *
     * @param  int  $month  Month in number.
     * @param  string  $language  Language.
     * @return string Month text.
     */
    private function getMonthText($month, $language = 'en')
    {
        $output = '';

        $details = $this->getNepaliMonthDetails();

        if (isset($details[$month][$language])) {
            $output = $details[$month][$language];
        }

        return $output;
    }

    /**
     * Get month details.
     *
     * @since 1.0.0
     *
     * @return array Month details.
     */
    private function getNepaliMonthDetails()
    {
        $output = [
            '1' => [
                'en' => 'Baishakh',
                'np' => 'वैशाख',
            ],
            '2' => [
                'en' => 'Jestha',
                'np' => 'जेठ',
            ],
            '3' => [
                'en' => 'Ashadh',
                'np' => 'असार',
            ],
            '4' => [
                'en' => 'Shrawan',
                'np' => 'साउन',
            ],
            '5' => [
                'en' => 'Bhadra',
                'np' => 'भदौ',
            ],
            '6' => [
                'en' => 'Ashwin',
                'np' => 'असोज',
            ],
            '7' => [
                'en' => 'Kartik',
                'np' => 'कात्तिक',
            ],
            '8' => [
                'en' => 'Mangsir',
                'np' => 'मंसिर',
            ],
            '9' => [
                'en' => 'Paush',
                'np' => 'पुष',
            ],
            '10' => [
                'en' => 'Magh',
                'np' => 'माघ',
            ],
            '11' => [
                'en' => 'Falgun',
                'np' => 'फागुन',
            ],
            '12' => [
                'en' => 'Chaitra',
                'np' => 'चैत',
            ],
        ];

        return $output;
    }

    /**
     * Get Nepali week details.
     *
     * @since 1.0.0
     *
     * @return array Week details.
     */
    private function getNepaliWeekDetails()
    {
        $output = [
            '1' => [
                'l' => 'आइतबार',
                'D' => 'आइत',
            ],
            '2' => [
                'l' => 'सोमबार',
                'D' => 'सोम',
            ],
            '3' => [
                'l' => 'मंगलबार',
                'D' => 'मंगल',
            ],
            '4' => [
                'l' => 'बुधबार',
                'D' => 'बुध',
            ],
            '5' => [
                'l' => 'बिहिबार',
                'D' => 'बिहि',
            ],
            '6' => [
                'l' => 'शुक्रबार',
                'D' => 'शुक्र',
            ],
            '7' => [
                'l' => 'शनिबार',
                'D' => 'शनि',
            ],
        ];

        return $output;
    }

    /**
     * Get English week details.
     *
     * @since 1.0.0
     *
     * @return array Week details.
     */
    private function getEnglishWeekDetails()
    {
        $output = [
            '1' => [
                'l' => 'Sunday',
                'D' => 'Sun',
            ],
            '2' => [
                'l' => 'Monday',
                'D' => 'Mon',
            ],
            '3' => [
                'l' => 'Tuesday',
                'D' => 'Tue',
            ],
            '4' => [
                'l' => 'Wednesday',
                'D' => 'Wed',
            ],
            '5' => [
                'l' => 'Thursday',
                'D' => 'Thu',
            ],
            '6' => [
                'l' => 'Friday',
                'D' => 'Fri',
            ],
            '7' => [
                'l' => 'Saturday',
                'D' => 'Sat',
            ],
        ];

        return $output;
    }
}
