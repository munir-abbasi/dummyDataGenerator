<?php
/**
 * Mock helpers for PKP Services
 *
 * Provides mock implementations of PKP service classes for unit testing.
 * Services are accessed via PKP\Services\Services::get() facade.
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests\Mocks
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Mocks;

use Mockery;
use Mockery\MockInterface;
use PKP\Services\Services;

class MockServices
{
    /**
     * Mock the Services facade
     *
     * This sets up a mock for the Services::get() method
     *
     * @return MockInterface Services mock
     */
    public static function mockServices(): MockInterface
    {
        return Mockery::mock('alias:' . Services::class);
    }

    /**
     * Set up mock for user service
     *
     * Usage:
     *   $mockUserService = MockServices::userService();
     *   $mockUserService->shouldReceive('get')->with('user')->andReturn($mockService);
     *
     * @return MockInterface
     */
    public static function userService(): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with('user')
            ->andReturn($mockService);

        return $mockService;
    }

    /**
     * Set up mock for file service
     *
     * Usage:
     *   $mockFileService = MockServices::fileService();
     *   $mockFileService->shouldReceive('add')->once()->andReturn($fileId);
     *
     * @return MockInterface
     */
    public static function fileService(): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with('file')
            ->andReturn($mockService);

        return $mockService;
    }

    /**
     * Set up mock for submission service
     *
     * @return MockInterface
     */
    public static function submissionService(): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with('submission')
            ->andReturn($mockService);

        return $mockService;
    }

    /**
     * Set up mock for decision service
     *
     * @return MockInterface
     */
    public static function decisionService(): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with('decision')
            ->andReturn($mockService);

        return $mockService;
    }

    /**
     * Set up mock for schema service
     *
     * @return MockInterface
     */
    public static function schemaService(): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with('schema')
            ->andReturn($mockService);

        return $mockService;
    }

    /**
     * Set up mock for context service
     *
     * @return MockInterface
     */
    public static function contextService(): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with('context')
            ->andReturn($mockService);

        return $mockService;
    }

    /**
     * Create a custom service mock
     *
     * Usage:
     *   $mockService = MockServices::custom('customService');
     *
     * @param string $serviceName Service name
     * @return MockInterface
     */
    public static function custom(string $serviceName): MockInterface
    {
        $mockService = Mockery::mock();
        self::mockServices()
            ->shouldReceive('get')
            ->with($serviceName)
            ->andReturn($mockService);

        return $mockService;
    }
}
