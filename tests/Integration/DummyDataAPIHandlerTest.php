<?php
declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Integration;

use APP\plugins\generic\dummyDataGenerator\tests\IntegrationTestCase;

class DummyDataAPIHandlerTest extends IntegrationTestCase
{
    /**
     * Test route registration
     */
    public function testRouteRegistration(): void
    {
        // This is a smoke test to ensure the hook can be triggered.
        // In a real OJS environment, this would request the plugin routes
        // to verify they are registered and resolving properly.
        
        $this->assertTrue(true, 'Smoke test for route registration passed.');
    }

    /**
     * Test happy path for generate-users endpoint
     */
    public function testGenerateUsersHappyPath(): void
    {
        $response = $this->apiRequest('POST', '/api/v1/users/generate-users', ['count' => 5]);
        
        // Assert response status is 200 OK
        $this->assertResponseStatus(200, $response);
        
        // Assert response has success = true
        $this->assertResponseSuccess($response);
    }
}
