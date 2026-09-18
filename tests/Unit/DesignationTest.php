<?php

namespace Tests\Unit;

use App\Models\Designation;
use PHPUnit\Framework\TestCase;

class DesignationTest extends TestCase
{
    public function test_designation_has_job_description_in_fillable(): void
    {
        $designation = new Designation;
        $this->assertContains('job_description', $designation->getFillable());
    }
}
