# Advanced Usage & Development Guide

**Version:** 1.1.0  
**Date:** 2026-06-07  
**Target Environment:** OJS 3.5+ / PHP 8.2+

---

> ⚠️ **Important:** This document captures the work done to make Dummy Data Generator work from the **command line** (CLI). The plugin's API routes work through the normal OJS web request cycle. However, if you want to generate data **from a CLI script** (e.g., for automated setup, CI/CD pipelines, Docker-based development), you'll need the workarounds described here.

---

## Table of Contents

1. [The Problem: CLI Bootstrap Context](#the-problem-cli-bootstrap-context)
2. [Approach A: Fix the Generator Classes (Plugin Code)](#approach-a-fix-the-generator-classes-plugin-code)
3. [Approach B: Direct PDO Generation (CLI Scripts)](#approach-b-direct-pdo-generation-cli-scripts)
4. [Database Schema Reference](#database-schema-reference)
5. [Complete CLI Generation Script](#complete-cli-generation-script)
6. [Verifying Data After CLI Generation](#verifying-data-after-cli-generation)
7. [Docker/Podman Notes](#dockerpodman-notes)

---

## The Problem: CLI Bootstrap Context

OJS is designed primarily as a web application. When you bootstrap OJS from the command line:

```php
define('INDEX_FILE_LOCATION', '/var/www/html/index.php');
define('ENABLE_SESSION', 0);
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/myjournal';
require '/var/www/html/lib/pkp/includes/bootstrap.php';
```

Several critical parts of OJS are unavailable:

| Component | Web Request | CLI Script | Impact |
|-----------|------------|------------|--------|
| `$request->getContext()` | Returns `Journal` | Returns `null` | Context scoping fails |
| `$request->getUser()` | Returns current user | Returns `null` | Decision recording fails |
| `$request->getSite()` | Returns `Site` | May fail | Locale resolution fails |
| `$request->getRouter()` | Returns `Router` | Returns `null` | Route errors |
| `updateSetting()` | Works via context | Method missing | Data tracking fails |

### Root Cause

OJS Repository classes (like `Repo::user()`, `Repo::submission()`) internally call `Application::get()->getRequest()->getContext()` to get the current journal context. From CLI, the request has no router or context set, so these calls either return `null` or throw an error.

---

## Approach A: Fix the Generator Classes (Plugin Code)

This is the approach taken in this plugin's source code. Each generator class was modified to handle null requests gracefully.

### Fix Pattern

Instead of:

```php
$primaryLocale = Application::get()->getRequest()->getSite()->getPrimaryLocale();
```

Use:

```php
$request = Application::get()->getRequest();
if ($request !== null) {
    $primaryLocale = $request->getSite()->getPrimaryLocale();
} else {
    $primaryLocale = \PKP\facades\Locale::getLocale();
}
```

### What Was Changed

| File | Fix | Purpose |
|------|-----|---------|
| `UserGenerator.php` | Null-safe locale, `setDateRegistered()`, `getByRoleIds()` API | Works from CLI, required field, compatible API |
| `SubmissionGenerator.php` | `getPrimaryLocale()` helper, null-safe `getUser()` | Locale resolution from CLI |
| `IssueGenerator.php` | `getPrimaryLocale()` helper | Locale resolution from CLI |
| `Faker.php` | Unique email via `uniqid()` | Prevents duplicate key errors on re-runs |

### Limitations

Even with these fixes, the plugin's **DataTracker** class calls `$context->updateSetting()` which is not available from CLI (the `Journal` object returned by `getById()` doesn't expose this method outside a web request). This means:

- ✅ **Data generation** works from CLI
- ❌ **Data tracking** (`trackUsers()`, `trackSubmissions()`, etc.) fails from CLI
- ❌ **Cleanup** via DataTracker fails from CLI

For cleanup from CLI, use the direct PDO approach (Approach B) instead.

---

## Approach B: Direct PDO Generation (CLI Scripts)

If you need to generate data from CLI and the plugin fixes aren't sufficient, use direct PDO queries. This was the approach used to generate 15 users, 20 submissions, and 1 issue in the Docker-based development environment.

### Bootstrap with Laravel's DB Facade

While `Repo::user()->add()` may fail from CLI, Laravel's `Illuminate\Support\Facades\DB::connection()` works:

```php
define('INDEX_FILE_LOCATION', '/var/www/html/index.php');
define('ENABLE_SESSION', 0);
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/myjournal';

ob_start();
require '/var/www/html/lib/pkp/includes/bootstrap.php';
ob_end_clean(); // Discard bootstrap's HTML output

$db = \Illuminate\Support\Facades\DB::connection();
```

### Database Connection Details

For environments where Laravel's DB doesn't work, use raw PDO:

```php
$pdo = new PDO(
    "mysql:host=db;dbname=devdb;charset=utf8",
    "devuser",
    "password",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

---

## Database Schema Reference

These are the MySQL tables involved in data generation for OJS 3.5+:

### users

| Column | Type | Notes |
|--------|------|-------|
| `user_id` | int(11) AUTO_INCREMENT | Primary key |
| `username` | varchar(32) UNIQUE | `dummy_user_X_hash` |
| `email` | varchar(255) UNIQUE | `dummy.user.X_hash@example.com` |
| `password` | varchar(255) | Bcrypt hash via `Validation::encryptCredentials()` |
| `date_registered` | datetime | **Must be set** (no default in strict mode) |
| `must_change_password` | smallint(6) | Set to 0 |
| `family_name` | varchar(255) | May be in `user_settings` table in some OJS versions |
| `given_name` | varchar(255) | May be in `user_settings` table in some OJS versions |

> ⚠️ **Note:** In some OJS 3.5+ installations, `given_name` and `family_name` are stored in the `user_settings` table rather than directly on the `users` table. The example script assumes they are direct columns (as in the Docker test environment). If your OJS stores them in `user_settings`, adjust the INSERT accordingly.

### user_groups

| Column | Type | Notes |
|--------|------|-------|
| `user_group_id` | int(11) AUTO_INCREMENT | |
| `context_id` | int(11) | Journal ID |
| `role_id` | int(11) | `65536` = Author, `16` = Site Admin, `17` = Manager |
| ... | | Other metadata columns |

### user_user_groups

| Column | Type | Notes |
|--------|------|-------|
| `user_group_id` | int(11) | FK to user_groups |
| `user_id` | int(11) | FK to users |

### submissions

| Column | Type | Notes |
|--------|------|-------|
| `submission_id` | int(11) AUTO_INCREMENT | |
| `context_id` | int(11) | Journal ID |
| `section_id` | int(11) | Section ID |
| `locale` | varchar(14) | e.g., `en_US` |
| `submission_progress` | int(11) | `1` = in progress |
| `status` | smallint(6) | `1` = STATUS_QUEUED, `3` = STATUS_PUBLISHED, `5` = STATUS_SCHEDULED |
| `date_submitted` | datetime | |
| `last_modified` | datetime | |
| `stage_id` | int(11) | `1`=submission, `3`=review, `5`=production |

### publications

| Column | Type | Notes |
|--------|------|-------|
| `publication_id` | int(11) AUTO_INCREMENT | |
| `submission_id` | int(11) | FK to submissions |
| `issue_id` | int(11) | FK to issues (nullable) |
| `status` | smallint(6) | Publication status |
| `primary_contact_id` | int(11) | FK to authors (nullable) |
| `date_published` | datetime | |
| `seq` | double | Sequence/order |

### authors

| Column | Type | Notes |
|--------|------|-------|
| `author_id` | int(11) AUTO_INCREMENT | |
| `submission_id` | int(11) | FK to submissions |
| `user_group_id` | int(11) | Must be an author user_group |
| `user_id` | int(11) | FK to users (nullable) |
| `seq` | double | Order |
| `primary_contact` | smallint(6) | Set to `1` for primary |
| `publication_id` | int(11) | FK to publications |

### issues

| Column | Type | Notes |
|--------|------|-------|
| `issue_id` | int(11) AUTO_INCREMENT | |
| `journal_id` | int(11) | Journal ID |
| `volume` | int(11) | |
| `number` | int(11) | |
| `year` | int(11) | |
| `published` | int(11) | `1` = published |
| `date_published` | datetime | |
| `last_modified` | datetime | |
| `access_status` | int(11) | `1` = open access |
| `current` | int(11) | `1` = current issue |

### Circular Dependency Trick

OJS has a circular foreign key between `authors` and `publications`:

- `authors.publication_id` → `publications.publication_id`
- `publications.primary_contact_id` → `authors.author_id`

**Solution:** Insert in this order:
1. Insert `publications` with `primary_contact_id = NULL`
2. Insert `authors` with the correct `publication_id`
3. `UPDATE publications SET primary_contact_id = ? WHERE publication_id = ?`

---

## Complete CLI Generation Script

Here is a complete working PHP script that generates data from CLI using PDO directly. Adapt the connection details and paths to your environment.

```php
<?php
/**
 * CLI Data Generation Script
 * 
 * Bootstrap OJS from CLI and generate dummy data using PDO.
 * Copy this to your OJS root and run:
 *   php generate_dummy_data.php
 */

// === CONFIGURATION ===
$journalId = 1;           // Your journal ID
$sectionId = 1;           // Your section ID
$userCount = 15;          // Number of users to generate
$submissionCount = 20;    // Number of submissions to generate

// DB connection (use docker service name or host)
$dbConfig = [
    'host' => 'db',
    'db'   => 'devdb',
    'user' => 'devuser',
    'pass' => 'YOUR_DB_PASSWORD',
];

// === BOOTSTRAP OJS (for password hashing) ===
define('INDEX_FILE_LOCATION', '/var/www/html/index.php');
define('ENABLE_SESSION', 0);
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/myjournal';

ob_start();
require '/var/www/html/lib/pkp/includes/bootstrap.php';
ob_end_clean();

// Hash helper using OJS's own validation
function hashPassword(string $username, string $password): string {
    return \PKP\security\Validation::encryptCredentials($username, $password);
}

// === PDO CONNECTION ===
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['db']};charset=utf8",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "DB connected\n";
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

// === STEP 1: GENERATE USERS ===
echo "\n=== Step 1: Generating $userCount users ===\n";

// Get author user group
$stmt = $pdo->prepare("SELECT user_group_id FROM user_groups WHERE context_id = ? AND role_id = 65536 LIMIT 1");
$stmt->execute([$journalId]);
$authorGroupId = $stmt->fetchColumn();

if (!$authorGroupId) {
    die("No author user group found for context $journalId\n");
}
echo "Author user group ID: $authorGroupId\n";

$userIds = [];
for ($i = 0; $i < $userCount; $i++) {
    $username = 'dummy_user_' . $i . '_' . substr(md5(uniqid()), 0, 6);
    $email = 'dummy.user.' . $i . '_' . substr(md5(uniqid()), 0, 6) . '@example.com';
    $password = hashPassword($username, 'DummyUser123!');
    $now = date('Y-m-d H:i:s');
    
    $names = [
        'John','Jane','Michael','Sarah','David','Emily',
        'Robert','Lisa','William','Jennifer','James','Maria',
    ];
    $surnames = [
        'Smith','Johnson','Williams','Brown','Jones','Garcia',
        'Miller','Davis','Rodriguez','Martinez','Hernandez',
    ];
    $given = $names[array_rand($names)];
    $family = $surnames[array_rand($surnames)];
    
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password, date_registered, must_change_password, family_name, given_name)
         VALUES (?, ?, ?, ?, 0, ?, ?)"
    );
    $stmt->execute([$username, $email, $password, $now, $family, $given]);
    $userId = (int) $pdo->lastInsertId();
    $userIds[] = $userId;
    
    // Assign author role
    $stmt = $pdo->prepare("INSERT INTO user_user_groups (user_group_id, user_id) VALUES (?, ?)");
    $stmt->execute([$authorGroupId, $userId]);
    
    echo "  Created user $userId: $given $family ($username)\n";
}
echo "✓ Created " . count($userIds) . " users\n";

// === STEP 2: GENERATE SUBMISSIONS ===
echo "\n=== Step 2: Generating $submissionCount submissions ===\n";

$topics = [
    'Machine Learning Applications','Climate Change Impact',
    'Public Health Policy','Educational Technology',
    'Economic Development','Social Media Influence',
    'Renewable Energy Systems','Urban Planning Methods',
    'Data Science Innovation','AI Ethics',
    'Sustainable Agriculture','Digital Humanities',
];

$submissionIds = [];
for ($i = 0; $i < $submissionCount; $i++) {
    $authorId = $userIds[$i % count($userIds)];
    $title = 'A Study on ' . $topics[array_rand($topics)];
    $now = date('Y-m-d H:i:s');
    
    // Insert submission
    $stmt = $pdo->prepare(
        "INSERT INTO submissions (context_id, section_id, locale, submission_progress, status, date_submitted, last_modified, stage_id)
         VALUES (?, ?, 'en_US', 1, 1, ?, ?, 5)"
    );
    $stmt->execute([$journalId, $sectionId, $now, $now]);
    $submissionId = (int) $pdo->lastInsertId();
    $submissionIds[] = $submissionId;
    
    // Insert submission settings (title, abstract, keywords)
    $stmt = $pdo->prepare(
        "INSERT INTO submission_settings (submission_id, locale, setting_name, setting_value)
         VALUES (?, 'en_US', ?, ?)"
    );
    $stmt->execute([$submissionId, 'title', $title]);
    $stmt->execute([$submissionId, 'abstract', 'Lorem ipsum abstract for ' . $title]);
    $stmt->execute([$submissionId, 'keywords', 'research, analysis, methodology']);
    
    // Insert publication (without primary_contact_id first)
    $stmt = $pdo->prepare(
        "INSERT INTO publications (submission_id, status, seq, date_published, access_status)
         VALUES (?, 1, ?, NULL, 1)"
    );
    $stmt->execute([$submissionId, $i + 1]);
    $publicationId = (int) $pdo->lastInsertId();
    
    // Insert author
    $stmt = $pdo->prepare(
        "INSERT INTO authors (submission_id, user_group_id, user_id, seq, primary_contact, publication_id)
         VALUES (?, ?, ?, 1, 1, ?)"
    );
    $stmt->execute([$submissionId, $authorGroupId, $authorId, $publicationId]);
    $authorRecordId = (int) $pdo->lastInsertId();
    
    // Update publication with primary_contact_id
    $stmt = $pdo->prepare("UPDATE publications SET primary_contact_id = ? WHERE publication_id = ?");
    $stmt->execute([$authorRecordId, $publicationId]);
    
    // Stage assignment
    $stmt = $pdo->prepare(
        "INSERT INTO stage_assignments (submission_id, user_group_id, user_id, date_assigned, stage_id)
         VALUES (?, ?, ?, ?, 1)"
    );
    $stmt->execute([$submissionId, $authorGroupId, $authorId, $now]);
    
    echo "  Created submission $submissionId: $title (author: user $authorId)\n";
}
echo "✓ Created " . count($submissionIds) . " submissions\n";

// === STEP 3: GENERATE ISSUE ===
echo "\n=== Step 3: Generating issue ===\n";

$year = date('Y');
$volume = random_int(1, 20);
$number = random_int(1, 12);
$now = date('Y-m-d H:i:s');

$stmt = $pdo->prepare(
    "INSERT INTO issues (journal_id, volume, number, year, published, date_published, last_modified, access_status, current)
     VALUES (?, ?, ?, ?, 1, ?, ?, 1, 1)"
);
$stmt->execute([$journalId, $volume, $number, $year, $now, $now]);
$issueId = (int) $pdo->lastInsertId();

// Issue settings
$stmt = $pdo->prepare("INSERT INTO issue_settings (issue_id, locale, setting_name, setting_value) VALUES (?, 'en_US', ?, ?)");
$stmt->execute([$issueId, 'title', "Vol. {$volume} No. {$number} ({$year})"]);
$stmt->execute([$issueId, 'description', 'Dummy issue generated for testing purposes.']);

// Publish submissions
$stmt = $pdo->prepare("SELECT publication_id FROM publications WHERE submission_id = ?");
$updatePub = $pdo->prepare("UPDATE publications SET issue_id = ?, status = 3, date_published = ? WHERE publication_id = ?");

foreach ($submissionIds as $submissionId) {
    $stmt->execute([$submissionId]);
    $pubId = $stmt->fetchColumn();
    if ($pubId) {
        $updatePub->execute([$issueId, $now, $pubId]);
    }
}

echo "✓ Created issue $issueId: Vol. {$volume} No. {$number} ({$year})\n";
echo "\n=== DONE ===\n";
echo "Users: " . count($userIds) . "\n";
echo "Submissions: " . count($submissionIds) . "\n";
echo "Issue: $issueId\n";
```

---

## Verifying Data After CLI Generation

Once data is generated via CLI, verify it through the OJS web interface:

1. **Clear OJS cache:**
   ```bash
   rm -rf /path/to/ojs/cache/*
   ```

2. **Check Archives page:** Navigate to `http://your-ojs-url/index.php/myjournal/issue/archive`
   - The generated issue should appear in the archive list

3. **Check Current Issue:** Navigate to `http://your-ojs-url/index.php/myjournal/issue/current`
   - If "No Current Issue" appears, check the database:
     ```sql
     SELECT issue_id, volume, number, published, current 
     FROM issues WHERE journal_id = 1;
     ```
   - Ensure `current = 1` and `published = 1`

4. **Check Users:** Navigate to the OJS Users list
   - Users should appear with `dummy_user_X_hash` usernames
   - They should have the Author role

---

## Docker/Podman Notes

### Docker Environment Setup

This plugin was developed and tested using Docker containers:

| Service | Container Name | Details |
|---------|---------------|---------|
| Web | `ojs83_web` | Apache + PHP 8.3 |
| DB | `ojs83_db` | MySQL 8 |

### Copying Files to Container

```bash
# Copy plugin files
docker cp classes/UserGenerator.php ojs83_web:/var/www/html/plugins/generic/dummyDataGenerator/classes/UserGenerator.php
docker cp classes/SubmissionGenerator.php ojs83_web:/var/www/html/plugins/generic/dummyDataGenerator/classes/SubmissionGenerator.php
docker cp classes/IssueGenerator.php ojs83_web:/var/www/html/plugins/generic/dummyDataGenerator/classes/IssueGenerator.php
docker cp classes/Faker.php ojs83_web:/var/www/html/plugins/generic/dummyDataGenerator/classes/Faker.php

# Copy and run CLI generation script
docker cp generate.php ojs83_web:/tmp/generate.php
docker exec ojs83_web php /tmp/generate.php
```

### Running CLI Scripts in Container

Use heredoc-style execution to avoid file copy issues:

```bash
docker exec -i ojs83_web php -d max_execution_time=300 << 'PHPSCRIPT'
<?php
// Your PHP code here
PHPSCRIPT
```

### Database Queries from Host

```bash
docker exec ojs83_db mysql -u devuser -pYOUR_DB_PASSWORD devdb -e "SELECT COUNT(*) FROM users"
```

### Cache Clearing in Container

```bash
docker exec ojs83_web rm -rf /var/www/html/cache/*
```

### Debugging PHP Errors

```bash
# Check Apache error log
docker exec ojs83_web tail -50 /var/log/apache2/error.log

# Check OJS error log
docker exec ojs83_web tail -50 /var/www/html/logs/error.log
```

---

## Summary

The CLI workaround is needed because OJS Repos are tightly coupled to the web request lifecycle. The approaches outlined above let you:

1. **Fix the plugin code** (Approach A) — Makes generators null-safe for CLI, but tracking/cleanup still won't work from CLI
2. **Use PDO directly** (Approach B) — Works for any CLI data generation, including cleanup

For production workflows, always use the **API endpoints** (which go through the normal web request cycle). The CLI approach is for development, testing, and CI/CD environments only.
