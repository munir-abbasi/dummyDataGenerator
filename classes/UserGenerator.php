<?php
/**
 * @file UserGenerator.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Generate dummy users with author role assignment
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

use APP\core\Application;
use APP\facades\Repo;
use PKP\security\Role;
use PKP\user\User;
use PKP\security\Validation;

class UserGenerator
{
    /**
     * Default password for all generated dummy users
     * Admins can change this via user profile editing
     */
    private const DEFAULT_PASSWORD = 'DummyUser123!';

    /**
     * Generate multiple dummy users
     *
     * @param int $count Number of users to create
     * @param int $contextId Journal/context ID
     * @return array Created user IDs
     */
    public function generateUsers(int $count, int $contextId): array
    {
        $userIds = [];
        $faker = new Faker();

        for ($i = 0; $i < $count; $i++) {
            $user = $this->createUser($faker, $contextId, $i);
            if ($user) {
                $userIds[] = $user->getId();
            }
        }

        return $userIds;
    }

    /**
     * Create a single user with author role
     *
     * @param Faker $faker Lorem ipsum content generator
     * @param int $contextId Context/journal ID
     * @param int $index User index for unique naming
     * @return User|null Created user object or null on failure
     */
    private function createUser(Faker $faker, int $contextId, int $index): ?User
    {
        // Create user object using verified Repo::user() API
        $user = Repo::user()->newDataObject();

        // Set unique username
        $user->setUsername($faker->generateUsername($index));

        // Set unique email
        $user->setEmail($faker->generateEmail($index));

        // Set name in primary locale
        $site = Application::get()->getRequest()->getSite();
        $primaryLocale = $site->getPrimaryLocale();

        $user->setGivenName($faker->getGivenName(), $primaryLocale);
        $user->setFamilyName($faker->getFamilyName(), $primaryLocale);

        // Hash password using PKP security
        $user->setPassword(Validation::encryptCredentials($user->getUsername(), self::DEFAULT_PASSWORD));
        $user->setMustChangePassword(0);

        // Insert user into database
        $userId = Repo::user()->add($user);
        $user = Repo::user()->get($userId);

        if (!$user) {
            error_log("DummyDataGenerator: Failed to create user {$index}");
            return null;
        }

        // Assign author role to user in context
        $this->assignAuthorRole($user->getId(), $contextId);

        return $user;
    }

    /**
     * Assign author role to user in context
     * Uses verified Repo::userGroup() API
     *
     * @param int $userId User ID
     * @param int $contextId Context/journal ID
     */
    private function assignAuthorRole(int $userId, int $contextId): void
    {
        try {
            // Get author user groups for context
            $userGroups = Repo::userGroup()->getCollector()
                ->filterByContextIds([$contextId])
                ->filterByRoleIds([Role::ROLE_ID_AUTHOR])
                ->getMany();

            // Assign user to all author groups
            foreach ($userGroups as $userGroup) {
                Repo::userGroup()->assignUserToGroup(
                    $userId,
                    $userGroup->getId(),
                    $contextId
                );
            }
        } catch (\Exception $e) {
            error_log("DummyDataGenerator: Failed to assign author role to user {$userId}: " . $e->getMessage());
        }
    }

    /**
     * Get default password for all generated users
     *
     * @return string Default password
     */
    public static function getDefaultPassword(): string
    {
        return self::DEFAULT_PASSWORD;
    }
}
