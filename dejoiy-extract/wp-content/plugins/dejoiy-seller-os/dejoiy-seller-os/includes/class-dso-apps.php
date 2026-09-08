<?php
if (!defined('ABSPATH')) exit;
class DSO_Apps {
    public function render() {
        ?>
        <div class="dso-page dso-apps">
            <div class="dso-page-header"><div><h1>Apps & Services</h1><p>Integrations and tools to enhance your store</p></div></div>
            <div class="dso-apps-grid">
                <div class="dso-app-card">
                    <div class="dso-app-icon">🚚</div>
                    <h3>Shiprocket</h3>
                    <p>Automated shipping, tracking & COD management</p>
                    <span class="dso-badge dso-badge-green">Active</span>
                </div>
                <div class="dso-app-card">
                    <div class="dso-app-icon">📊</div>
                    <h3>Google Analytics</h3>
                    <p>Track store traffic and customer behavior</p>
                    <a href="#" class="dso-btn dso-btn-sm dso-btn-secondary">Configure</a>
                </div>
                <div class="dso-app-card">
                    <div class="dso-app-icon">📧</div>
                    <h3>Email Marketing</h3>
                    <p>Send promotional emails to your customers</p>
                    <a href="#" class="dso-btn dso-btn-sm dso-btn-secondary">Connect</a>
                </div>
                <div class="dso-app-card">
                    <div class="dso-app-icon">🔔</div>
                    <h3>Push Notifications</h3>
                    <p>Send browser push notifications for orders & offers</p>
                    <span class="dso-badge dso-badge-green">Active</span>
                </div>
                <div class="dso-app-card">
                    <div class="dso-app-icon">💬</div>
                    <h3>WhatsApp Business</h3>
                    <p>Send order updates via WhatsApp</p>
                    <a href="#" class="dso-btn dso-btn-sm dso-btn-secondary">Connect</a>
                </div>
                <div class="dso-app-card">
                    <div class="dso-app-icon">📱</div>
                    <h3>Social Media</h3>
                    <p>Auto-post products to social media</p>
                    <a href="#" class="dso-btn dso-btn-sm dso-btn-secondary">Connect</a>
                </div>
            </div>
        </div>
        <?php
    }
}
