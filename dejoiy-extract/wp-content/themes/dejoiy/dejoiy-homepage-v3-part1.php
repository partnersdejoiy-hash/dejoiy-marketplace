<?php
/**
 * DEJOIY Homepage V3 — Amazon-Level Super Marketplace Redesign
 * Part 1: Design System + Header + Mega Menu + Hero
 *
 * @package Dejoiy
 */
if (!defined('ABSPATH')) exit;

/**
 * Design System CSS — embedded inline for zero external requests
 */
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
