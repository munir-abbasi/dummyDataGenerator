# API Documentation

**Version:** 1.0.0  
**Last Updated:** 2026-03-26

Complete API reference for the Dummy Data Generator plugin.

---

## Base URL

All API endpoints are relative to:
```
{base_url}/{journal_path}/api/v1/users/
```

**Example:**
```
https://ojs.example.com/my-journal/api/v1/users/
```

---

## Authentication

All endpoints require authentication using OJS API tokens.

### Obtaining API Token

1. Log in as Journal Manager or Site Admin
2. Go to: **Profile → API Tokens**
3. Click **Create New Token**
4. Copy the token (shown only once)

### Using API Token

Include token in Authorization header:
```bash
-H "Authorization: Bearer YOUR_API_TOKEN"
```

### Required Roles

All endpoints require one of these roles:
- `ROLE_ID_SITE_ADMIN` (Site Administrator)
- `ROLE_ID_MANAGER` (Journal Manager)

---

## Endpoints

### POST /generate-users

Generate dummy users with author role assignment.

**URL:**
```
POST {base_url}/{journal_path}/api/v1/users/generate-users
```

**Request Body:**
```json
{
  "count": 20
}
```

**Parameters:**
| Parameter | Type | Required | Default | Min | Max |
|-----------|------|----------|---------|-----|-----|
| count | integer | No | 10 | 1 | 100 |

**Example Request:**
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  https://ojs.example.com/journal/api/v1/users/generate-users \
  -d '{"count": 20}'
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "created": 20,
  "userIds": [101, 102, 103, 104, 105],
  "defaultPassword": "DummyUser123!",
  "message": "Successfully created 20 users with author role. All passwords set to: DummyUser123!"
}
```

**Error Responses:**

**400 Bad Request - Invalid Count:**
```json
{
  "success": false,
  "error": "Count must be a valid number between 1 and 100"
}
```

**400 Bad Request - No Context:**
```json
{
  "success": false,
  "error": "No context available. Please access this endpoint from a journal context."
}
```

**User Data Structure:**
```json
{
  "username": "dummy_user_0_a1b2c3",
  "email": "dummy.user.0@example.com",
  "givenName": "John",
  "familyName": "Smith",
  "password": "DummyUser123!", // hashed
  "role": "Author" // assigned to journal
}
```

---

### POST /generate-submissions

Generate complete dummy submissions through workflow.

**URL:**
```
POST {base_url}/{journal_path}/api/v1/users/generate-submissions
```

**Request Body:**
```json
{
  "count": 50
}
```

**Parameters:**
| Parameter | Type | Required | Default | Min | Max |
|-----------|------|----------|---------|-----|-----|
| count | integer | No | 20 | 1 | 200 |

**Prerequisites:**
- Users with author role must exist (generate users first)

**Example Request:**
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  https://ojs.example.com/journal/api/v1/users/generate-submissions \
  -d '{"count": 50}'
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "created": 50,
  "submissionIds": [201, 202, 203, 204, 205],
  "message": "Successfully created 50 submissions and progressed through workflow to production stage."
}
```

**Error Responses:**

**400 Bad Request - No Authors:**
```json
{
  "success": false,
  "error": "No author users found. Please generate users first."
}
```

**Submission Structure:**
- **Title:** Academic-style title (e.g., "A Study on Machine Learning Applications")
- **Abstract:** Lorem ipsum text (150-350 words)
- **Keywords:** 3-5 academic keywords
- **File:** Placeholder text manuscript
- **Workflow Stage:** Production (fully progressed)
- **Author:** Assigned from generated user pool (cyclic)

**Workflow Progression:**
```
Submission Created
    ↓
EXTERNAL_REVIEW (Decision::EXTERNAL_REVIEW)
    ↓
ACCEPT (Decision::ACCEPT)
    ↓
SEND_TO_PRODUCTION (Decision::SEND_TO_PRODUCTION)
```

---

### POST /generate-issue

Create and publish dummy issue with submissions.

