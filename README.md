# Dummy Data Generator Plugin for Open Journal Systems 3.5+

[![Release](https://img.shields.io/github/v/release/munir-abbasi/dummyDataGenerator?label=Release&color=blue)](https://github.com/munir-abbasi/dummyDataGenerator/releases/tag/v2.0.0.3)
[![OJS Compatibility](https://img.shields.io/badge/OJS-3.5.0--5%2B-green.svg)](https://pkp.sfu.ca/ojs/)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

A generic OJS 3.5+ plugin that extends the OJS REST API to programmatically generate dummy users, submissions, and published issues. Designed strictly for development, testing, staging, and demonstration environments to simulate an active journal state rapidly without manual editorial intervention.

---

## Table of Contents

- [Key Features](#key-features)
- [Requirements & Access Control](#requirements--access-control)
- [Installation](#installation)
- [API Usage & Endpoints](#api-usage--endpoints)
  - [1. Generate Users](#1-generate-users)
  - [2. Generate Submissions](#2-generate-submissions)
  - [3. Generate and Publish Issue](#3-generate-and-publish-issue)
  - [4. Cleanup Dummy Data](#4-cleanup-dummy-data)
- [Safety & Operational Guards](#safety--operational-guards)
  - [Production Guard (HTTP 403)](#production-guard-http-403)
  - [Rate Limiting (HTTP 429)](#rate-limiting-http-429)
  - [Pre-Generation Capacity Checks (HTTP 400)](#pre-generation-capacity-checks-http-400)
  - [Atomic Role Assignment & Workflow Decisions](#atomic-role-assignment--workflow-decisions)
  - [Safe, Retryable Cleanup](#safe-retryable-cleanup)
  - [Default User Credentials](#default-user-credentials)
- [Typical Workflow](#typical-workflow)
- [Testing](#testing)
- [Documentation Index](#documentation-index)
- [License](#license)

---

## Key Features

* **Automated Author User Generation:** Creates realistic dummy users localized to the journal's primary locale, automatically assigns them the Author role in the active context, and sets a standard test password.
* **Full Workflow Submissions:** Creates submissions with academic titles, abstracts, keywords, and temporary placeholder manuscripts (`manuscript.txt`).
* **Programmatic Editorial Simulation:** Applies verified OJS 3.5 editorial decision transitions automatically (Submission stage → External Review stage → Editorial Acceptance → Production stage).
* **Automated Issue Creation & Publication:** Groups tracked submissions into a newly published open-access issue with volume, number, and publication metadata.
* **Reversible Data Tracking:** Tracks all generated entities using the OJS Plugin Settings API (`dummyData_users`, `dummyData_submissions`, `dummyData_issues`).
* **Strict Cleanup Safety:** Safely purges only data created by this plugin in foreign-key order (Issues → Submissions → Users), requiring an explicit confirmation token and preserving tracking for any failed records to allow retries.
* **Built-in Production Guard:** Actively blocks dummy data generation on live production instances (`app_env = production`), returning HTTP 403 Forbidden.
* **Rate Limiting:** Enforces a 30-second cooldown per context on generation endpoints (cleanup is exempt) to prevent accidental server overload.

---

## Requirements & Access Control

### System Requirements
* **Open Journal Systems (OJS):** 3.5.0.5 or newer
* **PHP:** 8.2 or newer
* **Database:** MySQL 8.0+, MariaDB 10.4+, or PostgreSQL 13+

### Authorization
All endpoints extend the OJS REST API and enforce standard OJS authentication:
* Requests must supply an authorized API Key via the HTTP `Authorization` header:
  ```http
  Authorization: Bearer <API_KEY>
  ```
* Access is strictly restricted to authorized administrative roles:
  * **Site Administrator** (`ROLE_ID_SITE_ADMIN`)
  * **Journal Manager** (`ROLE_ID_MANAGER`)

---

## Installation

### Option 1: Release Archive (Recommended)

1. Download the latest release tarball from [GitHub Releases](https://github.com/munir-abbasi/dummyDataGenerator/releases/tag/v2.0.0.3):
   ```bash
   wget https://github.com/munir-abbasi/dummyDataGenerator/releases/download/v2.0.0.3/dummyDataGenerator-2.0.0.3.tar.gz
   ```
2. Extract the archive into your OJS `plugins/generic/` directory:
   ```bash
   tar -xzf dummyDataGenerator-2.0.0.3.tar.gz -C /path/to/ojs/plugins/generic/
   ```
3. Clear the application cache:
   ```bash
   php /path/to/ojs/tools/clearCache.php
   ```
4. Enable the plugin under **Settings → Website → Plugins → Generic Plugins → Dummy Data Generator**.

### Option 2: Git Clone

```bash
cd /path/to/ojs/plugins/generic
git clone https://github.com/munir-abbasi/dummyDataGenerator.git dummyDataGenerator
php /path/to/ojs/tools/clearCache.php
```

See [`INSTALLATION.md`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/INSTALLATION.md) for full directory permissions and troubleshooting steps.

---

## API Usage & Endpoints

All plugin endpoints extend the OJS users API under the journal context path:

```text
/index.php/{journal_path}/api/v1/users/
```

### Summary of Endpoints

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `POST` | `/api/v1/users/generate-users` | Generate dummy users with author roles | Manager / Admin |
| `POST` | `/api/v1/users/generate-submissions` | Generate submissions and progress through workflow | Manager / Admin |
| `POST` | `/api/v1/users/generate-issue` | Package tracked submissions into a published issue | Manager / Admin |
| `DELETE` | `/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA` | Safely remove all tracked dummy data | Manager / Admin |

---

### 1. Generate Users

Creates dummy users and assigns them the Author role in the current journal context.

```http
POST /index.php/{journal_path}/api/v1/users/generate-users
Content-Type: application/json
Authorization: Bearer <API_KEY>

{
    "count": 10
}
```

* **Parameters:** `count` (integer, optional, range: 1–100, default: 10).
* **cURL Example:**
  ```bash
  curl -X POST "https://journal.example.com/index.php/journal/api/v1/users/generate-users" \
       -H "Authorization: Bearer YOUR_API_KEY" \
       -H "Content-Type: application/json" \
       -d '{"count": 10}'
  ```
* **Success Response (HTTP 200):**
  ```json
  {
      "success": true,
      "created": 10,
      "userIds": [42, 43, 44, 45, 46, 47, 48, 49, 50, 51],
      "defaultPassword": "DummyUser123!",
      "message": "Successfully created 10 users with author role. All passwords set to: DummyUser123!"
  }
  ```

---

### 2. Generate Submissions

Creates dummy submissions with full metadata, attaches a placeholder manuscript file, and programmatically records editorial decisions (Acceptance and Send to Production).

```http
POST /index.php/{journal_path}/api/v1/users/generate-submissions
Content-Type: application/json
Authorization: Bearer <API_KEY>

{
    "count": 20
}
```

* **Parameters:** `count` (integer, optional, range: 1–200, default: 20).
* **Prerequisites:** Users must be generated first (used as submission authors), and the journal must have at least one section.
* **cURL Example:**
  ```bash
  curl -X POST "https://journal.example.com/index.php/journal/api/v1/users/generate-submissions" \
       -H "Authorization: Bearer YOUR_API_KEY" \
       -H "Content-Type: application/json" \
       -d '{"count": 20}'
  ```
* **Success Response (HTTP 200):**
  ```json
  {
      "success": true,
      "created": 20,
      "submissionIds": [101, 102, 103, ...],
      "message": "Successfully created 20 submissions and progressed through workflow to production stage."
  }
  ```

---

### 3. Generate and Publish Issue

Creates an open-access issue and assigns all currently tracked dummy submissions to it, setting the issue to published and current.

```http
POST /index.php/{journal_path}/api/v1/users/generate-issue
Authorization: Bearer <API_KEY>
```

* **Parameters:** None. Automatically schedules and publishes all tracked submissions.
* **cURL Example:**
  ```bash
  curl -X POST "https://journal.example.com/index.php/journal/api/v1/users/generate-issue" \
       -H "Authorization: Bearer YOUR_API_KEY"
  ```
* **Success Response (HTTP 200):**
  ```json
  {
      "success": true,
      "issueId": 15,
      "submissionsPublished": 20,
      "message": "Issue created and published with all submissions."
  }
  ```

---

### 4. Cleanup Dummy Data

Deletes only the dummy records generated by this plugin within the active journal context. Deletions are executed in strict referential integrity order (Issues → Submissions → Users).

```http
DELETE /index.php/{journal_path}/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA
Authorization: Bearer <API_KEY>
```

* **Query Parameters:** `confirm` (string, required, must match `DELETE_ALL_DUMMY_DATA`).
* **cURL Example:**
  ```bash
  curl -X DELETE "https://journal.example.com/index.php/journal/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA" \
       -H "Authorization: Bearer YOUR_API_KEY"
  ```
* **Success Response (HTTP 200):**
  ```json
  {
      "success": true,
      "deleted": {
          "users": 10,
          "submissions": 20,
          "issues": 1
      },
      "message": "Successfully cleaned up all dummy data."
  }
  ```

---

## Safety & Operational Guards

### Production Guard (HTTP 403)
To prevent accidental pollution of live journal records, the plugin inspects the OJS application container environment (`app()->isProduction()`) and `config.inc.php` (`app_env = production`). If running in production:
* All generation endpoints return **HTTP 403 Forbidden**:
  ```json
  {
      "success": false,
      "error": "Dummy data generation is disabled in production environments."
  }
  ```
* The `cleanup` endpoint remains permitted in production to allow emergency purging of test records created prior to promotion.

### Rate Limiting (HTTP 429)
* Generation endpoints enforce a **30-second cooldown** per journal context.
* Subsequent generation requests made within the cooldown window return **HTTP 429 Too Many Requests**:
  ```json
  {
      "success": false,
      "error": "Rate limited. Please wait 18 seconds before making another request."
  }
  ```
* `cleanup` is exempt from rate limiting to permit immediate data removal.

### Pre-Generation Capacity Checks (HTTP 400)
* Context settings are bounded to a maximum of **1,000 tracked entities** per type (`users`, `submissions`, `issues`).
* The plugin verifies capacity *before* creating records in the database. If `count` would exceed the threshold, the request is rejected immediately with **HTTP 400 Bad Request**, preventing untracked record creation.

### Atomic Role Assignment & Workflow Decisions
* User creation and author role assignment are executed within an atomic database transaction (`DB::transaction()`). If role assignment fails or no author group exists for the journal context, the user creation is rolled back.
* Editorial decisions record timestamps via `PKP\core\Core::getCurrentDate()` and propagate exceptions up to the API handler, ensuring workflow progression failures return descriptive HTTP 500 responses instead of half-baked records.

### Safe, Retryable Cleanup
* Tracking IDs are only cleared for records that are successfully deleted.
* If any entity fails to delete (e.g., due to an unexpected database lock), the endpoint returns **HTTP 500** with detailed error and failure statistics (`failed` and `errors` objects), and retains the remaining IDs in tracking storage so that cleanup can be safely retried.

### Default User Credentials
All dummy users are created with:
* **Username:** `dummy_user_{index}_{random_hex}`
* **Email:** `dummy.user.{index}_{random_hex}@example.com`
* **Default Password:** `DummyUser123!`
* **Role:** Author (assigned within the current journal context)

---

## Typical Workflow

A typical testing session follows this sequence:

```text
1. POST /api/v1/users/generate-users        (create 10 authors)
   ↓
2. POST /api/v1/users/generate-submissions  (create 20 submissions)
   ↓
3. POST /api/v1/users/generate-issue        (publish into issue)
   ↓
   [Perform QA, benchmarking, or frontend demonstration]
   ↓
4. DELETE /api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA
```

---

## Testing

The plugin includes a test suite covering pure unit tests and OJS integration tests.

### Running Tests Locally

```bash
# Install development dependencies
composer install --dev

# Run standalone unit tests (Faker, DataTracker, UserGenerator password)
composer test

# Run integration tests (API handler validations, production guard, confirmations)
composer test:integration

# Run the complete test suite directly
./vendor/bin/phpunit
```

### Standalone Test Stubs
When running outside of a live OJS web environment, `tests/bootstrap.php` loads `tests/Stubs/OJSStubs.php`, allowing complete CI test execution without requiring a live MySQL database.

---

## Documentation Index

* [`API_DOCUMENTATION.md`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/API_DOCUMENTATION.md): Complete API endpoint reference with request/response JSON schemas and status codes.
* [`INSTALLATION.md`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/INSTALLATION.md): Comprehensive installation, file permissions, and cache troubleshooting guide.
* [`ADVANCED_USAGE.md`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/ADVANCED_USAGE.md): CLI bootstrap context guide, direct database scripting, and Docker/Podman integration.
* [`CHANGELOG.md`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/CHANGELOG.md): Complete release history and OJS 3.5 compatibility migration notes.
* [`TESTING_CHECKLIST.md`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/TESTING_CHECKLIST.md): Step-by-step pre-release verification checklist.

---

## License

This plugin is distributed under the GNU General Public License v3.0 (GPLv3). See [`LICENSE`](file:///home/meer/Projects/ojs-test/production-ready/dummyDataGenerator/LICENSE) for terms.
