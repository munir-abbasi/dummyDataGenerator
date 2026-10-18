<?php
/**
 * @file DataTracker.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Track created entities for reversible cleanup
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

use APP\facades\Repo;

class DataTracker
{
    /**
     * Setting prefix for tracking data
     */
    private const TRACKING_SETTING_PREFIX = 'dummyData_';

    /**
     * Maximum number of tracked entities per context
     */
    private const MAX_TRACKED_ENTITIES = 1000;

    /**
     * Check if context has tracking capacity for new entities before creation
     *
     * @param \PKP\context\Context $context Context
     * @param string $type Entity type ('users', 'submissions', 'issues')
     * @param int $count Number of entities to be added
     * @throws \InvalidArgumentException if entity type is unknown
     * @throws \RuntimeException if tracking capacity would be exceeded
     */
    public function checkCapacity(\PKP\context\Context $context, string $type, int $count): void
    {
        $existing = match ($type) {
            'users' => $this->getTrackedUsers($context),
            'submissions' => $this->getTrackedSubmissions($context),
            'issues' => $this->getTrackedIssues($context),
            default => throw new \InvalidArgumentException("Invalid tracking entity type: {$type}"),
        };

        if (count($existing) + $count > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked ' . $type . ' limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }
    }

    /**
     * Track created users
     *
     * @param array $userIds User IDs to track
     * @param \PKP\context\Context $context Context
     */
    public function trackUsers(array $userIds, \PKP\context\Context $context): void
    {
        $existing = $this->getTrackedUsers($context);
        $merged = array_merge($existing, $userIds);

        // Limit to prevent context settings bloat
        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked users limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $context->updateSetting(
            self::TRACKING_SETTING_PREFIX . 'users',
            array_values(array_unique($merged)),
            'object'
        );
    }

    /**
     * Get tracked users
     *
     * @param \PKP\context\Context $context Context
     * @return array Array of user IDs
     */
    public function getTrackedUsers(\PKP\context\Context $context): array
    {
        $userIds = $context->getData(self::TRACKING_SETTING_PREFIX . 'users');
        return is_array($userIds) ? array_values($userIds) : [];
    }

    /**
     * Track created submissions
     *
     * @param array $submissionIds Submission IDs to track
     * @param \PKP\context\Context $context Context
     */
    public function trackSubmissions(array $submissionIds, \PKP\context\Context $context): void
    {
        $existing = $this->getTrackedSubmissions($context);
        $merged = array_merge($existing, $submissionIds);

        // Limit to prevent context settings bloat
        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked submissions limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $context->updateSetting(
            self::TRACKING_SETTING_PREFIX . 'submissions',
            array_values(array_unique($merged)),
            'object'
        );
    }

    /**
     * Get tracked submissions
     *
     * @param \PKP\context\Context $context Context
     * @return array Array of submission IDs
     */
    public function getTrackedSubmissions(\PKP\context\Context $context): array
    {
        $submissionIds = $context->getData(self::TRACKING_SETTING_PREFIX . 'submissions');
        return is_array($submissionIds) ? array_values($submissionIds) : [];
    }

    /**
     * Track created issues
     *
     * @param array $issueIds Issue IDs to track
     * @param \PKP\context\Context $context Context
     */
    public function trackIssues(array $issueIds, \PKP\context\Context $context): void
    {
        $existing = $this->getTrackedIssues($context);
        $merged = array_merge($existing, $issueIds);

        // Limit to prevent context settings bloat
        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked issues limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $context->updateSetting(
            self::TRACKING_SETTING_PREFIX . 'issues',
            array_values(array_unique($merged)),
            'object'
        );
    }

    /**
     * Get tracked issues
     *
     * @param \PKP\context\Context $context Context
     * @return array Array of issue IDs
     */
    public function getTrackedIssues(\PKP\context\Context $context): array
    {
        $issueIds = $context->getData(self::TRACKING_SETTING_PREFIX . 'issues');
        return is_array($issueIds) ? array_values($issueIds) : [];
    }

    /**
     * Delete all tracked entities with safe tracking preservation on failure
     *
     * @param \PKP\context\Context $context Context
     * @return array Deletion statistics and error details
     */
    public function cleanup(\PKP\context\Context $context): array
    {
        $deleted = [
            'users' => 0,
            'submissions' => 0,
            'issues' => 0,
        ];
        $remaining = [
            'users' => [],
            'submissions' => [],
            'issues' => [],
        ];
        $errors = [
            'users' => [],
            'submissions' => [],
            'issues' => [],
        ];

        // 1. Delete tracked issues first (they reference submissions)
        $this->cleanupIssues($context, $deleted, $remaining, $errors);

        // 2. Delete tracked submissions
        $this->cleanupSubmissions($context, $deleted, $remaining, $errors);

        // 3. Delete tracked users
        $this->cleanupUsers($context, $deleted, $remaining, $errors);

        // Persist tracking updates: clear setting if all gone, or keep remaining failed IDs
        $this->updateTrackingAfterCleanup($context, 'issues', $remaining['issues']);
        $this->updateTrackingAfterCleanup($context, 'submissions', $remaining['submissions']);
        $this->updateTrackingAfterCleanup($context, 'users', $remaining['users']);

        return [
            'users' => $deleted['users'],
            'submissions' => $deleted['submissions'],
            'issues' => $deleted['issues'],
            'failed' => [
                'users' => count($remaining['users']),
                'submissions' => count($remaining['submissions']),
                'issues' => count($remaining['issues']),
            ],
            'errors' => $errors,
        ];
    }

    /**
     * Delete tracked issues
     */
    private function cleanupIssues(\PKP\context\Context $context, array &$deleted, array &$remaining, array &$errors): void
    {
        $issueIds = $this->getTrackedIssues($context);
        foreach ($issueIds as $issueId) {
            try {
                $issue = Repo::issue()->get($issueId);
                if ($issue) {
                    Repo::issue()->delete($issue);
                    $deleted['issues']++;
                }
            } catch (\Exception $e) {
                $remaining['issues'][] = $issueId;
                $errors['issues'][] = "Failed to delete issue {$issueId}: " . $e->getMessage();
                error_log("DummyDataGenerator: Failed to delete issue {$issueId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Delete tracked submissions
     */
    private function cleanupSubmissions(\PKP\context\Context $context, array &$deleted, array &$remaining, array &$errors): void
    {
        $submissionIds = $this->getTrackedSubmissions($context);
        foreach ($submissionIds as $submissionId) {
            try {
                $submission = Repo::submission()->get($submissionId);
                if ($submission) {
                    Repo::submission()->delete($submission);
                    $deleted['submissions']++;
                }
            } catch (\Exception $e) {
                $remaining['submissions'][] = $submissionId;
                $errors['submissions'][] = "Failed to delete submission {$submissionId}: " . $e->getMessage();
                error_log("DummyDataGenerator: Failed to delete submission {$submissionId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Delete tracked users
     */
    private function cleanupUsers(\PKP\context\Context $context, array &$deleted, array &$remaining, array &$errors): void
    {
        $userIds = $this->getTrackedUsers($context);
        foreach ($userIds as $userId) {
            try {
                $user = Repo::user()->get($userId);
                if ($user) {
                    Repo::user()->delete($user);
                    $deleted['users']++;
                }
            } catch (\Exception $e) {
                $remaining['users'][] = $userId;
                $errors['users'][] = "Failed to delete user {$userId}: " . $e->getMessage();
                error_log("DummyDataGenerator: Failed to delete user {$userId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Update or clear tracking setting depending on remaining un-deleted entities
     */
    private function updateTrackingAfterCleanup(\PKP\context\Context $context, string $type, array $remaining): void
    {
        $settingKey = self::TRACKING_SETTING_PREFIX . $type;
        if (empty($remaining)) {
            $context->updateSetting($settingKey, null);
        } else {
            $context->updateSetting($settingKey, array_values($remaining), 'object');
        }
    }
}
