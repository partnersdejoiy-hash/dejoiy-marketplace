# DEJOIY.com vs Amazon.in — UI/UX Audit Prompt
## Use this entire file as a prompt on ChatGPT / Claude / Any AI

---

You are a senior UI/UX auditor. Compare **DEJOIY.com** (Indian e-commerce marketplace) against **Amazon.in** (industry standard).

Rate each section out of **10** and list specific **UI errors / broken elements / UX issues**.

---

## 🏠 1. HOMEPAGE

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Hero Banner / Slider | ✅ Present — Single product promo (Shopify Store Dev ₹9,999) | Dynamic multi-category hero banners | /10 |
| Navigation Bar | ✅ Present — Shop, Nexus, Books & courses, 📍 Delivery location, 🛒 Cart | Mega-menu with departments, search, account, orders | /10 |
| Search Bar | ❌ **MISSING on homepage** — No prominent search | Always visible, auto-suggest, voice search | /10 |
| Category Grid | ⚠️ Partial — Categories listed but no visual grid | Visual category tiles with icons | /10 |
| Featured Products | ❌ **No featured product carousel on homepage** | "Best Sellers", "Today's Deals" sections | /10 |
| Trust Badges | ❌ **MISSING** — No payment logos, security badges, COD info | UPI, COD, Free Delivery badges prominently shown | /10 |
| Footer | ⚠️ Basic — Missing key links | Comprehensive with 40+ links, social media, app download | /10 |

---

## 🔍 2. SEARCH & DISCOVERY

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Search Bar Visibility | ❌ Not prominent on all pages | Always visible in header | /10 |
| Auto-suggest / Autocomplete | ❌ **MISSING** | Real-time suggestions with images | /10 |
| Search Filters | ❌ **MISSING** — No filter sidebar on shop | Price, Brand, Rating, Prime, Size filters | /10 |
| Sort Options | ❌ **MISSING** on /shop/ page | Sort by: Price, Popularity, Newest, Rating | /10 |
| Search Results Layout | ❌ **Shop page 502 error** — Cannot load | Grid/List toggle with product cards | /10 |

---

## 🛒 3. PRODUCT PAGES

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Product Images | ⚠️ Single image visible (Nest Mini, Instax) | Multiple images, zoom, 360° view, video | /10 |
| Price Display | ✅ ₹ price with strikethrough original | MRP + deal price + EMI options | /10 |
| Add to Cart Button | ✅ Present but styling unclear | Bold orange "Add to Cart" + "Buy Now" | /10 |
| Product Description | ⚠️ Minimal text content | Rich bullet points, specs table, A+ content | /10 |
| Reviews & Ratings | ❌ **MISSING on product cards** | Star ratings + review count visible | /10 |
| Delivery Info | ❌ **MISSING** — No ETA shown on product page | "FREE delivery by [date]" shown | /10 |
| Size/Variant Selector | ❌ **MISSING** | Color swatches, size grid, dropdown | /10 |
| Related Products | ❌ **MISSING** "Customers also bought" | AI-powered recommendations carousel | /10 |

---

## 🧺 4. CART & CHECKOUT

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Cart Page | ✅ Working — Shows items with SKU numbers | Product image, name, price, quantity, savings | /10 |
| Cart Design | ⚠️ **Text-heavy, no product images in cart** | Clean card layout with thumbnails | /10 |
| Quantity Selector | ✅ Present (+ / - buttons) | Dropdown or input field | /10 |
| Coupon Field | ✅ Present ("Coupon: Clear shopping cart") | "Apply Coupon" with available coupons list | /10 |
| Checkout Flow | ⚠️ Unclear — No guest checkout option visible | Guest checkout + Login + New account | /10 |
| Payment Options | ❌ **Not visible** — UPI, Cards, COD, EMI missing | Multiple payment options with icons | /10 |
| Order Summary | ⚠️ Basic subtotal shown | Itemized: Subtotal + Delivery + Tax + Savings | /10 |

---

## 👤 5. ACCOUNT / MY ACCOUNT

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Login/Register | ✅ Present — Clean login/register tabs | OTP login + Email/Password + Social login | /10 |
| Dashboard | ⚠️ Basic — "My account" title only | Orders, Wishlist, Addresses, Payment methods | /10 |
| Order History | ❌ **Not visible** | Complete order history with tracking | /10 |
| Wishlist | ❌ **MISSING** | Save for Later + Wishlist | /10 |
| Address Book | ❌ **MISSING** | Saved addresses with edit/delete | /10 |
| Profile Edit | ❌ **MISSING** | Name, email, phone, password change | /10 |

