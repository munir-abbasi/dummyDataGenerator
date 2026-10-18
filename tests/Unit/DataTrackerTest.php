<?php
/**
 * @file tests/Unit/DataTrackerTest.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Unit tests for DataTracker cleanup and tracking logic
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\classes\DataTracker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PKP\context\Context;
use PHPUnit\Framework\TestCase;

class DataTrackerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private DataTracker $tracker;

    protected function setUp(): void
    {
        $this->tracker = new DataTracker();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_trackUsers_stores_user_ids(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn([]);
        $context->shouldReceive('updateSetting')
            ->with('dummyData_users', [1, 2, 3], 'object')
            ->once();

        $this->tracker->trackUsers([1, 2, 3], $context);
    }

    public function test_trackUsers_merges_with_existing(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn([1, 2]);
        $context->shouldReceive('updateSetting')
            ->with('dummyData_users', [1, 2, 3, 4], 'object')
            ->once();

        $this->tracker->trackUsers([3, 4], $context);
    }

    public function test_trackUsers_deduplicates(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn([1, 2]);
        $context->shouldReceive('updateSetting')
            ->with('dummyData_users', [1, 2, 3], 'object')
            ->once();

        $this->tracker->trackUsers([2, 3], $context);
    }

    public function test_trackUsers_throws_on_limit_exceeded(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum tracked users limit reached');

        $existingIds = range(1, 999);
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn($existingIds);

        $this->tracker->trackUsers([1000, 1001], $context);
    }

    public function test_getTrackedUsers_returns_empty_array_when_no_data(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn(null);

        $result = $this->tracker->getTrackedUsers($context);
        $this->assertEquals([], $result);
    }

    public function test_getTrackedUsers_returns_stored_ids(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn([10, 20, 30]);

        $result = $this->tracker->getTrackedUsers($context);
        $this->assertEquals([10, 20, 30], $result);
    }

    public function test_trackSubmissions_stores_submission_ids(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_submissions')
            ->andReturn([]);
        $context->shouldReceive('updateSetting')
            ->with('dummyData_submissions', [100, 200], 'object')
            ->once();

        $this->tracker->trackSubmissions([100, 200], $context);
    }

    public function test_trackSubmissions_throws_on_limit_exceeded(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum tracked submissions limit reached');

        $existingIds = range(1, 999);
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_submissions')
            ->andReturn($existingIds);

        $this->tracker->trackSubmissions([1000, 1001], $context);
    }

    public function test_getTrackedSubmissions_returns_empty_array_when_no_data(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_submissions')
            ->andReturn(null);

        $result = $this->tracker->getTrackedSubmissions($context);
        $this->assertEquals([], $result);
    }

    public function test_trackIssues_stores_issue_ids(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_issues')
            ->andReturn([]);
        $context->shouldReceive('updateSetting')
            ->with('dummyData_issues', [500], 'object')
            ->once();

        $this->tracker->trackIssues([500], $context);
    }

    public function test_getTrackedIssues_returns_empty_array_when_no_data(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_issues')
            ->andReturn(null);

        $result = $this->tracker->getTrackedIssues($context);
        $this->assertEquals([], $result);
    }

    public function test_checkCapacity_allows_when_within_limit(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn([1, 2, 3]);

        // Should not throw
        $this->tracker->checkCapacity($context, 'users', 10);
        $this->addToAssertionCount(1);
    }

    public function test_checkCapacity_throws_when_limit_exceeded(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum tracked users limit reached (1000)');

        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn(range(1, 995));

        $this->tracker->checkCapacity($context, 'users', 10);
    }

    public function test_checkCapacity_throws_on_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tracking entity type: invalid_type');

        $context = Mockery::mock(Context::class);
        $this->tracker->checkCapacity($context, 'invalid_type', 5);
    }

    public function test_getTrackedUsers_returns_reindexed_sequential_array(): void
    {
        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')
            ->with('dummyData_users')
            ->andReturn([2 => 10, 5 => 20]);

        $result = $this->tracker->getTrackedUsers($context);
        $this->assertEquals([10, 20], $result);
        $this->assertSame([0, 1], array_keys($result));
    }

    /**
     * Integration test: verify cleanup returns correct statistics structure
     * Requires OJS environment with Repo facades available
     *
     * @group integration
     */
    public function test_cleanup_returns_deletion_statistics(): void
    {
        if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
            $this->markTestSkipped('Requires OJS environment with Repo facades');
        }

        $context = Mockery::mock(Context::class);
        $context->shouldReceive('getData')->with('dummyData_users')->andReturn([]);
        $context->shouldReceive('getData')->with('dummyData_submissions')->andReturn([]);
        $context->shouldReceive('getData')->with('dummyData_issues')->andReturn([]);
        $context->shouldReceive('updateSetting')->with('dummyData_users', null)->once();
        $context->shouldReceive('updateSetting')->with('dummyData_submissions', null)->once();
        $context->shouldReceive('updateSetting')->with('dummyData_issues', null)->once();

        $result = $this->tracker->cleanup($context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('submissions', $result);
        $this->assertArrayHasKey('issues', $result);
        $this->assertEquals(0, $result['users']);
        $this->assertEquals(0, $result['submissions']);
        $this->assertEquals(0, $result['issues']);
    }
}
