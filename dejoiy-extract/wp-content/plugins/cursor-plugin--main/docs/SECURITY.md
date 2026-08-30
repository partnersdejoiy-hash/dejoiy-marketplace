# Security Guide

## Authentication

### API Keys

- Format: `dacb_` + 48 random characters
- Stored as `wp_hash_password()` hashes; only prefix stored for lookup
- Transmit via `X-Dejoiy-API-Key` header or `Authorization: Bearer dacb_...`
- Revoke instantly from **Connected Agents**

### JWT

- HS256 signed tokens with site-specific secret (`dejoiy_acb_jwt_secret`)
- Default expiry: 3600 seconds (configurable)
- Obtain via `POST /auth/token` with valid API key
- Use for session-based agent workflows

## Authorization

Permissions use dot notation: `files.read`, `plugins.delete`, `database.alter`.

Predefined sets:

| Set | Scope |
|-----|-------|
| `read_only` | Read files, structure, SELECT queries |
| `developer` | + write files, themes, WordPress content |
| `admin` / `*` | Full access |

## IP Allowlist

- **Global**: Enforced when enabled in Security Settings
- **Per-agent**: JSON array on agent record
- Supports exact IPs and CIDR notation (e.g. `10.0.0.0/8`)

## Approval Queue

When enabled, these actions return HTTP 202 with `approval_id`:

- `files.delete`
- `plugins.delete` / `plugins.deactivate`
- `database.write` / `database.alter`
- `themes.deploy`
- `deployment.deploy`
- `backup.restore`

Admins approve in **Activity Feed**. Re-execute with `approval_id` parameter after approval.

## File System Sandboxing

- Path traversal blocked (`..`, null bytes)
- Operations restricted to configured zones
- 5MB max read size per file
- Writes create version backups automatically

## Database Safety

- SELECT / SHOW / DESCRIBE allowed by default
- INSERT / UPDATE / DELETE require `database.write`
- ALTER / CREATE / DROP require `database.alter`
- Blocked patterns: `INTO OUTFILE`, `LOAD_FILE`, `GRANT`, etc.
- Sensitive options (auth keys, JWT secret) never exposed

## Audit Logging

Every authenticated request logs:

- Agent name and ID
- Timestamp, action, HTTP status
- Request path and payload (sanitized)
- Approval status
- Client IP address

## Production Checklist

- [ ] Use HTTPS only
- [ ] Start agents with `read_only` permissions
- [ ] Enable approval queue
- [ ] Configure IP allowlist
- [ ] Rotate API keys periodically
- [ ] Review audit logs weekly
- [ ] Disable Git deploy on shared hosting unless required
- [ ] Keep backup restores approval-gated

## Reporting Issues

Report security vulnerabilities to DEJOIY through your designated security channel before public disclosure.
