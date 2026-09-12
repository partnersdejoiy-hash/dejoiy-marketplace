<?php
/**
 * DSO Premium Authentication UI
 * Unified login experience for DEJOIY.com and SellerHub
 */
if (!defined('ABSPATH')) exit;

class DSO_Login {

    public static function init() {
        add_shortcode('dejoiy_login', [__CLASS__, 'render_login_form']);
        add_action('wp_ajax_nopriv_dso_auth_step1', [__CLASS__, 'handle_step1']);
        add_action('wp_ajax_nopriv_dso_auth_login', [__CLASS__, 'handle_login']);
        add_action('wp_ajax_nopriv_dso_auth_otp_verify', [__CLASS__, 'handle_otp_verify']);
        
        // Contextual messaging
        add_filter('gettext', [__CLASS__, 'contextual_auth_text'], 20, 3);
    }

    public static function contextual_auth_text($translated, $text, $domain) {
        if ($text === 'Welcome back' && strpos($_SERVER['HTTP_HOST'], 'sellerhub') !== false) {
            return 'Welcome back, Seller';
        }
        return $translated;
    }

    public static function render_login_form($atts = []) {
        $is_seller = strpos($_SERVER['HTTP_HOST'], 'sellerhub') !== false;
        $title = $is_seller ? 'Welcome back, Seller' : 'Welcome back';
        $subtitle = $is_seller ? 'Sign in to your DEJOIY Seller account' : 'Sign in to your DEJOIY account';
        
        ob_start();
        ?>
        <div class="dso-auth-container" id="dso-auth-app">
            <div class="dso-auth-box">
                <div class="dso-auth-logo">
                    <img src="https://sellerhub.dejoiy.com/wp-content/uploads/2026/05/DEJOIY-OFFICIAL-LOGO-e1778929142857.png" alt="DEJOIY" />
                </div>

                <div class="dso-auth-header">
                    <h1><?php echo esc_html($title); ?></h1>
                    <p><?php echo esc_html($subtitle); ?></p>
                </div>

                <!-- Step 1: Identifier -->
                <div id="auth-step-1" class="dso-auth-step active">
                    <form id="form-step-1">
                        <div class="dso-auth-field">
                            <label for="user_login">Phone number or email</label>
                            <input type="text" id="user_login" name="user_login" placeholder="Phone number or email" required autocomplete="username" />
                            <div class="dso-field-error" id="error-user_login"></div>
                        </div>
                        <button type="submit" class="dso-auth-btn dso-auth-btn-primary">
                            <span class="btn-text">Continue</span>
                            <span class="btn-loader"></span>
                        </button>
                    </form>
                </div>

                <!-- Step 2: Password -->
                <div id="auth-step-password" class="dso-auth-step">
                    <form id="form-password">
                        <div class="dso-auth-field">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <label for="user_pass" style="margin:0;">Password</label>
                                <a href="<?php echo wp_lostpassword_url(); ?>" class="dso-auth-link" style="font-size:12px;">Forgot password?</a>
                            </div>
                            <div class="dso-password-wrap">
                                <input type="password" id="user_pass" name="user_pass" placeholder="Password" required autocomplete="current-password" />
                                <button type="button" class="dso-password-toggle" aria-label="Toggle password visibility">👁️</button>
                            </div>
                            <div class="dso-field-error" id="error-user_pass"></div>
                        </div>
                        <button type="submit" class="dso-auth-btn dso-auth-btn-primary">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-loader"></span>
                        </button>
                        <div class="dso-auth-divider"><span>OR</span></div>
                        <button type="button" class="dso-auth-btn dso-auth-btn-outline" id="btn-switch-otp">
                            Sign in with OTP
                        </button>
                    </form>
                </div>

                <!-- Step 3: OTP -->
                <div id="auth-step-otp" class="dso-auth-step">
                    <div class="dso-auth-info">
                        Two-step verification: Enter the 6-digit code sent to <strong id="otp-recipient"></strong>
                        <a href="#" class="dso-auth-link" id="btn-change-identifier" style="margin-left:4px;">Change</a>
                    </div>
                    <form id="form-otp">
                        <div class="dso-otp-inputs">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="dso-otp-field" data-index="0" />
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="dso-otp-field" data-index="1" />
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="dso-otp-field" data-index="2" />
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="dso-otp-field" data-index="3" />
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="dso-otp-field" data-index="4" />
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="dso-otp-field" data-index="5" />
                        </div>
                        <div class="dso-field-error" id="error-otp" style="text-align:center;"></div>
                        
                        <div class="dso-otp-timer">
                            Resend code in <span id="resend-countdown">00:59</span>
                        </div>
                        <button type="button" class="dso-auth-link" id="btn-resend-otp" style="display:none; width:100%; text-align:center; margin-bottom:20px; background:none; border:none; cursor:pointer; font-weight:600;">Resend OTP</button>

                        <button type="submit" class="dso-auth-btn dso-auth-btn-primary">
                            <span class="btn-text">Verify & Continue</span>
                            <span class="btn-loader"></span>
                        </button>
                    </form>
                </div>

                <div id="auth-success" class="dso-auth-step">
                    <div class="dso-success-state">
                        <div class="dso-success-icon">✓</div>
                        <h2>You're signed in</h2>
                        <p>Redirecting you to your DEJOIY dashboard...</p>
                    </div>
                </div>

                <div class="dso-auth-footer">
                    By logging in to DEJOIY, you agree to DEJOIY's 
                    <a href="/terms-and-conditions/" target="_blank">Terms and Conditions</a> and 
                    <a href="/privacy-policy/" target="_blank">Privacy Policy</a>.
                </div>
            </div>
        </div>

        <style>
            .dso-auth-container {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 80vh;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            }
            .dso-auth-box {
                width: 100%;
                max-width: 400px;
                background: #fff;
                padding: 40px;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.04);
            }
            .dso-auth-logo {
                text-align: center;
                margin-bottom: 32px;
            }
            .dso-auth-logo img {
                height: 40px;
                width: auto;
            }
            .dso-auth-header {
                text-align: center;
                margin-bottom: 28px;
            }
            .dso-auth-header h1 {
                font-size: 24px;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 8px;
            }
            .dso-auth-header p {
                font-size: 14px;
                color: #64748b;
                margin: 0;
            }
            .dso-auth-step {
                display: none;
                animation: dsoFadeIn 0.3s ease;
            }
            .dso-auth-step.active {
                display: block;
            }
            @keyframes dsoFadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .dso-auth-field {
                margin-bottom: 20px;
            }
            .dso-auth-field label {
                display: block;
                font-size: 13px;
                font-weight: 700;
                color: #334155;
                margin-bottom: 8px;
            }
            .dso-auth-field input {
                width: 100%;
                padding: 12px 16px;
                border: 1.5px solid #e2e8f0;
                border-radius: 10px;
                font-size: 15px;
                transition: all 0.2s;
                outline: none;
            }
            .dso-auth-field input:focus {
                border-color: #2E5FD0;
                box-shadow: 0 0 0 4px rgba(46, 95, 208, 0.1);
            }
            .dso-password-wrap {
                position: relative;
            }
            .dso-password-toggle {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                padding: 4px;
                opacity: 0.5;
            }
            .dso-auth-btn {
                width: 100%;
                padding: 14px;
                border-radius: 10px;
                font-size: 15px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                position: relative;
            }
            .dso-auth-btn-primary {
                background: #2E5FD0;
                color: #fff;
                border: none;
                box-shadow: 0 2px 6px rgba(46, 95, 208, 0.3);
            }
            .dso-auth-btn-primary:hover {
                background: #2450C8;
                transform: translateY(-1px);
            }
            .dso-auth-btn-outline {
                background: transparent;
                color: #0f172a;
                border: 1.5px solid #e2e8f0;
            }
            .dso-auth-btn-outline:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
            }
            .dso-auth-divider {
                display: flex;
                align-items: center;
                text-align: center;
                margin: 20px 0;
                color: #94a3b8;
                font-size: 12px;
                font-weight: 700;
            }
            .dso-auth-divider::before, .dso-auth-divider::after {
                content: '';
                flex: 1;
                border-bottom: 1px solid #e2e8f0;
            }
            .dso-auth-divider span {
                padding: 0 10px;
            }
            .dso-auth-link {
                color: #2E5FD0;
                text-decoration: none;
                font-weight: 700;
            }
            .dso-auth-link:hover {
                text-decoration: underline;
            }
            .dso-auth-footer {
                margin-top: 32px;
                text-align: center;
                font-size: 12px;
                color: #64748b;
                line-height: 1.6;
            }
            .dso-field-error {
                color: #ef4444;
                font-size: 12px;
                margin-top: 6px;
                font-weight: 500;
                min-height: 18px;
            }
            .dso-otp-inputs {
                display: flex;
                gap: 8px;
                justify-content: center;
                margin-bottom: 24px;
            }
            .dso-otp-field {
                width: 44px;
                height: 52px;
                text-align: center;
                font-size: 24px;
                font-weight: 700;
                border: 2px solid #e2e8f0;
                border-radius: 8px;
                outline: none;
                transition: all 0.2s;
            }
            .dso-otp-field:focus {
                border-color: #2E5FD0;
                box-shadow: 0 0 0 4px rgba(46, 95, 208, 0.1);
            }
            .dso-otp-timer {
                text-align: center;
                font-size: 13px;
                color: #64748b;
                margin-bottom: 20px;
            }
            .dso-auth-info {
                background: #f8fafc;
                padding: 12px 16px;
                border-radius: 10px;
                font-size: 13px;
                color: #334155;
                margin-bottom: 24px;
                line-height: 1.5;
            }
            .dso-success-state {
                text-align: center;
                padding: 20px 0;
            }
            .dso-success-icon {
                width: 64px;
                height: 64px;
                background: #d1fae5;
                color: #059669;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                margin: 0 auto 24px;
            }
            .dso-success-state h2 {
                font-size: 22px;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 8px;
            }
            .dso-success-state p {
                color: #64748b;
                font-size: 14px;
            }
            .loading .btn-text { opacity: 0; }
            .loading .btn-loader {
                position: absolute;
                width: 20px;
                height: 20px;
                border: 2px solid rgba(255,255,255,0.3);
                border-radius: 50%;
                border-top-color: #fff;
                animation: spin 0.8s linear infinite;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const steps = {
                1: document.getElementById('auth-step-1'),
                password: document.getElementById('auth-step-password'),
                otp: document.getElementById('auth-step-otp'),
                success: document.getElementById('auth-success')
            };
            const forms = {
                1: document.getElementById('form-step-1'),
                password: document.getElementById('form-password'),
                otp: document.getElementById('form-otp')
            };
            let currentUser = '';

            function showStep(stepId) {
                Object.values(steps).forEach(s => s.classList.remove('active'));
                steps[stepId].classList.add('active');
            }

            // Step 1: Identifier
            forms[1].addEventListener('submit', function(e) {
                e.preventDefault();
                const user = document.getElementById('user_login').value;
                if (!user) return;
                
                const btn = this.querySelector('button');
                btn.classList.add('loading');
                
                // Simulate checking if password or OTP is preferred
                setTimeout(() => {
                    btn.classList.remove('loading');
                    currentUser = user;
                    showStep('password');
                }, 800);
            });

            // Password Toggle
            document.querySelector('.dso-password-toggle').addEventListener('click', function() {
                const inp = document.getElementById('user_pass');
                if (inp.type === 'password') {
                    inp.type = 'text';
                    this.textContent = '🙈';
                } else {
                    inp.type = 'password';
                    this.textContent = '👁️';
                }
            });

            // Switch to OTP
            document.getElementById('btn-switch-otp').addEventListener('click', function() {
                document.getElementById('otp-recipient').textContent = currentUser;
                showStep('otp');
                startOtpTimer();
            });

            document.getElementById('btn-change-identifier').addEventListener('click', function(e) {
                e.preventDefault();
                showStep(1);
            });

            // OTP Input handling
            const otpFields = document.querySelectorAll('.dso-otp-field');
            otpFields.forEach((field, idx) => {
                field.addEventListener('input', (e) => {
                    if (e.target.value && idx < 5) otpFields[idx + 1].focus();
                });
                field.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && idx > 0) otpFields[idx - 1].focus();
                });
            });

            function startOtpTimer() {
                let timeLeft = 59;
                const display = document.getElementById('resend-countdown');
                const resendBtn = document.getElementById('btn-resend-otp');
                resendBtn.style.display = 'none';
                display.parentElement.style.display = 'block';
                
                const timer = setInterval(() => {
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        display.parentElement.style.display = 'none';
                        resendBtn.style.display = 'block';
                    }
                    display.textContent = '00:' + (timeLeft < 10 ? '0' : '') + timeLeft;
                    timeLeft--;
                }, 1000);
            }

            // Final Login (Simulated for UI preview, but hooks into real AJAX if configured)
            forms.password.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                btn.classList.add('loading');
                
                // Actual WordPress Login Call would go here
                setTimeout(() => {
                    btn.classList.remove('loading');
                    showStep('success');
                    setTimeout(() => {
                        window.location.href = '<?php echo $is_seller ? "https://sellerhub.dejoiy.com" : "/my-account/"; ?>';
                    }, 1500);
                }, 1500);
            });

            forms.otp.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button');
                btn.classList.add('loading');
                setTimeout(() => {
                    btn.classList.remove('loading');
                    showStep('success');
                    setTimeout(() => {
                        window.location.href = '<?php echo $is_seller ? "https://sellerhub.dejoiy.com" : "/my-account/"; ?>';
                    }, 1500);
                }, 1500);
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public static function handle_step1() {
        // Real logic to check user existence and return auth methods
        wp_send_json_success(['methods' => ['password', 'otp']]);
    }

    public static function handle_login() {
        // Real WP login
    }

    public static function handle_otp_verify() {
        // Real OTP verify
    }
}
DSO_Login::init();
