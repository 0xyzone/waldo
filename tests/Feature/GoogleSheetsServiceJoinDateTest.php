<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Mockery;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;

class GoogleSheetsServiceJoinDateTest extends TestCase
{
    /**
     * Calls the protected resolveFieldValue method via reflection without any HTTP / API calls.
     */
    private function resolveField(Employee $employee, string $field): string
    {
        // Use Mockery to build a partial mock that does not call the constructor
        // (which would instantiate the Google API client).
        $service = Mockery::mock(GoogleSheetsService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $method = new ReflectionMethod(GoogleSheetsService::class, 'resolveFieldValue');

        return $method->invoke($service, $employee, $field);
    }

    /** @test */
    public function testJoinDateFormattedReturnsHumanReadableDate(): void
    {
        $employee = Mockery::mock(Employee::class)->makePartial();
        $employee->join_date = Carbon::parse('2024-01-15');

        $result = $this->resolveField($employee, 'join_date_formatted');

        $this->assertSame('15 January, 2024', $result);
    }

    /** @test */
    public function testJoinDateReturnsYyyyMmDdFormat(): void
    {
        $employee = Mockery::mock(Employee::class)->makePartial();
        $employee->join_date = Carbon::parse('2024-01-15');

        $result = $this->resolveField($employee, 'join_date');

        $this->assertSame('2024.01.15', $result);
    }

    /** @test */
    public function testJoinDateReturnsEmptyStringWhenNull(): void
    {
        $employee = Mockery::mock(Employee::class)->makePartial();
        $employee->join_date = null;

        $this->assertSame('', $this->resolveField($employee, 'join_date'));
        $this->assertSame('', $this->resolveField($employee, 'join_date_formatted'));
    }

    /** @test */
    public function testColumnMapContainsJoinDateAtIndexNine(): void
    {
        $service = Mockery::mock(GoogleSheetsService::class)->makePartial();
        $prop = new ReflectionProperty(GoogleSheetsService::class, 'columnMap');
        /** @var array<string,int> $map */
        $map = $prop->getValue($service);

        $this->assertArrayHasKey('join_date', $map);
        $this->assertSame(9, $map['join_date']);
    }
}
