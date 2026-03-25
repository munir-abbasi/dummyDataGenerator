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
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('user')
            ->andReturnSelf()
            ->getMock();
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
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('userGroup')
            ->andReturnSelf()
            ->getMock();
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
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('submission')
            ->andReturnSelf()
            ->getMock();
    }

    /**
     * Create a mock SubmissionFile repository
     *
     * @return MockInterface
     */
    public static function submissionFile(): MockInterface
    {
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('submissionFile')
            ->andReturnSelf()
            ->getMock();
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
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('issue')
            ->andReturnSelf()
            ->getMock();
    }

    /**
     * Create a mock Publication repository
     *
     * @return MockInterface
     */
    public static function publication(): MockInterface
    {
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('publication')
            ->andReturnSelf()
            ->getMock();
    }

    /**
     * Create a mock Decision repository
     *
     * @return MockInterface
     */
    public static function decision(): MockInterface
    {
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('decision')
            ->andReturnSelf()
            ->getMock();
    }

    /**
     * Create a mock Author repository
     *
     * @return MockInterface
     */
    public static function author(): MockInterface
    {
        return Mockery::mock('APP\facades\Repo')
            ->shouldReceive('author')
            ->andReturnSelf()
            ->getMock();
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
