<?php
/**
 * Mock PKP model classes
 *
 * Provides mock implementations of PKP model classes for unit testing.
 * These mocks simulate OJS model objects without database dependencies.
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests\Mocks
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Mocks;

use Mockery;
use Mockery\MockInterface;

class MockClasses
{
    /**
     * Create a mock User object
     *
     * @param array<string, mixed> $attributes User attributes
     * @return MockInterface&\PKP\user\User
     */
    public static function user(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\user\User */
        $user = Mockery::mock('PKP\user\User');

        $user->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $user->shouldReceive('getUsername')
            ->andReturn($attributes['username'] ?? 'testuser');

        $user->shouldReceive('getEmail')
            ->andReturn($attributes['email'] ?? 'test@example.com');

        $user->shouldReceive('getFullName')
            ->andReturn($attributes['fullName'] ?? 'Test User');

        $user->shouldReceive('getGivenName')
            ->andReturn($attributes['givenName'] ?? 'Test');

        $user->shouldReceive('getFamilyName')
            ->andReturn($attributes['familyName'] ?? 'User');

        $user->shouldReceive('getPassword')
            ->andReturn($attributes['password'] ?? 'hashed_password');

        $user->shouldReceive('getDateRegistered')
            ->andReturn($attributes['dateRegistered'] ?? date('Y-m-d H:i:s'));

        return $user;
    }

    /**
     * Create a mock Submission object
     *
     * @param array<string, mixed> $attributes Submission attributes
     * @return MockInterface&\PKP\submission\Submission
     */
    public static function submission(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\submission\Submission */
        $submission = Mockery::mock('PKP\submission\Submission');

        $submission->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $submission->shouldReceive('getContextId')
            ->andReturn($attributes['contextId'] ?? 1);

        $submission->shouldReceive('getSubmissionProgress')
            ->andReturn($attributes['progress'] ?? 1);

        $submission->shouldReceive('getStageId')
            ->andReturn($attributes['stageId'] ?? 1);

        $submission->shouldReceive('getStatus')
            ->andReturn($attributes['status'] ?? 1);

        $submission->shouldReceive('getLocale')
            ->andReturn($attributes['locale'] ?? 'en');

        return $submission;
    }

    /**
     * Create a mock Issue object
     *
     * @param array<string, mixed> $attributes Issue attributes
     * @return MockInterface&\PKP\issue\Issue
     */
    public static function issue(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\issue\Issue */
        $issue = Mockery::mock('PKP\issue\Issue');

        $issue->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $issue->shouldReceive('getJournalId')
            ->andReturn($attributes['journalId'] ?? 1);

        $issue->shouldReceive('getPublished')
            ->andReturn($attributes['published'] ?? false);

        $issue->shouldReceive('getCurrent')
            ->andReturn($attributes['current'] ?? false);

        $issue->shouldReceive('getVolume')
            ->andReturn($attributes['volume'] ?? 1);

        $issue->shouldReceive('getNumber')
            ->andReturn($attributes['number'] ?? 1);

        $issue->shouldReceive('getYear')
            ->andReturn($attributes['year'] ?? date('Y'));

        $issue->shouldReceive('getTitle')
            ->andReturn($attributes['title'] ?? null);

        $issue->shouldReceive('getDatePublished')
            ->andReturn($attributes['datePublished'] ?? null);

        return $issue;
    }

    /**
     * Create a mock Publication object
     *
     * @param array<string, mixed> $attributes Publication attributes
     * @return MockInterface&\PKP\publication\Publication
     */
    public static function publication(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\publication\Publication */
        $publication = Mockery::mock('PKP\publication\Publication');

        $publication->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $publication->shouldReceive('getSubmissionId')
            ->andReturn($attributes['submissionId'] ?? 1);

        $publication->shouldReceive('getStatus')
            ->andReturn($attributes['status'] ?? 1);

        $publication->shouldReceive('getPrimaryContactId')
            ->andReturn($attributes['primaryContactId'] ?? null);

        $publication->shouldReceive('getLocale')
            ->andReturn($attributes['locale'] ?? 'en');

        $publication->shouldReceive('getData')
            ->andReturn($attributes['data'] ?? []);

        return $publication;
    }

    /**
     * Create a mock SubmissionFile object
     *
     * @param array<string, mixed> $attributes SubmissionFile attributes
     * @return MockInterface&\PKP\submission\SubmissionFile
     */
    public static function submissionFile(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\submission\SubmissionFile */
        $file = Mockery::mock('PKP\submission\SubmissionFile');

        $file->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $file->shouldReceive('getSubmissionId')
            ->andReturn($attributes['submissionId'] ?? 1);

        $file->shouldReceive('getFileId')
            ->andReturn($attributes['fileId'] ?? 1);

        $file->shouldReceive('getFileStage')
            ->andReturn($attributes['fileStage'] ?? 1);

        $file->shouldReceive('getGenreId')
            ->andReturn($attributes['genreId'] ?? null);

        return $file;
    }

    /**
     * Create a mock Author object
     *
     * @param array<string, mixed> $attributes Author attributes
     * @return MockInterface&\PKP\author\Author
     */
    public static function author(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\author\Author */
        $author = Mockery::mock('PKP\author\Author');

        $author->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $author->shouldReceive('getSubmissionId')
            ->andReturn($attributes['submissionId'] ?? 1);

        $author->shouldReceive('getGivenName')
            ->andReturn($attributes['givenName'] ?? 'Test');

        $author->shouldReceive('getFamilyName')
            ->andReturn($attributes['familyName'] ?? 'Author');

        $author->shouldReceive('getEmail')
            ->andReturn($attributes['email'] ?? 'author@example.com');

        $author->shouldReceive('getPrimaryContact')
            ->andReturn($attributes['primaryContact'] ?? false);

        $author->shouldReceive('getSequence')
            ->andReturn($attributes['sequence'] ?? 1);

        return $author;
    }

    /**
     * Create a mock UserGroup object
     *
     * @param array<string, mixed> $attributes UserGroup attributes
     * @return MockInterface&\PKP\userGroup\UserGroup
     */
    public static function userGroup(array $attributes = []): MockInterface
    {
        /** @var MockInterface&\PKP\userGroup\UserGroup */
        $userGroup = Mockery::mock('PKP\userGroup\UserGroup');

        $userGroup->shouldReceive('getId')
            ->andReturn($attributes['id'] ?? 1);

        $userGroup->shouldReceive('getContextId')
            ->andReturn($attributes['contextId'] ?? 1);

        $userGroup->shouldReceive('getRoleId')
            ->andReturn($attributes['roleId'] ?? 1);

        $userGroup->shouldReceive('getName')
            ->andReturn($attributes['name'] ?? 'Author');

        return $userGroup;
    }

    /**
     * Create a mock Decision object
     *
     * @param array<string, mixed> $attributes Decision attributes
     * @return MockInterface
     */
    public static function decision(array $attributes = []): MockInterface
    {
        $decision = Mockery::mock();

        $decision->shouldReceive('getDecision')
            ->andReturn($attributes['decision'] ?? 1);

        $decision->shouldReceive('getSubmissionId')
            ->andReturn($attributes['submissionId'] ?? 1);

        $decision->shouldReceive('getFromStage')
            ->andReturn($attributes['fromStage'] ?? 1);

        $decision->shouldReceive('getEditorId')
            ->andReturn($attributes['editorId'] ?? 1);

        return $decision;
    }

    /**
     * Create a generic mock object with configurable methods
     *
     * @param array<string, mixed> $methodReturns Method name => return value pairs
     * @return MockInterface
     */
    public static function generic(array $methodReturns = []): MockInterface
    {
        $mock = Mockery::mock();

        foreach ($methodReturns as $method => $returnValue) {
            $mock->shouldReceive($method)->andReturn($returnValue);
        }

        return $mock;
    }
}
