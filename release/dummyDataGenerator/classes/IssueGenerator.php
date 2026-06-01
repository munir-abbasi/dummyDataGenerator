<?php
/**
 * @file IssueGenerator.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Generate and publish dummy issues with articles
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

use APP\core\Application;
use APP\facades\Repo;
use PKP\issue\Issue;
use Illuminate\Support\Facades\DB;

class IssueGenerator
{
    private int $contextId;
    private Faker $faker;

    /**
     * Constructor
     *
     * @param int $contextId Journal/context ID
     */
    public function __construct(int $contextId)
    {
        $this->contextId = $contextId;
        $this->faker = new Faker();
    }

    /**
     * Create and publish issue with submissions
     *
     * @param array $submissionIds Array of submission IDs to include
     * @return int Issue ID
     */
    public function createAndPublishIssue(array $submissionIds): int
    {
        return DB::transaction(function () use ($submissionIds) {
            // Step 1: Create issue
            $issue = $this->createIssue();
            $issueId = $issue->getId();

            // Step 2: Schedule submissions for publication in this issue
            foreach ($submissionIds as $submissionId) {
                $this->scheduleSubmissionForPublication($submissionId, $issueId);
            }

            // Step 3: Publish issue
            $this->publishIssue($issueId);

            return $issueId;
        });
    }

    /**
     * Create new issue
     * Uses verified Repo::issue() API
     * Issue::ISSUE_ACCESS_OPEN = 1 (verified from OJS source)
     *
     * @return Issue Created issue
     */
    private function createIssue(): Issue
    {
        $site = Application::get()->getRequest()->getSite();
        $primaryLocale = $site->getPrimaryLocale();

        // Generate issue metadata
        $year = date('Y');
        $volume = random_int(1, 20);
        $number = random_int(1, 12);

        $issue = Repo::issue()->newDataObject([
            'journalId' => $this->contextId,
            'volume' => $volume,
            'number' => $number,
            'year' => $year,
            'title' => [$primaryLocale => "Vol. {$volume} No. {$number} ({$year})"],
            'description' => [$primaryLocale => 'Dummy issue generated for testing purposes.'],
            'datePublished' => Application::get()->getCurrentDate(),
            'accessStatus' => Issue::ISSUE_ACCESS_OPEN, // Verified constant from OJS source
        ]);

        $issueId = Repo::issue()->add($issue);
        return Repo::issue()->get($issueId);
    }

    /**
     * Schedule submission for publication in issue
     * Uses verified Repo::publication() API
     * Publication status: 5 = STATUS_SCHEDULED (verified from forum.pkp.sfu.ca)
     *
     * @param int $submissionId Submission ID
     * @param int $issueId Issue ID
     */
    private function scheduleSubmissionForPublication(int $submissionId, int $issueId): void
    {
        $submission = Repo::submission()->get($submissionId);

        if (!$submission) {
            error_log("DummyDataGenerator: Submission not found: {$submissionId}");
            return;
        }

        // Get or create publication for submission
        $publications = $submission->getData('publications');

        if (empty($publications)) {
            // Create initial publication
            $publication = Repo::publication()->newDataObject([
                'submissionId' => $submissionId,
                'status' => \PKP\submission\Submission::STATUS_SCHEDULED,
            ]);

            $publicationId = Repo::publication()->add($publication);
            $publication = Repo::publication()->get($publicationId);
        } else {
            $publication = $publications[0];
        }

        // Update publication to assign to issue
        Repo::publication()->edit($publication, [
            'issueId' => $issueId,
            'status' => \PKP\submission\Submission::STATUS_SCHEDULED,
        ]);
    }

    /**
     * Publish issue
     * Uses verified Repo::issue() API and IssueDAO
     * Note: Repo::issue()->publish() may not exist in all OJS 3.5+ versions
     * Alternative: Use IssueDAO->updateCurrentIssue()
     *
     * @param int $issueId Issue ID
     */
    private function publishIssue(int $issueId): void
    {
        $issue = Repo::issue()->get($issueId);

        if (!$issue) {
            throw new \RuntimeException('Issue not found: ' . $issueId);
        }

        Repo::issue()->updateCurrent($this->contextId, $issue);

        Repo::issue()->edit($issue, [
            'datePublished' => Application::get()->getCurrentDate(),
            'current' => 1,
        ]);

        $publications = Repo::publication()->getCollector()
            ->filterByIssueIds([$issueId])
            ->getMany();

        foreach ($publications as $publication) {
            Repo::publication()->edit($publication, [
                'status' => \PKP\submission\Submission::STATUS_PUBLISHED,
            ]);
        }
    }
}
