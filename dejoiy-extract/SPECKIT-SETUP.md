# DEJOIY Spec Kit — Server Installation

## Spec Kit Status: ✅ Installed

| Component | Value |
|-----------|-------|
| **Spec Kit Version** | 1.0.1 (stable) |
| **Install Method** | `uv tool install specify-cli` |
| **Install Location** | `/root/.local/bin/specify` (uv-managed) |
| **Python** | 3.12.3 |
| **uv** | 0.12.7 |
| **Git** | 2.43.0 |
| **Coding Agent Integration** | Copilot |
| **DEJOIY Project Path** | `/root/dejoiy-extract/` |
| **Spec Kit Project Path** | `/root/dejoiy-extract/.specify/` |
| **Initialization** | ✅ Success |
| **.specify/** | ✅ Present |
| **Production Changes** | None |

## Available Commands

| Command | Purpose |
|---------|---------|
| `/speckit-constitution` | Establish project principles |
| `/speckit-specify` | Create baseline specification |
| `/speckit-clarify` | Ask structured questions to de-risk ambiguity |
| `/speckit-checklist` | Generate quality checklists |
| `/speckit-plan` | Create implementation plan |
| `/speckit-tasks` | Generate actionable tasks |
| `/speckit-analyze` | Cross-artifact consistency report |
| `/speckit-implement` | Execute implementation |
| `/speckit-converge` | Assess codebase, identify remaining work |
| `/speckit-taskstoissues` | Convert tasks to issues |

## Development Workflow

```
SPECIFY → CLARIFY → CHECKLIST → PLAN → TASKS → ANALYZE → IMPLEMENT → CONVERGE
```

## DEJOIY Constitution

18 engineering principles established in `.specify/memory/constitution.md`:

1. Production Safety
2. Human-Quality Engineering
3. Mobile-First Quality
4. Real Backend Integration
5. Accessibility
6. Performance
7. Visual Consistency
8. Security
9. Maintainability
10. Ecosystem Coherence
11. Backward Compatibility
12. No Fabricated Functionality
13. Testability
14. Observability
15. Reversible Changes
16. Clear Acceptance Criteria
17. Code Review Before Production
18. No Destructive Changes Without Justification

## Verification Commands

```bash
# Load PATH
export PATH="$HOME/.local/bin:$PATH"

# Check version
specify version

# Check installation
specify check

# Check for updates
specify self check

# List installed tools
uv tool list
```

## Upgrade Procedure

```bash
export PATH="$HOME/.local/bin:$PATH"
uv tool install specify-cli --force --from "git+https://github.com/github/spec-kit.git@vX.Y.Z"
```

Replace `vX.Y.Z` with the target stable release tag.

## What Was NOT Changed

- ❌ WordPress frontend — untouched
- ❌ WooCommerce — untouched
- ❌ Production configuration — untouched
- ❌ Public assets — untouched
- ❌ Database — untouched
- ❌ Website UI — untouched

## Git Repositories

| Repository | Path | Remote |
|------------|------|--------|
| DEJOIY Extract (main) | `/root/dejoiy-extract/` | `origin` → GitHub |
| DEJOIY Marketplace (local) | `/root/dejoiy-marketplace/` | None |
| ThreeUI Community | `/opt/dejoiy/threeui-community/` | `origin` → GitHub |
