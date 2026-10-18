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
        $this->plugin = Mockery::mock(DummyDataGeneratorPlugin::class);
        $this->handler = new DummyDataAPIHandler($this->plugin);
    }

    protected function tearDown(): void
    {
        \APP\core\Application::get()->setRequest(null);
        Mockery::close();
    }

    /**
     * Verify handler responds to generateUsers with validation error
     * when count is invalid
     */
    public function test_generateUsers_returns_error_with_invalid_count(): void
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('count', 10)
            ->andReturn(500); // Exceeds max_range of 100

        $response = $this->handler->generateUsers($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Verify handler responds to generateUsers with validation error
     * when no context is available
     */
    public function test_generateUsers_returns_error_without_context(): void
    {
        \APP\core\Application::get()->setRequest(null);

        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('count', 10)
            ->andReturn(5);

        $response = $this->handler->generateUsers($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Verify cleanup requires confirmation string
     */
    public function test_cleanup_requires_confirmation(): void
    {
        $context = Mockery::mock(\PKP\context\Context::class);
        $context->shouldReceive('getId')->andReturn(1);

        $appRequest = Mockery::mock('PKPRequest');
        $appRequest->shouldReceive('getContext')->andReturn($context);
        \APP\core\Application::get()->setRequest($appRequest);

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
     */
    public function test_cleanup_rejects_wrong_confirmation(): void
    {
        $context = Mockery::mock(\PKP\context\Context::class);
        $context->shouldReceive('getId')->andReturn(1);

        $appRequest = Mockery::mock('PKPRequest');
        $appRequest->shouldReceive('getContext')->andReturn($context);
        \APP\core\Application::get()->setRequest($appRequest);

        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('confirm')
            ->andReturn('WRONG_STRING');

        $response = $this->handler->cleanup($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Verify generation endpoints block and return HTTP 403 in production environment
     */
    public function test_generateUsers_blocks_when_production_environment_detected(): void
    {
        $context = Mockery::mock(\PKP\context\Context::class);
        $context->shouldReceive('getId')->andReturn(1);

        $appRequest = Mockery::mock('PKPRequest');
        $appRequest->shouldReceive('getContext')->andReturn($context);
        \APP\core\Application::get()->setRequest($appRequest);

        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('input')
            ->with('count', 10)
            ->andReturn(5);

        $response = $this->handler->generateUsers($request);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('disabled in production environments', $data['error']);
    }
}
