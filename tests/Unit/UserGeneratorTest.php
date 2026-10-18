<?php
/**
 * @file tests/Unit/UserGeneratorTest.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Unit tests for UserGenerator dummy user creation
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\classes\UserGenerator;
use PHPUnit\Framework\TestCase;

class UserGeneratorTest extends TestCase
{
    private UserGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new UserGenerator();
    }

    public function test_getDefaultPassword_returns_expected_value(): void
    {
        $password = UserGenerator::getDefaultPassword();
        $this->assertIsString($password);
        $this->assertNotEmpty($password);
        $this->assertEquals('DummyUser123!', $password);
    }

    public function test_getDefaultPassword_is_constant(): void
    {
        // Verify the password doesn't change between calls
        $password1 = UserGenerator::getDefaultPassword();
        $password2 = UserGenerator::getDefaultPassword();
        $this->assertEquals($password1, $password2);
    }

    /**
     * Integration test: verify generateUsers returns correct count
     * Requires OJS environment with database
     *
     * @group integration
     */
    public function test_generateUsers_returns_array_of_ids(): void
    {
        // This test requires a live OJS environment
        // Skip when running outside OJS
        if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
            $this->markTestSkipped('Requires OJS environment');
        }

        $result = $this->generator->generateUsers(3, 1);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        foreach ($result as $userId) {
            $this->assertIsInt($userId);
            $this->assertGreaterThan(0, $userId);
        }
    }
}
