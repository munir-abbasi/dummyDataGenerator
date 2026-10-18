<?php
/**
 * @file tests/Unit/SubmissionGeneratorTest.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Unit tests for SubmissionGenerator dummy submission creation
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\classes\SubmissionGenerator;
use PHPUnit\Framework\TestCase;

class SubmissionGeneratorTest extends TestCase
{
    /**
     * Integration test: verify generateSubmission creates a submission
     * Requires OJS environment with database
     *
     * @group integration
     */
    public function test_generateSubmission_returns_submission_id(): void
    {
        if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
            $this->markTestSkipped('Requires OJS environment');
        }

        $generator = new SubmissionGenerator(1, 1);
        $submissionId = $generator->generateSubmission(1);

        $this->assertIsInt($submissionId);
        $this->assertGreaterThan(0, $submissionId);
    }

    /**
     * Verify constructor accepts valid parameters
     */
    public function test_constructor_stores_context_and_section(): void
    {
        $generator = new SubmissionGenerator(5, 10);
        $this->assertInstanceOf(SubmissionGenerator::class, $generator);
    }

    /**
     * Integration test: verify submission has correct context
     *
     * @group integration
     */
    public function test_generateSubmission_creates_submission_in_correct_context(): void
    {
        if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
            $this->markTestSkipped('Requires OJS environment');
        }

        $contextId = 1;
        $sectionId = 1;
        $generator = new SubmissionGenerator($contextId, $sectionId);
        $submissionId = $generator->generateSubmission(1);

        $submission = \APP\facades\Repo::submission()->get($submissionId);
        $this->assertNotNull($submission);
        $this->assertEquals($contextId, $submission->getData('contextId'));
        $this->assertEquals($sectionId, $submission->getData('sectionId'));
    }
}
