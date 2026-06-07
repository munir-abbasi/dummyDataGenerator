# Dummy Data Generator Plugin — Testing Checklist

**Version:** 1.1.0  
**Date:** 2026-06-06  
**Target Environment:** OJS 3.5+ / PHP 8.2+

---

## 1. Installation

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 1.1 | Extract `dummyDataGenerator-1.1.0.tar.gz` into `plugins/generic/` | Directory structure intact | ☐ |
| 1.2 | Clear OJS cache (admin panel or `rm -rf cache/*`) | Cache cleared without errors | ☐ |
| 1.3 | Enable plugin via Website Administration → Plugins | Plugin enabled, no PHP fatal errors | ☐ |
| 1.4 | Verify plugin appears in plugin list as "Dummy Data Generator" | Display name correct | ☐ |
| 1.5 | Check `php error.log` after enabling | No warnings or errors | ☐ |

---

## 2. User Generation

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 2.1 | `POST /api/v1/users/generate-users` with `count: 1` | 200, `created: 1`, user ID returned | ☐ |
| 2.2 | `POST /api/v1/users/generate-users` with `count: 10` | 200, `created: 10`, 10 user IDs returned | ☐ |
| 2.3 | Verify generated users appear in OJS Users list | Users visible with correct names | ☐ |
| 2.4 | Verify users have Author role in current journal | Role assignment correct | ☐ |
| 2.5 | Verify usernames match `dummy_user_{index}_{hash}` pattern | Pattern matches | ☐ |
| 2.6 | Verify emails use `@example.com` domain | Emails end with `@example.com` | ☐ |
| 2.7 | Login as generated user with password `DummyUser123!` | Login succeeds | ☐ |
| 2.8 | `POST /api/v1/users/generate-users` with `count: 0` | 400 error (invalid count) | ☐ |
| 2.9 | `POST /api/v1/users/generate-users` with `count: 101` | 400 error (exceeds max) | ☐ |
| 2.10 | `POST /api/v1/users/generate-users` with `count: "abc"` | 400 error (invalid count) | ☐ |
| 2.11 | Verify `defaultPassword` is returned in response | `DummyUser123!` in response | ☐ |

---

## 3. Submission Generation

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 3.1 | Generate 5 users first, then `POST generate-submissions` with `count: 3` | 200, `created: 3` | ☐ |
| 3.2 | Verify submissions appear in journal submissions list | Submissions visible | ☐ |
| 3.3 | Verify submissions have academic-style titles | Titles match pattern "X on Y" | ☐ |
| 3.4 | Verify submissions have abstracts | Abstracts contain Lorem ipsum | ☐ |
| 3.5 | Verify submissions have keywords | 3-5 keywords present | ☐ |
| 3.6 | Verify submissions have `manuscript.txt` uploaded | File attached | ☐ |
| 3.7 | Verify submissions are assigned to generated authors | Author metadata correct | ☐ |
| 3.8 | Verify workflow has progressed through stages | Decision records present | ☐ |
| 3.9 | `POST generate-submissions` without generating users first | 400 error "No author users found" | ☐ |
| 3.10 | `POST generate-submissions` with `count: 201` | 400 error (exceeds max) | ☐ |
| 3.11 | Generate submissions on journal with no sections | 400 error "No sections found" | ☐ |

---

## 4. Issue Generation

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 4.1 | After generating submissions, `POST generate-issue` | 200, `issueId` and `submissionsPublished` returned | ☐ |
| 4.2 | Verify issue appears in journal issues list | Issue visible | ☐ |
| 4.3 | Verify issue title matches `Vol. X No. Y (YEAR)` | Title format correct | ☐ |
| 4.4 | Verify issue is set as current issue | Current issue flag set | ☐ |
| 4.5 | Verify submissions are published in the issue | Articles listed in issue | ☐ |
| 4.6 | Verify issue description says "Dummy issue generated for testing purposes" | Description present | ☐ |
| 4.7 | `POST generate-issue` without generating submissions first | 400 error "No submissions found" | ☐ |
| 4.8 | Verify issue has open access status | Access status correct | ☐ |

---

## 5. Cleanup

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 5.1 | `DELETE /api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA` | 200, deletion statistics returned | ☐ |
| 5.2 | Verify all dummy users are deleted | Users removed from Users list | ☐ |
| 5.3 | Verify all dummy submissions are deleted | Submissions removed | ☐ |
| 5.4 | Verify dummy issue is deleted | Issue removed | ☐ |
| 5.5 | Verify tracking settings are cleared | No residual tracking data | ☐ |
| 5.6 | `DELETE /api/v1/users/cleanup` without `confirm` param | 400 error "Confirmation required" | ☐ |
| 5.7 | `DELETE /api/v1/users/cleanup?confirm=WRONG_STRING` | 400 error "Confirmation required" | ☐ |
| 5.8 | Verify cleanup only deletes tracked data (create a real user first, then cleanup) | Real user preserved | ☐ |
| 5.9 | Verify deletion order: issues → submissions → users | Check OJS logs for order | ☐ |

---