**URL:**
```
POST {base_url}/{journal_path}/api/v1/users/generate-issue
```

**Request Body:**
```json
{}
```

**Parameters:** None

**Prerequisites:**
- Submissions must exist (generate submissions first)

**Example Request:**
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  https://ojs.example.com/journal/api/v1/users/generate-issue
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "issueId": 5,
  "submissionsPublished": 50,
  "message": "Issue created and published with all submissions."
}
```

**Error Responses:**

**400 Bad Request - No Submissions:**
```json
{
  "success": false,
  "error": "No submissions found. Please generate submissions first."
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "error": "Failed to create issue: [error message]"
}
```

**Issue Structure:**
```json
{
  "volume": 1,
  "number": 1,
  "year": 2024,
  "title": "Vol. 1 No. 1 (2024)",
  "description": "Dummy issue generated for testing purposes.",
  "datePublished": "2024-01-15",
  "accessStatus": "open_access",
  "current": true
}
```

---

### DELETE /cleanup

Delete all generated dummy data.

**URL:**
```
DELETE {base_url}/{journal_path}/api/v1/users/cleanup
```

**Query Parameters:**
```
?confirm=DELETE_ALL_DUMMY_DATA
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| confirm | string | Yes | Must be exactly `DELETE_ALL_DUMMY_DATA` |

**Example Request:**
```bash
curl -X DELETE \
  -H "Authorization: Bearer YOUR_TOKEN" \
  "https://ojs.example.com/journal/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "deleted": {
    "users": 20,
    "submissions": 50,
    "issues": 1
  },
  "message": "Successfully cleaned up all dummy data."
}
```

**Error Responses:**

**400 Bad Request - Confirmation Required:**
```json
{
  "success": false,
  "error": "Confirmation required. Please set 'confirm' parameter to 'DELETE_ALL_DUMMY_DATA'.",
  "requiredConfirm": "DELETE_ALL_DUMMY_DATA"
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "error": "Cleanup failed: [error message]"
}
```

**Deletion Order:**
1. Issues (reference submissions)
2. Submissions (reference publications)
3. Users (independent)
4. Tracking data (context settings)

---

## Error Handling

### Common Error Codes

| Code | Meaning | Cause |
|------|---------|-------|
| 200 | OK | Request successful |
| 400 | Bad Request | Invalid parameters, missing context |
| 401 | Unauthorized | Missing or invalid API token |
| 403 | Forbidden | Insufficient permissions |
| 500 | Internal Server Error | Server-side error |

### Error Response Format

All errors follow this structure:
```json
{
  "success": false,
  "error": "Human-readable error message"
}
```

---

## Rate Limiting

**Current Status:** Not implemented (planned for v1.1.0)

**Recommendation:** Wait 5 minutes between large generation requests to avoid database overload.

---

## Usage Examples

### Complete Workflow

```bash
# 1. Generate 20 users
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  http://ojs.example.com/journal/api/v1/users/generate-users \
  -d '{"count": 20}'

# 2. Generate 50 submissions
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  http://ojs.example.com/journal/api/v1/users/generate-submissions \
  -d '{"count": 50}'

# 3. Generate and publish issue
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  http://ojs.example.com/journal/api/v1/users/generate-issue

# 4. Cleanup (when done)
curl -X DELETE \
  -H "Authorization: Bearer TOKEN" \
  "http://ojs.example.com/journal/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA"
```

### Using JavaScript (Node.js)

