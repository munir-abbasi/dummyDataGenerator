# Contributing Guidelines

Thank you for your interest in contributing to the Dummy Data Generator plugin!

This document provides guidelines and instructions for contributing.

---

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check existing issues as you might find the problem already reported.

**When creating a bug report, include:**

- Clear title and description
- Steps to reproduce the issue
- Expected behavior vs actual behavior
- OJS version and PHP version
- Any error messages or logs
- Screenshots if applicable

**Example:**

```markdown
**Title:** Submission generation fails with "No context available"

**Description:**
When generating submissions via API, I get a 400 error.

**Steps to Reproduce:**
1. Enable plugin
2. Generate 10 users successfully
3. Try to generate 5 submissions

**Expected:** Submissions created
**Actual:** 400 error "No context available"

**Environment:**
- OJS 3.5.4
- PHP 8.3
- Apache 2.4

**Logs:**
[Error log snippet]
```

### Suggesting Features

Feature suggestions are welcome! Please create an issue with:

- Clear description of the feature
- Use case / problem it solves
- Examples of how it would work
- Any alternatives you've considered

### Pull Requests

**Before submitting a PR:**

1. Fork the repository
2. Create a branch from `main`
3. Make your changes
4. Test thoroughly
5. Update documentation if needed
6. Submit PR with clear description

**PR Guidelines:**

- One feature/fix per PR
- Follow existing code style (PSR-12)
- Add/update tests for new functionality
- Update documentation as needed
- Use descriptive commit messages

---

## Development Setup

### Prerequisites

- PHP 8.2 or later
- Composer
- OJS 3.5+ installation
- Git

### Setup Development Environment

1. **Fork and clone:**
   
   ```bash
   git clone https://github.com/your-username/dummyDataGenerator.git
   cd dummyDataGenerator
   ```

2. **Install dependencies:**
   
   ```bash
   composer install --dev
   ```

3. **Copy to OJS plugins:**
   
   ```bash
   cp -r . /path/to/ojs/plugins/generic/dummyDataGenerator/
   ```

4. **Enable plugin in OJS**

### CLI Development Considerations

This plugin was designed for both web API and CLI usage, but OJS has key limitations from CLI:

- **No request context:** `Application::get()->getRequest()->getContext()` returns `null` from CLI
- **No user session:** `$request->getUser()` returns `null` from CLI
- **No router:** `$request->getRouter()` returns `null` from CLI

When developing features that must work from CLI (e.g., automated test scripts, CI/CD pipelines), follow these patterns:

**Safe locale resolution:**
```php
$request = Application::get()->getRequest();
if ($request !== null) {
    $primaryLocale = $request->getSite()->getPrimaryLocale();
} else {
    $primaryLocale = \PKP\facades\Locale::getLocale();
}
```

**Safe user retrieval:**
```php
try {
    $request = Application::get()->getRequest();
    $user = $request !== null ? $request->getUser() : null;
} catch (\Exception $e) {
    $user = null;
}
```

**Database operations from CLI:**
- `Repo::user()->add()` works from CLI (tested)
- `Repo::submission()->add()` works from CLI (tested)
- `$context->updateSetting()` may fail from CLI — use Laravel's `DB::connection()` instead
- `Repo::userGroup()->getByRoleIds()` works from CLI (tested)
- See `ADVANCED_USAGE.md` for the full CLI workaround guide

> **Note on integration tests:** Tests for the API handler and DataTracker require the full OJS web request cycle (they depend on `getRequest()->getContext()`, `updateSetting()`, and the router). These will **not** work from CLI. Only pure unit tests (Faker, DataTracker internals) can be run from CLI.

### Running Tests

```bash
# All tests
composer test

# Unit tests only
composer test

# Integration tests
composer test:integration

# E2E tests
composer test:e2e

# With coverage
composer test:coverage
```

### Code Quality

```bash
# Static analysis
composer phpstan

# Check code style (if PHP CS Fixer added)
composer cs-check

# Fix code style
composer cs-fix
```

---

## Coding Standards

### PHP Standards

- **PSR-12** coding style
- **Strict types** enabled (`declare(strict_types=1);`)
- **Type declarations** for all parameters and returns
- **PSR-4** autoloading

### File Structure

```
dummyDataGenerator/
├── classes/              # Core business logic
│   ├── UserGenerator.php
│   ├── SubmissionGenerator.php
│   ├── IssueGenerator.php
│   ├── DataTracker.php
│   └── Faker.php
├── api/                  # API handlers
│   └── DummyDataAPIHandler.php
├── tests/                # Test suite
│   ├── Unit/            # Unit tests
│   └── Integration/     # Integration tests
├── locale/               # Translations
│   └── en/              # English strings
├── DummyDataGeneratorPlugin.php
├── version.xml
├── composer.json
├── phpunit.xml.dist
├── ADVANCED_USAGE.md     # CLI/Docker workaround guide
└── TESTING_CHECKLIST.md  # Manual testing checklist
```

### Naming Conventions

- **Classes:** PascalCase (e.g., `UserGenerator`)
- **Methods:** camelCase (e.g., `generateUsers()`)
- **Files:** Match class name (e.g., `UserGenerator.php`)
- **Tests:** `{ClassName}Test.php` (e.g., `UserGeneratorTest.php`)

### Documentation

All files must include:

- File header with copyright, license, author
- Class/method DocBlocks
- `@since` tag for version
- `@param` and `@return` for methods

**Example:**

