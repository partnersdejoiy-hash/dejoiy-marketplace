=== DEJOIY AI Control Bridge ===
Contributors: dejoiy
Tags: ai, mcp, rest-api, automation, cursor
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enterprise AI operating layer for WordPress — secure APIs for Cursor, Claude, OpenAI Agents, and MCP clients.

== Description ==

DEJOIY AI Control Bridge allows authorized AI agents to securely manage your WordPress ecosystem through authenticated REST APIs and MCP-compatible endpoints.

* File system access (themes, plugins, uploads)
* Database introspection and controlled queries
* Plugin and theme management
* Deployment engine with Git integration
* Backup and restore
* Audit logging and approval queue
* JWT and API key authentication

== Installation ==

1. Run build-zip.sh and upload dejoiy-ai-control-bridge.zip via Plugins → Add New → Upload Plugin, OR copy all files to /wp-content/plugins/dejoiy-ai-control-bridge/
2. Activate through the Plugins screen
3. Go to **AI Control Center** → **Connected Agents** to create an API key
4. Configure security settings and connect your AI client

See `docs/INSTALLATION.md` for full setup including Cursor MCP configuration.

== Frequently Asked Questions ==

= Is this safe for production? =

Use read-only agent permissions by default, enable IP allowlists, and keep the approval queue enabled for destructive operations.

= How do I connect Cursor? =

Copy `cursor-mcp-config.json` to your project and replace `YOUR_SITE_URL` and `YOUR_API_KEY`.

== Changelog ==

= 1.0.0 =
* Initial release
