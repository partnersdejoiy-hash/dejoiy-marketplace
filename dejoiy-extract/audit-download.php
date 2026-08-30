<?php
// Audit Download Page - Direct PHP file bypassing WordPress routing
// Place this in the WordPress root directory (public_html/)
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEJOIY Audit Report — Download</title>
    <meta name="description" content="DEJOIY.com vs Amazon.in UI/UX Audit Report">
    <meta name="robots" content="noindex, nofollow">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#0a0a0a;color:#e0e0e0;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow-x:hidden}
        .bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:60px 60px;z-index:0}
        .bg-glow{position:fixed;width:600px;height:600px;border-radius:50%;filter:blur(150px);opacity:.15;z-index:0}
        .bg-glow-1{top:-200px;left:-100px;background:#ff6b35}
        .bg-glow-2{bottom:-200px;right:-100px;background:#00c9a7}
        .dj-audit{position:relative;z-index:1;max-width:680px;width:90%;text-align:center;padding:40px 0}
        .dj-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,107,53,.1);border:1px solid rgba(255,107,53,.25);border-radius:50px;padding:8px 20px;font-size:13px;font-weight:600;color:#ff6b35;margin-bottom:32px;letter-spacing:.5px}
        .dj-badge::before{content:'';width:8px;height:8px;background:#ff6b35;border-radius:50%;animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}
        .dj-audit h1{font-size:42px;font-weight:800;line-height:1.15;margin-bottom:16px;background:linear-gradient(135deg,#fff 0%,#a0a0a0 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .dj-audit h1 span{background:linear-gradient(135deg,#ff6b35,#ff9a5c);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .dj-sub{font-size:17px;color:#888;margin-bottom:48px;line-height:1.6}
        .dj-sub strong{color:#ccc}
        .dj-stats{display:flex;justify-content:center;gap:32px;margin-bottom:48px;flex-wrap:wrap}
        .dj-stat{text-align:center}
        .dj-stat-num{font-size:32px;font-weight:800;color:#ff6b35}
        .dj-stat-label{font-size:12px;color:#666;text-transform:uppercase;letter-spacing:1px;margin-top:4px}
        .dj-dl-btn{display:inline-flex;align-items:center;gap:12px;background:linear-gradient(135deg,#ff6b35,#e85d26);color:#fff;font-size:18px;font-weight:700;padding:20px 48px;border:none;border-radius:16px;cursor:pointer;transition:all .3s;text-decoration:none;box-shadow:0 8px 32px rgba(255,107,53,.3);position:relative;overflow:hidden}
        .dj-dl-btn::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);transition:left .5s}
        .dj-dl-btn:hover::before{left:100%}
        .dj-dl-btn:hover{transform:translateY(-3px);box-shadow:0 12px 40px rgba(255,107,53,.45)}
        .dj-dl-btn:active{transform:translateY(0)}
        .dj-dl-btn svg{width:22px;height:22px}
        .dj-btn-row{display:flex;gap:12px;justify-content:center;margin-top:20px;flex-wrap:wrap}
        .dj-cp-btn{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#aaa;font-size:14px;font-weight:600;padding:14px 28px;border-radius:12px;cursor:pointer;transition:all .3s}
        .dj-cp-btn:hover{background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.2)}
        .dj-cp-btn svg{width:16px;height:16px}
        .dj-toast{position:fixed;bottom:40px;left:50%;transform:translateX(-50%) translateY(100px);background:#00c9a7;color:#000;font-weight:700;padding:14px 28px;border-radius:12px;font-size:14px;z-index:999;transition:transform .4s cubic-bezier(.34,1.56,.64,1);pointer-events:none}
        .dj-toast.show{transform:translateX(-50%) translateY(0)}
        .dj-preview{margin-top:48px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:24px;text-align:left;max-height:320px;overflow-y:auto}
        .dj-preview::-webkit-scrollbar{width:6px}
        .dj-preview::-webkit-scrollbar-track{background:transparent}
        .dj-preview::-webkit-scrollbar-thumb{background:#333;border-radius:3px}
        .dj-preview-title{font-size:11px;text-transform:uppercase;letter-spacing:2px;color:#555;margin-bottom:16px}
        .dj-preview pre{font-family:'JetBrains Mono','Fira Code',monospace;font-size:12px;line-height:1.7;color:#999;white-space:pre-wrap;word-break:break-word}
        .dj-preview pre .hl{color:#ff6b35;font-weight:600}
        .dj-preview pre .ok{color:#00c9a7}
        .dj-preview pre .err{color:#ff4757}
        .dj-footer{margin-top:48px;font-size:12px;color:#444}
        .dj-footer a{color:#666;text-decoration:none}
        .dj-footer a:hover{color:#ff6b35}
    </style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-glow bg-glow-1"></div>
<div class="bg-glow bg-glow-2"></div>
<div class="dj-audit">
    <div class="dj-badge">AUDIT REPORT READY</div>
    <h1>DEJOIY <span>vs</span> Amazon<br>UI/UX Audit</h1>
    <p class="dj-sub"><strong>8 categories</strong> analyzed &middot; <strong>50+ elements</strong> checked<br>Ready to download as <strong>.md</strong> file for AI prompt</p>
    <div class="dj-stats">
        <div class="dj-stat"><div class="dj-stat-num">8</div><div class="dj-stat-label">Categories</div></div>
        <div class="dj-stat"><div class="dj-stat-num">50+</div><div class="dj-stat-label">UI Elements</div></div>
        <div class="dj-stat"><div class="dj-stat-num">10</div><div class="dj-stat-label">Critical Errors</div></div>
    </div>
    <button class="dj-dl-btn" onclick="djDownload()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download Audit File (.md)
    </button>
    <div class="dj-btn-row">
        <button class="dj-cp-btn" onclick="djCopy()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Copy to Clipboard
        </button>
        <button class="dj-cp-btn" onclick="window.open('https://chatgpt.com','_blank')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Open ChatGPT
        </button>
    </div>
    <div class="dj-preview">
        <div class="dj-preview-title">&#128196; File Preview</div>
        <pre id="dj-pt"></pre>
    </div>
    <div class="dj-footer">Generated by <a href="https://dejoiy.com">DEJOIY</a> &middot; August 2026</div>
</div>
<div class="dj-toast" id="dj-toast">&#10003; Copied to clipboard!</div>
<script>
var djAudit='# DEJOIY.com vs Amazon.in \u2014 UI/UX Audit Prompt\n## Use this entire file as a prompt on ChatGPT / Claude / Any AI\n\n---\n\nYou are a senior UI/UX auditor. Compare **DEJOIY.com** (Indian e-commerce marketplace) against **Amazon.in** (industry standard).\n\nRate each section out of **10** and list specific **UI errors / broken elements / UX issues**.\n\n---\n\n## 1. HOMEPAGE\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Hero Banner / Slider | Present \u2014 Single product promo | Dynamic multi-category hero banners | /10 |\n| Navigation Bar | Present \u2014 Shop, Nexus, Books & courses | Mega-menu with departments | /10 |\n| Search Bar | **MISSING** on homepage | Always visible, auto-suggest, voice search | /10 |\n| Category Grid | Partial \u2014 Categories listed but no visual grid | Visual category tiles with icons | /10 |\n| Featured Products | **MISSING** \u2014 No featured carousel | "Best Sellers", "Today\'s Deals" sections | /10 |\n| Trust Badges | **MISSING** \u2014 No payment logos, COD info | UPI, COD, Free Delivery badges shown | /10 |\n| Footer | Basic \u2014 Missing key links | Comprehensive with 40+ links | /10 |\n\n---\n\n## 2. SEARCH & DISCOVERY\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Search Bar Visibility | Not prominent on all pages | Always visible in header | /10 |\n| Auto-suggest | **MISSING** | Real-time suggestions with images | /10 |\n| Filters | **MISSING** \u2014 No filter sidebar | Price, Brand, Rating, Prime, Size filters | /10 |\n| Sort Options | **MISSING** on /shop/ page | Sort by: Price, Popularity, Newest, Rating | /10 |\n| Results Layout | **Shop page 502 error** \u2014 Cannot load | Grid/List toggle with product cards | /10 |\n\n---\n\n## 3. PRODUCT PAGES\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Product Images | Single image visible | Multiple images, zoom, 360 view, video | /10 |\n| Price Display | Price with strikethrough original | MRP + deal price + EMI options | /10 |\n| Add to Cart Button | Present but styling unclear | Bold orange "Add to Cart" + "Buy Now" | /10 |\n| Product Description | Minimal text content | Rich bullet points, specs table, A+ content | /10 |\n| Reviews & Ratings | **MISSING** on product cards | Star ratings + review count visible | /10 |\n| Delivery Info | **MISSING** \u2014 No ETA shown | "FREE delivery by [date]" shown | /10 |\n| Size/Variant Selector | **MISSING** | Color swatches, size grid, dropdown | /10 |\n| Related Products | **MISSING** | AI-powered recommendations carousel | /10 |\n\n---\n\n## 4. CART & CHECKOUT\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Cart Page | Working \u2014 Shows items with SKU | Product image, name, price, quantity, savings | /10 |\n| Cart Design | Text-heavy, no product images | Clean card layout with thumbnails | /10 |\n| Quantity Selector | Present (+ / - buttons) | Dropdown or input field | /10 |\n| Coupon Field | Present | "Apply Coupon" with available coupons list | /10 |\n| Checkout Flow | Unclear \u2014 No guest checkout visible | Guest checkout + Login + New account | /10 |\n| Payment Options | **Not visible** \u2014 UPI, Cards, COD, EMI missing | Multiple payment options with icons | /10 |\n| Order Summary | Basic subtotal shown | Itemized: Subtotal + Delivery + Tax + Savings | /10 |\n\n---\n\n## 5. ACCOUNT / MY ACCOUNT\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Login/Register | Present \u2014 Clean login/register tabs | OTP login + Email/Password + Social login | /10 |\n| Dashboard | Basic \u2014 "My account" title only | Orders, Wishlist, Addresses, Payment methods | /10 |\n| Order History | **Not visible** | Complete order history with tracking | /10 |\n| Wishlist | **MISSING** | Save for Later + Wishlist | /10 |\n| Address Book | **MISSING** | Saved addresses with edit/delete | /10 |\n| Profile Edit | **MISSING** | Name, email, phone, password change | /10 |\n\n---\n\n## 6. MOBILE RESPONSIVENESS\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Mobile Menu | Hamburger menu present | Slide-out drawer with categories | /10 |\n| Touch Targets | Small buttons \u2014 hard to tap | 48px minimum touch targets | /10 |\n| Mobile Search | **MISSING** \u2014 No search on mobile | Always-visible search bar | /10 |\n| Page Load Speed | Slow \u2014 Multiple HTTP requests | Optimized with lazy loading | /10 |\n| Mobile Checkout | **Not tested** | One-tap checkout with saved info | /10 |\n\n---\n\n## 7. VISUAL DESIGN & BRANDING\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| Color Scheme | Inconsistent \u2014 Mixed colors | Consistent blue/orange brand palette | /10 |\n| Typography | Mixed fonts \u2014 Not cohesive | Clean, readable system fonts | /10 |\n| Logo Placement | DEJOIY logo visible in header | Amazon logo centered with search | /10 |\n| Spacing & Alignment | Inconsistent padding/margins | Uniform grid system | /10 |\n| White Space | **Too cluttered** in some areas | Clean, breathable layouts | /10 |\n\n---\n\n## 8. PERFORMANCE & TECHNICAL\n\n| Element | DEJOIY Status | Amazon Standard | Rating |\n|---------|--------------|-----------------|--------|\n| SSL/HTTPS | Working \u2014 Valid certificate | Always HTTPS | /10 |\n| Page Speed | **Slow** \u2014 Multiple redirects | < 3 seconds load time | /10 |\n| 404 Errors | /shop/ returns 502 Bad Gateway | Graceful error pages | /10 |\n| Broken Links | Some pages unreachable | All links functional | /10 |\n| SEO Meta Tags | Basic \u2014 Missing Open Graph | Rich snippets, meta tags, structured data | /10 |\n\n---\n\n## OVERALL RATING SUMMARY\n\n| Category | Score | Weight | Weighted |\n|----------|-------|--------|----------|\n| Homepage | /10 | 15% | |\n| Search & Discovery | /10 | 20% | |\n| Product Pages | /10 | 20% | |\n| Cart & Checkout | /10 | 15% | |\n| Account | /10 | 10% | |\n| Mobile | /10 | 10% | |\n| Visual Design | /10 | 5% | |\n| Performance | /10 | 5% | |\n| **TOTAL** | | **100%** | **/10** |\n\n---\n\n## TOP 10 CRITICAL UI ERRORS TO FIX\n\n1. **Search Bar Missing** \u2014 No search functionality visible on homepage\n2. **Shop Page Down** \u2014 /shop/ returns 502 Bad Gateway error\n3. **No Product Filters** \u2014 Users cannot filter/sort products\n4. **No Trust Badges** \u2014 Missing payment security indicators\n5. **No Delivery ETA** \u2014 No estimated delivery date shown\n6. **No Reviews/Ratings** \u2014 Product cards lack social proof\n7. **Cart Design Poor** \u2014 No images, text-heavy layout\n8. **Mobile Search Missing** \u2014 No search on mobile devices\n9. **Missing Wishlist** \u2014 No save-for-later functionality\n10. **No Order Tracking** \u2014 Users cannot track their orders\n\n---\n\n## RECOMMENDATIONS (Priority Order)\n\n### P0 \u2014 Fix Immediately\n- Add search bar to header (desktop + mobile)\n- Fix /shop/ page 502 error\n- Add trust badges (SSL, COD, UPI, Free Delivery)\n\n### P1 \u2014 Fix This Week\n- Add product filters and sort options\n- Add delivery ETA on product pages\n- Add review/rating system\n- Improve cart page design with product images\n\n### P2 \u2014 Fix This Month\n- Add wishlist functionality\n- Implement order tracking\n- Add "Customers Also Bought" recommendations\n- Optimize page load speed\n\n### P3 \u2014 Future Improvements\n- Add mega-menu navigation\n- Implement auto-suggest search\n- Add A+ content for product pages\n- Implement one-tap checkout\n\n---\n\n## HOW TO USE THIS PROMPT\n\n1. Copy this entire file\n2. Paste into ChatGPT / Claude / Gemini\n3. Add: "Rate DEJOIY.com out of 100 and give specific UI/UX feedback"\n4. The AI will analyze and give detailed suggestions\n\n---\n\n*Generated by Buffy (Codebuff) \u2014 August 2026*\n*Based on live analysis of dejoiy.com*';
var djEl=document.getElementById('dj-pt');
var djLines=djAudit.split('\n').slice(0,30);
djEl.innerHTML=djLines.map(function(l){if(l.indexOf('#')===0)return'<span class="hl">'+l.replace(/</g,'&lt;')+'</span>';if(l.indexOf('MISSING')>-1)return'<span class="err">'+l.replace(/</g,'&lt;')+'</span>';if(l.indexOf('Present')>-1||l.indexOf('Working')>-1)return'<span class="ok">'+l.replace(/</g,'&lt;')+'</span>';return l.replace(/</g,'&lt;')}).join('\n')+'\n... (click download for full file)';
function djDownload(){var b=new Blob([djAudit],{type:'text/markdown'});var u=URL.createObjectURL(b);var a=document.createElement('a');a.href=u;a.download='dejoiy-amazon-ui-audit.md';document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(u)}
function djCopy(){navigator.clipboard.writeText(djAudit).then(function(){var t=document.getElementById('dj-toast');t.classList.add('show');setTimeout(function(){t.classList.remove('show')},2500)})}
</script>
</body>
</html>
