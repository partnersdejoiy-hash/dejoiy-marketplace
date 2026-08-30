<?php
/**
 * DEJOIY Homepage V3 — Combined single file
 * @package Dejoiy
 */
if (!defined('ABSPATH')) exit;

function dejoiy_v3_design_system_css() {
return '
/* ============================================
   DEJOIY DESIGN SYSTEM V3
   Amazon-level usability + Premium identity
   ============================================ */

/* --- CSS Custom Properties --- */
:root {
  --djv3-navy: #050816;
  --djv3-navy-mid: #0B1230;
  --djv3-blue-deep: #101A45;
  --djv3-blue: #287BFF;
  --djv3-pink: #FF168C;
  --djv3-magenta: #E83CFF;
  --djv3-purple: #8A4DFF;
  --djv3-white: #FFFFFF;
  --djv3-soft: #F5F7FF;
  --djv3-muted: #9AA3B5;
  --djv3-border: #E2E6EF;
  --djv3-surface: #F8F9FC;
  --djv3-text: #1A1D26;
  --djv3-text-secondary: #5A6177;
  --djv3-success: #00C48C;
  --djv3-warning: #FFB800;
  --djv3-error: #FF4757;
  --djv3-radius-sm: 6px;
  --djv3-radius-md: 10px;
  --djv3-radius-lg: 16px;
  --djv3-radius-xl: 24px;
  --djv3-shadow-sm: 0 1px 3px rgba(5,8,22,0.06);
  --djv3-shadow-md: 0 4px 16px rgba(5,8,22,0.08);
  --djv3-shadow-lg: 0 8px 32px rgba(5,8,22,0.12);
  --djv3-shadow-xl: 0 16px 48px rgba(5,8,22,0.16);
  --djv3-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --djv3-font-display: "Inter", var(--djv3-font);
  --djv3-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  --djv3-max: 1440px;
  --djv3-header-h: 64px;
  --djv3-utility-h: 36px;
}

/* --- Reset & Base --- */
.djv3 *, .djv3 *::before, .djv3 *::after { box-sizing: border-box; margin: 0; padding: 0; }
.djv3 { font-family: var(--djv3-font); color: var(--djv3-text); background: var(--djv3-white); line-height: 1.5; -webkit-font-smoothing: antialiased; }
.djv3 img { max-width: 100%; height: auto; display: block; }
.djv3 a { color: inherit; text-decoration: none; }
.djv3 button { font-family: inherit; cursor: pointer; border: none; background: none; }
.djv3 input, .djv3 select { font-family: inherit; }
.djv3 ul, .djv3 ol { list-style: none; }
.djv3 .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

/* --- Container --- */
.djv3-container { max-width: var(--djv3-max); margin: 0 auto; padding: 0 20px; }
@media (min-width: 768px) { .djv3-container { padding: 0 32px; } }
@media (min-width: 1200px) { .djv3-container { padding: 0 48px; } }

/* --- Typography --- */
.djv3-h1 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; line-height: 1.15; letter-spacing: -0.02em; }
.djv3-h2 { font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; line-height: 1.2; letter-spacing: -0.015em; }
.djv3-h3 { font-size: clamp(1.125rem, 2vw, 1.5rem); font-weight: 600; line-height: 1.3; }
.djv3-body { font-size: 1rem; line-height: 1.6; }
.djv3-small { font-size: 0.875rem; line-height: 1.5; }
.djv3-caption { font-size: 0.75rem; line-height: 1.4; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }

