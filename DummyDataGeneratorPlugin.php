<?php
/**
 * @file DummyDataGeneratorPlugin.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @class DummyDataGeneratorPlugin
 * @brief Generate dummy users, submissions, and issues for OJS development/testing
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator;

use APP\core\Application;
use APP\plugins\generic\dummyDataGenerator\api\DummyDataAPIHandler;
use APP\template\TemplateManager;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\security\Role;

class DummyDataGeneratorPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::getName()
     */
    public function getName(): string
    {
        return 'dummyDataGenerator';
    }

    /**
     * @copydoc Plugin::getPluginPath()
     */
    public function getPluginPath(): string
    {
        return __DIR__;
    }

    /**
     * @copydoc Plugin::getHideManagement()
     */
    public function getHideManagement(): bool
    {
        // Keep plugin visible in management interface
        return false;
    }

    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path, $mainContextId);

        if ($success && $this->getEnabled($mainContextId)) {
            // Only register API routes if we have a valid context
            if ($mainContextId !== null) {
                $this->registerAPIHandler();
            }
        }

        return $success;
    }

    /**
     * Register custom API handler for dummy data operations
     * Extends the users API endpoint with custom routes
     */
    private function registerAPIHandler(): void
    {
        // Register custom API handler that extends PKPUserController
        Hook::add('APIHandler::users', function (string $hookName, $apiHandler): bool {
            $controller = new DummyDataAPIHandler($this);
            
            // Add our custom routes to the existing handler
            $apiHandler->addRoute(
                'POST',
                'generate-users',
                [$controller, 'generateUsers'],
                'dummyData.generateUsers',
                [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
            );

            $apiHandler->addRoute(
                'POST',
                'generate-submissions',
                [$controller, 'generateSubmissions'],
                'dummyData.generateSubmissions',
                [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
            );

            $apiHandler->addRoute(
                'POST',
                'generate-issue',
                [$controller, 'generateIssue'],
                'dummyData.generateIssue',
                [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
            );

            $apiHandler->addRoute(
                'DELETE',
                'cleanup',
                [$controller, 'cleanup'],
                'dummyData.cleanup',
                [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER]
            );

            return Hook::CONTINUE;
        });
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.dummyDataGenerator.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription(): string
    {
        return __('plugins.generic.dummyDataGenerator.description');
    }
}

// For backwards compatibility -- expect this to be removed approx. OJS/OMP/OPS 3.6
if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\dummyDataGenerator\DummyDataGeneratorPlugin', '\DummyDataGeneratorPlugin');
}
