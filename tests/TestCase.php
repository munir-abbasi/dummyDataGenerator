<?php
/**
 * Base test case for unit tests
 *
 * Provides common setup, teardown, and helper methods for unit testing
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Mockery;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up before each test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Reset any static state between tests
    }

    /**
     * Tear down after each test
     *
     * Closes Mockery containers to prevent memory leaks
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Close Mockery containers
        Mockery::close();
        parent::tearDown();
    }
}
