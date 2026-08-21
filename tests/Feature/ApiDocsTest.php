<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    /**
     * Test that the API documentation page loads successfully.
     */
    public function test_api_docs_page_is_accessible(): void
    {
        $response = $this->get('/api-docs');

        $response->assertStatus(200);
        $response->assertSee('Waldo REST API');
        $response->assertSee('/api/v1/employees');
    }

    /**
     * Test that the OpenAPI JSON specification is generated correctly.
     */
    public function test_api_docs_spec_returns_valid_openapi_json(): void
    {
        $response = $this->get('/api-docs/spec');

        $response->assertStatus(200);
        $response->assertJsonPath('openapi', '3.0.3');
        $response->assertJsonStructure([
            'openapi',
            'info' => ['title', 'version'],
            'paths' => [
                '/api/v1/employees',
                '/api/v1/employees/{employeeCode}',
                '/letters/fonts/api',
            ],
            'components' => ['schemas'],
        ]);
    }
}
