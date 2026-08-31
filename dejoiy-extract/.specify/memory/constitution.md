# DEJOIY Constitution

## Core Principles

### I. Production Safety
Every change is evaluated for impact on the live DEJOIY marketplace.
- Never modify production without explicit justification
- Prefer additive changes over destructive replacements
- Always have a rollback strategy
- Identify affected routes, hooks, database behavior, and WooCommerce flows before implementing

### II. Human-Quality Engineering
Code this like a senior human product engineer who personally owns DEJOIY.
- Inspect existing implementation before modifying
- Understand dependencies and callers
- Question assumptions
- Choose the simplest robust solution
- Review results critically before shipping

### III. Mobile-First Quality
Mobile is a first-class product, not a shrunk desktop.
- Design mobile independently from desktop
- Use intentional composition, not scaled-down layouts
- Minimum 44–48px touch targets
- Thumb-friendly controls
- App-like experience on phones
- Support 360px–1920px across all breakpoints

### IV. Real Backend Integration
Never fabricate functionality.
- No fake API responses
- No fabricated products, reviews, or delivery dates
- No hardcoded data when real backend data exists
- No fake payment methods or checkout flows
- Always connect to actual WooCommerce/WordPress data

### V. Accessibility
Every interaction must be accessible.
- Keyboard navigation on desktop/tablet
- Visible focus states
- Semantic HTML
- ARIA where required
- Sufficient color contrast (WCAG AA)
- Alt text on images
- Screen reader labels
- Reduced motion support

### VI. Performance
Performance is part of UX.
- Lazy image loading
- Responsive image sizes
- Skeleton loading states
- Minimal layout shifts
- Optimized API calls
- No unnecessary global scripts
- Component-level imports where possible
- Respect device capability

### VII. Visual Consistency
One DEJOIY design language across all surfaces.
- Consistent typography hierarchy
- 8px-based spacing system
- Consistent buttons, cards, inputs
- DEJOIY accent colors used intentionally
- Premium, not loud
- Clean neutral surfaces with accent moments

### VIII. Security
Protect the platform and its users.
- Sanitize all inputs
- Use WordPress nonces
- Escape all output
- Protect authentication flows
- Never expose secrets in frontend
- Validate API requests
- Follow WordPress security best practices

### IX. Maintainability
Write code that future developers can understand and modify.
- Clean, readable code
- No dead code
- No duplicated functions or CSS
- Clear file organization
- Meaningful naming
- Comments where intent is not obvious

### X. Ecosystem Coherence
DEJOIY is one connected ecosystem, not disconnected pages.
- Shop, Learn (Nexus), Create (Custom Studio), Hire, Renew, QuickMart — all belong to one product
- JOI connects the entire experience
- Shared header, navigation, typography, motion, trust language
- Cross-ecosystem discovery and recommendations

### XI. Backward Compatibility
Preserve what works.
- Do not break existing WooCommerce functionality
- Do not remove existing working features
- Do not override WordPress core templates unnecessarily
- Extend rather than replace
- Test existing flows after changes

### XII. No Fabricated Functionality
Never claim a feature is complete because a button exists.
- UI + interaction + backend/data + error handling + responsive = complete
- Never create fake search, checkout, reviews, or orders
- Never pretend personalized recommendations exist when they don't
- Transparent about what the backend actually supports

### XIII. Testability
Every implementation should be verifiable.
- Check console for errors
- Test responsive states
- Test edge cases
- Test existing flows for regressions
- Verify error states
- Test with JavaScript disabled where relevant

### XIV. Observability
Make issues discoverable.
- Meaningful error messages
- Console logging for development
- Graceful degradation
- Never show raw PHP errors to users
- Designed error states (404, 500, empty, loading)

### XV. Reversible Changes
Every change should be undoable.
- Prefer git branches for significant work
- Do not modify production directly when staging is available
- Keep backup of critical files before modification
- Document what was changed and why

### XVI. Clear Acceptance Criteria
Every feature has defined completion criteria.
- What problem does it solve?
- Who is it for?
- What are the acceptance criteria?
- What could break?
- What existing functionality must be preserved?

### XVII. Code Review Before Production
Significant changes require review.
- Self-review before committing
- Check for sensitive data
- Check for console errors
- Check for responsive issues
- Check for accessibility issues
- Check for performance impact

### XVIII. No Destructive Changes Without Justification
Never delete or replace without clear reasoning.
- Understand what depends on the code being changed
- Prefer deprecation over deletion
- Consolidate duplicates rather than picking one arbitrarily
- Document removal decisions

## Technology Stack

- **Platform**: WordPress + WooCommerce
- **Theme**: DEJOIY Child Theme
- **Frontend**: PHP templates, custom CSS, custom JavaScript
- **Integration**: Elementor (where used)
- **Marketplace**: WooCommerce marketplace modules
- **Motion**: DEJOIY Motion Adapters (powered by ThreeUI Community)
- **Development**: GitHub Spec Kit for spec-driven workflow

## Development Roles

For meaningful features, reason through these responsibilities:

1. **Product Owner** — Defines business objective, user problem, priority, acceptance criteria
2. **UX/Product Designer** — Defines user flow, interaction design, responsive behavior, visual system
3. **Architect** — Defines technical approach, dependencies, integration points, risk
4. **Frontend Engineer** — Handles UI, responsive layouts, interactions, accessibility
5. **WordPress/WooCommerce Engineer** — Handles PHP, hooks, APIs, server-side logic
6. **Database Engineer** — Reviews schema, queries, data integrity, migration safety
7. **AI/JOI Engineer** — Handles JOI intelligence, search intent, recommendations
8. **Performance Engineer** — Reviews load time, JS, CSS, database queries, caching
9. **Security Engineer** — Reviews authentication, sanitization, nonce usage, sensitive data
10. **QA Engineer** — Verifies functionality, regressions, edge cases, responsive states
11. **Accessibility Reviewer** — Verifies keyboard access, focus, contrast, labels, screen readers
12. **SEO Engineer** — Reviews metadata, schema, canonicalization, crawlability
13. **DevOps/Release Engineer** — Reviews deployment, rollback, environment, production safety
14. **Code Reviewer** — Reviews correctness, maintainability, duplication, security, consistency

## Spec-Driven Workflow

For every meaningful DEJOIY feature:

1. **SPECIFY** — `/speckit-specify` — Define what to build and why
2. **CLARIFY** — `/speckit-clarify` — Ask structured questions to de-risk ambiguity
3. **CHECKLIST** — `/speckit-checklist` — Generate quality checklists
4. **PLAN** — `/speckit-plan` — Create implementation plan
5. **TASKS** — `/speckit-tasks` — Generate actionable tasks
6. **ANALYZE** — `/speckit-analyze` — Cross-artifact consistency report
7. **IMPLEMENT** — `/speckit-implement` — Execute implementation
8. **CONVERGE** — `/speckit-converge` — Assess codebase, identify remaining work

## Quality Bar

The final result must have:
- Apple-level clarity
- Amazon-level commerce depth
- Myntra-level visual merchandising
- Meesho-level simplicity
- Flipkart-level marketplace breadth
- DEJOIY-level ecosystem intelligence

But it must still feel unmistakably: **DEJOIY**.
