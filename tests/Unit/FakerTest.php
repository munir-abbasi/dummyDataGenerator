<?php
/**
 * @file tests/Unit/FakerTest.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Unit tests for Faker content generator
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\classes\Faker;
use PHPUnit\Framework\TestCase;

class FakerTest extends TestCase
{
    private Faker $faker;

    protected function setUp(): void
    {
        $this->faker = new Faker();
    }

    public function test_generateUsername_returns_unique_format(): void
    {
        $username1 = $this->faker->generateUsername(0);
        $username2 = $this->faker->generateUsername(1);

        $this->assertIsString($username1);
        $this->assertIsString($username2);
        $this->assertStringStartsWith('dummy_user_0_', $username1);
        $this->assertStringStartsWith('dummy_user_1_', $username2);
        $this->assertNotEquals($username1, $username2);
    }

    public function test_generateUsername_includes_index(): void
    {
        $username = $this->faker->generateUsername(42);
        $this->assertStringContainsString('dummy_user_42_', $username);
    }

    public function test_generateEmail_returns_example_domain(): void
    {
        $email = $this->faker->generateEmail(0);
        $this->assertIsString($email);
        $this->assertStringEndsWith('@example.com', $email);
        $this->assertMatchesRegularExpression('/^dummy\.user\.0_[a-f0-9]{6}@example\.com$/', $email);
    }

    public function test_generateEmail_is_unique_per_index(): void
    {
        $email1 = $this->faker->generateEmail(0);
        $email2 = $this->faker->generateEmail(1);

        $this->assertNotEquals($email1, $email2);
    }

    public function test_getGivenName_returns_string(): void
    {
        $name = $this->faker->getGivenName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_getGivenName_returns_valid_name(): void
    {
        $validNames = [
            'John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily',
            'Robert', 'Lisa', 'William', 'Jennifer', 'James', 'Maria',
            'Thomas', 'Linda', 'Charles', 'Elizabeth', 'Daniel', 'Patricia'
        ];

        // Run multiple times to increase chance of hitting different values
        for ($i = 0; $i < 50; $i++) {
            $name = $this->faker->getGivenName();
            $this->assertContains($name, $validNames);
        }
    }

    public function test_getFamilyName_returns_string(): void
    {
        $name = $this->faker->getFamilyName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function test_getFamilyName_returns_valid_name(): void
    {
        $validNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia',
            'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez',
            'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore'
        ];

        for ($i = 0; $i < 50; $i++) {
            $name = $this->faker->getFamilyName();
            $this->assertContains($name, $validNames);
        }
    }

    public function test_generateTitle_returns_non_empty_string(): void
    {
        $title = $this->faker->generateTitle();
        $this->assertIsString($title);
        $this->assertNotEmpty($title);
    }

    public function test_generateTitle_contains_study_word(): void
    {
        $titleWords = [
            'Study', 'Analysis', 'Research', 'Investigation',
            'Examination', 'Review', 'Assessment', 'Evaluation',
            'Exploration', 'Survey', 'Perspective', 'Approach',
            'Framework', 'Methodology', 'System'
        ];

        $found = false;
        $title = $this->faker->generateTitle();
        foreach ($titleWords as $word) {
            if (str_contains($title, $word)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Title should contain at least one expected title word');
    }

    public function test_generateAbstract_returns_non_empty_string(): void
    {
        $abstract = $this->faker->generateAbstract();
        $this->assertIsString($abstract);
        $this->assertNotEmpty($abstract);
    }

    public function test_generateAbstract_contains_lorem_ipsum(): void
    {
        $abstract = $this->faker->generateAbstract();
        $this->assertStringContainsString('Lorem ipsum', $abstract);
    }

    public function test_generateAbstract_has_multiple_sentences(): void
    {
        $abstract = $this->faker->generateAbstract();
        $sentences = explode('. ', trim($abstract));
        // Should have 4-6 sentences
        $this->assertGreaterThanOrEqual(4, count($sentences));
        $this->assertLessThanOrEqual(7, count($sentences)); // +1 for trailing period
    }

    public function test_generateKeywords_returns_array(): void
    {
        $keywords = $this->faker->generateKeywords();
        $this->assertIsArray($keywords);
        $this->assertNotEmpty($keywords);
    }

    public function test_generateKeywords_has_3_to_5_items(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $keywords = $this->faker->generateKeywords();
            $this->assertGreaterThanOrEqual(3, count($keywords));
            $this->assertLessThanOrEqual(5, count($keywords));
        }
    }

    public function test_generateKeywords_contains_valid_keywords(): void
    {
        $validKeywords = [
            'research', 'analysis', 'study', 'methodology', 'framework',
            'innovation', 'technology', 'system', 'model', 'approach',
            'evaluation', 'assessment', 'development', 'application', 'theory'
        ];

        $keywords = $this->faker->generateKeywords();
        foreach ($keywords as $keyword) {
            $this->assertContains($keyword, $validKeywords);
        }
    }

    public function test_generateDate_returns_valid_format(): void
    {
        $date = $this->faker->generateDate();
        $this->assertIsString($date);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    public function test_generateDate_is_in_past(): void
    {
        $date = $this->faker->generateDate();
        $this->assertLessThanOrEqual(date('Y-m-d'), $date);
    }

    public function test_generateDate_is_within_two_years(): void
    {
        $date = $this->faker->generateDate();
        $timestamp = strtotime($date);
        $twoYearsAgo = strtotime('-2 years');
        $now = time();

        $this->assertGreaterThanOrEqual($twoYearsAgo, $timestamp);
        $this->assertLessThanOrEqual($now, $timestamp);
    }
}
