<?php
/**
 * Sample data fixtures for testing
 *
 * Provides consistent test data for unit and integration tests
 */

declare(strict_types=1);

return [
    'userNames' => [
        ['firstName' => 'John', 'lastName' => 'Doe'],
        ['firstName' => 'Jane', 'lastName' => 'Smith'],
        ['firstName' => 'Robert', 'lastName' => 'Johnson'],
        ['firstName' => 'Emily', 'lastName' => 'Williams'],
        ['firstName' => 'Michael', 'lastName' => 'Brown'],
        ['firstName' => 'Sarah', 'lastName' => 'Jones'],
        ['firstName' => 'David', 'lastName' => 'Garcia'],
        ['firstName' => 'Lisa', 'lastName' => 'Martinez'],
        ['firstName' => 'James', 'lastName' => 'Anderson'],
        ['firstName' => 'Maria', 'lastName' => 'Taylor'],
    ],
    'submissionTitles' => [
        'A Comprehensive Study on Machine Learning Applications in Healthcare',
        'Analysis of Climate Change Impact on Coastal Ecosystems',
        'Novel Approaches to Renewable Energy Storage Systems',
        'The Role of Artificial Intelligence in Modern Diagnostics',
        'Quantum Computing: Challenges and Opportunities in Cryptography',
        'Sustainable Urban Planning: A Multi-Disciplinary Approach',
        'Advances in Nanotechnology for Drug Delivery Systems',
        'Economic Impacts of Global Trade Policies on Developing Nations',
    ],
    'keywords' => [
            ['machine learning', 'data science', 'artificial intelligence', 'neural networks', 'deep learning'],
            ['climate change', 'ecosystem', 'biodiversity', 'conservation', 'environmental science'],
            ['renewable energy', 'energy storage', 'sustainability', 'battery technology', 'solar power'],
            ['healthcare', 'AI', 'diagnostics', 'patient care', 'medical imaging'],
            ['quantum computing', 'qubits', 'superposition', 'entanglement', 'cryptography'],
            ['urban planning', 'sustainability', 'infrastructure', 'smart cities', 'transportation'],
            ['nanotechnology', 'drug delivery', 'biomedicine', 'nanoparticles', 'therapeutics'],
            ['economics', 'trade policy', 'development', 'globalization', 'emerging markets'],
        ],
    'abstracts' => [
        'This study presents a comprehensive analysis of machine learning applications in various healthcare domains. We examine recent advances in deep learning, natural language processing, and computer vision for medical diagnostics. Our findings suggest significant potential for cross-domain applications and improved patient outcomes through AI-assisted decision making.',
        'We investigate the impact of climate change on coastal ecosystems through longitudinal field studies spanning five years. Data collected from multiple sites reveals significant shifts in species distribution, ecosystem dynamics, and biodiversity patterns. Results indicate urgent need for conservation interventions.',
        'This paper introduces novel approaches to renewable energy storage using advanced battery technologies. We present experimental results demonstrating improved energy density, charging rates, and cycle life compared to conventional lithium-ion systems. Implications for grid-scale storage and electric vehicles are discussed.',
        'A systematic review of artificial intelligence applications in modern medical diagnostics. We analyze performance metrics across multiple AI systems deployed in clinical settings. Findings highlight both promising capabilities and important limitations requiring further research.',
    ],
    'issueVolumes' => [
        ['volume' => 1, 'number' => 1],
        ['volume' => 1, 'number' => 2],
        ['volume' => 2, 'number' => 1],
        ['volume' => 2, 'number' => 2],
        ['volume' => 3, 'number' => 1],
    ],
];
