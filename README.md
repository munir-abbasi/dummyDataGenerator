# Dummy Data Generator Plugin for OJS 3.5+

[![Tests](https://github.com/munir-abbasi/dummyDataGenerator/actions/workflows/test.yml/badge.svg)](https://github.com/munir-abbasi/dummyDataGenerator/actions/workflows/test.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![OJS Version](https://img.shields.io/badge/OJS-3.5+-green.svg)](https://pkp.sfu.ca/ojs/)
[![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](LICENSE)

**Author:** [Munir Abbasi](https://github.com/munir-abbasi)  
**Website:** [SyntaxHouse](https://syntaxhouse.com)  
**Version:** 1.0.0  
**Status:** Production Ready

A plugin for generating dummy data (users, submissions, issues) for Open Journal Systems (OJS) 3.5+ development and testing environments.

## ⚡ Quick Start

```bash
# 1. Copy plugin to OJS
cp -r dummyDataGenerator /path/to/ojs/plugins/generic/

# 2. Clear cache (via admin panel or manually)
# Admin panel: Website Administration → Settings → Website → Clear Cache
# OR manually: rm -rf /path/to/ojs/cache/*

# 3. Enable plugin via Website Administration → Settings → Website → Plugins

# 4. Generate test data via API
curl -X POST http://your-ojs-url/api/v1/users/generate-users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"count": 20}'
```

## 📋 Requirements

- **OJS Version:** 3.5.0 or later
- **PHP Version:** 8.2 or later (8.3+ recommended)
- **Permissions:** Journal Manager or Site Administrator access

## ✨ Features

### Generate Dummy Users
- Configurable number of users (1-100)
- Default password: `DummyUser123!`
- Automatic author role assignment
- Secure password hashing with PKP PasswordHasher

### Generate Dummy Submissions
- Configurable number of submissions (1-200)
- Includes submission files (placeholder manuscripts)
- Progresses through editorial workflow using Decision APIs
- Assigns to existing authors cyclically
- Database transaction support for data integrity

### Generate Dummy Issues
- Automatically schedules all submissions for publication
- Publishes the issue making it the current issue
- Fallback publishing mechanism for compatibility

### Reversible Generation
- Track all created entities for cleanup
- One-click cleanup to delete all generated data
- Explicit confirmation required for safety

## 📦 Installation

### Method 1: Manual Installation

1. **Copy plugin directory:**
   ```bash
   cp -r dummyDataGenerator /path/to/ojs/plugins/generic/
   ```

2. **Clear OJS cache:**
   - **Via Admin Panel:** Website Administration → Settings → Website → Clear Cache
   - **OR Manually:** `rm -rf /path/to/ojs/cache/*`

3. **Enable plugin:**
   - Navigate to: Website Administration → Settings → Website → Plugins
   - Find "Dummy Data Generator" and enable it

### Method 2: CLI Installation (if available)

```bash
# Copy plugin
cp -r dummyDataGenerator /path/to/ojs/plugins/generic/

# Clear cache (manual)
rm -rf /path/to/ojs/cache/*

# Enable via CLI (only if tools/plugins.php exists)
php tools/plugins.php enable dummyDataGenerator
```

**Note:** The `tools/plugins.php` script may not be available in all OJS installations. Use the web interface if CLI tools are unavailable.

### Troubleshooting

**Plugin not showing up?**
- Verify plugin directory is in `plugins/generic/`
- Check file permissions (should be readable by web server)
- Clear template cache: `php tools/clearCache.php`

**Error: "No context available"?**
- Ensure you're accessing API from a journal context
- URL should include journal path: `http://your-ojs-url/journal-path/api/...`

For detailed installation instructions, see [INSTALLATION.md](INSTALLATION.md)

## 🚀 Usage

### API Endpoints

All endpoints are under `/api/v1/users/` and require Manager or Admin role.

#### Generate Users

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  http://your-ojs-url/api/v1/users/generate-users \
  -d '{"count": 20}'
```

**Response:**
```json
{
  "success": true,
  "created": 20,
  "userIds": [1, 2, 3, ...],
  "defaultPassword": "DummyUser123!",
  "message": "Users created with author role..."
}
```

#### Generate Submissions

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  http://your-ojs-url/api/v1/users/generate-submissions \
  -d '{"count": 50}'
```

**Prerequisites:** Generate users first to have authors available.

#### Generate Issue

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  http://your-ojs-url/api/v1/users/generate-issue
```

**Prerequisites:** Generate submissions first.

#### Cleanup All Data

```bash
curl -X DELETE \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  http://your-ojs-url/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA
```

⚠️ **Warning:** This permanently deletes all generated data.

For complete API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

## 📊 Generated Data Structure

### Users
- **Username:** `dummy_user_{index}_{hash}`
- **Email:** `dummy.user.{index}@example.com`
- **Name:** Random combination from predefined lists
- **Password:** `DummyUser123!` (hashed)
- **Role:** Author (assigned to journal context)

### Submissions
- **Title:** Generated from academic topics
- **Abstract:** Lorem ipsum text (150-350 words)
- **Keywords:** 3-5 random academic keywords
- **Files:** Placeholder text manuscript
- **Workflow Status:** Progressed to Production stage
- **Author:** Assigned from generated user pool

### Issues
- **Volume/Number:** Auto-generated
- **Title:** "Vol. X No. Y (YEAR)"
- **Status:** Published (current issue)
- **Articles:** All generated submissions scheduled

## ⚠️ Important Warnings

### Development/Testing Use Only
This plugin creates **permanent data** in your OJS database. **Do not use on production installations** unless you intend to keep the generated data.

### Default Password
All generated users share the password `DummyUser123!`. Change passwords before using generated accounts for any purpose.

### User Invitation System
OJS 3.5 uses invitation-based user management. This plugin bypasses invitations using internal APIs for testing convenience. Generated users are created directly without email verification.

### File Generation
Submissions include placeholder text files, not actual PDFs. Replace with real files if needed for specific testing scenarios.

## 🧪 Testing

### Run Tests

```bash
# Install dependencies
composer install --dev

# Run all tests
composer test

# Run specific test suite
composer test:integration

# Generate coverage report
composer test:coverage
```

### Test Coverage Targets

| Component | Target | Status |
|-----------|--------|--------|
| UserGenerator | 90% | ✅ |
| SubmissionGenerator | 90% | ✅ |
| IssueGenerator | 85% | ✅ |
| DataTracker | 95% | ✅ |
| Faker | 80% | ✅ |
| APIHandler | 85% | ✅ |

## 📚 Documentation

| Document | Description |
|----------|-------------|
| [INSTALLATION.md](INSTALLATION.md) | Complete installation guide with troubleshooting |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Full API reference with examples |
| [CHANGELOG.md](CHANGELOG.md) | Version history and upgrade notes |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Contribution guidelines |

## 🐛 Known Limitations

- **Rate Limiting:** No cooldown between API requests (planned for v1.1.0)
- **File Types:** Text placeholders only, no PDF generation (planned for v1.2.0)
- **Translations:** English only, community translations welcomed
- **OJS Version:** 3.5.x support only, 3.6+ compatibility TBD

For detailed technical concerns, see the codebase documentation.

## 🔒 Security Considerations

- **Role-based Access:** All endpoints require Manager or Admin role
- **Password Handling:** Uses PKP's `PasswordHasher` for secure hashing
- **Input Validation:** Count parameters validated (1-100 users, 1-200 submissions)
- **Cleanup Safety:** Requires explicit confirmation before deletion

## 🤝 Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### How to Contribute
- Report bugs via GitHub Issues
- Submit pull requests for fixes/features
- Add translations for your language
- Improve documentation

## 📄 License

This plugin is distributed under the **GNU GPL v3** license.

## 🙏 Support

For issues, questions, or contributions:

- **GitHub:** [munir-abbasi/dummyDataGenerator](https://github.com/munir-abbasi/dummyDataGenerator)
- **Website:** [SyntaxHouse](https://syntaxhouse.com)
- **PKP Forum:** [PKP Community Forum](https://forum.pkp.sfu.ca)

## 📝 Version History

### 1.0.0 (2026-03-26) - Production Ready
- ✅ User generation with author roles
- ✅ Submission generation with workflow progression
- ✅ Issue creation and publication
- ✅ Reversible cleanup
- ✅ Comprehensive error handling
- ✅ Database transactions
- ✅ Complete documentation suite
- ✅ CI/CD pipeline with GitHub Actions

---

**Developed by Munir Abbasi | [SyntaxHouse](https://syntaxhouse.com)**
