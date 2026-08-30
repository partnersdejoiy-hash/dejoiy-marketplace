# REST API Documentation

**Base URL:** `{site}/wp-json/dejoiy-ai/v1`

**Authentication:** `X-Dejoiy-API-Key: dacb_...` or `Authorization: Bearer <jwt|api_key>`

## Structure

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/structure` | structure.read | Full WP ecosystem overview |
| GET | `/logs/read?path=&lines=` | structure.read | Read log file tail |

## Files

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/files/tree?zone=&path=&depth=` | files.read | Directory tree |
| GET | `/files/read?zone=&path=` | files.read | Read file |
| POST | `/files/write` | files.write | Write file `{zone, path, content}` |
| POST | `/files/create` | files.create | Create file/dir |
| DELETE | `/files/delete?zone=&path=` | files.delete | Delete (approval) |
| POST | `/files/rename` | files.rename | Rename `{zone, from, to}` |
| GET | `/files/search?zone=&pattern=` | files.read | Glob search |

**Zones:** `wp-content`, `themes`, `plugins`, `uploads`, `mu-plugins`

## Database

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/database/tables` | database.read | List tables |
| GET | `/database/schema?table=` | database.read | Column/index schema |
| GET/POST | `/database/query` | database.read+ | Execute SQL `{sql}` |

## WordPress

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| POST | `/wordpress/posts` | wordpress.write | Create post/page |
| POST | `/wordpress/products` | wordpress.write | Create WooCommerce product |
| POST | `/wordpress/menus` | wordpress.write | Create menu `{name}` |
| POST | `/wordpress/users` | wordpress.write | Create user |
| POST | `/wordpress/post-types` | wordpress.write | Register CPT |
| GET | `/wordpress/options?key=` | wordpress.read | Read options |
| POST | `/wordpress/options` | wordpress.write | Update option |
| GET | `/wordpress/woocommerce` | wordpress.read | WooCommerce config |
| GET | `/wordpress/wcfm` | wordpress.read | WCFM config |
| POST | `/cache/clear` | cache.clear | Clear caches |
| POST | `/migrations/run` | migrations.run | Run SQL migrations |

## Plugins

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/plugins` | plugins.read | List installed |
| POST | `/plugins/install` | plugins.install | Install `{source}` |
| POST | `/plugins/activate` | plugins.activate | Activate `{file}` |
| POST | `/plugins/deactivate` | plugins.deactivate | Deactivate (approval) |
| DELETE | `/plugins/delete` | plugins.delete | Delete (approval) |
| POST | `/plugins/update` | plugins.update | Update plugin |
| POST | `/plugins/create` | plugins.install | Scaffold `{slug, name}` |

## Themes

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/themes` | themes.read | List themes |
| GET | `/themes/templates?theme=` | themes.read | List templates |
| GET | `/themes/template/read` | themes.read | Read template |
| POST | `/themes/template/write` | themes.write | Write template (approval) |
| POST | `/themes/child` | themes.create | Create child theme |

## Deployment

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/deployment/history` | deployment.read | Deployment log |
| GET | `/deployment/git-status` | deployment.read | Git state |
| POST | `/deployment/deploy` | deployment.write | Deploy (approval) |
| POST | `/deployment/rollback` | deployment.write | Rollback `{deployment_id}` |

## Backup

| Method | Endpoint | Permission | Description |
|--------|----------|------------|-------------|
| GET | `/backup` | backup.read | List backups |
| POST | `/backup/create` | backup.create | Create `{type}` |
| POST | `/backup/restore` | backup.restore | Restore (approval) |

## MCP & Discovery

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/mcp/manifest` | Public | MCP manifest |
| GET | `/mcp/tools` | Public | Tool definitions |
| POST | `/mcp/execute` | Required | Run tool `{tool, arguments}` |
| GET | `/openapi` | Public | OpenAPI YAML |
| POST | `/auth/token` | API Key | Issue JWT |

## Response Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 202 | Approval required — check `approval_id` |
| 401 | Missing/invalid authentication |
| 403 | Permission denied or IP blocked |
| 404 | Resource not found |
| 413 | File too large |

Full OpenAPI spec: `openapi/openapi.yaml`
