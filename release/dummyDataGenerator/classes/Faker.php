<?php
/**
 * @file Faker.php
 *
 * Copyright (c) 2025 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Lorem Ipsum content generator for dummy data
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @link https://github.com/munir-abbasi/dummyDataGenerator
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

class Faker
{
    /**
     * Sample first names for dummy users
     */
    private const FIRST_NAMES = [
        'John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily',
        'Robert', 'Lisa', 'William', 'Jennifer', 'James', 'Maria',
        'Thomas', 'Linda', 'Charles', 'Elizabeth', 'Daniel', 'Patricia'
    ];

    /**
     * Sample last names for dummy users
     */
    private const LAST_NAMES = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia',
        'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez',
        'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore'
    ];

    /**
     * Sample academic topics for dummy titles
     */
    private const TOPICS = [
        'Machine Learning Applications', 'Climate Change Impact',
        'Public Health Policy', 'Educational Technology',
        'Economic Development Strategies', 'Social Media Influence',
        'Renewable Energy Systems', 'Urban Planning Methods',
        'Data Science Innovation', 'Artificial Intelligence Ethics',
        'Sustainable Agriculture', 'Digital Humanities',
        'Biomedical Engineering', 'Psychological Studies',
        'Environmental Conservation', 'International Relations'
    ];

    /**
     * Action words for titles
     */
    private const TITLE_WORDS = [
        'Study', 'Analysis', 'Research', 'Investigation',
        'Examination', 'Review', 'Assessment', 'Evaluation',
        'Exploration', 'Survey', 'Perspective', 'Approach',
        'Framework', 'Methodology', 'System'
    ];

    /**
     * Generate deterministic username
     *
     * @param int $index User index
     * @return string Unique username
     */
    public function generateUsername(int $index): string
    {
        return 'dummy_user_' . $index . '_' . substr(md5(uniqid()), 0, 6);
    }

    /**
     * Generate deterministic email
     *
     * @param int $index User index
     * @return string Unique email address
     */
    public function generateEmail(int $index): string
    {
        return 'dummy.user.' . $index . '@example.com';
    }

    /**
     * Get random given name
     *
     * @return string First name
     */
    public function getGivenName(): string
    {
        return self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
    }

    /**
     * Get random family name
     *
     * @return string Last name
     */
    public function getFamilyName(): string
    {
        return self::LAST_NAMES[array_rand(self::LAST_NAMES)];
    }

    /**
     * Generate Lorem Ipsum title for submission
     *
     * @return string Generated title
     */
    public function generateTitle(): string
    {
        $word = self::TITLE_WORDS[array_rand(self::TITLE_WORDS)];
        $topic = self::TOPICS[array_rand(self::TOPICS)];

        $prefixes = ['A ', 'An ', 'The ', ''];
        return $prefixes[array_rand($prefixes)] . $word . ' on ' . $topic;
    }

    /**
     * Generate Lorem Ipsum abstract
     *
     * @return string Generated abstract (200-300 words)
     */
    public function generateAbstract(): string
    {
        $sentences = [
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
            "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
            "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.",
            "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore.",
            "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.",
            "Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.",
            "Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet consectetur.",
            "Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit.",
            "Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil.",
            "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis."
        ];

        // Select 4-6 random sentences
        $numSentences = random_int(4, 6);
        $selected = array_rand($sentences, $numSentences);

        $abstract = '';
        foreach ((array)$selected as $index) {
            $abstract .= $sentences[$index] . ' ';
        }

        return trim($abstract);
    }

    /**
     * Generate random keywords
     *
     * @return array Array of 3-5 keywords
     */
    public function generateKeywords(): array
    {
        $keywords = [
            'research', 'analysis', 'study', 'methodology', 'framework',
            'innovation', 'technology', 'system', 'model', 'approach',
            'evaluation', 'assessment', 'development', 'application', 'theory'
        ];

        $numKeywords = random_int(3, 5);
        $selected = array_rand($keywords, $numKeywords);

        return array_map(fn($i) => $keywords[$i], (array)$selected);
    }

    /**
     * Generate random date within past 2 years
     *
     * @return string Date in YYYY-MM-DD format
     */
    public function generateDate(): string
    {
        $timestamp = time() - random_int(0, 63072000); // Past 2 years
        return date('Y-m-d', $timestamp);
    }
}