---

## 📱 6. MOBILE RESPONSIVENESS

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Mobile Menu | ⚠️ Hamburger menu present | Slide-out drawer with categories | /10 |
| Touch Targets | ⚠️ Small buttons — hard to tap | 48px minimum touch targets | /10 |
| Mobile Search | ❌ **MISSING** — No search on mobile | Always-visible search bar | /10 |
| Page Load Speed | ⚠️ Slow — Multiple HTTP requests | Optimized with lazy loading | /10 |
| Mobile Checkout | ❌ **Not tested** | One-tap checkout with saved info | /10 |

---

## 🎨 7. VISUAL DESIGN & BRANDING

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| Color Scheme | ⚠️ Inconsistent — Mixed colors | Consistent blue/orange brand palette | /10 |
| Typography | ⚠️ Mixed fonts — Not cohesive | Clean, readable system fonts | /10 |
| Logo Placement | ✅ DEJOIY logo visible in header | Amazon logo centered with search | /10 |
| Spacing & Alignment | ⚠️ Inconsistent padding/margins | Uniform grid system | /10 |
| White Space | ❌ **Too cluttered** in some areas | Clean, breathable layouts | /10 |

---

## ⚡ 8. PERFORMANCE & TECHNICAL

| Element | DEJOIY Status | Amazon Standard | Rating |
|---------|--------------|-----------------|--------|
| SSL/HTTPS | ✅ Working — Valid certificate | Always HTTPS | /10 |
| Page Speed | ❌ **Slow** — Multiple redirects | < 3 seconds load time | /10 |
| 404 Errors | ⚠️ /shop/ returns 502 Bad Gateway | Graceful error pages | /10 |
| Broken Links | ⚠️ Some pages unreachable | All links functional | /10 |
| SEO Meta Tags | ⚠️ Basic — Missing Open Graph | Rich snippets, meta tags, structured data | /10 |

---

## 🏆 OVERALL RATING SUMMARY

| Category | Score | Weight | Weighted |
|----------|-------|--------|----------|
| Homepage | /10 | 15% | |
| Search & Discovery | /10 | 20% | |
| Product Pages | /10 | 20% | |
| Cart & Checkout | /10 | 15% | |
| Account | /10 | 10% | |
| Mobile | /10 | 10% | |
| Visual Design | /10 | 5% | |
| Performance | /10 | 5% | |
| **TOTAL** | | **100%** | **/10** |

---

## 🚨 TOP 10 CRITICAL UI ERRORS TO FIX

1. **❌ Search Bar Missing** — No search functionality visible on homepage
2. **❌ Shop Page Down** — /shop/ returns 502 Bad Gateway error
3. **❌ No Product Filters** — Users cannot filter/sort products
4. **❌ No Trust Badges** — Missing payment security indicators
5. **❌ No Delivery ETA** — No estimated delivery date shown
6. **❌ No Reviews/Ratings** — Product cards lack social proof
7. **❌ Cart Design Poor** — No images, text-heavy layout
8. **❌ Mobile Search Missing** — No search on mobile devices
9. **❌ Missing Wishlist** — No save-for-later functionality
10. **❌ No Order Tracking** — Users cannot track their orders

---

## 💡 RECOMMENDATIONS (Priority Order)

### P0 — Fix Immediately
- Add search bar to header (desktop + mobile)
- Fix /shop/ page 502 error
- Add trust badges (SSL, COD, UPI, Free Delivery)

### P1 — Fix This Week
- Add product filters and sort options
- Add delivery ETA on product pages
- Add review/rating system
- Improve cart page design with product images

### P2 — Fix This Month
- Add wishlist functionality
- Implement order tracking
- Add "Customers Also Bought" recommendations
- Optimize page load speed

### P3 — Future Improvements
- Add mega-menu navigation
- Implement auto-suggest search
- Add A+ content for product pages
- Implement one-tap checkout

---

## 📋 HOW TO USE THIS PROMPT

1. Copy this entire file
2. Paste into ChatGPT / Claude / Gemini
3. Add: "Rate DEJOIY.com out of 100 and give specific UI/UX feedback"
4. The AI will analyze and give detailed suggestions

---

*Generated by Buffy (Codebuff) — August 2026*
*Based on live analysis of dejoiy.com*
