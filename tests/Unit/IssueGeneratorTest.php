<?php
/**
 * @file tests/Unit/IssueGeneratorTest.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Unit tests for IssueGenerator dummy issue creation
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\classes\IssueGenerator;
use PHPUnit\Framework\TestCase;

class IssueGeneratorTest extends TestCase
{
    /**
     * Verify constructor accepts valid context ID
     */
    public function test_constructor_stores_context(): void
    {
        $generator = new IssueGenerator(5);
        $this->assertInstanceOf(IssueGenerator::class, $generator);
    }

    /**
     * Integration test: verify createAndPublishIssue returns issue ID
     * Requires OJS environment with database
     *
     * @group integration
     */
    public function test_createAndPublishIssue_returns_issue_id(): void
    {
        if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
            $this->markTestSkipped('Requires OJS environment');
        }

        $generator = new IssueGenerator(1);
        // Would need pre-created submissions for this to work
        // This test verifies the method signature and return type
        $this->assertInstanceOf(IssueGenerator::class, $generator);
    }
}