/* --- Buttons --- */
.djv3-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: var(--djv3-radius-md); font-weight: 600; font-size: 0.9375rem; transition: all var(--djv3-transition); white-space: nowrap; }
.djv3-btn--primary { background: var(--djv3-blue); color: #fff; }
.djv3-btn--primary:hover { background: #1a6ae6; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(40,123,255,0.3); }
.djv3-btn--secondary { background: var(--djv3-soft); color: var(--djv3-text); border: 1px solid var(--djv3-border); }
.djv3-btn--secondary:hover { background: var(--djv3-white); border-color: var(--djv3-blue); color: var(--djv3-blue); }
.djv3-btn--ghost { color: var(--djv3-blue); }
.djv3-btn--ghost:hover { background: rgba(40,123,255,0.06); }
.djv3-btn--lg { padding: 14px 28px; font-size: 1rem; border-radius: var(--djv3-radius-lg); }
.djv3-btn--sm { padding: 6px 14px; font-size: 0.8125rem; }
.djv3-btn--icon { padding: 10px; border-radius: 50%; }
.djv3-btn--pink { background: var(--djv3-pink); color: #fff; }
.djv3-btn--pink:hover { background: #e6137e; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(255,22,140,0.3); }

/* --- Badges --- */
.djv3-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 100px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
.djv3-badge--deal { background: var(--djv3-pink); color: #fff; }
.djv3-badge--eco { background: var(--djv3-blue); color: #fff; }
.djv3-badge--new { background: var(--djv3-success); color: #fff; }
.djv3-badge--pro { background: var(--djv3-purple); color: #fff; }
.djv3-badge--gold { background: linear-gradient(135deg, #D4AF37, #F5D98E); color: #1A1D26; }

/* --- Cards --- */
.djv3-card { background: var(--djv3-white); border-radius: var(--djv3-radius-lg); border: 1px solid var(--djv3-border); overflow: hidden; transition: all var(--djv3-transition); }
.djv3-card:hover { box-shadow: var(--djv3-shadow-lg); transform: translateY(-2px); border-color: transparent; }
.djv3-card__img { width: 100%; aspect-ratio: 4/5; object-fit: cover; background: var(--djv3-surface); }
.djv3-card__body { padding: 14px 16px; }
.djv3-card__title { font-size: 0.9375rem; font-weight: 600; line-height: 1.3; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.djv3-card__price { font-size: 1.125rem; font-weight: 700; color: var(--djv3-text); }
.djv3-card__price--old { font-size: 0.8125rem; color: var(--djv3-muted); text-decoration: line-through; font-weight: 400; margin-left: 6px; }
.djv3-card__meta { font-size: 0.75rem; color: var(--djv3-text-secondary); margin-top: 4px; }
.djv3-card__fav { position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: all var(--djv3-transition); backdrop-filter: blur(4px); }
.djv3-card__fav:hover { background: var(--djv3-pink); color: #fff; transform: scale(1.1); }
.djv3-card__fav.is-active { background: var(--djv3-pink); color: #fff; }
.djv3-card--relative { position: relative; }

/* --- Section Layout --- */
.djv3-section { padding: 48px 0; }
@media (min-width: 768px) { .djv3-section { padding: 64px 0; } }
@media (min-width: 1200px) { .djv3-section { padding: 80px 0; } }
.djv3-section__header { margin-bottom: 28px; display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.djv3-section__title { font-size: clamp(1.375rem, 2.5vw, 1.75rem); font-weight: 700; }
.djv3-section__subtitle { font-size: 0.9375rem; color: var(--djv3-text-secondary); margin-top: 4px; }
.djv3-section__action { font-size: 0.875rem; font-weight: 600; color: var(--djv3-blue); display: flex; align-items: center; gap: 4px; white-space: nowrap; }
.djv3-section__action:hover { text-decoration: underline; }

/* --- Scroll Row --- */
.djv3-scroll-row { display: flex; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; padding-bottom: 8px; scrollbar-width: none; }
.djv3-scroll-row::-webkit-scrollbar { display: none; }
.djv3-scroll-row > * { scroll-snap-align: start; flex-shrink: 0; }

/* --- Grid --- */
.djv3-grid { display: grid; gap: 16px; }
.djv3-grid--2 { grid-template-columns: repeat(2, 1fr); }
.djv3-grid--3 { grid-template-columns: repeat(3, 1fr); }
.djv3-grid--4 { grid-template-columns: repeat(4, 1fr); }
.djv3-grid--6 { grid-template-columns: repeat(6, 1fr); }
@media (max-width: 767px) {
  .djv3-grid--3, .djv3-grid--4, .djv3-grid--6 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .djv3-grid--3, .djv3-grid--4 { grid-template-columns: 1fr 1fr; }
  .djv3-grid--6 { grid-template-columns: repeat(3, 1fr); }
}

/* --- Tabs --- */
.djv3-tabs { display: flex; gap: 4px; border-bottom: 2px solid var(--djv3-border); margin-bottom: 24px; overflow-x: auto; scrollbar-width: none; }
.djv3-tabs::-webkit-scrollbar { display: none; }
.djv3-tab { padding: 10px 18px; font-size: 0.875rem; font-weight: 600; color: var(--djv3-text-secondary); border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all var(--djv3-transition); white-space: nowrap; }
.djv3-tab:hover { color: var(--djv3-text); }
.djv3-tab.is-active { color: var(--djv3-blue); border-bottom-color: var(--djv3-blue); }

/* ============================================
   HEADER
   ============================================ */
.djv3-header { position: sticky; top: 0; z-index: 1000; background: var(--djv3-navy); }
.djv3-utility { background: var(--djv3-navy-mid); height: var(--djv3-utility-h); display: flex; align-items: center; font-size: 0.75rem; color: var(--djv3-muted); }
.djv3-utility__in { display: flex; align-items: center; gap: 20px; width: 100%; overflow-x: auto; scrollbar-width: none; }
.djv3-utility__in::-webkit-scrollbar { display: none; }
.djv3-utility a { color: var(--djv3-muted); transition: color var(--djv3-transition); }
.djv3-utility a:hover { color: var(--djv3-white); }
.djv3-utility__sep { width: 1px; height: 14px; background: rgba(255,255,255,0.15); flex-shrink: 0; }

/* Primary Header */
.djv3-primary { height: var(--djv3-header-h); display: flex; align-items: center; gap: 16px; }
.djv3-logo { flex-shrink: 0; }
.djv3-logo img { height: 32px; width: auto; }
.djv3-logo span { font-size: 1.5rem; font-weight: 800; color: var(--djv3-white); letter-spacing: -0.02em; }
.djv3-logo--wordmark span { background: linear-gradient(135deg, var(--djv3-blue), var(--djv3-purple)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* Search Bar */
.djv3-search { flex: 1; max-width: 720px; position: relative; }
.djv3-search__form { display: flex; height: 44px; border-radius: var(--djv3-radius-md); overflow: hidden; background: var(--djv3-white); }
.djv3-search__cat { display: none; padding: 0 14px; font-size: 0.8125rem; font-weight: 600; color: var(--djv3-text-secondary); border-right: 1px solid var(--djv3-border); background: var(--djv3-surface); cursor: pointer; min-width: 120px; }
@media (min-width: 768px) { .djv3-search__cat { display: flex; align-items: center; } }
.djv3-search__input { flex: 1; padding: 0 16px; font-size: 0.9375rem; border: none; outline: none; background: transparent; min-width: 0; }
.djv3-search__input::placeholder { color: var(--djv3-muted); }
.djv3-search__btn { width: 48px; display: flex; align-items: center; justify-content: center; background: var(--djv3-blue); color: #fff; transition: background var(--djv3-transition); flex-shrink: 0; }
.djv3-search__btn:hover { background: #1a6ae6; }
.djv3-search__btn svg { width: 20px; height: 20px; }
.djv3-search__joi { display: none; width: 44px; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--djv3-pink), var(--djv3-purple)); color: #fff; font-size: 0.75rem; font-weight: 700; border-radius: 0 var(--djv3-radius-md) var(--djv3-radius-md) 0; }
@media (min-width: 768px) { .djv3-search__joi { display: flex; } }

/* Header Actions */
.djv3-actions { display: flex; align-items: center; gap: 4px; margin-left: auto; }
.djv3-action { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px 10px; color: var(--djv3-muted); font-size: 0.6875rem; transition: color var(--djv3-transition); position: relative; border-radius: var(--djv3-radius-sm); }
.djv3-action:hover { color: var(--djv3-white); background: rgba(255,255,255,0.08); }
.djv3-action svg { width: 22px; height: 22px; }
.djv3-action__count { position: absolute; top: 2px; right: 6px; min-width: 16px; height: 16px; border-radius: 100px; background: var(--djv3-pink); color: #fff; font-size: 0.625rem; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 0 4px; }

/* Secondary Nav */
.djv3-nav { background: var(--djv3-navy-mid); border-top: 1px solid rgba(255,255,255,0.06); }
.djv3-nav__in { display: flex; align-items: center; gap: 0; height: 44px; overflow-x: auto; scrollbar-width: none; }
.djv3-nav__in::-webkit-scrollbar { display: none; }
.djv3-nav__item { display: flex; align-items: center; gap: 6px; padding: 0 16px; height: 100%; font-size: 0.8125rem; font-weight: 500; color: rgba(255,255,255,0.75); white-space: nowrap; transition: all var(--djv3-transition); position: relative; }
.djv3-nav__item:hover, .djv3-nav__item.is-active { color: var(--djv3-white); }
.djv3-nav__item.is-active::after { content: ""; position: absolute; bottom: 0; left: 16px; right: 16px; height: 2px; background: var(--djv3-blue); border-radius: 2px 2px 0 0; }
.djv3-nav__browse { display: flex; align-items: center; gap: 8px; padding: 0 18px; height: 100%; font-size: 0.8125rem; font-weight: 700; color: var(--djv3-white); background: rgba(255,255,255,0.06); margin-right: 4px; cursor: pointer; }
.djv3-nav__browse:hover { background: rgba(255,255,255,0.1); }
.djv3-nav__browse svg { width: 18px; height: 18px; }

/* Mega Menu */
.djv3-mega { position: absolute; top: 100%; left: 0; right: 0; background: var(--djv3-white); border-radius: 0 0 var(--djv3-radius-lg) var(--djv3-radius-lg); box-shadow: var(--djv3-shadow-xl); padding: 32px 0; z-index: 999; display: none; }
.djv3-mega.is-open { display: block; animation: djv3SlideDown 0.2s ease-out; }
@keyframes djv3SlideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.djv3-mega__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 32px; max-width: var(--djv3-max); margin: 0 auto; padding: 0 48px; }
.djv3-mega__col h4 { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--djv3-muted); margin-bottom: 12px; }
.djv3-mega__col a { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 0.875rem; color: var(--djv3-text); transition: color var(--djv3-transition); }
.djv3-mega__col a:hover { color: var(--djv3-blue); }
.djv3-mega__col a .mega-icon { width: 28px; height: 28px; border-radius: var(--djv3-radius-sm); display: flex; align-items: center; justify-content: center; font-size: 0.875rem; flex-shrink: 0; }
.djv3-mega__col a .mega-icon--shop { background: rgba(40,123,255,0.08); }
.djv3-mega__col a .mega-icon--learn { background: rgba(138,77,255,0.08); }
.djv3-mega__col a .mega-icon--create { background: rgba(255,22,140,0.08); }
.djv3-mega__col a .mega-icon--grab { background: rgba(255,184,0,0.08); }
.djv3-mega__col a .mega-icon--renew { background: rgba(0,196,140,0.08); }
.djv3-mega__col a .mega-icon--hire { background: rgba(232,60,255,0.08); }
.djv3-mega__promo { margin-top: 24px; padding: 20px 24px; background: linear-gradient(135deg, var(--djv3-navy), var(--djv3-blue-deep)); border-radius: var(--djv3-radius-md); display: flex; align-items: center; justify-content: space-between; gap: 16px; color: var(--djv3-white); }
.djv3-mega__promo-text h4 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
.djv3-mega__promo-text p { font-size: 0.8125rem; color: rgba(255,255,255,0.7); }

/* ============================================
   HERO
   ============================================ */
.djv3-hero { background: linear-gradient(135deg, var(--djv3-navy) 0%, var(--djv3-navy-mid) 50%, var(--djv3-blue-deep) 100%); color: var(--djv3-white); padding: 48px 0; position: relative; overflow: hidden; }
@media (min-width: 768px) { .djv3-hero { padding: 64px 0; } }
@media (min-width: 1200px) { .djv3-hero { padding: 80px 0; } }
.djv3-hero__bg { position: absolute; inset: 0; pointer-events: none; }
.djv3-hero__orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; }
.djv3-hero__orb--1 { width: 400px; height: 400px; background: var(--djv3-blue); top: -100px; right: -100px; }
.djv3-hero__orb--2 { width: 300px; height: 300px; background: var(--djv3-purple); bottom: -80px; left: -60px; }
.djv3-hero__orb--3 { width: 200px; height: 200px; background: var(--djv3-pink); top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.2; }
.djv3-hero__in { position: relative; z-index: 1; display: grid; grid-template-columns: 1fr; gap: 40px; align-items: center; }
@media (min-width: 1024px) { .djv3-hero__in { grid-template-columns: 1fr 1fr; } }
.djv3-hero__content { max-width: 560px; }
.djv3-hero__kicker { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: rgba(255,255,255,0.08); border-radius: 100px; font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.1); }
.djv3-hero__kicker-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--djv3-success); animation: djv3Pulse 2s infinite; }
@keyframes djv3Pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.djv3-hero__title { font-size: clamp(2.25rem, 5vw, 3.5rem); font-weight: 800; line-height: 1.1; letter-spacing: -0.03em; margin-bottom: 16px; }
.djv3-hero__title span { background: linear-gradient(135deg, var(--djv3-blue), var(--djv3-magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.djv3-hero__desc { font-size: 1.125rem; color: rgba(255,255,255,0.7); line-height: 1.6; margin-bottom: 28px; max-width: 480px; }
.djv3-hero__ctas { display: flex; gap: 12px; flex-wrap: wrap; }
.djv3-hero__worlds { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
@media (min-width: 768px) { .djv3-hero__worlds { grid-template-columns: repeat(3, 1fr); } }
.djv3-hero-world { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--djv3-radius-lg); text-align: center; transition: all var(--djv3-transition); cursor: pointer; }
.djv3-hero-world:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }
.djv3-hero-world__icon { width: 44px; height: 44px; border-radius: var(--djv3-radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.djv3-hero-world__icon--shop { background: rgba(40,123,255,0.15); }
.djv3-hero-world__icon--learn { background: rgba(138,77,255,0.15); }
.djv3-hero-world__icon--create { background: rgba(255,22,140,0.15); }
.djv3-hero-world__icon--grab { background: rgba(255,184,0,0.15); }
.djv3-hero-world__icon--renew { background: rgba(0,196,140,0.15); }
.djv3-hero-world__icon--hire { background: rgba(232,60,255,0.15); }
.djv3-hero-world__label { font-size: 0.8125rem; font-weight: 600; color: var(--djv3-white); }
.djv3-hero-world__sub { font-size: 0.6875rem; color: rgba(255,255,255,0.5); }
';
}


function dejoiy_v3_section_css() {
return '
/* ============================================
   JOI AI DISCOVERY
   ============================================ */
.djv3-joi { background: linear-gradient(180deg, var(--djv3-soft) 0%, var(--djv3-white) 100%); padding: 48px 0; }
.djv3-joi__in { max-width: 720px; margin: 0 auto; text-align: center; }
.djv3-joi__badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: linear-gradient(135deg, rgba(255,22,140,0.08), rgba(138,77,255,0.08)); border-radius: 100px; font-size: 0.6875rem; font-weight: 700; color: var(--djv3-purple); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.06em; }
.djv3-joi__title { font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 700; margin-bottom: 8px; }
.djv3-joi__desc { font-size: 0.9375rem; color: var(--djv3-text-secondary); margin-bottom: 24px; }
.djv3-joi__box { background: var(--djv3-white); border: 2px solid var(--djv3-border); border-radius: var(--djv3-radius-xl); padding: 6px; display: flex; gap: 0; box-shadow: var(--djv3-shadow-md); transition: border-color var(--djv3-transition); }
.djv3-joi__box:focus-within { border-color: var(--djv3-blue); box-shadow: 0 0 0 4px rgba(40,123,255,0.1); }
.djv3-joi__input { flex: 1; padding: 14px 20px; font-size: 1rem; border: none; outline: none; background: transparent; min-width: 0; }
.djv3-joi__input::placeholder { color: var(--djv3-muted); }
.djv3-joi__submit { padding: 14px 28px; background: linear-gradient(135deg, var(--djv3-blue), var(--djv3-purple)); color: #fff; border-radius: var(--djv3-radius-lg); font-weight: 700; font-size: 0.9375rem; transition: all var(--djv3-transition); }
.djv3-joi__submit:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(40,123,255,0.3); }
.djv3-joi__chips { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 20px; }
.djv3-joi__chip { padding: 8px 16px; background: var(--djv3-white); border: 1px solid var(--djv3-border); border-radius: 100px; font-size: 0.8125rem; color: var(--djv3-text-secondary); transition: all var(--djv3-transition); cursor: pointer; }
.djv3-joi__chip:hover { border-color: var(--djv3-blue); color: var(--djv3-blue); background: rgba(40,123,255,0.04); }

/* ============================================
   CATEGORY DISCOVERY
   ============================================ */
.djv3-cats { padding: 40px 0; }
.djv3-cats__scroll { display: flex; gap: 12px; overflow-x: auto; scrollbar-width: none; padding-bottom: 4px; }
.djv3-cats__scroll::-webkit-scrollbar { display: none; }
.djv3-cat { display: flex; flex-direction: column; align-items: center; gap: 10px; min-width: 90px; text-align: center; cursor: pointer; transition: transform var(--djv3-transition); }
.djv3-cat:hover { transform: translateY(-3px); }
.djv3-cat__icon { width: 64px; height: 64px; border-radius: 50%; background: var(--djv3-surface); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 2px solid transparent; transition: all var(--djv3-transition); }
.djv3-cat:hover .djv3-cat__icon { border-color: var(--djv3-blue); background: rgba(40,123,255,0.04); }
.djv3-cat__label { font-size: 0.75rem; font-weight: 600; color: var(--djv3-text); }

/* ============================================
   WORLD CARDS
   ============================================ */
.djv3-worlds__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
@media (min-width: 768px) { .djv3-worlds__grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1024px) { .djv3-worlds__grid { grid-template-columns: repeat(6, 1fr); } }
.djv3-world-card { position: relative; padding: 24px 16px; border-radius: var(--djv3-radius-lg); text-align: center; transition: all var(--djv3-transition); overflow: hidden; cursor: pointer; border: 1px solid var(--djv3-border); }
.djv3-world-card:hover { transform: translateY(-4px); box-shadow: var(--djv3-shadow-lg); border-color: transparent; }
.djv3-world-card__icon { font-size: 2rem; margin-bottom: 12px; display: block; }
.djv3-world-card__title { font-size: 0.9375rem; font-weight: 700; margin-bottom: 4px; }
.djv3-world-card__desc { font-size: 0.75rem; color: var(--djv3-text-secondary); line-height: 1.4; }
.djv3-world-card__cta { display: inline-flex; align-items: center; gap: 4px; margin-top: 12px; font-size: 0.8125rem; font-weight: 600; color: var(--djv3-blue); }
.djv3-world-card--shop { background: linear-gradient(135deg, rgba(40,123,255,0.03), rgba(40,123,255,0.08)); }
.djv3-world-card--learn { background: linear-gradient(135deg, rgba(138,77,255,0.03), rgba(138,77,255,0.08)); }
.djv3-world-card--create { background: linear-gradient(135deg, rgba(255,22,140,0.03), rgba(255,22,140,0.08)); }
.djv3-world-card--grab { background: linear-gradient(135deg, rgba(255,184,0,0.03), rgba(255,184,0,0.08)); }
.djv3-world-card--renew { background: linear-gradient(135deg, rgba(0,196,140,0.03), rgba(0,196,140,0.08)); }
.djv3-world-card--hire { background: linear-gradient(135deg, rgba(232,60,255,0.03), rgba(232,60,255,0.08)); }

/* ============================================
   DEALS SECTION
   ============================================ */
.djv3-deals { background: var(--djv3-soft); }
.djv3-deal-card { position: relative; }
.djv3-deal-card__badge { position: absolute; top: 12px; left: 12px; z-index: 2; }
.djv3-deal-card__discount { position: absolute; top: 12px; right: 12px; z-index: 2; background: var(--djv3-pink); color: #fff; padding: 4px 10px; border-radius: 100px; font-size: 0.6875rem; font-weight: 700; }

/* ============================================
   WORLD SECTIONS (Nexus, Studio, etc.)
   ============================================ */
.djv3-world-section { padding: 48px 0; }
.djv3-world-section--nexus { background: linear-gradient(135deg, #F8F5FF 0%, var(--djv3-white) 100%); }
.djv3-world-section--studio { background: linear-gradient(135deg, #FFF0F6 0%, var(--djv3-white) 100%); }
.djv3-world-section--renew { background: linear-gradient(135deg, #F0FFF8 0%, var(--djv3-white) 100%); }
.djv3-world-section--services { background: linear-gradient(135deg, #FFF8E6 0%, var(--djv3-white) 100%); }
.djv3-world-section__header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.djv3-world-section__icon { font-size: 1.5rem; margin-right: 8px; }

/* Service Card (different from product card) */
.djv3-service-card { background: var(--djv3-white); border: 1px solid var(--djv3-border); border-radius: var(--djv3-radius-lg); padding: 20px; transition: all var(--djv3-transition); }
.djv3-service-card:hover { box-shadow: var(--djv3-shadow-lg); transform: translateY(-2px); border-color: transparent; }
.djv3-service-card__cat { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--djv3-purple); margin-bottom: 8px; }
.djv3-service-card__title { font-size: 0.9375rem; font-weight: 600; line-height: 1.3; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.djv3-service-card__provider { font-size: 0.8125rem; color: var(--djv3-text-secondary); margin-bottom: 12px; }
.djv3-service-card__footer { display: flex; align-items: center; justify-content: space-between; }
.djv3-service-card__price { font-size: 1rem; font-weight: 700; }
.djv3-service-card__rating { display: flex; align-items: center; gap: 4px; font-size: 0.8125rem; color: var(--djv3-warning); }

/* ============================================
   TRUST STRIP
   ============================================ */
.djv3-trust { background: var(--djv3-navy); color: var(--djv3-white); padding: 24px 0; }
.djv3-trust__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
@media (min-width: 768px) { .djv3-trust__grid { grid-template-columns: repeat(4, 1fr); } }
.djv3-trust__item { display: flex; align-items: center; gap: 12px; }
.djv3-trust__icon { width: 40px; height: 40px; border-radius: var(--djv3-radius-md); background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0; }
.djv3-trust__text h4 { font-size: 0.8125rem; font-weight: 700; }
.djv3-trust__text p { font-size: 0.6875rem; color: rgba(255,255,255,0.6); }

/* ============================================
   SELLER CTA
   ============================================ */
.djv3-seller-cta { background: linear-gradient(135deg, var(--djv3-navy), var(--djv3-blue-deep)); color: var(--djv3-white); padding: 64px 0; text-align: center; position: relative; overflow: hidden; }
.djv3-seller-cta__bg { position: absolute; inset: 0; background: radial-gradient(circle at 30% 50%, rgba(40,123,255,0.15) 0%, transparent 60%); pointer-events: none; }
.djv3-seller-cta__in { position: relative; z-index: 1; max-width: 640px; margin: 0 auto; }
.djv3-seller-cta__title { font-size: clamp(1.75rem, 3.5vw, 2.5rem); font-weight: 800; margin-bottom: 12px; }
.djv3-seller-cta__desc { font-size: 1rem; color: rgba(255,255,255,0.7); margin-bottom: 28px; max-width: 480px; margin-left: auto; margin-right: auto; }
.djv3-seller-cta__roles { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-bottom: 32px; }
.djv3-seller-role { padding: 12px 20px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--djv3-radius-md); font-size: 0.875rem; font-weight: 600; transition: all var(--djv3-transition); }
.djv3-seller-role:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); }

/* ============================================
   FOOTER
   ============================================ */
.djv3-footer { background: var(--djv3-navy); color: rgba(255,255,255,0.7); padding: 48px 0 24px; }
.djv3-footer__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; margin-bottom: 40px; }
@media (min-width: 768px) { .djv3-footer__grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1024px) { .djv3-footer__grid { grid-template-columns: 2fr repeat(4, 1fr); } }
.djv3-footer__brand { max-width: 280px; }
.djv3-footer__brand-name { font-size: 1.5rem; font-weight: 800; color: var(--djv3-white); margin-bottom: 8px; }
.djv3-footer__brand-desc { font-size: 0.8125rem; line-height: 1.6; }
.djv3-footer__col h4 { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--djv3-white); margin-bottom: 16px; }
.djv3-footer__col a { display: block; padding: 4px 0; font-size: 0.8125rem; color: rgba(255,255,255,0.6); transition: color var(--djv3-transition); }
.djv3-footer__col a:hover { color: var(--djv3-white); }
.djv3-footer__bottom { padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; font-size: 0.75rem; }
.djv3-footer__social { display: flex; gap: 12px; }
.djv3-footer__social a { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.6); transition: all var(--djv3-transition); }
.djv3-footer__social a:hover { background: var(--djv3-blue); color: #fff; }

/* ============================================
   MOBILE BOTTOM NAV
   ============================================ */
.djv3-bottom-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 1001; background: var(--djv3-white); border-top: 1px solid var(--djv3-border); padding: 6px 0 env(safe-area-inset-bottom, 6px); }
@media (max-width: 767px) { .djv3-bottom-nav { display: flex; justify-content: space-around; } }
.djv3-bottom-nav__item { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 4px 12px; color: var(--djv3-muted); font-size: 0.625rem; font-weight: 600; position: relative; }
.djv3-bottom-nav__item.is-active { color: var(--djv3-blue); }
.djv3-bottom-nav__item svg { width: 22px; height: 22px; }
.djv3-bottom-nav__item--joi { position: relative; top: -12px; }
.djv3-bottom-nav__item--joi .djv3-bottom-nav__icon { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--djv3-blue), var(--djv3-purple)); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 4px 16px rgba(40,123,255,0.3); }

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 767px) {
  .djv3-hero__worlds { grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .djv3-hero-world { padding: 14px 8px; }
  .djv3-hero-world__icon { width: 36px; height: 36px; font-size: 1rem; }
  .djv3-hero-world__label { font-size: 0.6875rem; }
  .djv3-worlds__grid { grid-template-columns: repeat(2, 1fr); }
  .djv3-footer__grid { grid-template-columns: repeat(2, 1fr); }
  .djv3-trust__grid { grid-template-columns: 1fr; }
  .djv3-section__header { flex-direction: column; align-items: flex-start; }
  body { padding-bottom: 72px; }
}

/* ============================================
   ANIMATIONS
   ============================================ */
.djv3-reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease, transform 0.5s ease; }
.djv3-reveal.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) {
  .djv3-reveal { opacity: 1; transform: none; transition: none; }
  .djv3-hero-world:hover, .djv3-card:hover, .djv3-world-card:hover { transform: none; }
}

/* ============================================
   SKELETON LOADER
   ============================================ */
.djv3-skeleton { background: linear-gradient(90deg, var(--djv3-surface) 25%, #E8ECF4 50%, var(--djv3-surface) 75%); background-size: 200% 100%; animation: djv3Shimmer 1.5s infinite; border-radius: var(--djv3-radius-md); }
@keyframes djv3Shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.djv3-skeleton--text { height: 14px; margin-bottom: 8px; }
.djv3-skeleton--title { height: 24px; width: 60%; margin-bottom: 12px; }
.djv3-skeleton--img { aspect-ratio: 4/5; }
';
}


function dejoiy_v3_render_header() {
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    ?>
    <header class="djv3-header" id="djv3-header">
        <!-- Utility Bar -->
        <div class="djv3-utility">
            <div class="djv3-container djv3-utility__in">
                <a href="<?php echo esc_url(home_url('/')); ?>">🏠 Home</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>">Sell on DEJOIY</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/dejoy-festival-sale/')); ?>">🎉 Deals</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/my-account/orders/')); ?>">📦 Track Order</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/contact/')); ?>">💬 Support</a>
            </div>
        </div>

        <!-- Primary Header -->
        <div class="djv3-container djv3-primary">
            <!-- Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="djv3-logo djv3-logo--wordmark">
                <span>DEJOIY</span>
            </a>

            <!-- Search -->
            <div class="djv3-search">
                <form class="djv3-search__form" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
                    <input type="hidden" name="post_type" value="product">
                    <button type="button" class="djv3-search__cat">All ▾</button>
                    <input class="djv3-search__input" type="text" name="s" placeholder="Search products, services, books, custom items..." autocomplete="off">
                    <button type="submit" class="djv3-search__btn" aria-label="Search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <a href="<?php echo esc_url(home_url('/?joi=1')); ?>" class="djv3-search__joi" title="Ask JOI AI">JOI</a>
                </form>
            </div>

            <!-- Actions -->
            <div class="djv3-actions">
                <a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="djv3-action" title="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Account</span>
                </a>
                <a href="<?php echo esc_url(home_url('/my-account/wishlist/')); ?>" class="djv3-action" title="Wishlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span>Wishlist</span>
                </a>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="djv3-action" title="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span>Cart</span>
                    <?php if (function_exists('WC') && WC()->cart->get_cart_contents_count() > 0): ?>
                        <span class="djv3-action__count"><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Secondary Nav -->
        <nav class="djv3-nav" id="djv3-nav">
            <div class="djv3-container djv3-nav__in">
                <button type="button" class="djv3-nav__browse" id="djv3-browse-btn" aria-expanded="false" aria-controls="djv3-mega">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                    Browse All
                </button>
                <a href="<?php echo esc_url($shop_url); ?>" class="djv3-nav__item">Shop</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>" class="djv3-nav__item">Nexus</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>" class="djv3-nav__item">Custom Studio</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-quick-mart/')); ?>" class="djv3-nav__item">QuickMart</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>" class="djv3-nav__item">Renew</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>" class="djv3-nav__item">Hire</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-festival-sale/')); ?>" class="djv3-nav__item" style="color:#FF168C">Deals</a>
                <a href="<?php echo esc_url(home_url('/?joi=1')); ?>" class="djv3-nav__item">✨ Ask JOI</a>
            </div>

            <!-- Mega Menu -->
            <div class="djv3-mega" id="djv3-mega" role="menu">
                <div class="djv3-mega__grid">
                    <div class="djv3-mega__col">
                        <h4>Shop</h4>
                        <a href="<?php echo esc_url($shop_url); ?>"><span class="mega-icon mega-icon--shop">🛍️</span> Marketplace</a>
                        <a href="<?php echo esc_url(home_url('/fashion/fashion/')); ?>"><span class="mega-icon mega-icon--shop">👗</span> Fashion</a>
                        <a href="<?php echo esc_url(home_url('/electronics/electronics/')); ?>"><span class="mega-icon mega-icon--shop">📱</span> Electronics</a>
                        <a href="<?php echo esc_url(home_url('/home-kitchen/home-kitchen/')); ?>"><span class="mega-icon mega-icon--shop">🏠</span> Home & Kitchen</a>
                        <a href="<?php echo esc_url(home_url('/beauty-personal-care/beauty-personal-care/')); ?>"><span class="mega-icon mega-icon--shop">💄</span> Beauty</a>
                        <a href="<?php echo esc_url(home_url('/sports-fitness/sports-fitness/')); ?>"><span class="mega-icon mega-icon--shop">⚡</span> Sports</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Learn</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>"><span class="mega-icon mega-icon--learn">📚</span> Nexus Library</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>"><span class="mega-icon mega-icon--learn">📖</span> Books</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-nexus-lms/')); ?>"><span class="mega-icon mega-icon--learn">🎓</span> Courses</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Create</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>"><span class="mega-icon mega-icon--create">🎨</span> Custom Studio</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>"><span class="mega-icon mega-icon--create">👕</span> Custom T-Shirts</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>"><span class="mega-icon mega-icon--create">☕</span> Custom Mugs</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Grab & Renew</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-quick-mart/')); ?>"><span class="mega-icon mega-icon--grab">⚡</span> QuickMart</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>"><span class="mega-icon mega-icon--renew">♻️</span> Renew</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>"><span class="mega-icon mega-icon--renew">💻</span> Refurbished Laptops</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Hire & Discover</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>"><span class="mega-icon mega-icon--hire">🤝</span> Services</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-festival-sale/')); ?>"><span class="mega-icon mega-icon--shop">🏷️</span> Deals</a>
                        <a href="<?php echo esc_url(home_url('/all-categories/')); ?>"><span class="mega-icon mega-icon--shop">📂</span> All Categories</a>
                    </div>
                </div>
                <div class="djv3-mega__promo">
                    <div class="djv3-mega__promo-text">
                        <h4>✨ Build Your Business on DEJOIY</h4>
                        <p>Sell products, offer services, publish books — start your journey today.</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>" class="djv3-btn djv3-btn--primary djv3-btn--sm">Start Selling →</a>
                </div>
            </div>
        </nav>
    </header>
    <?php
}

function dejoiy_v3_render_hero() {
    $gateways = dejoiy_universe_gateways();
    ?>
    <section class="djv3-hero djv3-reveal">
        <div class="djv3-hero__bg">
            <div class="djv3-hero__orb djv3-hero__orb--1"></div>
            <div class="djv3-hero__orb djv3-hero__orb--2"></div>
            <div class="djv3-hero__orb djv3-hero__orb--3"></div>
        </div>
        <div class="djv3-container djv3-hero__in">
            <div class="djv3-hero__content">
                <div class="djv3-hero__kicker">
                    <span class="djv3-hero__kicker-dot"></span>
                    India's Next-Gen Marketplace
                </div>
                <h1 class="djv3-hero__title">
                    Everything You Need.<br><span>One DEJOIY.</span>
                </h1>
                <p class="djv3-hero__desc">Shop, learn, create, grab, renew and hire — all in one joyful platform built for India.</p>
                <div class="djv3-hero__ctas">
                    <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="djv3-btn djv3-btn--primary djv3-btn--lg">Explore DEJOIY →</a>
                    <a href="<?php echo esc_url(home_url('/welcome-to-the-dejoiy-universe-indias-next-generation-marketplace/')); ?>" class="djv3-btn djv3-btn--secondary djv3-btn--lg" style="border-color:rgba(255,255,255,0.2);color:#fff;background:rgba(255,255,255,0.06);">Discover the Universe</a>
                </div>
            </div>
            <div class="djv3-hero__worlds" role="navigation" aria-label="DEJOIY Ecosystem">
                <?php
                $world_icons = array(
                    'marketplace' => array('icon' => '🛍️', 'cls' => 'shop'),
                    'nexus' => array('icon' => '📚', 'cls' => 'learn'),
                    'studio' => array('icon' => '🎨', 'cls' => 'create'),
                    'quickmart' => array('icon' => '⚡', 'cls' => 'grab'),
                    'refurbished' => array('icon' => '♻️', 'cls' => 'renew'),
                    'services' => array('icon' => '🤝', 'cls' => 'hire'),
                );
                foreach ($gateways as $key => $g) :
                    $wi = isset($world_icons[$key]) ? $world_icons[$key] : array('icon' => '◆', 'cls' => 'shop');
                ?>
                <a href="<?php echo esc_url($g['url']); ?>" class="djv3-hero-world">
                    <span class="djv3-hero-world__icon djv3-hero-world__icon--<?php echo esc_attr($wi['cls']); ?>"><?php echo $wi['icon']; ?></span>
                    <span class="djv3-hero-world__label"><?php echo esc_html($g['verb']); ?></span>
                    <span class="djv3-hero-world__sub"><?php echo esc_html($g['label']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_joi() {
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $examples = dejoiy_universe_joi_examples();
    ?>
    <section class="djv3-joi djv3-reveal">
        <div class="djv3-container djv3-joi__in">
            <div class="djv3-joi__badge">✨ Powered by JOI Intelligence</div>
            <h2 class="djv3-joi__title">Ask JOI anything</h2>
            <p class="djv3-joi__desc">JOI understands what you need across all DEJOIY worlds — products, books, services, custom items and more.</p>
            <form class="djv3-joi__box" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
                <input type="hidden" name="post_type" value="product">
                <input class="djv3-joi__input" type="text" name="s" placeholder="Find me a laptop under ₹40,000..." autocomplete="off">
                <button type="submit" class="djv3-joi__submit">Discover</button>
            </form>
            <div class="djv3-joi__chips">
                <?php foreach ($examples as $ex) : ?>
                    <a class="djv3-joi__chip" href="<?php echo esc_url($ex['url']); ?>"><?php echo esc_html($ex['label']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}


function dejoiy_v3_render_categories() {
    $cats = array(
        array('icon' => '📱', 'label' => 'Electronics', 'url' => home_url('/electronics/electronics/')),
        array('icon' => '👗', 'label' => 'Fashion', 'url' => home_url('/fashion/fashion/')),
        array('icon' => '🏠', 'label' => 'Home', 'url' => home_url('/home-kitchen/home-kitchen/')),
        array('icon' => '💄', 'label' => 'Beauty', 'url' => home_url('/beauty-personal-care/beauty-personal-care/')),
        array('icon' => '📚', 'label' => 'Books', 'url' => home_url('/dejoiy-library/?dejoiy_library=1')),
        array('icon' => '🎮', 'label' => 'Toys', 'url' => home_url('/toys-games/toys-games/')),
        array('icon' => '🏋️', 'label' => 'Sports', 'url' => home_url('/sports-fitness/sports-fitness/')),
        array('icon' => '🎨', 'label' => 'Studio', 'url' => home_url('/dejoiy-custom-studio/')),
        array('icon' => '🤝', 'label' => 'Services', 'url' => home_url('/dejoiy-services/')),
        array('icon' => '♻️', 'label' => 'Renew', 'url' => home_url('/dejoiy-refurbished/')),
        array('icon' => '⚡', 'label' => 'QuickMart', 'url' => home_url('/dejoiy-quick-mart/')),
        array('icon' => '🏷️', 'label' => 'Deals', 'url' => home_url('/dejoiy-festival-sale/')),
    );
    ?>
    <section class="djv3-cats djv3-reveal">
        <div class="djv3-container">
            <div class="djv3-section__header">
                <div>
                    <h2 class="djv3-section__title">Popular Categories</h2>
                    <p class="djv3-section__subtitle">Explore what DEJOIY has to offer</p>
                </div>
                <a href="<?php echo esc_url(home_url('/all-categories/')); ?>" class="djv3-section__action">View All →</a>
            </div>
            <div class="djv3-cats__scroll">
                <?php foreach ($cats as $cat) : ?>
                    <a href="<?php echo esc_url($cat['url']); ?>" class="djv3-cat">
                        <span class="djv3-cat__icon"><?php echo $cat['icon']; ?></span>
                        <span class="djv3-cat__label"><?php echo esc_html($cat['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_worlds() {
    $worlds = array(
        array('icon' => '🛍️', 'title' => 'Shop', 'desc' => 'Everything you love.', 'url' => home_url('/shop/'), 'cls' => 'shop', 'cta' => 'Explore Marketplace'),
        array('icon' => '📚', 'title' => 'Nexus', 'desc' => 'Read. Learn. Grow.', 'url' => home_url('/dejoiy-library/?dejoiy_library=1'), 'cls' => 'learn', 'cta' => 'Enter Nexus'),
        array('icon' => '🎨', 'title' => 'Custom Studio', 'desc' => 'Create it your way.', 'url' => home_url('/dejoiy-custom-studio/'), 'cls' => 'create', 'cta' => 'Open Studio'),
        array('icon' => '⚡', 'title' => 'QuickMart', 'desc' => 'Essentials, instantly.', 'url' => home_url('/dejoiy-quick-mart/'), 'cls' => 'grab', 'cta' => 'Grab Now'),
        array('icon' => '♻️', 'title' => 'Renew', 'desc' => 'Premium tech. Smarter prices.', 'url' => home_url('/dejoiy-refurbished/'), 'cls' => 'renew', 'cta' => 'Shop Renew'),
        array('icon' => '🤝', 'title' => 'Hire', 'desc' => 'Find trusted expertise.', 'url' => home_url('/dejoiy-services/'), 'cls' => 'hire', 'cta' => 'Explore Services'),
    );
    ?>
    <section class="djv3-section djv3-reveal">
        <div class="djv3-container">
            <div class="djv3-section__header">
                <div>
                    <h2 class="djv3-section__title">Explore the DEJOIY Universe</h2>
                    <p class="djv3-section__subtitle">One platform. Many worlds.</p>
                </div>
            </div>
            <div class="djv3-worlds__grid">
                <?php foreach ($worlds as $w) : ?>
                    <a href="<?php echo esc_url($w['url']); ?>" class="djv3-world-card djv3-world-card--<?php echo esc_attr($w['cls']); ?>">
                        <span class="djv3-world-card__icon"><?php echo $w['icon']; ?></span>
                        <h3 class="djv3-world-card__title"><?php echo esc_html($w['title']); ?></h3>
                        <p class="djv3-world-card__desc"><?php echo esc_html($w['desc']); ?></p>
                        <span class="djv3-world-card__cta"><?php echo esc_html($w['cta']); ?> →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_trust() {
    ?>
    <section class="djv3-trust djv3-reveal">
        <div class="djv3-container">
            <div class="djv3-trust__grid">
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">🚚</span>
                    <div class="djv3-trust__text">
                        <h4>Convenient Delivery</h4>
                        <p>Fast & reliable shipping</p>
                    </div>
                </div>
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">🔒</span>
                    <div class="djv3-trust__text">
                        <h4>100% Secure Payments</h4>
                        <p>Razorpay protected</p>
                    </div>
                </div>
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">💰</span>
                    <div class="djv3-trust__text">
                        <h4>Best Price Guarantee</h4>
                        <p>Competitive pricing</p>
                    </div>
                </div>
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">🔄</span>
                    <div class="djv3-trust__text">
                        <h4>Easy Returns</h4>
                        <p>Hassle-free return policy</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_seller_cta() {
    ?>
    <section class="djv3-seller-cta djv3-reveal">
        <div class="djv3-seller-cta__bg"></div>
        <div class="djv3-container djv3-seller-cta__in">
            <h2 class="djv3-seller-cta__title">Build Your Business on DEJOIY</h2>
            <p class="djv3-seller-cta__desc">DEJOIY is not just for buyers. Sell products, offer services, publish books — become part of India's growing ecosystem.</p>
            <div class="djv3-seller-cta__roles">
                <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>" class="djv3-seller-role">🛍️ Become a Seller</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>" class="djv3-seller-role">🤝 Become a Service Provider</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>" class="djv3-seller-role">📚 Become an Author</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>" class="djv3-seller-role">🎨 Become a Creator</a>
            </div>
            <a href="<?php echo esc_url(home_url('/vendor-register/')); ?>" class="djv3-btn djv3-btn--primary djv3-btn--lg">Start Your Journey →</a>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_footer() {
    ?>
    <footer class="djv3-footer">
        <div class="djv3-container">
            <div class="djv3-footer__grid">
                <div class="djv3-footer__brand">
                    <div class="djv3-footer__brand-name">DEJOIY</div>
                    <p class="djv3-footer__brand-desc">India's next-generation digital marketplace. One platform. Many worlds.</p>
                </div>
                <div class="djv3-footer__col">
                    <h4>Shop</h4>
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>">Marketplace</a>
                    <a href="<?php echo esc_url(home_url('/all-categories/')); ?>">Categories</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-festival-sale/')); ?>">Deals</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>">Renew</a>
                </div>
                <div class="djv3-footer__col">
                    <h4>Explore</h4>
                    <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>">Nexus</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>">Custom Studio</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-quick-mart/')); ?>">QuickMart</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>">Services</a>
                </div>
                <div class="djv3-footer__col">
                    <h4>Support</h4>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a>
                    <a href="<?php echo esc_url(home_url('/my-account/orders/')); ?>">Track Orders</a>
                    <a href="<?php echo esc_url(home_url('/my-account/')); ?>">My Account</a>
                    <a href="<?php echo esc_url(home_url('/faq/')); ?>">FAQ</a>
                </div>
                <div class="djv3-footer__col">
                    <h4>Business</h4>
                    <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>">Become a Seller</a>
                    <a href="<?php echo esc_url(home_url('/vendor-register/')); ?>">Vendor Registration</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>">Offer Services</a>
                </div>
            </div>
            <div class="djv3-footer__bottom">
                <span>© <?php echo date('Y'); ?> DEJOIY. All rights reserved.</span>
                <div class="djv3-footer__social">
                    <a href="#" aria-label="Instagram">📷</a>
                    <a href="#" aria-label="Twitter">🐦</a>
                    <a href="#" aria-label="YouTube">📺</a>
                    <a href="#" aria-label="LinkedIn">💼</a>
                </div>
            </div>
        </div>
    </footer>
    <?php
}

function dejoiy_v3_render_bottom_nav() {
    ?>
    <nav class="djv3-bottom-nav" id="djv3-bottom-nav" aria-label="Mobile navigation">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="djv3-bottom-nav__item is-active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
        </a>
        <a href="<?php echo esc_url(home_url('/all-categories/')); ?>" class="djv3-bottom-nav__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            Discover
        </a>
        <a href="<?php echo esc_url(home_url('/?joi=1')); ?>" class="djv3-bottom-nav__item djv3-bottom-nav__item--joi">
            <span class="djv3-bottom-nav__icon">✨</span>
            JOI
        </a>
        <a href="<?php echo esc_url(home_url('/my-account/wishlist/')); ?>" class="djv3-bottom-nav__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Favorites
        </a>
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="djv3-bottom-nav__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Cart
        </a>
    </nav>
    <?php
}

function dejoiy_v3_render_js() {
    ?>
    <script>
    (function(){
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        /* Mega menu toggle */
        var btn = document.getElementById('djv3-browse-btn');
        var mega = document.getElementById('djv3-mega');
        if(btn && mega) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = mega.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open);
            });
            document.addEventListener('click', function(e) {
                if(!mega.contains(e.target) && e.target !== btn) {
                    mega.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }
        /* Scroll reveal */
        if(!reduce) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if(entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {threshold: 0.1});
            document.querySelectorAll('.djv3-reveal').forEach(function(el) {
                observer.observe(el);
            });
        } else {
            document.querySelectorAll('.djv3-reveal').forEach(function(el) {
                el.classList.add('is-visible');
            });
        }
        /* Sticky header shadow */
        var header = document.getElementById('djv3-header');
        if(header) {
            window.addEventListener('scroll', function() {
                header.style.boxShadow = window.scrollY > 10 ? '0 2px 12px rgba(0,0,0,0.1)' : 'none';
            }, {passive: true});
        }
        /* Search suggestions */
        var searchInputs = document.querySelectorAll('.djv3-search__input, .djv3-joi__input');
        searchInputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                this.closest('.djv3-search__form, .djv3-joi__box').style.borderColor = 'var(--djv3-blue)';
            });
            input.addEventListener('blur', function() {
                this.closest('.djv3-search__form, .djv3-joi__box').style.borderColor = '';
            });
        });
    })();
    </script>
    <?php
}


function dejoiy_v3_shelf($args, $world = 'market', $limit = 8) {
    $defaults = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'no_found_rows' => true,
    );
    $q = new WP_Query(array_merge($defaults, $args));
    if (!$q->have_posts()) { wp_reset_postdata(); return; }
    echo '<div class="djv3-scroll-row">';
    while ($q->have_posts()) { $q->the_post();
        $pid = get_the_ID();
        $product = wc_get_product($pid);
        if (!$product) continue;
        $url = function_exists('dejoiy_ecosystem_product_url') ? dejoiy_ecosystem_product_url($pid) : get_permalink($pid);
        $img = get_the_post_thumbnail_url($pid, 'woocommerce_thumbnail');
        if (!$img) {
            $gallery = $product->get_gallery_image_ids();
            if (!empty($gallery)) $img = wp_get_attachment_image_url($gallery[0], 'woocommerce_thumbnail');
        }
        $name = $product->get_name();
        $price_html = wp_strip_all_tags($product->get_price_html());
        $on_sale = $product->is_on_sale();
        $rating = $product->get_average_rating();
        $seller = '';
        $author_id = (int) get_post_field('post_author', $pid);
        if ($author_id > 0 && function_exists('wcfm_get_vendor_store_name')) {
            $seller = wcfm_get_vendor_store_name($author_id);
        }
        ?>
        <article class="djv3-card djv3-card--relative" style="min-width:220px;max-width:260px;width:220px;">
            <button type="button" class="djv3-card__fav" aria-label="Save to favorites">♡</button>
            <a href="<?php echo esc_url($url); ?>">
                <img class="djv3-card__img" src="<?php echo esc_url($img ?: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22280%22 height=%22350%22%3E%3Crect fill=%22%23F5F7FF%22 width=%22280%22 height=%22350%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239AA3B5%22 font-family=%22sans-serif%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E'); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async" width="280" height="350">
                <?php if ($on_sale) : ?>
                    <span class="djv3-deal-card__discount">SALE</span>
                <?php endif; ?>
                <div class="djv3-card__body">
                    <h3 class="djv3-card__title"><?php echo esc_html($name); ?></h3>
                    <div><?php echo wp_kses_post($product->get_price_html()); ?></div>
                    <?php if ($rating > 0) : ?>
                        <div class="djv3-card__meta">★ <?php echo esc_html($rating); ?></div>
                    <?php endif; ?>
                    <?php if ($seller) : ?>
                        <div class="djv3-card__meta"><?php echo esc_html($seller); ?></div>
                    <?php endif; ?>
                </div>
            </a>
        </article>
        <?php
    }
    wp_reset_postdata();
    echo '</div>';
}

/**
 * Render the complete V3 homepage
 */
function dejoiy_v3_render() {
    if (!class_exists('WooCommerce')) return;

    ob_start();

    // Inject CSS
    echo '<meta name="theme-color" content="#050816">';
echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>';
echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>';
echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>';
echo '<link rel="dns-prefetch" href="https://joi.dejoiy.tech">';
echo '<link rel="dns-prefetch" href="https://vendors.dejoiy.tech">';
echo '<meta property="og:title" content="DEJOIY - India\'s Next-Generation Marketplace">';
echo '<meta property="og:description" content="Shop, create, learn, sell and grow - all in one ecosystem.">';
echo '<meta property="og:type" content="website">';
echo '<meta name="twitter:card" content="summary_large_image">';
echo '<style>' . dejoiy_v3_design_system_css() . dejoiy_v3_section_css() . '</style>';

    // Wrapper
    echo '<div class="djv3">';

    // Header
    dejoiy_v3_render_header();

    // Hero
    dejoiy_v3_render_hero();

    // Categories
    dejoiy_v3_render_categories();

    // JOI AI
    dejoiy_v3_render_joi();

    // DEJOIY Worlds
    dejoiy_v3_render_worlds();

    // Deals Section
    echo '<section class="djv3-section djv3-deals djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-section__header"><div><h2 class="djv3-section__title">🏷️ Joy Deals</h2><p class="djv3-section__subtitle">Best prices, curated for you</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-festival-sale/')) . '" class="djv3-section__action">View All Deals →</a></div>';
    dejoiy_v3_shelf(array(
        'posts_per_page' => 8,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(array(
            'key' => '_sale_price',
            'compare' => 'EXISTS',
        )),
    ), 'deals', 8);
    echo '</div></section>';

    // Nexus Section
    $nexus_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 8,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('dejoiy-library', 'e-books', 'courses'),
        )),
    ));
    if (!empty($nexus_posts)) {
        echo '<section class="djv3-world-section djv3-world-section--nexus djv3-reveal"><div class="djv3-container">';
        echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">📚</span> DEJOIY Nexus</h2><p class="djv3-section__subtitle">Read. Learn. Grow. — Books, eBooks & courses.</p></div>';
        echo '<a href="' . esc_url(home_url('/dejoiy-library/?dejoiy_library=1')) . '" class="djv3-section__action">Enter Nexus →</a></div>';
        echo '<div class="djv3-scroll-row">';
        foreach ($nexus_posts as $p) {
            $product = wc_get_product($p->ID);
            if (!$product) continue;
            $url = function_exists('dejoiy_ecosystem_product_url') ? dejoiy_ecosystem_product_url($p->ID) : get_permalink($p->ID);
            $img = get_the_post_thumbnail_url($p->ID, 'woocommerce_thumbnail');
            if (!$img) {
                $gallery = $product->get_gallery_image_ids();
                if (!empty($gallery)) $img = wp_get_attachment_image_url($gallery[0], 'woocommerce_thumbnail');
            }
            ?>
            <article class="djv3-card djv3-card--relative" style="min-width:200px;max-width:240px;width:200px;">
                <a href="<?php echo esc_url($url); ?>">
                    <img class="djv3-card__img" src="<?php echo esc_url($img ?: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22260%22%3E%3Crect fill=%22%23F5F7FF%22 width=%22200%22 height=%22260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239AA3B5%22 font-family=%22sans-serif%22 font-size=%2213%22%3E📚%3C/text%3E%3C/svg%3E'); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy" width="200" height="260">
                    <div class="djv3-card__body">
                        <span class="djv3-badge djv3-badge--eco" style="margin-bottom:6px;">Nexus</span>
                        <h3 class="djv3-card__title"><?php echo esc_html($product->get_name()); ?></h3>
                        <div><?php echo wp_kses_post($product->get_price_html()); ?></div>
                    </div>
                </a>
            </article>
            <?php
        }
        echo '</div></div></section>';
    }

    // Custom Studio Section
    $studio_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 6,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('customized-products', 'custom-t-shirts'),
        )),
    ));
    echo '<section class="djv3-world-section djv3-world-section--studio djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">🎨</span> Create Something That\'s Yours</h2><p class="djv3-section__subtitle">Custom T-Shirts, Mugs, Caps & more — designed by you.</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-custom-studio/')) . '" class="djv3-section__action">Open Custom Studio →</a></div>';
    if (!empty($studio_posts)) {
        dejoiy_v3_shelf(array('post__in' => array_map(function($p) { return $p->ID; }, $studio_posts), 'orderby' => 'post__in'), 'studio', 6);
    } else {
        echo '<p style="color:var(--djv3-muted);text-align:center;padding:40px 0;">Custom Studio products coming soon. <a href="' . esc_url(home_url('/dejoiy-custom-studio/')) . '" style="color:var(--djv3-blue);font-weight:600;">Open Studio →</a></p>';
    }
    echo '</div></section>';

    // Services / Hire Section
    $service_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 6,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('services', 'digital-marketing', 'graphic-design', 'content-writing'),
        )),
    ));
    echo '<section class="djv3-world-section djv3-world-section--services djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">🤝</span> Need a Professional?</h2><p class="djv3-section__subtitle">Hire trusted experts for your business & personal needs.</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-services/')) . '" class="djv3-section__action">Explore Services →</a></div>';
    if (!empty($service_posts)) {
        echo '<div class="djv3-scroll-row">';
        foreach ($service_posts as $p) {
            $product = wc_get_product($p->ID);
            if (!$product) continue;
            $url = function_exists('dejoiy_ecosystem_product_url') ? dejoiy_ecosystem_product_url($p->ID) : get_permalink($p->ID);
            ?>
            <article class="djv3-service-card" style="min-width:260px;max-width:300px;width:280px;">
                <a href="<?php echo esc_url($url); ?>">
                    <div class="djv3-service-card__cat">Service</div>
                    <h3 class="djv3-service-card__title"><?php echo esc_html($product->get_name()); ?></h3>
                    <div class="djv3-service-card__footer">
                        <span class="djv3-service-card__price"><?php echo wp_strip_all_tags($product->get_price_html()); ?></span>
                        <?php if ($product->get_average_rating() > 0) : ?>
                            <span class="djv3-service-card__rating">★ <?php echo esc_html($product->get_average_rating()); ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p style="color:var(--djv3-muted);text-align:center;padding:40px 0;">Professional services launching soon. <a href="' . esc_url(home_url('/sell-on-dejoiy/')) . '" style="color:var(--djv3-blue);font-weight:600;">Become a Service Provider →</a></p>';
    }
    echo '</div></section>';

    // Renew / Refurbished Section
    $renew_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 6,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('renewed-refurbished', 'refurbished'),
        )),
    ));
    echo '<section class="djv3-world-section djv3-world-section--renew djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">♻️</span> Renew — Save More, Choose Smarter</h2><p class="djv3-section__subtitle">Certified refurbished tech at smarter prices.</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-refurbished/')) . '" class="djv3-section__action">Shop Renew →</a></div>';
    if (!empty($renew_posts)) {
        dejoiy_v3_shelf(array('post__in' => array_map(function($p) { return $p->ID; }, $renew_posts), 'orderby' => 'post__in'), 'renew', 6);
    } else {
        echo '<p style="color:var(--djv3-muted);text-align:center;padding:40px 0;">Refurbished products coming soon.</p>';
    }
    echo '</div></section>';

    // Trending / Recommended Section
    $trending = dejoiy_universe_get_products(array(
        'posts_per_page' => 8,
        'meta_key' => 'total_sales',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
    ));
    if (!empty($trending)) {
        echo '<section class="djv3-section djv3-reveal"><div class="djv3-container">';
        echo '<div class="djv3-section__header"><div><h2 class="djv3-section__title">🔥 Trending on DEJOIY</h2><p class="djv3-section__subtitle">What people are loving right now</p></div>';
        echo '<a href="' . esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')) . '" class="djv3-section__action">View All →</a></div>';
        dejoiy_v3_shelf(array('post__in' => array_map(function($p) { return $p->ID; }, $trending), 'orderby' => 'post__in'), 'trending', 8);
        echo '</div></section>';
    }

    // Trust Strip
    dejoiy_v3_render_trust();

    // Seller CTA
    dejoiy_v3_render_seller_cta();

    // Footer
    dejoiy_v3_render_footer();

    // Mobile Bottom Nav
    dejoiy_v3_render_bottom_nav();

    echo '</div>'; /* .djv3 */

    // JS
    dejoiy_v3_render_js();

    return ob_get_clean();
}
