<?php
/**
 * @file tests/Integration/DummyDataAPIHandlerTest.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Integration tests for DummyDataAPIHandler endpoints
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Integration;

use APP\plugins\generic\dummyDataGenerator\api\DummyDataAPIHandler;
use APP\plugins\generic\dummyDataGenerator\DummyDataGeneratorPlugin;
use Mockery;
use PHPUnit\Framework\TestCase;

class DummyDataAPIHandlerTest extends TestCase
{
    private DummyDataAPIHandler $handler;
    private DummyDataGeneratorPlugin $plugin;

    protected function setUp(): void
    {
        if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
            $this->markTestSkipped('Requires OJS environment');
        }

        $this->plugin = Mockery::mock(DummyDataGeneratorPlugin::class);
        $this->handler = new DummyDataAPIHandler($this->plugin);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Verify handler responds to generateUsers with validation error
     * when no context is available
     *
     * @group integration
     */
    public function test_generateUsers_returns_error_without_context(): void
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('count', 10)
            ->andReturn(5);

        $response = $this->handler->generateUsers($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Verify cleanup requires confirmation string
     *
     * @group integration
     */
    public function test_cleanup_requires_confirmation(): void
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('confirm')
            ->andReturn(null);

        $response = $this->handler->cleanup($request);
        $this->assertEquals(400, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    /**
     * Verify cleanup rejects incorrect confirmation string
     *
     * @group integration
     */
    public function test_cleanup_rejects_wrong_confirmation(): void
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('confirm')
            ->andReturn('WRONG_STRING');

        $response = $this->handler->cleanup($request);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