```javascript
const axios = require('axios');

const API_BASE = 'https://ojs.example.com/journal/api/v1/users';
const TOKEN = 'your-api-token';

const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Authorization': `Bearer ${TOKEN}`,
    'Content-Type': 'application/json'
  }
});

// Generate users
async function generateUsers(count = 20) {
  const response = await api.post('/generate-users', { count });
  console.log(`Created ${response.data.created} users`);
  return response.data.userIds;
}

// Generate submissions
async function generateSubmissions(count = 50) {
  const response = await api.post('/generate-submissions', { count });
  console.log(`Created ${response.data.created} submissions`);
  return response.data.submissionIds;
}

// Generate issue
async function generateIssue() {
  const response = await api.post('/generate-issue');
  console.log(`Created issue ${response.data.issueId}`);
  return response.data.issueId;
}

// Cleanup
async function cleanup() {
  const response = await api.delete('/cleanup', {
    params: { confirm: 'DELETE_ALL_DUMMY_DATA' }
  });
  console.log('Cleanup complete:', response.data.deleted);
}

// Run complete workflow
(async () => {
  await generateUsers(20);
  await generateSubmissions(50);
  await generateIssue();
  // await cleanup(); // Uncomment to delete all data
})();
```

### Using Python

```python
import requests

API_BASE = 'https://ojs.example.com/journal/api/v1/users'
TOKEN = 'your-api-token'

headers = {
    'Authorization': f'Bearer {TOKEN}',
    'Content-Type': 'application/json'
}

def generate_users(count=20):
    response = requests.post(
        f'{API_BASE}/generate-users',
        headers=headers,
        json={'count': count}
    )
    data = response.json()
    print(f"Created {data['created']} users")
    return data['userIds']

def generate_submissions(count=50):
    response = requests.post(
        f'{API_BASE}/generate-submissions',
        headers=headers,
        json={'count': count}
    )
    data = response.json()
    print(f"Created {data['created']} submissions")
    return data['submissionIds']

def generate_issue():
    response = requests.post(
        f'{API_BASE}/generate-issue',
        headers=headers
    )
    data = response.json()
    print(f"Created issue {data['issueId']}")
    return data['issueId']

def cleanup():
    response = requests.delete(
        f'{API_BASE}/cleanup',
        headers=headers,
        params={'confirm': 'DELETE_ALL_DUMMY_DATA'}
    )
    data = response.json()
    print(f"Cleanup complete: {data['deleted']}")

# Run workflow
if __name__ == '__main__':
    generate_users(20)
    generate_submissions(50)
    generate_issue()
    # cleanup()  # Uncomment to delete all data
```

---

## Best Practices

### 1. Generate in Order
Always generate in this sequence:
```
Users → Submissions → Issue → Cleanup
```

### 2. Test with Small Counts First
```bash
# Test with minimal data
curl -X POST ... -d '{"count": 2}'

# Then generate full dataset
curl -X POST ... -d '{"count": 50}'
```

### 3. Backup Before Large Generations
```bash
mysqldump -u ojs_user -p ojs_database > backup_before_dummy_data.sql
```

### 4. Document Generated Data
Save user IDs and passwords for testing:
```bash
curl ... | jq '.userIds' > generated_user_ids.json
echo "Default Password: DummyUser123!" >> generated_user_ids.json
```

### 5. Cleanup After Testing
Always cleanup after completing tests to avoid database bloat.

---

## Troubleshooting

### API Returns 404 Not Found

**Cause:** Plugin not enabled or wrong URL

**Solution:**
1. Verify plugin is enabled in admin panel
2. Check URL includes journal path
3. Clear OJS cache

### API Returns 401 Unauthorized

**Cause:** Invalid or missing API token

**Solution:**
1. Generate new API token in profile
2. Include token in Authorization header
3. Verify token format: `Bearer YOUR_TOKEN`

### API Returns 403 Forbidden

**Cause:** Insufficient permissions

**Solution:**
1. Ensure user has Manager or Site Admin role
2. Check role assignment in user profile

### Generation Hangs or Times Out

**Cause:** Large dataset, slow database

**Solution:**
1. Reduce count parameter
2. Increase PHP max_execution_time
3. Check database performance

---

## Support

For API issues or questions:
- **GitHub Issues:** [Create an issue](https://github.com/munir-abbasi/dummyDataGenerator/issues)
- **Documentation:** [README.md](README.md)
- **Installation:** [INSTALLATION.md](INSTALLATION.md)
