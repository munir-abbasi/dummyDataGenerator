<?php
/**
 * Mock helpers for PKP Repo classes
 *
 * Provides mock implementations of PKP repository classes for unit testing.
 * Use these mocks to isolate unit tests from database dependencies.
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests\Mocks
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Mocks;

use Mockery;
use Mockery\MockInterface;

class MockRepo
{
    private static ?MockInterface $alias = null;

    /**
     * Initialize the Repo alias mock (called once per test)
     *
     * @return MockInterface The alias mock for APP\facades\Repo
     */
    public static function init(): MockInterface
    {
        if (self::$alias === null) {
            self::$alias = Mockery::mock('alias:APP\facades\Repo');
        }
        return self::$alias;
    }

    /**
     * Reset the alias mock between tests
     */
    public static function reset(): void
    {
        self::$alias = null;
    }

    /**
     * Create a mock User repository
     *
     * Usage:
     *   $mockUserRepo = MockRepo::user();
     *   $mockUserRepo->shouldReceive('add')->once()->andReturn($mockUser);
     *
     * @return MockInterface
     */
    public static function user(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('user')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock UserGroup repository
     *
     * Usage:
     *   $mockUserGroupRepo = MockRepo::userGroup();
     *   $mockUserGroupRepo->shouldReceive('assign')->with($userId, $contextId)->once();
     *
     * @return MockInterface
     */
    public static function userGroup(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('userGroup')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock Submission repository
     *
     * Usage:
     *   $mockSubmissionRepo = MockRepo::submission();
     *   $mockSubmissionRepo->shouldReceive('add')->once()->andReturn($mockSubmission);
     *
     * @return MockInterface
     */
    public static function submission(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('submission')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock SubmissionFile repository
     *
     * @return MockInterface
     */
    public static function submissionFile(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('submissionFile')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock Issue repository
     *
     * Usage:
     *   $mockIssueRepo = MockRepo::issue();
     *   $mockIssueRepo->shouldReceive('add')->once()->andReturn($mockIssue);
     *
     * @return MockInterface
     */
    public static function issue(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('issue')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock Publication repository
     *
     * @return MockInterface
     */
    public static function publication(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('publication')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock Decision repository
     *
     * @return MockInterface
     */
    public static function decision(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('decision')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a mock Author repository
     *
     * @return MockInterface
     */
    public static function author(): MockInterface
    {
        $mock = Mockery::mock();
        self::init()->shouldReceive('author')->andReturn($mock);
        return $mock;
    }

    /**
     * Create a generic mock that can be configured for any repo
     *
     * Usage:
     *   $mockRepo = MockRepo::generic();
     *   $mockRepo->shouldReceive('add', 'get', 'edit', 'delete');
     *
     * @return MockInterface
     */
    public static function generic(): MockInterface
    {
        return Mockery::mock();
    }
}
