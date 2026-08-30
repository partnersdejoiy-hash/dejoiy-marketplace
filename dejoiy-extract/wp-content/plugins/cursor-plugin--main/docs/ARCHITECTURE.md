# DEJOIY AI Control Bridge — Architecture

## Overview

DEJOIY AI Control Bridge is an enterprise AI operating layer for WordPress. It exposes a secure REST API namespace (`dejoiy-ai/v1`) and MCP-compatible endpoints so external AI systems can manage the WordPress ecosystem with authentication, authorization, audit logging, and human approval for critical actions.

## Layered Architecture

```
┌─────────────────────────────────────────────────────────────┐
│  AI Clients (Cursor, Claude, OpenAI Agents, MCP)            │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTPS + API Key / JWT
┌───────────────────────────▼─────────────────────────────────┐
│  REST API Layer (Controllers)                                │
│  Files · Database · WordPress · Plugins · Themes · Deploy    │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│  Security Layer                                              │
│  Auth · Permissions · IP Allowlist · Approval Queue · Audit  │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│  Service Layer                                               │
│  FileSystem · Database · PluginManager · Backup · Deploy     │
└───────────────────────────┬─────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────┐
│  WordPress Core · WooCommerce · WCFM · Filesystem · MySQL     │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

| Component | Namespace | Responsibility |
|-----------|-----------|----------------|
| `Plugin` | Root | Bootstrap, hooks, container |
| `Container` | `Container\` | Dependency injection |
| `RestAuthMiddleware` | `Auth\` | API key + JWT validation |
| `PermissionChecker` | `Auth\` | Role-based capabilities |
| `AuditLogger` | `Security\` | Immutable action trail |
| `ApprovalQueue` | `Security\` | Critical action gating |
| `FileSystemService` | `Services\` | Sandboxed file I/O |
| `DatabaseService` | `Services\` | Query tier enforcement |
| `McpController` | `Api\` | Tool dispatch + discovery |

## Request Flow

1. Request hits `rest_api_init` registered route.
2. `RestAuthMiddleware` validates API key or JWT.
3. `IpAllowlist` checks client IP (global + per-agent).
4. `PermissionChecker` validates required capability.
5. `ActionClassifier` determines if approval is required.
6. Service executes business logic.
7. `AuditLogger` records agent, action, result, approval status.

## Admin UI

**AI Control Center** (`manage_options` only):

- Dashboard, Connected Agents, Activity Feed
- File Manager, Database Manager (API reference)
- Deployment Center, Backup Center
- Audit Logs, Security Settings

## Rollback System

1. **File versions**: Each write/delete backs up to `wp-content/dejoiy-acb-versions/` with DB tracking in `dejoiy_acb_file_versions`.
2. **Deployments**: Git commit hash stored per deployment; rollback checks out prior commit.
3. **Backups**: ZIP archives with checksum verification before restore.

## Extension Points

- Add migrations in `migrations/*.sql`
- Register custom MCP tools via `mcp/tools.json` or `ToolRegistry`
- Filter hooks can be added in future versions for `dejoiy_acb_permissions`, `dejoiy_acb_critical_actions`
