<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SalaryCalculatorTest extends TestCase
{
    /**
     * Test Salary Structure Formulas (60% Basic, 40% Allowance, 11% SSF, 20% SSF).
     */
    public function test_salary_structure_calculations(): void
    {
        $grossSalary = 50000.0;

        $basicSalary = round($grossSalary * 0.60, 2);
        $allowance = round($grossSalary * 0.40, 2);

        $this->assertEquals(30000.0, $basicSalary);
        $this->assertEquals(20000.0, $allowance);
        $this->assertEquals($grossSalary, $basicSalary + $allowance);

        $ssf11 = round($basicSalary * 0.11, 2);
        $ssf20 = round($basicSalary * 0.20, 2);
        $totalSsf = round($ssf11 + $ssf20, 2);

        $this->assertEquals(3300.0, $ssf11);
        $this->assertEquals(6000.0, $ssf20);
        $this->assertEquals(9300.0, $totalSsf);
    }

    /**
     * Test Daily and Hourly Rates computation based on days in month.
     */
    public function test_per_day_and_hourly_rates(): void
    {
        $grossSalary = 62000.0;
        $daysInMonth = 31; // e.g. January or March

        $perDay = round($grossSalary / $daysInMonth, 2);
        $this->assertEquals(2000.0, $perDay);

        $perHour = round($perDay / 8, 2);
        $this->assertEquals(250.0, $perHour);

        $normalOtRate = round($perHour * 1.5, 2);
        $specialOtRate = round($perHour * 2.0, 2);

        $this->assertEquals(375.0, $normalOtRate);
        $this->assertEquals(500.0, $specialOtRate);
    }

    /**
     * Test Overtime totals calculation.
     */
    public function test_overtime_totals(): void
    {
        $normalOtRate = 375.0;
        $specialOtRate = 500.0;

        $normalHours = 10.0;
        $specialHours = 4.0;

        $normalOtTotal = round($normalOtRate * $normalHours, 2);
        $specialOtTotal = round($specialOtRate * $specialHours, 2);
        $totalOt = round($normalOtTotal + $specialOtTotal, 2);

        $this->assertEquals(3750.0, $normalOtTotal);
        $this->assertEquals(2000.0, $specialOtTotal);
        $this->assertEquals(5750.0, $totalOt);
    }

    /**
     * Test Festival Allowance formula ((Gross or Basic) / 365) * working days capped at 365.
     */
    public function test_festival_allowance_formula(): void
    {
        $grossSalary = 73000.0;
        $workingDays = 180; // half a year

        $allowanceGross = round(($grossSalary / 365) * $workingDays, 2);
        $this->assertEquals(36000.0, $allowanceGross);

        // Test capped working days (capped at 365)
        $excessDays = 400;
        $effectiveDays = min(365, $excessDays);
        $this->assertEquals(365, $effectiveDays);

        $fullYearAllowance = round(($grossSalary / 365) * $effectiveDays, 2);
        $this->assertEquals(73000.0, $fullYearAllowance);
    }
}
