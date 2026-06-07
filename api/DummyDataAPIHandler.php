<?php
/**
 * @file DummyDataAPIHandler.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief API handler for dummy data operations
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\api;

use APP\core\Application;
use APP\plugins\generic\dummyDataGenerator\DummyDataGeneratorPlugin;
use APP\plugins\generic\dummyDataGenerator\classes\UserGenerator;
use APP\plugins\generic\dummyDataGenerator\classes\SubmissionGenerator;
use APP\plugins\generic\dummyDataGenerator\classes\IssueGenerator;
use APP\plugins\generic\dummyDataGenerator\classes\DataTracker;
use APP\facades\Repo;
use PKP\security\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DummyDataAPIHandler
{
    private DummyDataGeneratorPlugin $plugin;

    /**
     * Rate limiting: cooldown between requests in seconds
     */
    private const RATE_LIMIT_COOLDOWN = 30;

    /**
     * Setting key for last request timestamp
     */
    private const RATE_LIMIT_SETTING_KEY = 'dummyData_lastRequest';

    /**
     * Constructor
     */
    public function __construct(DummyDataGeneratorPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Check rate limiting for the current context
     *
     * @param \PKP\context\Context $context
     * @return string|null Error message if rate limited, null if OK
     */
    private function checkRateLimit(\PKP\context\Context $context): ?string
    {
        $lastRequest = $context->getData(self::RATE_LIMIT_SETTING_KEY);
        if ($lastRequest && (time() - (int) $lastRequest) < self::RATE_LIMIT_COOLDOWN) {
            $remaining = self::RATE_LIMIT_COOLDOWN - (time() - (int) $lastRequest);
            return __('plugins.generic.dummyDataGenerator.error.rateLimited', ['seconds' => $remaining]);
        }
        return null;
    }

    /**
     * Update rate limit timestamp after successful request
     *
     * @param \PKP\context\Context $context
     */
    private function updateRateLimit(\PKP\context\Context $context): void
    {
        $context->updateSetting(self::RATE_LIMIT_SETTING_KEY, (string) time(), 'string');
    }

    /**
     * Check if running in production environment
     *
     * @return bool True if production environment detected
     */
    private function isProductionEnvironment(): bool
    {
        return defined('APP_ENV') && APP_ENV === 'production';
    }

    /**
     * Generate dummy users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateUsers(Request $request): JsonResponse
    {
        $count = filter_var(
            $request->input('count', 10),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 100]]
        );

        if ($count === false) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.invalidCount'),
            ], 400);
        }

        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noContext'),
            ], 400);
        }

        // Production environment warning
        if ($this->isProductionEnvironment()) {
            error_log('DummyDataGenerator: WARNING - Data generation requested in production environment');
        }

        // Rate limiting check
        $rateLimitError = $this->checkRateLimit($context);
        if ($rateLimitError !== null) {
            return new JsonResponse([
                'success' => false,
                'error' => $rateLimitError,
            ], 429);
        }

        $contextId = $context->getId();

        $generator = new UserGenerator();
        $userIds = $generator->generateUsers($count, $contextId);

        // Track for cleanup
        $tracker = new DataTracker();
        $tracker->trackUsers($userIds, $context);

        // Update rate limit
        $this->updateRateLimit($context);

        // Audit log
        error_log('DummyDataGenerator: Generated ' . count($userIds) . ' users in context ' . $contextId);

        return new JsonResponse([
            'success' => true,
            'created' => count($userIds),
            'userIds' => $userIds,
            'defaultPassword' => UserGenerator::getDefaultPassword(),
            'message' => __('plugins.generic.dummyDataGenerator.success.usersCreated', ['count' => count($userIds)]),
        ]);
    }

    /**
     * Generate dummy submissions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateSubmissions(Request $request): JsonResponse
    {
        $count = filter_var(
            $request->input('count', 20),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 200]]
        );

        if ($count === false) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.invalidCount'),
            ], 400);
        }

        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noContext'),
            ], 400);
        }

        // Production environment warning
        if ($this->isProductionEnvironment()) {
            error_log('DummyDataGenerator: WARNING - Submission generation requested in production environment');
        }

        // Rate limiting check
        $rateLimitError = $this->checkRateLimit($context);
        if ($rateLimitError !== null) {
            return new JsonResponse([
                'success' => false,
                'error' => $rateLimitError,
            ], 429);
        }

        $contextId = $context->getId();

        // Get available authors (tracked dummy users)
        $tracker = new DataTracker();
        $authorIds = $tracker->getTrackedUsers($context);

        if (empty($authorIds)) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noAuthors'),
            ], 400);
        }

        // Get section
        $sections = Repo::section()->getCollector()
            ->filterByContextIds([$contextId])
            ->getMany();
        $section = $sections->first();
        if (!$section) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noSections'),
            ], 400);
        }
        $sectionId = $section->getId();

        $generator = new SubmissionGenerator($contextId, $sectionId);
        $submissionIds = [];

        // Generate submissions, cycling through available authors
        $authorCount = count($authorIds);
        for ($i = 0; $i < $count; $i++) {
            $authorId = $authorIds[$i % $authorCount];
            $submissionIds[] = $generator->generateSubmission($authorId);
        }

        // Track for cleanup
        $tracker->trackSubmissions($submissionIds, $context);

        // Update rate limit
        $this->updateRateLimit($context);

        // Audit log
        error_log('DummyDataGenerator: Generated ' . count($submissionIds) . ' submissions in context ' . $contextId);

        return new JsonResponse([
            'success' => true,
            'created' => count($submissionIds),
            'submissionIds' => $submissionIds,
            'message' => __('plugins.generic.dummyDataGenerator.success.submissionsCreated', ['count' => count($submissionIds)]),
        ]);
    }

    /**
     * Generate and publish dummy issue
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateIssue(Request $request): JsonResponse
    {
        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noContext'),
            ], 400);
        }

        // Production environment warning
        if ($this->isProductionEnvironment()) {
            error_log('DummyDataGenerator: WARNING - Issue generation requested in production environment');
        }

        // Rate limiting check
        $rateLimitError = $this->checkRateLimit($context);
        if ($rateLimitError !== null) {
            return new JsonResponse([
                'success' => false,
                'error' => $rateLimitError,
            ], 429);
        }

        $contextId = $context->getId();

        // Get tracked submissions
        $tracker = new DataTracker();
        $submissionIds = $tracker->getTrackedSubmissions($context);

        if (empty($submissionIds)) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noSubmissions'),
            ], 400);
        }

        $generator = new IssueGenerator($contextId);

        try {
            $issueId = $generator->createAndPublishIssue($submissionIds);

            // Track issue
            $tracker->trackIssues([$issueId], $context);

            // Update rate limit
            $this->updateRateLimit($context);

            // Audit log
            error_log('DummyDataGenerator: Generated issue ' . $issueId . ' with ' . count($submissionIds) . ' submissions in context ' . $contextId);

            return new JsonResponse([
                'success' => true,
                'issueId' => $issueId,
                'submissionsPublished' => count($submissionIds),
                'message' => __('plugins.generic.dummyDataGenerator.success.issueCreated'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.issueCreationFailed', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Cleanup all dummy data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cleanup(Request $request): JsonResponse
    {
        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.noContext'),
            ], 400);
        }

        // Production environment warning
        if ($this->isProductionEnvironment()) {
            error_log('DummyDataGenerator: WARNING - Cleanup requested in production environment');
        }

        // Note: Cleanup is exempt from rate limiting to allow immediate data removal

        // Require explicit confirmation for cleanup
        $confirm = $request->input('confirm');
        if ($confirm !== 'DELETE_ALL_DUMMY_DATA') {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.confirmationRequired'),
            ], 400);
        }

        $tracker = new DataTracker();

        try {
            $deleted = $tracker->cleanup($context);

            // Audit log
            error_log('DummyDataGenerator: Cleanup completed in context ' . $context->getId() . ' — deleted ' . $deleted['users'] . ' users, ' . $deleted['submissions'] . ' submissions, ' . $deleted['issues'] . ' issues');

            return new JsonResponse([
                'success' => true,
                'deleted' => $deleted,
                'message' => __('plugins.generic.dummyDataGenerator.success.cleanup'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => __('plugins.generic.dummyDataGenerator.error.cleanupFailed', ['error' => $e->getMessage()]),
            ], 500);
        }
    }
}
