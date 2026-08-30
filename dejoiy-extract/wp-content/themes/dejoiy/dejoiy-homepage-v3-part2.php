<?php
/**
 * DEJOIY Homepage V3 — Part 2: Remaining CSS + Section Styles
 */
if (!defined('ABSPATH')) exit;

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
