# Database Schema

All tables use the WordPress table prefix plus `dejoiy_acb_`.

## `{prefix}dejoiy_acb_agents`

Connected AI agents and API credentials.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(191) | Agent display name |
| api_key_hash | varchar(255) | Hashed API key |
| api_key_prefix | varchar(16) | Key lookup prefix |
| jwt_secret | varchar(255) | Optional per-agent secret |
| permissions | longtext | JSON permission array |
| ip_allowlist | longtext | JSON IP list |
| status | varchar(20) | active / revoked |
| last_seen | datetime | Last API access |
| created_at | datetime | Created timestamp |
| updated_at | datetime | Updated timestamp |

## `{prefix}dejoiy_acb_audit_logs`

Immutable audit trail.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| agent_id | bigint | FK to agents |
| agent_name | varchar(191) | Denormalized name |
| action | varchar(100) | Action slug |
| resource_type | varchar(50) | Optional resource type |
| resource_id | varchar(191) | Optional resource ID |
| request_method | varchar(10) | HTTP method |
| request_path | varchar(500) | REST path |
| request_payload | longtext | JSON request body |
| response_status | int | HTTP status |
| result | longtext | JSON result summary |
| ip_address | varchar(45) | Client IP |
| approval_status | varchar(20) | not_required / pending / approved |
| approval_id | bigint | FK to approvals |
| created_at | datetime | Event time |

## `{prefix}dejoiy_acb_approvals`

Action approval queue.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| agent_id | bigint | Requesting agent |
| action | varchar(100) | Action slug |
| payload | longtext | JSON execution payload |
| status | varchar(20) | pending / approved / rejected / expired |
| requested_at | datetime | Queue time |
| resolved_at | datetime | Resolution time |
| resolved_by | bigint | WordPress user ID |
| resolution_note | text | Admin note |
| expires_at | datetime | Auto-expire time |

## `{prefix}dejoiy_acb_deployments`

Deployment history.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| agent_id | bigint | Deploying agent |
| version | varchar(50) | Version label |
| commit_hash | varchar(64) | Git commit |
| branch | varchar(191) | Git branch |
| status | varchar(20) | pending / running / completed / failed |
| log | longtext | Deployment log |
| rollback_of | bigint | Parent deployment for rollbacks |
| created_at | datetime | Start time |
| completed_at | datetime | End time |

## `{prefix}dejoiy_acb_backups`

Backup registry.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| type | varchar(30) | full / database / theme / plugin |
| file_path | varchar(500) | Archive path |
| file_size | bigint | Bytes |
| checksum | varchar(64) | MD5 checksum |
| status | varchar(20) | completed / failed |
| metadata | longtext | JSON options |
| created_by | bigint | WP user ID |
| agent_id | bigint | Agent ID |
| created_at | datetime | Created time |

## `{prefix}dejoiy_acb_file_versions`

File version history for rollback.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| file_path | varchar(500) | Absolute path |
| version | int | Version number |
| content_hash | varchar(64) | MD5 of content |
| backup_path | varchar(500) | Backup file path |
| agent_id | bigint | Modifying agent |
| created_at | datetime | Version time |
