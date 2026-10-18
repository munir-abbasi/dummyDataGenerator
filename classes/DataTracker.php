<?php
/**
 * @file DataTracker.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Track created entities for reversible cleanup using OJS Plugin Settings API
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

use APP\facades\Repo;
use PKP\context\Context;
use PKP\plugins\Plugin;
use PKP\plugins\PluginRegistry;
use PKP\db\DAORegistry;

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

    private ?Plugin $plugin;

    /**
     * Constructor
     *
     * @param Plugin|null $plugin Optional plugin instance for settings operations
     */
    public function __construct(?Plugin $plugin = null)
    {
        $this->plugin = $plugin;
    }

    /**
     * Resolve context ID from Context object or integer
     */
    private function resolveContextId(Context|int $context): int
    {
        return $context instanceof Context ? $context->getId() : (int) $context;
    }

    /**
     * Retrieve a setting using OJS Plugin API
     */
    private function getSetting(int $contextId, string $name): mixed
    {
        if ($this->plugin !== null) {
            return $this->plugin->getSetting($contextId, $name);
        }

        if (class_exists(PluginRegistry::class)) {
            $plugin = PluginRegistry::getPlugin('generic', 'dummyDataGenerator');
            if ($plugin !== null) {
                return $plugin->getSetting($contextId, $name);
            }
        }

        if (class_exists(DAORegistry::class)) {
            $dao = DAORegistry::getDAO('PluginSettingsDAO');
            if ($dao) {
                return $dao->getSetting($contextId, 'dummyDataGenerator', $name);
            }
        }

        return null;
    }

    /**
     * Update or delete a setting using OJS Plugin API
     */
    private function updateSetting(int $contextId, string $name, mixed $value, ?string $type = null): void
    {
        if ($this->plugin !== null) {
            $this->plugin->updateSetting($contextId, $name, $value, $type);
            return;
        }

        if (class_exists(PluginRegistry::class)) {
            $plugin = PluginRegistry::getPlugin('generic', 'dummyDataGenerator');
            if ($plugin !== null) {
                $plugin->updateSetting($contextId, $name, $value, $type);
                return;
            }
        }

        if (class_exists(DAORegistry::class)) {
            $dao = DAORegistry::getDAO('PluginSettingsDAO');
            if ($dao) {
                if ($value === null) {
                    $dao->deleteSetting($contextId, 'dummyDataGenerator', $name);
                } else {
                    $dao->updateSetting($contextId, 'dummyDataGenerator', $name, $value, $type);
                }
                return;
            }
        }
    }

    /**
     * Check if context has tracking capacity for new entities before creation
     *
     * @param Context|int $context Context or context ID
     * @param string $type Entity type ('users', 'submissions', 'issues')
     * @param int $count Number of entities to be added
     * @throws \InvalidArgumentException if entity type is unknown
     * @throws \RuntimeException if tracking capacity would be exceeded
     */
    public function checkCapacity(Context|int $context, string $type, int $count): void
    {
        $contextId = $this->resolveContextId($context);
        $existing = match ($type) {
            'users' => $this->getTrackedUsers($contextId),
            'submissions' => $this->getTrackedSubmissions($contextId),
            'issues' => $this->getTrackedIssues($contextId),
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
     * @param Context|int $context Context or context ID
     */
    public function trackUsers(array $userIds, Context|int $context): void
    {
        $contextId = $this->resolveContextId($context);
        $existing = $this->getTrackedUsers($contextId);
        $merged = array_merge($existing, $userIds);

        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked users limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $this->updateSetting(
            $contextId,
            self::TRACKING_SETTING_PREFIX . 'users',
            array_values(array_unique($merged)),
            'object'
        );
    }

    /**
     * Get tracked users
     *
     * @param Context|int $context Context or context ID
     * @return array Array of user IDs
     */
    public function getTrackedUsers(Context|int $context): array
    {
        $contextId = $this->resolveContextId($context);
        $userIds = $this->getSetting($contextId, self::TRACKING_SETTING_PREFIX . 'users');
        return is_array($userIds) ? array_values($userIds) : [];
    }

    /**
     * Track created submissions
     *
     * @param array $submissionIds Submission IDs to track
     * @param Context|int $context Context or context ID
     */
    public function trackSubmissions(array $submissionIds, Context|int $context): void
    {
        $contextId = $this->resolveContextId($context);
        $existing = $this->getTrackedSubmissions($contextId);
        $merged = array_merge($existing, $submissionIds);

        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked submissions limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $this->updateSetting(
            $contextId,
            self::TRACKING_SETTING_PREFIX . 'submissions',
            array_values(array_unique($merged)),
            'object'
        );
    }

    /**
     * Get tracked submissions
     *
     * @param Context|int $context Context or context ID
     * @return array Array of submission IDs
     */
    public function getTrackedSubmissions(Context|int $context): array
    {
        $contextId = $this->resolveContextId($context);
        $submissionIds = $this->getSetting($contextId, self::TRACKING_SETTING_PREFIX . 'submissions');
        return is_array($submissionIds) ? array_values($submissionIds) : [];
    }

    /**
     * Track created issues
     *
     * @param array $issueIds Issue IDs to track
     * @param Context|int $context Context or context ID
     */
    public function trackIssues(array $issueIds, Context|int $context): void
    {
        $contextId = $this->resolveContextId($context);
        $existing = $this->getTrackedIssues($contextId);
        $merged = array_merge($existing, $issueIds);

        if (count($merged) > self::MAX_TRACKED_ENTITIES) {
            throw new \RuntimeException(
                'Maximum tracked issues limit reached (' . self::MAX_TRACKED_ENTITIES . ')'
            );
        }

        $this->updateSetting(
            $contextId,
            self::TRACKING_SETTING_PREFIX . 'issues',
            array_values(array_unique($merged)),
            'object'
        );
    }

    /**
     * Get tracked issues
     *
     * @param Context|int $context Context or context ID
     * @return array Array of issue IDs
     */
    public function getTrackedIssues(Context|int $context): array
    {
        $contextId = $this->resolveContextId($context);
        $issueIds = $this->getSetting($contextId, self::TRACKING_SETTING_PREFIX . 'issues');
        return is_array($issueIds) ? array_values($issueIds) : [];
    }

    /**
     * Delete all tracked entities with safe tracking preservation on failure
     *
     * @param Context|int $context Context or context ID
     * @return array Deletion statistics and error details
     */
    public function cleanup(Context|int $context): array
    {
        $contextId = $this->resolveContextId($context);
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
        $this->cleanupIssues($contextId, $deleted, $remaining, $errors);

        // 2. Delete tracked submissions
        $this->cleanupSubmissions($contextId, $deleted, $remaining, $errors);

        // 3. Delete tracked users
        $this->cleanupUsers($contextId, $deleted, $remaining, $errors);

        // Persist tracking updates: clear setting if all gone, or keep remaining failed IDs
        $this->updateTrackingAfterCleanup($contextId, 'issues', $remaining['issues']);
        $this->updateTrackingAfterCleanup($contextId, 'submissions', $remaining['submissions']);
        $this->updateTrackingAfterCleanup($contextId, 'users', $remaining['users']);

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
    private function cleanupIssues(int $contextId, array &$deleted, array &$remaining, array &$errors): void
    {
        $issueIds = $this->getTrackedIssues($contextId);
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
    private function cleanupSubmissions(int $contextId, array &$deleted, array &$remaining, array &$errors): void
    {
        $submissionIds = $this->getTrackedSubmissions($contextId);
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
    private function cleanupUsers(int $contextId, array &$deleted, array &$remaining, array &$errors): void
    {
        $userIds = $this->getTrackedUsers($contextId);
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
    private function updateTrackingAfterCleanup(int $contextId, string $type, array $remaining): void
    {
        $settingKey = self::TRACKING_SETTING_PREFIX . $type;
        if (empty($remaining)) {
            $this->updateSetting($contextId, $settingKey, null);
        } else {
            $this->updateSetting($contextId, $settingKey, array_values($remaining), 'object');
        }
    }
}
