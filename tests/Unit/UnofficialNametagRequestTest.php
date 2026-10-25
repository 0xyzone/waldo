<?php

namespace Tests\Unit;

use App\Filament\Resources\UnofficialNametagRequests\UnofficialNametagRequestResource;
use App\Models\UnofficialNametagRequest;
use PHPUnit\Framework\TestCase;

class UnofficialNametagRequestTest extends TestCase
{
    public function test_model_has_expected_fillable_attributes(): void
    {
        $model = new UnofficialNametagRequest;
        $expected = [
            'source',
            'employee_code',
            'employee_name',
            'name_on_tag',
            'remarks',
            'status',
            'created_by',
        ];

        $this->assertEquals($expected, $model->getFillable());
    }

    public function test_resource_is_configured_under_purchase_and_store(): void
    {
        $this->assertSame('Purchase & Store', UnofficialNametagRequestResource::getNavigationGroup());
        $this->assertSame('Unofficial NameTag Requests', UnofficialNametagRequestResource::getNavigationLabel());
        $this->assertSame('Unofficial NameTag Request', UnofficialNametagRequestResource::getModelLabel());
        $this->assertSame('Unofficial NameTag Requests', UnofficialNametagRequestResource::getPluralModelLabel());
    }

    public function test_status_defaults_and_options(): void
    {
        $model = new UnofficialNametagRequest([
            'employee_name' => 'John Doe',
        ]);

        $this->assertSame('John Doe', $model->employee_name);
    }
}
