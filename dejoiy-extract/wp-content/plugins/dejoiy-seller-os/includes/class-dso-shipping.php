<?php
/**
 * DSO Shipping - Fulfillment, Logistics, and Carrier Management for DEJOIY
 */
if (!defined('ABSPATH')) exit;

class DSO_Shipping {

    public function render() {
        ?>
        <div class="dso-page dso-shipping">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Fulfillment</span>
                        <span>/</span>
                        <span>Shipping Overview</span>
                    </div>
                    <h1 class="dso-page-title">Shipping & Logistics Hub</h1>
                    <p class="dso-page-subtitle">Configure automated courier dispatch, warehouse pickup points, and package specifications</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=shipping-tracking" class="dso-btn dso-btn-outline">Track Shipments</a>
                    <a href="?section=shipping-pickup" class="dso-btn dso-btn-primary">+ Add Pickup Address</a>
                </div>
            </div>

            <div class="dso-stats-row">
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Default Carrier</span>
                    <span class="dso-stat-val dso-text-primary">Shiprocket</span>
                    <span class="dso-stat-sub">Air & Surface Express</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Avg Dispatch SLA</span>
                    <span class="dso-stat-val dso-text-success">18 Hours</span>
                    <span class="dso-stat-sub">Same day fulfillment</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Coverage Reach</span>
                    <span class="dso-stat-val">29,000+</span>
                    <span class="dso-stat-sub">PIN codes nationwide</span>
                </div>
            </div>

            <div class="dso-grid-3 dso-mb-4">
                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>📦 Active Shipments</h3>
                        <p class="dso-text-muted">Monitor orders currently in transit across courier partner hubs.</p>
                        <a href="?section=shipments" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Active Shipments →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>📍 Warehouse Pickup Locations</h3>
                        <p class="dso-text-muted">Set verified primary and secondary dispatch hub addresses for daily courier pickup.</p>
                        <a href="?section=shipping-pickup" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">Manage Pickup Hubs →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>📐 Packaging Guidelines</h3>
                        <p class="dso-text-muted">Dimensions, volumetric weight formulas, and eco-friendly packing requirements.</p>
                        <a href="?section=shipping-packaging" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">Read Standards →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function shipments() {
        $o_handler = new DSO_Orders();
        $orders = $o_handler->get_orders(0, 50);
        ?>
        <div class="dso-page dso-shipments">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=shipping">Shipping</a>
                        <span>/</span>
                        <span>Shipments</span>
                    </div>
                    <h1 class="dso-page-title">Marketplace Shipments & Dispatches</h1>
                    <p class="dso-page-subtitle">Track air waybills, courier handovers, and estimated delivery dates</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Courier</th>
                                    <th>AWB Tracking</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><strong>#<?php echo esc_html($o['number']); ?></strong></td>
                                        <td>Shiprocket Express</td>
                                        <td><code>AWB-<?php echo substr(md5($o['id']), 0, 10); ?></code></td>
                                        <td><?php echo esc_html($o['city']); ?></td>
                                        <td><?php echo $o_handler->status_badge($o['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function tracking() {
        ?>
        <div class="dso-page dso-shipping-tracking">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=shipping">Shipping</a>
                        <span>/</span>
                        <span>Carrier Tracking</span>
                    </div>
                    <h1 class="dso-page-title">Live Carrier Tracking Lookup</h1>
                    <p class="dso-page-subtitle">Track real-time shipment milestones with integrated national carriers</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <div class="dso-form-group dso-mb-4" style="max-width: 480px;">
                        <label class="dso-label">Enter Air Waybill (AWB) / Tracking Number</label>
                        <div class="dso-flex dso-gap-2">
                            <input type="text" class="dso-input" placeholder="e.g., 1423859201" />
                            <button type="button" class="dso-btn dso-btn-primary">Track</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function delivery() {
        ?>
        <div class="dso-page dso-shipping-delivery">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=shipping">Shipping</a>
                        <span>/</span>
                        <span>Delivery Rates</span>
                    </div>
                    <h1 class="dso-page-title">Delivery Rates & Zone Matrix</h1>
                    <p class="dso-page-subtitle">Shipping fee structures across Intra-City, Regional, and National zones</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Shipping Zone</th>
                                    <th>Standard TAT</th>
                                    <th>Base Rate (Up to 500g)</th>
                                    <th>Additional 500g</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Local / Intra-City</strong></td>
                                    <td>24-48 Hours</td>
                                    <td>₹40.00</td>
                                    <td>₹25.00</td>
                                </tr>
                                <tr>
                                    <td><strong>Regional / State</strong></td>
                                    <td>2-3 Days</td>
                                    <td>₹65.00</td>
                                    <td>₹35.00</td>
                                </tr>
                                <tr>
                                    <td><strong>National / Metro to Metro</strong></td>
                                    <td>3-4 Days</td>
                                    <td>₹85.00</td>
                                    <td>₹45.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function pickup() {
        ?>
        <div class="dso-page dso-shipping-pickup">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=shipping">Shipping</a>
                        <span>/</span>
                        <span>Pickup Points</span>
                    </div>
                    <h1 class="dso-page-title">Warehouse Pickup Addresses</h1>
                    <p class="dso-page-subtitle">Locations where couriers arrive daily to collect dispatched shipments</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Primary Warehouse Hub</h3>
                    <span class="dso-badge dso-badge-green">Verified</span>
                </div>
                <div class="dso-card-body">
                    <p><strong>DEJOIY Central Distribution Warehouse</strong></p>
                    <p class="dso-text-muted">Sector 18, Udyog Vihar, Gurugram, Haryana - 122015<br/>Phone: +91 98765 43210 • Contact: Fulfillment Manager</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function packaging() {
        ?>
        <div class="dso-page dso-shipping-packaging">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=shipping">Shipping</a>
                        <span>/</span>
                        <span>Packaging</span>
                    </div>
                    <h1 class="dso-page-title">Packaging & Labeling Guidelines</h1>
                    <p class="dso-page-subtitle">Mandatory standards to prevent in-transit damages and carrier volumetric surcharges</p>
                </div>
            </div>

            <div class="dso-grid-2">
                <div class="dso-card">
                    <div class="dso-card-header"><h3 class="dso-card-title">Volumetric Weight Formula</h3></div>
                    <div class="dso-card-body">
                        <code>Volumetric Weight (kg) = (Length x Width x Height in cm) / 5000</code>
                        <p class="dso-mt-3 dso-text-muted">Carriers bill whichever is higher between Dead Weight and Volumetric Weight. Use snug boxes without excessive empty space.</p>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header"><h3 class="dso-card-title">Label Placement Rule</h3></div>
                    <div class="dso-card-body">
                        <ul class="dso-clean-list">
                            <li>✓ Print thermal label at minimum 203 DPI</li>
                            <li>✓ Affix flat on largest box surface without folding over edges</li>
                            <li>✓ Do not cover barcode with transparent tape</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
