## API Overview
This plugin extends the existing Open Journal Systems API by adding custom routes under the `/api/v1/users` endpoint.
These routes are designed for development and testing purposes. They allow generation of dummy users, submissions, issues, and provide cleanup utilities.
**Important:**
All endpoints documented here are **plugin-defined extensions**. They are not part of the core OJS API.
---
## Endpoint Structure
All routes are registered by extending the existing OJS users API handler.
```
/index.php/{journal_path}/api/v1/users/{plugin-route}
```
Examples:
```
POST   /api/v1/users/generate-users
POST   /api/v1/users/generate-submissions
POST   /api/v1/users/generate-issue
DELETE /api/v1/users/cleanup
```
---
## Authentication & Authorization
Authentication and authorization are inherited from and handled by the OJS API layer. The plugin itself does not issue or validate tokens.
* Requests must satisfy standard OJS API authentication requirements
* Typically includes API key usage via the `Authorization` header
* Exact configuration depends on the host OJS installation
Access to all plugin routes is explicitly restricted to:
* Site Administrator (`ROLE_ID_SITE_ADMIN`)
* Journal Manager (`ROLE_ID_MANAGER`)
---
## Endpoints
### 1. Generate Users
```
POST /api/v1/users/generate-users
```
Creates dummy users and assigns them the Author role within the current journal context.
**Request Body**
```json
{
  "count": 20
}
```
Constraints:
* Default: 10
* Range: 1–100
**Response**
```json
{
  "success": true,
  "created": 20,
  "userIds": [...],
  "defaultPassword": "DummyUser123!"
}
```
---
### 2. Generate Submissions
```
POST /api/v1/users/generate-submissions
```
Creates dummy submissions using existing generated users as authors.
Submissions are programmatically populated with:
* title
* abstract
* keywords
* manuscript file
The plugin applies editorial decisions internally to simulate progression through workflow stages.
**Request Body**
```json
{
  "count": 50
}
```
Constraints:
* Default: 20
* Range: 1–200
**Prerequisite**
* Dummy users must already exist
**Response**
```json
{
  "success": true,
  "created": 50,
  "submissionIds": [...]
}
```
---
### 3. Generate Issue
```
POST /api/v1/users/generate-issue
```
Creates an issue and assigns previously generated submissions to it.
The plugin:
* creates a new issue
* schedules submissions for publication
* applies publication status changes internally
**Prerequisite**
* Submissions must exist
**Response**
```json
{
  "success": true,
  "issueId": 5,
  "submissionsPublished": 50
}
```
---
### 4. Cleanup
```
DELETE /api/v1/users/cleanup
```
Deletes all dummy data created by this plugin within the current journal context.
**Query Parameter**
```
confirm=DELETE_ALL_DUMMY_DATA
```
**Response**
```json
{
  "success": true,
  "deleted": {
    "users": 20,
    "submissions": 50,
    "issues": 1
  }
}
```
**Note:**
Deletion is performed in dependency order:
1. Issues
2. Submissions
3. Users
---
## Workflow Behavior
This plugin does not execute the full editorial workflow as performed through the OJS interface.
Instead, it programmatically applies editorial decisions using OJS internal services to simulate workflow progression, including:
* moving submissions to review
* accepting submissions
* sending submissions to production
This approach is intended for testing and development scenarios. Note that this does not claim full parity with the normal editorial UI flow.
---
## Data Tracking
All generated entities are tracked using journal context settings.
This allows:
* controlled cleanup
* isolation of plugin-generated data
* safe repeated testing
---
## Limitations
* Not intended for production editorial use
* Workflow actions are simulated, not user-driven
* Publication and workflow states depend on internal API behavior of OJS
* Authentication depends on host OJS configuration
---
## Recommended Usage
Typical sequence:
```
generate-users → generate-submissions → generate-issue → cleanup
```
Start with small counts before large-scale generation.
