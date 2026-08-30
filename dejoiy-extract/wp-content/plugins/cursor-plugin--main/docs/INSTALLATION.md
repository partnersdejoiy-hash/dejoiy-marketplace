# Installation Guide

## Requirements

- WordPress 6.0+
- PHP 7.4+ with ZipArchive extension
- HTTPS strongly recommended
- Git (optional, for deployment engine)

## Install

### Upload via WordPress admin

1. From the repository root, run `./build-zip.sh` to create `dejoiy-ai-control-bridge.zip`.
2. In WordPress go to **Plugins → Add New → Upload Plugin** and choose that zip file.
3. Click **Install Now**, then **Activate**.

If you see **"No valid plugins were found"**, the zip structure is wrong. The file `dejoiy-ai-control-bridge.php` must sit at the **root** of the zip (inside one folder named `dejoiy-ai-control-bridge`), not inside a nested repo folder. Use `build-zip.sh` or download the latest GitHub zip from `main` where the plugin files are at the repository root.

### Manual install

1. Copy all plugin files to `wp-content/plugins/dejoiy-ai-control-bridge/`.
2. Activate **DEJOIY AI Control Bridge** in WordPress admin → Plugins.
3. On activation, custom tables and default options are created automatically.

## Create an AI Agent

1. Go to **AI Control Center** → **Connected Agents**.
2. Enter an agent name (e.g. `Cursor Production`).
3. Select a permission set:
   - **Read Only** — structure, files read, SELECT queries
   - **Developer** — file write, theme edit, cache clear
   - **Admin** — full access (`*` permission)
4. Click **Create Agent & API Key**.
5. Copy the `dacb_*` key immediately — it is shown only once.

## Configure Security

1. **AI Control Center** → **Security Settings**
2. Enable **Require Approval** for production sites.
3. Add IP addresses to the global allowlist if agents connect from fixed IPs.
4. Enable Git integration and set repository path if using deployments.

## JWT Authentication (Optional)

```bash
# Issue JWT
curl -X POST "https://yoursite.com/wp-json/dejoiy-ai/v1/auth/token" \
  -H "X-Dejoiy-API-Key: dacb_YOUR_KEY"

# Use JWT for subsequent requests
curl "https://yoursite.com/wp-json/dejoiy-ai/v1/structure" \
  -H "Authorization: Bearer eyJ..."
```

## Cursor MCP Setup

1. Copy `cursor-mcp-config.json` to your project root or `~/.cursor/`.
2. Replace placeholders:
   - `YOUR_SITE_URL` → `https://yoursite.com`
   - `YOUR_API_KEY` → your `dacb_*` key
3. Restart Cursor to load MCP configuration.
4. Verify discovery: `GET /wp-json/dejoiy-ai/v1/mcp/manifest`

## Verify Installation

```bash
curl "https://yoursite.com/wp-json/dejoiy-ai/v1/mcp/manifest"
curl "https://yoursite.com/wp-json/dejoiy-ai/v1/structure" \
  -H "X-Dejoiy-API-Key: dacb_YOUR_KEY"
```

## Uninstall

Deactivating preserves data. Deleting the plugin runs `uninstall.php` which drops all `dejoiy_acb_*` tables and options.
