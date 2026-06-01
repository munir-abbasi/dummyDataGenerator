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
     * Constructor
     */
    public function __construct(DummyDataGeneratorPlugin $plugin)
    {
        $this->plugin = $plugin;
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

        $contextId = $context->getId();

        $generator = new UserGenerator();
        $userIds = $generator->generateUsers($count, $contextId);

        // Track for cleanup
        $tracker = new DataTracker();
        $tracker->trackUsers($userIds, $context);

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
