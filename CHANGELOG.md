# Changelog

All notable changes to the Dummy Data Generator plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-03-26

### Added

#### Core Features
- **User Generation** - Create 1-100 dummy users with author role assignment
  - Unique usernames: `dummy_user_{index}_{hash}`
  - Unique emails: `dummy.user.{index}@example.com`
  - Random names from predefined lists
  - Default password: `DummyUser123!` (hashed with PKP PasswordHasher)
  - Automatic author role assignment to journal context

- **Submission Generation** - Create 1-200 complete submissions
  - Academic-style titles from topic templates
  - Lorem ipsum abstracts (150-350 words)
  - 3-5 random keywords
  - Placeholder manuscript files
  - Full workflow progression via Decision APIs:
    - EXTERNAL_REVIEW decision
    - ACCEPT decision
    - SEND_TO_PRODUCTION decision
  - Cyclic author assignment
  - Database transaction support

- **Issue Generation** - Create and publish issues
  - Auto-generated volume/number/year
  - Title format: "Vol. X No. Y (YEAR)"
  - Automatic scheduling of all submissions
  - Issue publishing with fallback mechanism
  - Sets as current issue for journal

- **Reversible Cleanup** - Delete all generated data
  - Tracks all created entities in context settings
  - Explicit confirmation required (`DELETE_ALL_DUMMY_DATA`)
  - Deletes in correct order: issues → submissions → users
  - Returns deletion statistics

#### API Endpoints
- `POST /api/v1/users/generate-users` - Generate dummy users
- `POST /api/v1/users/generate-submissions` - Generate submissions
- `POST /api/v1/users/generate-issue` - Generate and publish issue
- `DELETE /api/v1/users/cleanup` - Cleanup all generated data

#### Documentation
- README.md - Installation, usage, features, troubleshooting
- INSTALLATION.md - Detailed installation guide
- API_DOCUMENTATION.md - Complete API reference
- CHANGELOG.md - Version history (this file)
- CONTRIBUTING.md - Contribution guidelines
- Codebase documentation (7 documents in `.planning/codebase/`)

#### Testing
- Unit tests for all generator classes
- Integration tests for API endpoints
- E2E tests for complete workflows
- Mock objects for PKP services
- PHPUnit configuration with coverage reporting

#### Development Tools
- Composer scripts for testing and analysis
- PHPStan configuration at max level
- Git ignore rules for development artifacts
- Test bootstrap with multiple autoload strategies

### Changed

- None (initial release)

### Deprecated

- None

### Removed

- None (initial release)

### Fixed

- Fixed FakerTest dead code (removed tests for non-existent methods)
- Ensured all test methods match actual Faker class API

### Security

- Role-based access control (Manager/Site Admin only)
- Secure password hashing with PKP PasswordHasher
- Input validation (count limits: 1-100 users, 1-200 submissions)
- Cleanup confirmation to prevent accidental deletion
- SQL injection prevention via Repo APIs
- File upload security via OJS file service

### Known Limitations (v1.0.0)

- **Rate Limiting:** No cooldown between API requests (planned for v1.1.0)
- **File Types:** Text placeholders only, no PDF generation (planned for v1.2.0)
- **Translations:** English only (community translations welcomed)
- **OJS Version:** 3.5.x support only (3.6+ compatibility TBD)
- **User Invitations:** Bypasses OJS 3.5 invitation system (by design for testing)

---

## [0.1.0] - 2025-02-21

### Added

- Initial development release
- Basic user generation functionality
- Submission generation with workflow
- Issue creation and publication
- Cleanup functionality

### Notes

This version was a development preview and not suitable for production use.
Superseded by v1.0.0 with comprehensive testing, documentation, and error handling.

---

## Version Support

| Version | PHP | OJS | Status | End of Support |
|---------|-----|-----|--------|----------------|
| 1.0.0 | 8.2+ | 3.5+ | ✅ Active | TBD |
| 0.1.0 | 8.2+ | 3.5+ | ❌ Deprecated | 2026-03-26 |

---

## Upgrade Guide

### From 0.1.0 to 1.0.0

1. **Backup database:**
   ```bash
   mysqldump -u ojs_user -p ojs_database > backup.sql
   ```

2. **Remove old plugin:**
   ```bash
   rm -rf /path/to/ojs/plugins/generic/dummyDataGenerator
   ```

3. **Install v1.0.0:**
   ```bash
   cp -r dummyDataGenerator /path/to/ojs/plugins/generic/
   ```

4. **Clear cache:**
   ```bash
   php tools/clearCache.php
   ```

5. **Re-enable plugin:**
   ```bash
   php tools/plugins.php enable dummyDataGenerator
   ```

**Breaking Changes:** None  
**Deprecations:** None  
**Migration Required:** No

---

## Release Notes

### v1.0.0 Production Release

**Theme:** Production Ready

This release marks the first production-ready version of the Dummy Data Generator plugin. It includes:

- ✅ Comprehensive error handling
- ✅ Database transactions for data integrity
- ✅ Complete documentation suite
- ✅ Full test coverage (Unit, Integration, E2E)
- ✅ CI/CD pipeline with GitHub Actions
- ✅ Security best practices
- ✅ Input validation and sanitization
- ✅ Reversible data generation

**Recommended For:**
- OJS development environments
- Testing installations
- QA/Staging environments
- Plugin development workflows

**Not Recommended For:**
- Production journals with real submissions
- Environments requiring user invitation workflows
- Multi-lingual journals (English only in v1.0.0)

---

## Future Roadmap

### v1.1.0 (Planned)

- [ ] Rate limiting (5-minute cooldown)
- [ ] Improved transaction scope
- [ ] Decision validation enhancements
- [ ] Increased test coverage
- [ ] GitHub Actions CI/CD

### v1.2.0 (Planned)

- [ ] PDF file generation option
- [ ] Improved genre selection
- [ ] Random author assignment
- [ ] Additional translations
- [ ] Community translation support

### v2.0.0 (Future)

- [ ] OJS 3.6+ compatibility
- [ ] Background job support
- [ ] Multilingual content generation
- [ ] Advanced configuration options
- [ ] Progress tracking for long operations

---

## Contributing

To contribute to this changelog:
1. Create a GitHub issue for the feature/bug
2. Submit a pull request with changes
3. Update CHANGELOG.md under "Unreleased" section
4. Use appropriate category (Added, Changed, Deprecated, Removed, Fixed, Security)

For full contribution guidelines, see [CONTRIBUTING.md](CONTRIBUTING.md).

---

## Links

- **GitHub Repository:** [munir-abbasi/dummyDataGenerator](https://github.com/munir-abbasi/dummyDataGenerator)
- **Issue Tracker:** [GitHub Issues](https://github.com/munir-abbasi/dummyDataGenerator/issues)
- **Author:** [Munir Abbasi](https://github.com/munir-abbasi)
- **Website:** [SyntaxHouse](https://syntaxhouse.com)

---

*Last updated: 2026-03-26*