```php
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
 * @since 3.5.0
 */

declare(strict_types=1);

namespace APP\plugins\generic\dummyDataGenerator\classes;

/**
 * Generate dummy users
 */
class UserGenerator
{
    /**
     * Generate multiple dummy users
     *
     * @param int $count Number of users
     * @param int $contextId Context ID
     * @return array User IDs
     */
    public function generateUsers(int $count, int $contextId): array
    {
        // Implementation
    }
}
```

---

## Testing Guidelines

### Writing Tests

**Unit Tests:**

- Test one thing per method
- Use descriptive names: `test_generateUsers_creates_correct_number_of_users()`
- Mock all external dependencies
- Keep tests fast (<1ms each)

**Integration Tests:**

- Test API endpoints
- Use in-memory database
- Test multiple components together

**E2E Tests:**

- Test complete workflows
- Simulate real API usage
- Verify end state

### Test Coverage

Target coverage:

- UserGenerator: 90%
- SubmissionGenerator: 90%
- IssueGenerator: 85%
- DataTracker: 95%
- Faker: 80%
- APIHandler: 85%
- Overall: ≥85%

---

## Translations

We welcome translations for all languages!

### Adding a Translation

1. Create directory: `locale/{lang_code}/`
2. Copy `locale/en/default.po` to new directory
3. Translate all `msgstr` values
4. Test in OJS with that locale
5. Submit PR

**Example:**

```bash
mkdir -p locale/fr/
cp locale/en/default.po locale/fr/
# Edit locale/fr/default.po with French translations
```

### Translation Guidelines

- Keep `msgid` in English
- Translate `msgstr` to target language
- Preserve formatting and placeholders
- Test all translated strings in context

**Example:**

```po
msgid "plugins.generic.dummyDataGenerator.success.usersCreated"
msgstr "Successfully created {$count} users with author role..."

# French translation:
msgid "plugins.generic.dummyDataGenerator.success.usersCreated"
msgstr "{$count} utilisateurs créés avec succès avec le rôle d'auteur..."
```

---

## Documentation

### Updating Documentation

When adding features or fixing bugs, update:

- README.md (if user-facing change)
- API_DOCUMENTATION.md (if API change)
- CHANGELOG.md (always)
- Inline code comments (if complex logic)

### Documentation Style

- Clear and concise
- Include examples
- Use code blocks for commands
- Add screenshots for UI changes
- Link to related documentation

---

## Git Workflow

### Branch Naming

```
feature/add-rate-limiting
fix/submission-workflow-error
docs/update-installation-guide
refactor/user-generator-class
```

### Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): subject

body (optional)

footer (optional)
```

**Types:**

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style (formatting)
- `refactor`: Code restructuring
- `test`: Tests
- `chore`: Maintenance

**Examples:**

```
feat(api): add rate limiting to generate-users endpoint

Implement 5-minute cooldown between generation requests.
Store last request timestamp in context settings.

Closes #44

---

fix(submissions): handle missing genre gracefully

Add fallback to default genre if "Article Text" not found.
Log warning when fallback used.

Fixes #29

---

docs(readme): add troubleshooting section

Include common installation issues and solutions.
Add error code reference table.
```

### Pull Request Process

1. **Create branch from `main`:**
   
   ```bash
   git checkout main
   git pull origin main
   git checkout -b feature/your-feature
   ```

2. **Make changes and commit:**
   
   ```bash
   git add .
   git commit -m "feat: add your feature"
   ```

3. **Push to your fork:**
   
   ```bash
   git push origin feature/your-feature
   ```

4. **Create PR on GitHub:**
   
   - Go to repository
   - Click "Pull Requests"
   - Click "New Pull Request"
   - Select your branch
   - Fill in PR template

5. **Code review:**
   
   - Maintainer reviews code
   - Address feedback
   - Push fixes to same branch

6. **Merge:**
   
   - Maintainer merges PR
   - Branch deleted

---

## Architecture Decisions

### When to Create ADR

Create Architecture Decision Record (ADR) when:

- Adding major feature
- Changing core architecture
- Introducing new dependency
- Making breaking change

### ADR Template

```markdown
# ADR-{number}: {title}

## Status

{Proposed | Accepted | Deprecated | Superseded}

## Context

What is the issue that we're seeing that is motivating this decision?

## Decision

What is the change that we're proposing and/or doing?

## Consequences

What becomes easier or more difficult to do because of this change?
```

---

## 🔒 Security

### Reporting Security Vulnerabilities

**Do not create public issues for security vulnerabilities!**

Email security concerns to: munir@syntaxhouse.com

Include:

- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Security Guidelines

When contributing:

- Never commit secrets or credentials
- Use prepared statements (Repo APIs handle this)
- Validate all input
- Sanitize output
- Follow least privilege principle

---

## 💬 Communication

### Where to Ask Questions

- **GitHub Issues:** For bugs and feature requests
- **GitHub Discussions:** For questions and ideas
- **PKP Forum:** For OJS-specific questions
- **Email:** munir@syntaxhouse.com for direct contact

### Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Accept constructive criticism
- Focus on what's best for the community

---

## Recognition

Contributors are recognized in:

- README.md (Contributors section)
- CHANGELOG.md (for significant contributions)
- GitHub Contributors page

---

## Checklist for Contributors

Before submitting PR:

- [ ] Code follows PSR-12
- [ ] All tests pass
- [ ] PHPStan passes (no errors)
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Commit messages follow convention
- [ ] No debug code (var_dump, etc.)
- [ ] No unrelated changes
- [ ] Branch is up to date with main

---

## Thank You!

Your contributions make this plugin better for everyone. We appreciate your time and effort!

For questions, don't hesitate to reach out.

---

**Developed by Munir Abbasi | [SyntaxHouse](https://syntaxhouse.com)**
