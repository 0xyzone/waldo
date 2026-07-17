<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\GoogleSheetsService;
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
    public function test_join_date_formatted_returns_as_is(): void
    {
        $employee = Mockery::mock(Employee::class)->makePartial();
        $employee->join_date_formatted = '15 January, 2024';

        $result = $this->resolveField($employee, 'join_date_formatted');

        $this->assertSame('15 January, 2024', $result);
    }

    /** @test */
    public function test_join_date_formatted_returns_empty_string_when_null(): void
    {
        $employee = Mockery::mock(Employee::class)->makePartial();
        $employee->join_date_formatted = null;

        $this->assertSame('', $this->resolveField($employee, 'join_date_formatted'));
    }

    /** @test */
    public function test_join_date_is_not_in_column_map(): void
    {
        $service = Mockery::mock(GoogleSheetsService::class)->makePartial();
        $prop = new ReflectionProperty(GoogleSheetsService::class, 'columnMap');
        /** @var array<string,int> $map */
        $map = $prop->getValue($service);

        $this->assertArrayNotHasKey('join_date', $map);
    }

    /** @test */
    public function test_join_date_formatted_is_mapped_correctly(): void
    {
        $service = Mockery::mock(GoogleSheetsService::class)->makePartial();
        $prop = new ReflectionProperty(GoogleSheetsService::class, 'columnMap');
        /** @var array<string,int> $map */
        $map = $prop->getValue($service);

        $this->assertArrayHasKey('join_date_formatted', $map);
        $this->assertSame(8, $map['join_date_formatted']);
    }
}
