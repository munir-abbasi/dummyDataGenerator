<?php
/**
 * Faker unit test
 *
 * Comprehensive tests for all data generation methods
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests\Unit
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\tests\TestCase;
use APP\plugins\generic\dummyDataGenerator\classes\Faker;

class FakerTest extends TestCase
{
    private Faker $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = new Faker();
    }

    /**
     * Test getGivenName() returns non-empty string
     */
    public function test_getGivenName_returns_non_empty_string(): void
    {
        $name = $this->faker->getGivenName();

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test getGivenName() returns variety across multiple calls
     */
    public function test_getGivenName_returns_variety(): void
    {
        $names = [];
        for ($i = 0; $i < 10; $i++) {
            $names[] = $this->faker->getGivenName();
        }

        // Verify we get some variety (not all same)
        $uniqueNames = array_unique($names);
        $this->assertGreaterThan(1, count($uniqueNames));
    }

    /**
     * Test getFamilyName() returns non-empty string
     */
    public function test_getFamilyName_returns_non_empty_string(): void
    {
        $name = $this->faker->getFamilyName();

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test generateEmail() returns valid email format
     */
    public function test_generateEmail_returns_valid_email_format(): void
    {
        $email = $this->faker->generateEmail(1);

        $this->assertIsString($email);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
        $this->assertStringContainsString('dummy.user.1@example.com', $email);
    }

    /**
     * Test generateEmail() is unique per index
     */
    public function test_generateEmail_unique_per_index(): void
    {
        $email1 = $this->faker->generateEmail(1);
        $email2 = $this->faker->generateEmail(2);
        $email3 = $this->faker->generateEmail(100);

        $this->assertNotEquals($email1, $email2);
        $this->assertNotEquals($email2, $email3);
        $this->assertNotEquals($email1, $email3);
    }

    /**
     * Test generateEmail() follows expected pattern
     */
    public function test_generateEmail_follows_expected_pattern(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $email = $this->faker->generateEmail($i);
            $this->assertMatchesRegularExpression(
                '/^dummy\.user\.' . $i . '@example\.com$/',
                $email
            );
        }
    }

    /**
     * Test generateUsername() returns non-empty string
     */
    public function test_generateUsername_returns_non_empty_string(): void
    {
        $username = $this->faker->generateUsername(1);

        $this->assertIsString($username);
        $this->assertNotEmpty($username);
    }

    /**
     * Test generateUsername() is unique per index
     */
    public function test_generateUsername_unique_per_index(): void
    {
        $username1 = $this->faker->generateUsername(1);
        $username2 = $this->faker->generateUsername(2);

        $this->assertNotEquals($username1, $username2);
    }

    /**
     * Test generateUsername() contains index
     */
    public function test_generateUsername_contains_index(): void
    {
        $username = $this->faker->generateUsername(42);
        $this->assertStringContainsString('42', $username);
    }

    /**
     * Test generateTitle() returns non-empty academic title
     */
    public function test_generateTitle_returns_non_empty_title(): void
    {
        $title = $this->faker->generateTitle();

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
        $this->assertGreaterThan(20, strlen($title));
    }

    /**
     * Test generateTitle() produces variety
     */
    public function test_generateTitle_produces_variety(): void
    {
        $titles = [];
        for ($i = 0; $i < 5; $i++) {
            $titles[] = $this->faker->generateTitle();
        }

        $uniqueTitles = array_unique($titles);
        $this->assertGreaterThan(1, count($uniqueTitles));
    }

    /**
     * Test generateTitle() returns academic-sounding titles
     */
    public function test_generateTitle_returns_academic_titles(): void
    {
        $title = $this->faker->generateTitle();
        $this->assertMatchesRegularExpression('/[A-Z]/', $title);
        $this->assertGreaterThan(15, strlen($title));
    }

    /**
     * Test generateAbstract() produces lorem ipsum text
     */
    public function test_generateAbstract_produces_lorem_ipsum_text(): void
    {
        $abstract = $this->faker->generateAbstract();

        $this->assertIsString($abstract);
        $wordCount = str_word_count($abstract);
        // Abstract contains 4-6 lorem ipsum sentences (typically 50-100 words)
        $this->assertGreaterThanOrEqual(40, $wordCount);
        $this->assertLessThanOrEqual(150, $wordCount);
    }

    /**
     * Test generateAbstract() contains lorem ipsum text
     */
    public function test_generateAbstract_contains_lorem_ipsum(): void
    {
        $abstract = $this->faker->generateAbstract();

        $this->assertStringContainsString('lorem', strtolower($abstract));
        $this->assertStringContainsString('ipsum', strtolower($abstract));
    }

    /**
     * Test generateAbstract() returns coherent paragraphs
     */
    public function test_generateAbstract_returns_coherent_paragraphs(): void
    {
        $abstract = $this->faker->generateAbstract();

        // Should contain multiple sentences (4-6 typically)
        $sentences = preg_split('/[.!?]+/', $abstract);
        $this->assertGreaterThanOrEqual(4, count(array_filter($sentences)));
    }

    /**
     * Test generateKeywords() returns correct count
     */
    public function test_generateKeywords_returns_correct_count(): void
    {
        $keywords = $this->faker->generateKeywords();

        $this->assertIsArray($keywords);
        $this->assertGreaterThanOrEqual(3, count($keywords));
        $this->assertLessThanOrEqual(5, count($keywords));
    }

    /**
     * Test generateKeywords() returns array of strings
     */
    public function test_generateKeywords_returns_array_of_strings(): void
    {
        $keywords = $this->faker->generateKeywords();

        foreach ($keywords as $keyword) {
            $this->assertIsString($keyword);
            $this->assertNotEmpty($keyword);
        }
    }

    /**
     * Test generateKeywords() produces variety
     */
    public function test_generateKeywords_produces_variety(): void
    {
        $allKeywords = [];
        for ($i = 0; $i < 5; $i++) {
            $allKeywords = array_merge($allKeywords, $this->faker->generateKeywords());
        }

        $uniqueKeywords = array_unique($allKeywords);
        $this->assertGreaterThan(5, count($uniqueKeywords));
    }

    /**
     * Test generateDate() returns valid date format
     */
    public function test_generateDate_returns_valid_date_format(): void
    {
        $date = $this->faker->generateDate();

        $this->assertIsString($date);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
        
        // Verify date is within past 2 years
        $timestamp = strtotime($date);
        $twoYearsAgo = time() - 63072000;
        $this->assertGreaterThanOrEqual($twoYearsAgo, $timestamp);
        $this->assertLessThanOrEqual(time(), $timestamp);
    }
}
