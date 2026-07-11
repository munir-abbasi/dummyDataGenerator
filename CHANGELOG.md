# Changelog

All notable changes to the Dummy Data Generator plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0.0] - 2026-07-11

### Fixed

- **`Services::get('file')` → `app()->get('file')`:** The `APP\core\Services` class is removed in OJS 3.5.0.5. Replaced with the DI container `app()->get('file')` call, which returns the identical `PKPFileService` singleton via Laravel's service container. The `add($from, $to)` signature and return type are unchanged. See `SubmissionGenerator.php`.

### Changed

- **Version bump:** 1.1.0 → 2.0.0.0
- `version.xml` release updated for OJS 3.5.0.5 compatibility

### Removed

- `use APP\core\Services` import from `SubmissionGenerator.php` (class no longer exists in OJS 3.5.0.5)

---

## [1.1.0] - 2026-06-01

### Fixed

- **Section validation:** Submission generation no longer crashes when the journal has no sections defined; returns a descriptive 400 error instead
- **E2E cleanup confirmation:** Fixed confirmation token format (now uses `DELETE_ALL_DUMMY_DATA` string to match production API contract)
- **MockServices namespace:** Corrected from `PKP\Services\Services` to `APP\core\Services` for OJS 3.5+ compatibility
- **CLI compatibility — UserGenerator:** Added null-safe locale resolution via `getRequest()` check, explicit `setDateRegistered()` for strict DB modes, and fixed userGroup API from `getCollector()` → `getByRoleIds()`
- **CLI compatibility — SubmissionGenerator:** Added `getPrimaryLocale()` helper with null-safe fallback, null-safe `getRequest()->getUser()` for decision recording
- **CLI compatibility — IssueGenerator:** Added `getPrimaryLocale()` helper with null-safe fallback
- **CLI compatibility — Faker:** Unique email generation via `uniqid()` hash to prevent duplicate key errors on re-runs
- **Plugin loading:** Changed `version.xml` lazy-load from `1` to `0` for reliable route registration

### Added

- New locale key `plugins.generic.dummyDataGenerator.error.noSections` for section-missing error message
- **Rate limiting:** 30-second cooldown between generation requests (cleanup exempt) to prevent abuse
- **Production environment warning:** Logs warning when data generation or cleanup is requested in production
- **Audit logging:** Logs successful generation and cleanup operations with context and counts
- **API documentation:** Complete API reference in `API_DOCUMENTATION.md`
- **Test scaffolding:** PHPUnit configuration, bootstrap, and unit/integration test files for all core classes
- **CLI/development documentation:** [`ADVANCED_USAGE.md`](ADVANCED_USAGE.md) with complete CLI workaround guidance, database schema reference, PDO generation script, and Docker/Podman integration notes

### Changed

- **Version bump:** 1.0.0 → 1.1.0

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

#### Testing

- Unit tests for Faker and DataTracker classes
- Integration test scaffolding for API endpoints and generators
- PHPUnit configuration with coverage reporting
- Test bootstrap with OJS environment detection

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

| Version | PHP  | OJS      | Status           | End of Support |
| ------- | ---- | -------- | ---------------- | -------------- |
| 2.0.0.0 | 8.2+ | 3.5.0.5+ | [x] Active       | TBD            |
| 1.1.0   | 8.2+ | 3.5+     | [x] Maintained   | 2026-07-11     |
| 1.0.0   | 8.2+ | 3.5+     | [ ] Deprecated   | 2026-07-11     |
| 0.1.0   | 8.2+ | 3.5+     | [ ] Deprecated   | 2026-03-26     |

---

## Upgrade Guide

### From 1.1.0 to 2.0.0.0

1. **Backup database:**
   
   ```bash
   mysqldump -u ojs_user -p ojs_database > backup.sql
   ```

2. **Replace plugin files:**
   
   ```bash
   cp -r dummyDataGenerator /path/to/ojs/plugins/generic/
   ```

3. **Clear cache and re-enable:**
   
   ```bash
   php tools/clearCache.php
   ```

**Breaking Changes:** None — database schema unchanged, settings preserved, Repo API calls identical  
**Deprecations:** None  
**Migration Required:** No — file replacement only

### From 1.0.0 to 1.1.0

1. **Backup database:**
   
   ```bash
   mysqldump -u ojs_user -p ojs_database > backup.sql
   ```

2. **Replace plugin files:**
   
   ```bash
   cp -r dummyDataGenerator /path/to/ojs/plugins/generic/
   ```

3. **Clear cache and re-enable:**
   
   ```bash
   php tools/clearCache.php
   ```

**Breaking Changes:** None  
**Deprecations:** None  
**Migration Required:** No

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

### v2.0.0.0 OJS 3.5.0.5 Compatibility Release

**Theme:** API Compatibility

This release addresses the removal of the `Services` class in OJS 3.5.0.5:

- ✅ `Services::get('file')` replaced with `app()->get('file')` — identical `PKPFileService` singleton, same `add($from, $to)` signature
- ✅ All other `Repo::*()` calls pre-verified against OJS 3.5.0.5 source
- ✅ Locale format (`locale/en/default.po`) already compatible with OJS 3.5 PO-based system

**Recommended For:** All users on OJS 3.5.0.5+  
**Upgrade Path:** File replacement only — no breaking changes, no database migration

### v1.1.0 Maintenance Release

**Theme:** Stability & Compatibility

This release addresses runtime defects discovered in v1.0.0:

- ✅ Section validation prevents crashes on misconfigured journals
- ✅ E2E test confirmation token aligned with production API contract
- ✅ MockServices namespace fixed for OJS 3.5+ compatibility
- ✅ New descriptive locale key for section-missing errors

**Recommended For:** All v1.0.0 users  
**Upgrade Path:** File replacement only — no breaking changes

### v1.0.0 Production Release

**Theme:** Production Ready

This release marks the first production-ready version of the Dummy Data Generator plugin. It includes:

- ✅ Comprehensive error handling
- ✅ Database transactions for data integrity
- ✅ Complete documentation suite
- ✅ Test scaffolding with unit and integration tests
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

### v1.1.0 (Released 2026-06-01)

- [x] Section validation with descriptive error response
- [x] E2E cleanup confirmation token fix
- [x] MockServices namespace correction
- [ ] Rate limiting (5-minute cooldown) — carried forward
- [ ] GitHub Actions CI/CD — carried forward

### v2.0.0.0 (Released 2026-07-11)

- [x] `Services::get('file')` → `app()->get('file')` replacement for OJS 3.5.0.5
- [x] Version bump and documentation updates

### v2.1.0 (Planned)

- [ ] PDF file generation option
- [ ] Improved genre selection
- [ ] Random author assignment
- [ ] Additional translations
- [ ] Community translation support

### v3.0.0 (Future)

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

*Last updated: 2026-06-01*
