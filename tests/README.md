# Test Suite

## Running Tests

### Prerequisites

Install development dependencies:

```bash
composer install --dev
```

### Unit Tests

Test individual classes in isolation:

```bash
./vendor/bin/phpunit --testsuite=Unit
```

### Integration Tests

Test API endpoints and database operations:

```bash
./vendor/bin/phpunit --testsuite=Integration
```

### End-to-End Tests

Test complete workflows:

```bash
./vendor/bin/phpunit --testsuite=E2E
```

### All Tests

Run complete test suite:

```bash
./vendor/bin/phpunit
```

### With Coverage

Generate HTML coverage report:

```bash
./vendor/bin/phpunit --coverage-html=coverage/html
```

View coverage in browser:

```bash
# Open coverage/html/index.html in your browser
```

### Composer Scripts

Convenient npm-style commands:

```bash
composer test              # Run unit tests
composer test:integration  # Run integration tests
composer test:e2e          # Run e2e tests
composer test:coverage     # Generate coverage report
composer phpstan           # Run static analysis
```

## Test Structure

```
tests/
├── Unit/                    # Unit tests for individual classes
│   ├── UserGeneratorTest.php
│   ├── SubmissionGeneratorTest.php
│   ├── IssueGeneratorTest.php
│   ├── DataTrackerTest.php
│   └── FakerTest.php
├── Integration/             # Integration tests
│   └── APIHandlerTest.php
├── E2E/                     # End-to-end workflow tests
│   └── FullWorkflowTest.php
├── fixtures/                # Sample data for tests
│   └── sampleData.php
├── Mocks/                   # Mock objects for PKP services
│   ├── MockRepo.php
│   ├── MockServices.php
│   └── MockClasses.php
├── bootstrap.php            # Test bootstrap
├── TestCase.php             # Base unit test class
├── IntegrationTestCase.php  # Base integration test class
└── E2ETestCase.php          # Base E2E test class
```

## Test Coverage Targets

| Component | Target |
|-----------|--------|
| UserGenerator | 90% |
| SubmissionGenerator | 90% |
| IssueGenerator | 85% |
| DataTracker | 95% |
| Faker | 80% |
| APIHandler | 85% |
| **Overall** | **≥ 85%** |

## Writing Tests

### Unit Test Example

```php
<?php

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Unit;

use APP\plugins\generic\dummyDataGenerator\tests\TestCase;
use APP\plugins\generic\dummyDataGenerator\classes\UserGenerator;

class UserGeneratorTest extends TestCase
{
    public function test_generate_creates_users(): void
    {
        $generator = new UserGenerator();
        // Test implementation
    }
}
```

### Integration Test Example

```php
<?php

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\tests\Integration;

use APP\plugins\generic\dummyDataGenerator\tests\IntegrationTestCase;

class APIHandlerTest extends IntegrationTestCase
{
    public function test_generateUsers_requires_auth(): void
    {
        // Test implementation
    }
}
```

## Debugging Tests

### Verbose Output

```bash
./vendor/bin/phpunit --verbose
```

### Stop on First Failure

```bash
./vendor/bin/phpunit --stop-on-failure
```

### Run Specific Test

```bash
./vendor/bin/phpunit --filter test_generate_creates_users
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Unit/UserGeneratorTest.php
```

## Continuous Integration

Tests run automatically on:
- Every push to `main` or `develop` branches
- Every pull request

View results at: GitHub Actions tab

## Troubleshooting

### PHPUnit Not Found

```bash
# Ensure dependencies are installed
composer install --dev

# Use vendor binary
./vendor/bin/phpunit
```

### Bootstrap Errors

Check that OJS is properly configured in your test environment.

### Mocking Errors

Ensure Mockery is installed: `composer require --dev mockery/mockery`