## 6. Rate Limiting

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 6.1 | Generate users, then immediately try again | 429 error with cooldown message | ☐ |
| 6.2 | Wait 30 seconds, then generate again | 200 success | ☐ |
| 6.3 | Verify cleanup is NOT rate-limited | Cleanup succeeds immediately after generation | ☐ |
| 6.4 | Verify rate limit message includes remaining seconds | Message shows "Please wait X seconds" | ☐ |
| 6.5 | Verify rate limit is per-journal (generate in Journal A, then immediately in Journal B) | Journal B request succeeds (not blocked by Journal A limit) | ☐ |

---

## 7. Authorization

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 7.1 | Call endpoint without API token | 401 unauthorized | ☐ |
| 7.2 | Call endpoint with Author role token | 403 forbidden | ☐ |
| 7.3 | Call endpoint with Manager role token | 200 success | ☐ |
| 7.4 | Call endpoint with Site Admin token | 200 success | ☐ |
| 7.5 | Call endpoint from wrong journal context | 400 error "No context available" | ☐ |

---

## 8. Context/Journal Scoping

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 8.1 | Generate data in Journal A, then check Journal B | No cross-journal data contamination | ☐ |
| 8.2 | Cleanup in Journal A does not affect Journal B | Journal B data preserved | ☐ |
| 8.3 | Tracking data is per-journal | Each journal has independent tracking | ☐ |

---

## 9. Error Handling & Edge Cases

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 9.1 | Call generate-submissions on journal with sections but no author user groups | 400 error or graceful fallback | ☐ |
| 9.2 | Call generate-issue after only generating users | 400 error "No submissions found" | ☐ |
| 9.3 | Verify API responses have consistent JSON structure | `{success, ...}` in all responses | ☐ |
| 9.4 | Verify error responses include descriptive messages | Human-readable error messages | ☐ |
| 9.5 | Check PHP error logs after all operations | No unexpected errors | ☐ |

---

## 10. Production Warning

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 10.1 | Check PHP error log after any generation request | Audit log entry present | ☐ |
| 10.2 | If `APP_ENV=production`, verify warning in logs | "WARNING - Data generation requested in production" | ☐ |

---

## 11. Data Integrity

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 11.1 | Generate 10 users, verify all 10 have unique usernames | No duplicates | ☐ |
| 11.2 | Generate 10 users, verify all 10 have unique emails | No duplicates | ☐ |
| 11.3 | Verify all generated users have password `DummyUser123!` | Password matches | ☐ |
| 11.4 | Generate 5 submissions, verify each has a different title | Titles not identical | ☐ |
| 11.5 | Verify submission abstracts are non-empty | Abstracts present | ☐ |
| 11.6 | Verify issue volume/number are integers | Valid metadata | ☐ |

---

## 12. Multi-Journal Installation

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 12.1 | Install on multi-journal OJS, generate data in Journal A | Data created in Journal A only | ☐ |
| 12.2 | Generate data in Journal B | Data created in Journal B only | ☐ |
| 12.3 | Cleanup Journal A | Journal A data removed, Journal B intact | ☐ |

---

## 13. Uninstallation

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 13.1 | Run cleanup before disabling plugin | All dummy data removed | ☐ |
| 13.2 | Disable plugin via admin panel | Plugin disabled without errors | ☐ |
| 13.3 | Remove plugin directory | Directory removed cleanly | ☐ |
| 13.4 | Clear cache after removal | No errors, plugin gone from list | ☐ |
| 13.5 | Verify no orphaned database records remain | No dummy data remains | ☐ |

---

## 14. Visual / UI Checks

| # | Test | Expected Result | Status |
|---|------|----------------|--------|
| 14.1 | Generated issues show correct title in issue management | "Vol. X No. Y (YEAR)" displayed | ☐ |
| 14.2 | Generated submissions show correct titles in submission list | Academic-style titles visible | ☐ |
| 14.3 | Generated users appear in user management with correct roles | Author role badge shown | ☐ |
| 14.4 | Published issue articles are accessible (open access) | Articles viewable | ☐ |

---

## Notes

- **Test order matters:** Follow the typical workflow: Users → Submissions → Issue → Cleanup
- **Always cleanup after testing** on shared/staging environments
- **Check PHP error logs** (`logs/error.log`) after each major test section
- **Multi-journal testing** requires a journal with at least one section defined
- **Rate limiting** means you must wait 30 seconds between generation requests

---

## Quick Smoke Test (5 minutes)

For a fast verification, run these in order:

```bash
# 1. Generate 2 users
curl -X POST -H "Authorization: Bearer TOKEN" -H "Content-Type: application/json" \
  http://localhost/my-journal/api/v1/users/generate-users -d '{"count": 2}'

# 2. Generate 1 submission (after 30s cooldown)
curl -X POST -H "Authorization: Bearer TOKEN" -H "Content-Type: application/json" \
  http://localhost/my-journal/api/v1/users/generate-submissions -d '{"count": 1}'

# 3. Generate issue (after 30s cooldown)
curl -X POST -H "Authorization: Bearer TOKEN" -H "Content-Type: application/json" \
  http://localhost/my-journal/api/v1/users/generate-issue -d '{}'

# 4. Cleanup
curl -X DELETE -H "Authorization: Bearer TOKEN" \
  "http://localhost/my-journal/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA"
```

**Note:** To get your API token, go to OJS → Profile → API Keys → Add API Key.

**Expected:** All return 200 with `success: true`.
