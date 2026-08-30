# DEJOIY AI Control Bridge

Enterprise-grade WordPress plugin that provides a secure AI operating layer for the DEJOIY ecosystem.

## Install in WordPress

### Option A — Build the correct zip (recommended)

From this repository root:

```bash
chmod +x build-zip.sh
./build-zip.sh
```

Upload **`dejoiy-ai-control-bridge.zip`** via **Plugins → Add New → Upload Plugin**.

### Option B — Download from GitHub

Use **Code → Download ZIP** on the `main` branch. The plugin main file (`dejoiy-ai-control-bridge.php`) is at the **root** of the archive, so WordPress will detect it.

> **Important:** Do not zip the whole repo again yourself if it adds an extra folder layer. WordPress needs `dejoiy-ai-control-bridge.php` (with the Plugin Name header) at the **top level** of the zip you upload—not inside a nested subfolder with only `README.md` at the top.

### Option C — Manual folder install

Copy all files to:

`wp-content/plugins/dejoiy-ai-control-bridge/`

Then activate **DEJOIY AI Control Bridge** in **Plugins**.

## After activation

1. Open **AI Control Center → Connected Agents**
2. Create an agent and save the `dacb_*` API key (shown once)
3. See [docs/INSTALLATION.md](docs/INSTALLATION.md) for Cursor/MCP setup

## Documentation

| Document | Path |
|----------|------|
| Architecture | [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) |
| Installation | [docs/INSTALLATION.md](docs/INSTALLATION.md) |
| Security | [docs/SECURITY.md](docs/SECURITY.md) |
| REST API | [docs/REST-API.md](docs/REST-API.md) |
| Database Schema | [docs/DATABASE-SCHEMA.md](docs/DATABASE-SCHEMA.md) |
| OpenAPI | [openapi/openapi.yaml](openapi/openapi.yaml) |

## License

GPL-2.0-or-later
