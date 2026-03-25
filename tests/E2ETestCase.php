<?php
/**
 * Base test case for end-to-end tests
 *
 * Provides full workflow testing with real OJS instance
 * and complete generation workflows
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests;

abstract class E2ETestCase extends IntegrationTestCase
{
    /** @var array Generated user IDs for cleanup */
    protected array $generatedUserIds = [];

    /** @var array Generated submission IDs for cleanup */
    protected array $generatedSubmissionIds = [];

    /** @var ?int Generated issue ID for cleanup */
    protected ?int $generatedIssueId = null;

    /**
     * Set up before each E2E test
     *
     * Ensures plugin is enabled and test environment is ready
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure plugin is enabled
        $this->ensurePluginEnabled();
    }

    /**
     * Tear down after each E2E test
     *
     * Cleans up all generated data
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Clean up generated data
        $this->cleanupGeneratedData();

        parent::tearDown();
    }

    /**
     * Ensure plugin is enabled in test environment
     *
     * Override in subclasses based on OJS plugin management
     *
     * @return void
     */
    protected function ensurePluginEnabled(): void
    {
        // In real OJS environment, check if plugin is enabled
        // Enable if necessary
    }

    /**
     * Generate users via API
     *
     * @param int $count Number of users to generate
     * @return array API response
     */
    protected function generateUsers(int $count): array
    {
        $response = $this->apiRequest('POST', '/api/v1/users/generate-users', [
            'count' => $count,
        ]);

        // Track generated users for cleanup
        if (isset($response['body']['userIds']) && is_array($response['body']['userIds'])) {
            $this->generatedUserIds = array_merge($this->generatedUserIds, $response['body']['userIds']);
        }

        return $response;
    }

    /**
     * Generate submissions via API
     *
     * @param int $count Number of submissions to generate
     * @return array API response
     */
    protected function generateSubmissions(int $count): array
    {
        $response = $this->apiRequest('POST', '/api/v1/users/generate-submissions', [
            'count' => $count,
        ]);

        // Track generated submissions for cleanup
        if (isset($response['body']['submissionIds']) && is_array($response['body']['submissionIds'])) {
            $this->generatedSubmissionIds = array_merge($this->generatedSubmissionIds, $response['body']['submissionIds']);
        }

        return $response;
    }

    /**
     * Generate issue via API
     *
     * @return array API response
     */
    protected function generateIssue(): array
    {
        $response = $this->apiRequest('POST', '/api/v1/users/generate-issue', []);

        // Track generated issue for cleanup
        if (isset($response['body']['issueId'])) {
            $this->generatedIssueId = (int) $response['body']['issueId'];
        }

        return $response;
    }

    /**
     * Cleanup all generated data via API
     *
     * @return array API response
     */
    protected function cleanup(): array
    {
        return $this->apiRequest('DELETE', '/api/v1/users/cleanup', [
            'confirm' => true,
        ]);
    }

    /**
     * Get current issue via API
     *
     * @return array API response
     */
    protected function getCurrentIssue(): array
    {
        return $this->apiRequest('GET', '/api/v1/issues/current', []);
    }

    /**
     * Get all users via API
     *
     * @return array API response
     */
    protected function getAllUsers(): array
    {
        return $this->apiRequest('GET', '/api/v1/users', []);
    }

    /**
     * Get all submissions via API
     *
     * @return array API response
     */
    protected function getAllSubmissions(): array
    {
        return $this->apiRequest('GET', '/api/v1/submissions', []);
    }

    /**
     * Clean up generated data
     *
     * Deletes all tracked users, submissions, and issues
     *
     * @return void
     */
    protected function cleanupGeneratedData(): void
    {
        // Use cleanup API if available
        if (!empty($this->generatedUserIds) || !empty($this->generatedSubmissionIds) || $this->generatedIssueId !== null) {
            try {
                $this->cleanup();
            } catch (\Exception $e) {
                // Log error but don't fail test
                error_log('E2E cleanup failed: ' . $e->getMessage());
            }
        }

        // Reset tracking arrays
        $this->generatedUserIds = [];
        $this->generatedSubmissionIds = [];
        $this->generatedIssueId = null;
    }

    /**
     * Assert complete workflow execution
     *
     * Verifies that users, submissions, and issue were created successfully
     *
     * @param int $expectedUserCount
     * @param int $expectedSubmissionCount
     * @return void
     */
    protected function assertWorkflowComplete(int $expectedUserCount, int $expectedSubmissionCount): void
    {
        $this->assertCount($expectedUserCount, $this->generatedUserIds, 'Expected user count mismatch');
        $this->assertCount($expectedSubmissionCount, $this->generatedSubmissionIds, 'Expected submission count mismatch');
        $this->assertNotNull($this->generatedIssueId, 'Issue ID should not be null');
    }
}
