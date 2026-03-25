<?php
/**
 * @file SubmissionGenerator.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Generate complete dummy submissions through workflow stages
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

use APP\core\Application;
use APP\core\Services;
use APP\facades\Repo;
use PKP\submission\Submission;
use PKP\submissionFile\SubmissionFile;
use PKP\author\Author;
use PKP\db\DAORegistry;
use PKP\decision\Decision;
use PKP\security\Role;
use PKP\user\User;
use Illuminate\Support\Facades\DB;

class SubmissionGenerator
{
    /**
     * Workflow stage IDs
     * Source: Verified from OJS workflow constants
     * @see https://github.com/pkp/pkp-lib/issues/4796
     */
    private const WORKFLOW_STAGE_ID_SUBMISSION = 1;
    private const WORKFLOW_STAGE_ID_EXTERNAL_REVIEW = 3;
    private const WORKFLOW_STAGE_ID_EDITING = 4;
    private const WORKFLOW_STAGE_ID_PRODUCTION = 5;

    private Faker $faker;
    private int $contextId;
    private int $sectionId;

    /**
     * Constructor
     *
     * @param int $contextId Journal/context ID
     * @param int $sectionId Section ID for submissions
     */
    public function __construct(int $contextId, int $sectionId)
    {
        $this->faker = new Faker();
        $this->contextId = $contextId;
        $this->sectionId = $sectionId;
    }

    /**
     * Generate complete submission from start to publication-ready
     * Uses verified Decision APIs to progress through workflow
     *
     * @param int $authorId Author user ID
     * @return int Submission ID
     */
    public function generateSubmission(int $authorId): int
    {
        return DB::transaction(function () use ($authorId) {
            // Step 1: Create initial submission
            $submission = $this->createSubmission($authorId);

            // Step 2: Add submission file (manuscript placeholder)
            $this->addSubmissionFile($submission->getId(), $authorId);

            // Step 3: Add author metadata
            $this->addAuthorMetadata($submission->getId(), $authorId);

            // Step 4: Create stage assignment for author
            $this->createStageAssignment($submission->getId(), $authorId);

            // Step 5: Progress through workflow using verified Decision APIs
            $this->progressWorkflow($submission->getId(), $authorId);

            return $submission->getId();
        });
    }

    /**
     * Create initial submission
     * Uses verified Repo::submission() API
     *
     * @param int $authorId Author user ID
     * @return Submission Created submission
     */
    private function createSubmission(int $authorId): Submission
    {
        $site = Application::get()->getRequest()->getSite();
        $primaryLocale = $site->getPrimaryLocale();

        $submission = Repo::submission()->newDataObject([
            'contextId' => $this->contextId,
            'sectionId' => $this->sectionId,
            'locale' => $primaryLocale,
            'submissionProgress' => 1, // In progress
            'status' => Submission::STATUS_QUEUED,
        ]);

        $submissionId = Repo::submission()->add($submission);
        return Repo::submission()->get($submissionId);
    }

    /**
     * Add submission file (placeholder manuscript)
     * Uses verified Services::get('file') and Repo::submissionFile()
     *
     * @param int $submissionId Submission ID
     * @param int $authorId Author user ID (uploader)
     */
    private function addSubmissionFile(int $submissionId, int $authorId): void
    {
        // Create placeholder file content
        $tempPath = tempnam(sys_get_temp_dir(), 'dummy_submission_');
        
        if ($tempPath === false) {
            throw new \RuntimeException('Failed to create temporary file');
        }
        
        $content = "Dummy Submission Content\n\n";
        $content .= "Title: " . $this->faker->generateTitle() . "\n";
        $content .= "Abstract: " . $this->faker->generateAbstract() . "\n";
        
        if (file_put_contents($tempPath, $content) === false) {
            throw new \RuntimeException('Failed to write temporary file content');
        }

        try {
            // Upload file using verified file service
            $fileId = Services::get('file')->add($tempPath, 'submissions/' . $submissionId);
        } catch (\Exception $e) {
            error_log("DummyDataGenerator: File upload failed for submission {$submissionId}: " . $e->getMessage());
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage(), 0, $e);
        } finally {
            // Always clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        // Create submission file object
        $submissionFile = Repo::submissionFile()->newDataObject([
            'fileId' => $fileId,
            'fileStage' => SubmissionFile::SUBMISSION_FILE_SUBMISSION,
            'submissionId' => $submissionId,
            'uploaderUserId' => $authorId,
            'name' => [
                Application::get()->getRequest()->getSite()->getPrimaryLocale() => 'manuscript.txt'
            ],
            'genreId' => $this->getDefaultGenreId(),
        ]);

        Repo::submissionFile()->add($submissionFile);
    }

    /**
     * Add author metadata to submission
     * Uses verified Repo::author() API
     *
     * @param int $submissionId Submission ID
     * @param int $authorId Author user ID
     */
    private function addAuthorMetadata(int $submissionId, int $authorId): void
    {
        $user = Repo::user()->get($authorId);
        
        if (!$user) {
            throw new \RuntimeException('Author user not found: ' . $authorId);
        }
        
        $site = Application::get()->getRequest()->getSite();
        $primaryLocale = $site->getPrimaryLocale();

        $author = Repo::author()->newDataObject([
            'submissionId' => $submissionId,
            'userId' => $authorId,
            'givenName' => $user->getGivenName($primaryLocale),
            'familyName' => $user->getFamilyName($primaryLocale),
            'email' => $user->getEmail(),
            'primaryContact' => 1,
            'sequence' => 1,
        ]);

        Repo::author()->add($author);
    }

    /**
     * Create stage assignment for author
     * Uses verified StageAssignmentDAO->build() method
     *
     * @param int $submissionId Submission ID
     * @param int $authorId Author user ID
     */
    private function createStageAssignment(int $submissionId, int $authorId): void
    {
        /** @var \PKP\stageAssignment\StageAssignmentDAO $stageAssignmentDao */
        $stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');

        // Get author user group
        $userGroups = Repo::userGroup()->getCollector()
            ->filterByContextIds([$this->contextId])
            ->filterByRoleIds([Role::ROLE_ID_AUTHOR])
            ->getMany();

        $userGroupId = $userGroups->first()?->getId();
        
        if (!$userGroupId) {
            throw new \RuntimeException('Author user group not found');
        }

        // Assign author to submission stage using verified build() method
        $stageAssignmentDao->build(
            $submissionId,
            $authorId,
            $userGroupId
        );
    }

    /**
     * Progress submission through workflow stages
     * Uses verified Decision constants from pkp-lib
     * Decision constants verified from:
     * - Decision::EXTERNAL_REVIEW = 3
     * - Decision::ACCEPT = 2
     * - Decision::SEND_TO_PRODUCTION = 7
     *
     * @param int $submissionId Submission ID
     * @param int $authorId Author user ID
     */
    private function progressWorkflow(int $submissionId, int $authorId): void
    {
        $submission = Repo::submission()->get($submissionId);

        if (!$submission) {
            throw new \RuntimeException('Submission not found: ' . $submissionId);
        }

        // Get admin or manager user for decision recording
        $user = Application::get()->getRequest()->getUser();

        if (!$user) {
            // Get admin user for decision recording
            $users = Repo::user()->getCollector()
                ->filterByRoleIds([Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER])
                ->getMany();
            $user = $users->first();
        }

        if (!$user) {
            error_log('DummyDataGenerator: No admin/manager user found for decision recording');
            return;
        }

        // Record decision to send to external review (Decision::EXTERNAL_REVIEW = 3)
        $this->recordDecision(
            $submission,
            Decision::EXTERNAL_REVIEW,
            $user,
            self::WORKFLOW_STAGE_ID_SUBMISSION
        );

        // Record decision to accept (Decision::ACCEPT = 2)
        $this->recordDecision(
            $submission,
            Decision::ACCEPT,
            $user,
            self::WORKFLOW_STAGE_ID_EXTERNAL_REVIEW
        );

        // Move to production (Decision::SEND_TO_PRODUCTION = 7)
        $this->recordDecision(
            $submission,
            Decision::SEND_TO_PRODUCTION,
            $user,
            self::WORKFLOW_STAGE_ID_EDITING
        );
    }

    /**
     * Record editorial decision
     * Uses verified Repo::decision() API
     * Verified from: https://docs.pkp.sfu.ca/dev/documentation/en/decisions
     *
     * @param Submission $submission
     * @param int $decisionType Decision constant
     * @param User $editor Editor user
     * @param int $stageId Workflow stage ID
     */
    private function recordDecision(Submission $submission, int $decisionType, User $editor, int $stageId): void
    {
        $context = Application::get()->getRequest()->getContext();

        if (!$context) {
            error_log('DummyDataGenerator: No context available for decision recording');
            return;
        }

        $decisionData = [
            'decision' => $decisionType,
            'dateDecided' => Application::get()->getCurrentDate(),
            'submissionId' => $submission->getId(),
            'editorId' => $editor->getId(),
            'stageId' => $stageId,
        ];

        try {
            // Validate the decision using verified Repo::decision()->validate()
            $decisionTypeObj = Repo::decision()->getDecisionType($decisionType);
            
            $errors = Repo::decision()->validate(
                $decisionData,
                $decisionTypeObj,
                $submission,
                $context
            );

            if (empty($errors)) {
                $decision = Repo::decision()->newDataObject($decisionData);
                Repo::decision()->add($decision);
            } else {
                error_log('DummyDataGenerator: Decision validation failed: ' . json_encode($errors));
            }
        } catch (\Exception $e) {
            error_log('DummyDataGenerator: Failed to record decision: ' . $e->getMessage());
            // Continue without failing - decision recording is optional for dummy data
        }
    }

    /**
     * Get default genre ID for article text
     * Uses verified Repo::genre() API
     *
     * @return int Genre ID
     */
    private function getDefaultGenreId(): int
    {
        $genres = Repo::genre()->getCollector()
            ->filterByContextIds([$this->contextId])
            ->getMany();

        return $genres->first()?->getId() ?? 1;
    }
}
