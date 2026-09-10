<?php
/**
 * DSO Learn - DEJOIY Seller University, Academy & Operational Standards
 */
if (!defined('ABSPATH')) exit;

class DSO_Learn {

    public function render() {
        ?>
        <div class="dso-page dso-learn">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Learn</span>
                        <span>/</span>
                        <span>Seller University</span>
                    </div>
                    <h1 class="dso-page-title">DEJOIY Seller University</h1>
                    <p class="dso-page-subtitle">Masterclasses, optimization checklists, and policy guides to grow your GMV 10x on DEJOIY</p>
                </div>
            </div>

            <div class="dso-grid-3">
                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-app-icon" style="font-size: 32px;">🎓</div>
                        <h3>Interactive Video Courses</h3>
                        <p class="dso-text-muted">Structured curriculum covering onboarding, listing architecture, logistics, and advertising.</p>
                        <a href="?section=learn-tutorials" class="dso-btn dso-btn-sm dso-btn-primary dso-mt-3">Watch Tutorials →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-app-icon" style="font-size: 32px;">📑</div>
                        <h3>Listing & SEO Playbook</h3>
                        <p class="dso-text-muted">Keyword research frameworks and category attribute mapping for 100% LQS compliance.</p>
                        <a href="?section=learn-guides" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">Read Guides →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-app-icon" style="font-size: 32px;">⚖️</div>
                        <h3>Marketplace Standards & Fee Cards</h3>
                        <p class="dso-text-muted">Transparent commission fee schedules, fulfillment SLAs, return policies, and seller code of conduct.</p>
                        <a href="?section=learn-policies" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Policies →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function knowledgebase() {
        ?>
        <div class="dso-page dso-kb">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=learn">Learn</a>
                        <span>/</span>
                        <span>Knowledgebase</span>
                    </div>
                    <h1 class="dso-page-title">Seller Knowledgebase & Help Articles</h1>
                    <p class="dso-page-subtitle">Frequently asked questions and operational walkthroughs</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <div class="dso-faq-list">
                        <div class="dso-faq-item dso-mb-4">
                            <h4>How and when do I receive payouts for delivered orders?</h4>
                            <p class="dso-text-muted">Payouts are settled directly to your registered bank account every Wednesday for all orders where the customer return window (7 days post-delivery) has expired.</p>
                        </div>
                        <div class="dso-faq-item dso-mb-4">
                            <h4>What is the Listing Quality Score (LQS) and how do I reach 90%+?</h4>
                            <p class="dso-text-muted">LQS measures how complete and attractive your listing is to buyers. Ensure your title is ≥25 characters, add 3+ clear square photos, specify MRP and selling price, and complete category-specific specifications.</p>
                        </div>
                        <div class="dso-faq-item">
                            <h4>How do customer returns and RTOs (Return to Origin) work?</h4>
                            <p class="dso-text-muted">In case of undelivered shipments (RTO), items are routed back to your warehouse without commission deduction. For customer returns, pickup is managed by DEJOIY integrated logistics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function guides() {
        ?>
        <div class="dso-page dso-guides">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=learn">Learn</a>
                        <span>/</span>
                        <span>Guides</span>
                    </div>
                    <h1 class="dso-page-title">Listing Optimization & SEO Guides</h1>
                    <p class="dso-page-subtitle">Actionable manuals designed to maximize search visibility and organic rank</p>
                </div>
            </div>

            <div class="dso-grid-2">
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">1. High-Conversion Title Architecture</h3>
                    </div>
                    <div class="dso-card-body">
                        <p>Follow the standard DEJOIY naming formula:</p>
                        <code>[Brand Name] + [Core Item Type] + [Key Specification/Material] + [Color / Size / Pack]</code>
                        <p class="dso-mt-2 dso-text-muted"><em>Example: DEJOIY Essentials Men's 100% Combed Cotton Oversized T-Shirt (Midnight Black, Large)</em></p>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">2. Professional Photography Standards</h3>
                    </div>
                    <div class="dso-card-body">
                        <ul class="dso-clean-list">
                            <li>✓ Minimum resolution: 1000 x 1000 pixels (enables hover zoom)</li>
                            <li>✓ Pure white or neutral background for primary cover image</li>
                            <li>✓ Include lifestyle in-context photos and close-ups of fabric/ports</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function tutorials() {
        ?>
        <div class="dso-page dso-tutorials">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=learn">Learn</a>
                        <span>/</span>
                        <span>Video Masterclasses</span>
                    </div>
                    <h1 class="dso-page-title">Video Tutorials & Masterclasses</h1>
                    <p class="dso-page-subtitle">Watch quick 3-minute video guides on operating your store like a pro</p>
                </div>
            </div>

            <div class="dso-grid-3">
                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-video-thumb dso-mb-3" style="width:100%; height:120px; background:#1e293b; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px;">
                            ▶️
                        </div>
                        <h4>Mastering Product Creation</h4>
                        <p class="dso-text-muted">Learn how to use smart category fields and variation matrices in 3 minutes.</p>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-video-thumb dso-mb-3" style="width:100%; height:120px; background:#1e293b; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px;">
                            ▶️
                        </div>
                        <h4>Order Dispatch & Packaging</h4>
                        <p class="dso-text-muted">How to generate shipping labels, print packing slips, and book courier pickups.</p>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-video-thumb dso-mb-3" style="width:100%; height:120px; background:#1e293b; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px;">
                            ▶️
                        </div>
                        <h4>Reconciling Payouts & Taxes</h4>
                        <p class="dso-text-muted">Understanding GST TCS deductions, commission credit notes, and weekly bank settlements.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function policies() {
        ?>
        <div class="dso-page dso-policies">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=learn">Learn</a>
                        <span>/</span>
                        <span>Policies & Fees</span>
                    </div>
                    <h1 class="dso-page-title">Marketplace Policies & Standards</h1>
                    <p class="dso-page-subtitle">Clear, enforceable operational standards for DEJOIY partner sellers</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <div class="dso-policy-section dso-mb-4">
                        <h3>1. Seller Service Level Agreement (SLA)</h3>
                        <p class="dso-text-muted">All orders must be packaged and marked 'Ready to Ship' within 24 business hours of order placement. Orders unfulfilled after 72 hours are subject to automated cancellation.</p>
                    </div>

                    <div class="dso-policy-section dso-mb-4">
                        <h3>2. Commission & Fee Schedule</h3>
                        <p class="dso-text-muted">DEJOIY operates on a transparent, category-based fee model ranging between 5% and 15%. No listing fees, no monthly software subscription fees.</p>
                    </div>

                    <div class="dso-policy-section">
                        <h3>3. Authenticity & Zero-Tolerance Counterfeit Policy</h3>
                        <p class="dso-text-muted">Sellers offering fake, counterfeit, or expired merchandise will have their accounts immediately suspended and payouts withheld in accordance with Indian e-commerce consumer protection regulations.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
