# DEJOIY Motion Component Catalog

> Internal reference for the DEJOIY motion system.
> Powered by ThreeUI Community (MIT License).
> DEJOIY adapter layer — not raw ThreeUI exposure.

## Source

- **ThreeUI Community**: https://github.com/MengTo/threeui
- **License**: MIT (Copyright 2026 Meng To)
- **Location**: `/opt/dejoiy/threeui-community`
- **Version**: 1.1.0

## ThreeUI Components Used

| ThreeUI Component | ThreeUI Source | DEJOIY Wrapper | Purpose |
|---|---|---|---|
| Brand Orbs | `BrandOrbs.ts` | `dm-ambient-orb` | Soft floating background orbs |
| Energy Orb | `SemanticBloom.ts` | `dm-joi-orb` | JOI interactive intelligence indicator |
| Liquid Form | `LiquidFormBackground.ts` | `dm-liquid-bg` | Hero gradient animation |
| Constellation Field | `ConstellationField.ts` | `dm-constellation` | Network dot animation |
| Liquid Metal Button | `LiquidMetalButton.ts` | `dm-btn-premium` | Premium CTA buttons |
| Orbital Sphere | `OrbitalSphereBackground.ts` | `dm-joi-orb__ring` | JOI orbital ring animation |
| Structure Flow | `StructureFlowCollection.ts` | `dm-reveal-stagger` | Staggered entrance animation |
| Predictive Arc | `PredictiveArcCanvas.ts` | (planned) | JOI intelligence visualization |

## DEJOIY Component Set

### 1. Ambient Orbs (`dm-ambient-orb`)
- **ThreeUI source**: Brand Orbs
- **DEJOIY use**: Background atmosphere on homepage, shop pages
- **Performance cost**: Low (CSS only, no WebGL)
- **Mobile behavior**: Reduced opacity, smaller blur
- **Fallback**: Static gradient

### 2. JOI Orb (`dm-joi-orb`)
- **ThreeUI source**: Energy Orb / Orbital Sphere
- **DEJOIY use**: JOI trigger in header, PDP, cart
- **Performance cost**: Low (CSS + lightweight JS for tilt)
- **Mobile behavior**: No 3D tilt, static
- **Fallback**: Simple gradient circle

### 3. Premium Button (`dm-btn-premium`)
- **ThreeUI source**: Liquid Metal Button
- **DEJOIY use**: Primary CTAs (Explore, Ask JOI, Add to Cart)
- **Performance cost**: Minimal
- **Mobile behavior**: Same, touch feedback
- **Fallback**: Standard button

### 4. Ecosystem Cards (`dm-ecosystem-card`)
- **ThreeUI source**: Glassmorphism concepts
- **DEJOIY use**: Shop, Nexus, Create, QuickMart, Renew, Hire cards
- **Performance cost**: Low (CSS perspective + JS tilt)
- **Mobile behavior**: No tilt, tap feedback only
- **Fallback**: Flat cards

### 5. Liquid Background (`dm-liquid-bg`)
- **ThreeUI source**: Liquid Form Background
- **DEJOIY use**: Homepage hero section
- **Performance cost**: Medium (CSS blur + animation)
- **Mobile behavior**: Smaller blobs, less blur
- **Fallback**: Static gradient

### 6. Constellation Field (`dm-constellation`)
- **ThreeUI source**: Constellation Field
- **DEJOIY use**: Hero section ambient dots
- **Performance cost**: Low (CSS only)
- **Mobile behavior**: Disabled for performance
- **Fallback**: None needed

### 7. Scroll Reveal (`dm-reveal`)
- **ThreeUI source**: Structure Flow concepts
- **DEJOIY use**: Section entrance animations
- **Performance cost**: Minimal (IntersectionObserver)
- **Mobile behavior**: Same, respects reduced motion
- **Fallback**: Always visible

### 8. Particles (`dm-particle`)
- **ThreeUI source**: Particle concepts
- **DEJOIY use**: Subtle floating ambient dots
- **Performance cost**: Low (CSS only)
- **Mobile behavior**: Disabled
- **Fallback**: None needed

## Performance Rules

1. **Never globally load heavy WebGL** — Use CSS-first approach
2. **Lazy activation** — Only initialize when visible
3. **Pause offscreen** — Stop animations when not visible
4. **Mobile fallbacks** — Static alternatives on phone
5. **Reduced motion** — Respect `prefers-reduced-motion`
6. **Skip checkout/account** — No motion on transactional pages

## File Structure

```
dejoiy-motion/
├── dejoiy-motion-adapters.php    # WordPress enqueue + init
├── dejoiy-motion-adapters.css    # All motion styles + tokens
├── dejoiy-motion-adapters.js     # Lightweight vanilla JS
└── MOTION-CATALOG.md             # This file
```

## Integration Points

| Page | Components Used |
|---|---|
| Homepage | Ambient orbs, liquid bg, constellation, ecosystem cards, scroll reveal |
| Shop | Scroll reveal, ambient orbs |
| Product | JOI orb, premium button, scroll reveal |
| Cart | Premium button |
| Header | JOI orb (if present) |
| Footer | Ambient orbs (desktop only) |
| Checkout | None (performance) |
| Account | None (performance) |

## ThreeUI License Compliance

- ✅ MIT License preserved
- ✅ Copyright notice: "Copyright (c) 2026 Meng To"
- ✅ No Pro/Beta components used
- ✅ No remote assets fetched
- ✅ DEJOIY adapter layer wraps components
- ✅ No raw ThreeUI exposed to frontend
- ✅ `ASSET-LICENSES.md` available at `/opt/dejoiy/threeui-community/`
- ✅ `FONT-LICENSES.md` available at `/opt/dejoiy/threeui-community/`
- ✅ `THIRD_PARTY_NOTICES.md` available at `/opt/dejoiy/threeui-community/`
