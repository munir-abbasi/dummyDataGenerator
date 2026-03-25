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

use APP\core\Application;
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
     * Track created users
     *
     * @param array $userIds User IDs to track
     * @param int $contextId Context ID
     */
    public function trackUsers(array $userIds, int $contextId): void
    {
        $existing = $this->getTrackedUsers($contextId);
        $merged = array_merge($existing, $userIds);

        // Limit to prevent context settings bloat
        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked users limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $context = Application::get()->getRequest()->getContext();
        if ($context) {
            $context->updateSetting(
                self::TRACKING_SETTING_PREFIX . 'users',
                array_unique($merged),
                'object'
            );
        }
    }

    /**
     * Get tracked users
     *
     * @param int $contextId Context ID
     * @return array Array of user IDs
     */
    public function getTrackedUsers(int $contextId): array
    {
        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return [];
        }

        $userIds = $context->getData(self::TRACKING_SETTING_PREFIX . 'users');
        return is_array($userIds) ? $userIds : [];
    }

    /**
     * Track created submissions
     *
     * @param array $submissionIds Submission IDs to track
     * @param int $contextId Context ID
     */
    public function trackSubmissions(array $submissionIds, int $contextId): void
    {
        $existing = $this->getTrackedSubmissions($contextId);
        $merged = array_merge($existing, $submissionIds);

        // Limit to prevent context settings bloat
        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked submissions limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $context = Application::get()->getRequest()->getContext();
        if ($context) {
            $context->updateSetting(
                self::TRACKING_SETTING_PREFIX . 'submissions',
                array_unique($merged),
                'object'
            );
        }
    }

    /**
     * Get tracked submissions
     *
     * @param int $contextId Context ID
     * @return array Array of submission IDs
     */
    public function getTrackedSubmissions(int $contextId): array
    {
        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return [];
        }

        $submissionIds = $context->getData(self::TRACKING_SETTING_PREFIX . 'submissions');
        return is_array($submissionIds) ? $submissionIds : [];
    }

    /**
     * Track created issues
     *
     * @param array $issueIds Issue IDs to track
     * @param int $contextId Context ID
     */
    public function trackIssues(array $issueIds, int $contextId): void
    {
        $existing = $this->getTrackedIssues($contextId);
        $merged = array_merge($existing, $issueIds);

        // Limit to prevent context settings bloat
        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked issues limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $context = Application::get()->getRequest()->getContext();
        if ($context) {
            $context->updateSetting(
                self::TRACKING_SETTING_PREFIX . 'issues',
                array_unique($merged),
                'object'
            );
        }
    }

    /**
     * Get tracked issues
     *
     * @param int $contextId Context ID
     * @return array Array of issue IDs
     */
    public function getTrackedIssues(int $contextId): array
    {
        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return [];
        }

        $issueIds = $context->getData(self::TRACKING_SETTING_PREFIX . 'issues');
        return is_array($issueIds) ? $issueIds : [];
    }

    /**
     * Delete all tracked entities
     *
     * @param int $contextId Context ID
     * @return array Deletion statistics
     */
    public function cleanup(int $contextId): array
    {
        $deleted = [
            'users' => 0,
            'submissions' => 0,
            'issues' => 0,
        ];

        // Delete tracked issues first (they reference submissions)
        $issueIds = $this->getTrackedIssues($contextId);
        foreach ($issueIds as $issueId) {
            try {
                Repo::issue()->delete($issueId);
                $deleted['issues']++;
            } catch (\Exception $e) {
                error_log("DummyDataGenerator: Failed to delete issue {$issueId}: " . $e->getMessage());
            }
        }

        // Delete tracked submissions
        $submissionIds = $this->getTrackedSubmissions($contextId);
        foreach ($submissionIds as $submissionId) {
            try {
                Repo::submission()->delete($submissionId);
                $deleted['submissions']++;
            } catch (\Exception $e) {
                error_log("DummyDataGenerator: Failed to delete submission {$submissionId}: " . $e->getMessage());
            }
        }

        // Delete tracked users
        $userIds = $this->getTrackedUsers($contextId);
        foreach ($userIds as $userId) {
            try {
                Repo::user()->delete($userId);
                $deleted['users']++;
            } catch (\Exception $e) {
                error_log("DummyDataGenerator: Failed to delete user {$userId}: " . $e->getMessage());
            }
        }

        // Clear tracking settings
        $context = Application::get()->getRequest()->getContext();
        if ($context) {
            $context->updateSetting(self::TRACKING_SETTING_PREFIX . 'users', null);
            $context->updateSetting(self::TRACKING_SETTING_PREFIX . 'submissions', null);
            $context->updateSetting(self::TRACKING_SETTING_PREFIX . 'issues', null);
        }

        return $deleted;
    }
}
