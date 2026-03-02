<?php
/**
 * Plugin Name: Chatwoot Widget By Multilat
 * Plugin URI:  https://multilat.xyz
 * Description: Adds The Chatwoot Live Chat Widget To Your WordPress Site With Defer Loading, Dark Mode, Position, Locale, and Toggle Support.
 * Version:     1.2.0
 * Author:      Multilat
 * Author URI:  https://multilat.xyz
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Settings
function chatwoot_widget_register_settings() {
    register_setting('chatwoot_widget_options', 'chatwoot_widget_settings', [
        'type'              => 'array',
        'sanitize_callback' => 'chatwoot_widget_sanitize_settings',
        'default'           => [
            'enabled'        => '0',
            'base_url'       => '',
            'website_token'  => '',
            'dark_mode'      => 'light',
            'defer_load'     => '1',
            'locale'         => '',
            'widget_type'    => 'standard',
            'position'       => 'right',
            'launcher_text'  => '',
            'logged_in_only' => '0',
        ],
    ]);
}
add_action('admin_init', 'chatwoot_widget_register_settings');

// Sanitize Settings
function chatwoot_widget_sanitize_settings($input) {
    $sanitized = [];
    $sanitized['enabled']       = isset($input['enabled']) ? '1' : '0';
    $sanitized['base_url']      = esc_url_raw(rtrim($input['base_url'] ?? '', '/'));
    $sanitized['website_token'] = sanitize_text_field($input['website_token'] ?? '');
    $sanitized['dark_mode']     = in_array($input['dark_mode'] ?? '', ['light', 'auto'], true)
                                  ? $input['dark_mode'] : 'light';
    $sanitized['defer_load']    = isset($input['defer_load']) ? '1' : '0';
    $sanitized['locale']        = sanitize_text_field($input['locale'] ?? '');
    $sanitized['widget_type']   = in_array($input['widget_type'] ?? '', ['standard', 'expanded_bubble'], true)
                                  ? $input['widget_type'] : 'standard';
    $sanitized['position']      = in_array($input['position'] ?? '', ['left', 'right'], true)
                                  ? $input['position'] : 'right';
    $sanitized['launcher_text'] = sanitize_text_field($input['launcher_text'] ?? '');
    $sanitized['logged_in_only'] = isset($input['logged_in_only']) ? '1' : '0';
    return $sanitized;
}

// Add Admin Menu
function chatwoot_widget_admin_menu() {
    add_options_page(
        'Chatwoot Widget',
        'Chatwoot Widget',
        'manage_options',
        'chatwoot-widget',
        'chatwoot_widget_settings_page'
    );
}
add_action('admin_menu', 'chatwoot_widget_admin_menu');

// Enqueue Font Awesome on Our Settings Page
function chatwoot_widget_admin_assets($hook) {
    if ($hook !== 'settings_page_chatwoot-widget') {
        return;
    }
    wp_enqueue_style(
        'chatwoot-widget-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );
}
add_action('admin_enqueue_scripts', 'chatwoot_widget_admin_assets');

// Settings Page
function chatwoot_widget_settings_page() {
    $settings = get_option('chatwoot_widget_settings', []);
    $defaults = [
        'enabled'        => '0',
        'base_url'       => '',
        'website_token'  => '',
        'dark_mode'      => 'light',
        'defer_load'     => '1',
        'locale'         => '',
        'widget_type'    => 'standard',
        'position'       => 'right',
        'launcher_text'  => '',
        'logged_in_only' => '0',
    ];
    $settings = wp_parse_args($settings, $defaults);
    ?>
    <style>
    /* CSS Variables */
    .mcw-admin {
        --mcw-primary: #334EFC;
        --mcw-primary-hover: #2a42d9;
        --mcw-accent: #00EFAE;
        --mcw-accent-hover: #00d99e;
        --mcw-danger: #ef4444;
        --mcw-warning: #f59e0b;
        --mcw-info: #3b82f6;
        --mcw-success: #10b981;
        --mcw-dark: #1e293b;
        --mcw-gray-300: #cbd5e1;
        --mcw-gray-400: #94a3b8;
        --mcw-gray: #64748b;
        --mcw-light: #f8fafc;
        --mcw-border: #e2e8f0;
        --mcw-white: #ffffff;
        --mcw-radius: 12px;
        --mcw-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
        --mcw-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        --mcw-gradient: linear-gradient(135deg, #00EFAE 0%, #334EFC 100%);
        --mcw-transition: all 0.2s ease;

        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        color: var(--mcw-dark);
        line-height: 1.5;
        max-width: 900px;
        margin: 20px auto;
    }

    /* Header */
    .mcw-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        background: var(--mcw-white);
        border: 1px solid var(--mcw-border);
        border-radius: var(--mcw-radius) var(--mcw-radius) 0 0;
        border-bottom: none;
    }

    .mcw-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mcw-header-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--mcw-dark);
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mcw-header-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 600;
        background: var(--mcw-gradient);
        color: white;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .mcw-header-meta {
        font-size: 13px;
        color: var(--mcw-gray);
        margin-top: 4px;
    }

    .mcw-header-meta a {
        color: var(--mcw-primary);
        text-decoration: none;
    }

    .mcw-header-meta a:hover {
        text-decoration: underline;
    }

    /* Gradient Save Button */
    .mcw-btn-gradient {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        background: var(--mcw-gradient);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--mcw-transition);
        text-decoration: none;
    }

    .mcw-btn-gradient:hover {
        background: linear-gradient(135deg, #00d99e 0%, #2a42d9 100%);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(51, 78, 252, 0.3);
    }

    /* Cards */
    .mcw-card {
        background: var(--mcw-white);
        border: 1px solid var(--mcw-border);
        border-radius: var(--mcw-radius);
        box-shadow: var(--mcw-shadow);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .mcw-card:first-of-type {
        border-radius: 0 0 var(--mcw-radius) var(--mcw-radius);
        margin-top: 0;
    }

    .mcw-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--mcw-border);
        background: var(--mcw-light);
    }

    .mcw-card-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 16px;
        color: white;
        flex-shrink: 0;
    }

    .mcw-card-header h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--mcw-dark);
        margin: 0;
        padding: 0;
    }

    .mcw-card-body {
        padding: 20px;
    }

    /* Grid System */
    .mcw-grid {
        display: grid;
        gap: 20px;
    }

    .mcw-grid-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    @media (max-width: 768px) {
        .mcw-grid-2 {
            grid-template-columns: 1fr;
        }

        .mcw-header {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }

        .mcw-feature-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Form Elements */
    .mcw-form-group {
        margin-bottom: 20px;
    }

    .mcw-form-group:last-child {
        margin-bottom: 0;
    }

    .mcw-form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--mcw-dark);
        margin-bottom: 8px;
    }

    .mcw-input {
        width: 100% !important;
        max-width: 100% !important;
        height: 40px !important;
        padding: 0 14px !important;
        font-size: 14px !important;
        line-height: 38px !important;
        border: 1px solid var(--mcw-border) !important;
        border-radius: 8px !important;
        background: white !important;
        transition: var(--mcw-transition);
        box-sizing: border-box !important;
        color: var(--mcw-dark) !important;
    }

    .mcw-input:focus {
        outline: none;
        border-color: var(--mcw-primary);
        box-shadow: 0 0 0 3px rgba(51, 78, 252, 0.1);
    }

    /* Select Dropdown — Custom Caret */
    select.mcw-input {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        width: 100% !important;
        max-width: 100% !important;
        height: 40px !important;
        padding: 0 44px 0 14px !important;
        font-size: 14px !important;
        line-height: 38px !important;
        border: 1px solid var(--mcw-border) !important;
        border-radius: 8px !important;
        background-color: white !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        background-size: 18px !important;
        cursor: pointer;
        box-sizing: border-box !important;
    }

    .mcw-help-text {
        font-size: 12px;
        color: var(--mcw-gray);
        margin-top: 6px;
        margin-bottom: 0;
    }

    /* Feature Card Grid */
    .mcw-feature-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .mcw-feature-card {
        background: var(--mcw-light);
        border: 1px solid var(--mcw-border);
        border-radius: 10px;
        padding: 14px 20px;
        cursor: pointer;
        transition: var(--mcw-transition);
    }

    .mcw-feature-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .mcw-feature-card-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .mcw-feature-card-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
    }

    .mcw-feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: var(--mcw-transition);
    }

    .mcw-feature-icon i {
        font-size: 18px;
        transition: color 0.2s;
    }

    .mcw-feature-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--mcw-dark);
        margin-bottom: 2px;
    }

    .mcw-feature-desc {
        font-size: 12px;
        color: var(--mcw-gray);
    }

    /* Toggle Switch */
    .mcw-toggle {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        margin-left: 16px;
        flex-shrink: 0;
    }

    .mcw-toggle-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .mcw-toggle-slider {
        width: 56px;
        height: 30px;
        background: var(--mcw-gray-300);
        border-radius: 9999px;
        transition: var(--mcw-transition);
        position: relative;
    }

    .mcw-toggle-slider::after {
        content: "";
        position: absolute;
        width: 24px;
        height: 24px;
        background: var(--mcw-white);
        border-radius: 50%;
        top: 3px;
        left: 3px;
        transition: var(--mcw-transition);
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .mcw-toggle-input:checked + .mcw-toggle-slider {
        background: var(--mcw-gradient);
    }

    .mcw-toggle-input:checked + .mcw-toggle-slider::after {
        transform: translateX(26px);
    }

    /* Info Box */
    .mcw-info-box {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: var(--mcw-radius);
        padding: 16px;
    }

    .mcw-info-box h4 {
        font-size: 14px;
        font-weight: 600;
        color: #0369a1;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mcw-info-box p {
        font-size: 13px;
        color: #0c4a6e;
        margin: 0 0 8px 0;
    }

    .mcw-info-box p:last-child {
        margin-bottom: 0;
    }

    .mcw-info-box code {
        background: rgba(14, 165, 233, 0.1);
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
    }

    .mcw-info-box pre {
        background: #e0f2fe;
        padding: 10px 14px;
        border-radius: 8px;
        margin: 8px 0;
        overflow-x: auto;
        font-size: 12px;
        line-height: 1.6;
    }

    .mcw-info-box pre code {
        background: none;
        padding: 0;
    }

    /* Footer */
    .mcw-footer {
        padding: 16px 20px;
        text-align: center;
        font-size: 13px;
        color: var(--mcw-gray);
    }

    .mcw-footer a {
        color: var(--mcw-primary);
        text-decoration: none;
    }

    .mcw-footer a:hover {
        text-decoration: underline;
    }
    </style>

    <div class="mcw-admin">
        <!-- Header -->
        <div class="mcw-header">
            <div class="mcw-header-left">
                <div>
                    <div class="mcw-header-title">
                        <i class="fas fa-comments" style="color: var(--mcw-primary);"></i>
                        Chatwoot Widget
                        <span class="mcw-header-badge">v1.2.0</span>
                    </div>
                    <div class="mcw-header-meta">By <a href="https://multilat.xyz" target="_blank">Multilat</a></div>
                </div>
            </div>
            <button type="submit" form="mcw-settings-form" class="mcw-btn-gradient">
                <i class="fas fa-save"></i>
                <span>Save Settings</span>
            </button>
        </div>

        <form method="post" action="options.php" id="mcw-settings-form">
            <?php settings_fields('chatwoot_widget_options'); ?>

            <!-- Connection Settings -->
            <div class="mcw-card">
                <div class="mcw-card-header">
                    <span class="mcw-card-icon" style="background: linear-gradient(135deg, #00EFAE 0%, #334EFC 100%);">
                        <i class="fas fa-plug"></i>
                    </span>
                    <h3>Connection Settings</h3>
                </div>
                <div class="mcw-card-body">
                    <div class="mcw-grid mcw-grid-2">
                        <div class="mcw-form-group">
                            <label for="mcw_base_url">Base URL</label>
                            <input type="url" id="mcw_base_url" name="chatwoot_widget_settings[base_url]"
                                   value="<?php echo esc_attr($settings['base_url']); ?>"
                                   class="mcw-input" placeholder="https://chat.example.com">
                            <p class="mcw-help-text">Your Chatwoot Instance URL (Without Trailing Slash)</p>
                        </div>

                        <div class="mcw-form-group">
                            <label for="mcw_website_token">Website Token</label>
                            <input type="text" id="mcw_website_token" name="chatwoot_widget_settings[website_token]"
                                   value="<?php echo esc_attr($settings['website_token']); ?>"
                                   class="mcw-input" placeholder="xxxxxxxxxxxxxxxxxxxxxxxx">
                            <p class="mcw-help-text">Found In Chatwoot &gt; Settings &gt; Inboxes &gt; Configuration</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget Features -->
            <div class="mcw-card">
                <div class="mcw-card-header">
                    <span class="mcw-card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                        <i class="fas fa-sliders-h"></i>
                    </span>
                    <h3>Widget Features</h3>
                </div>
                <div class="mcw-card-body">
                    <div class="mcw-feature-grid">
                        <?php
                        $features = [
                            [
                                'name'        => 'enabled',
                                'label'       => 'Enable Widget',
                                'icon'        => 'fa-comments',
                                'desc'        => 'Show Chatwoot Widget on Your Site',
                                'color'       => '#00EFAE',
                                'check_value' => '1',
                                'hidden_val'  => null,
                            ],
                            [
                                'name'        => 'dark_mode',
                                'label'       => 'Auto Dark Mode',
                                'icon'        => 'fa-moon',
                                'desc'        => 'Follow User System Theme Preference',
                                'color'       => '#8b5cf6',
                                'check_value' => 'auto',
                                'hidden_val'  => 'light',
                            ],
                            [
                                'name'        => 'defer_load',
                                'label'       => 'Defer Loading',
                                'icon'        => 'fa-bolt',
                                'desc'        => 'Load Widget After Page Is Fully Loaded',
                                'color'       => '#f59e0b',
                                'check_value' => '1',
                                'hidden_val'  => null,
                            ],
                            [
                                'name'        => 'logged_in_only',
                                'label'       => 'Logged-In Only',
                                'icon'        => 'fa-lock',
                                'desc'        => 'Only Show Widget To Logged-In Users',
                                'color'       => '#ef4444',
                                'check_value' => '1',
                                'hidden_val'  => null,
                            ],
                        ];

                        foreach ($features as $feature):
                            $currentVal = $settings[$feature['name']] ?? '';
                            $isEnabled = $currentVal === $feature['check_value'];
                            $iconBg = $isEnabled
                                ? 'linear-gradient(135deg, ' . $feature['color'] . '33, ' . $feature['color'] . '1a)'
                                : '#f1f5f9';
                            $iconColor = $isEnabled ? $feature['color'] : '#94a3b8';
                        ?>
                        <div class="mcw-feature-card" onclick="mcwToggleFeature(this)">
                            <div class="mcw-feature-card-inner">
                                <div class="mcw-feature-card-left">
                                    <div class="mcw-feature-icon" style="background: <?php echo $iconBg; ?>;">
                                        <i class="fas <?php echo $feature['icon']; ?>" style="color: <?php echo $iconColor; ?>;"></i>
                                    </div>
                                    <div>
                                        <div class="mcw-feature-label"><?php echo esc_html($feature['label']); ?></div>
                                        <div class="mcw-feature-desc"><?php echo esc_html($feature['desc']); ?></div>
                                    </div>
                                </div>
                                <label class="mcw-toggle" onclick="event.stopPropagation()">
                                    <?php if ($feature['hidden_val'] !== null): ?>
                                    <input type="hidden" name="chatwoot_widget_settings[<?php echo $feature['name']; ?>]" value="<?php echo esc_attr($feature['hidden_val']); ?>">
                                    <?php endif; ?>
                                    <input type="checkbox" class="mcw-toggle-input" name="chatwoot_widget_settings[<?php echo $feature['name']; ?>]"
                                           value="<?php echo esc_attr($feature['check_value']); ?>"
                                           <?php echo $isEnabled ? 'checked' : ''; ?>
                                           onchange="mcwUpdateFeatureCard(this)">
                                    <span class="mcw-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Widget Appearance -->
            <div class="mcw-card">
                <div class="mcw-card-header">
                    <span class="mcw-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-paint-brush"></i>
                    </span>
                    <h3>Widget Appearance</h3>
                </div>
                <div class="mcw-card-body">
                    <div class="mcw-grid mcw-grid-2">
                        <div class="mcw-form-group">
                            <label for="mcw_locale">Locale</label>
                            <select id="mcw_locale" name="chatwoot_widget_settings[locale]" class="mcw-input">
                                <option value="" <?php selected($settings['locale'], ''); ?>>Auto (Browser Default)</option>
                                <option value="ar" <?php selected($settings['locale'], 'ar'); ?>>Arabic</option>
                                <option value="bn" <?php selected($settings['locale'], 'bn'); ?>>Bengali</option>
                                <option value="zh" <?php selected($settings['locale'], 'zh'); ?>>Chinese</option>
                                <option value="nl" <?php selected($settings['locale'], 'nl'); ?>>Dutch</option>
                                <option value="en" <?php selected($settings['locale'], 'en'); ?>>English</option>
                                <option value="fr" <?php selected($settings['locale'], 'fr'); ?>>French</option>
                                <option value="de" <?php selected($settings['locale'], 'de'); ?>>German</option>
                                <option value="hi" <?php selected($settings['locale'], 'hi'); ?>>Hindi</option>
                                <option value="it" <?php selected($settings['locale'], 'it'); ?>>Italian</option>
                                <option value="ja" <?php selected($settings['locale'], 'ja'); ?>>Japanese</option>
                                <option value="ko" <?php selected($settings['locale'], 'ko'); ?>>Korean</option>
                                <option value="pt" <?php selected($settings['locale'], 'pt'); ?>>Portuguese</option>
                                <option value="ru" <?php selected($settings['locale'], 'ru'); ?>>Russian</option>
                                <option value="es" <?php selected($settings['locale'], 'es'); ?>>Spanish</option>
                                <option value="tr" <?php selected($settings['locale'], 'tr'); ?>>Turkish</option>
                            </select>
                            <p class="mcw-help-text">Language For The Chat Widget Interface</p>
                        </div>

                        <div class="mcw-form-group">
                            <label for="mcw_widget_type">Widget Type</label>
                            <select id="mcw_widget_type" name="chatwoot_widget_settings[widget_type]" class="mcw-input">
                                <option value="standard" <?php selected($settings['widget_type'], 'standard'); ?>>Standard (Icon Only)</option>
                                <option value="expanded_bubble" <?php selected($settings['widget_type'], 'expanded_bubble'); ?>>Expanded Bubble (Icon + Text)</option>
                            </select>
                            <p class="mcw-help-text">Standard Shows A Chat Icon. Expanded Bubble Shows An Icon With Text Label.</p>
                        </div>

                        <div class="mcw-form-group">
                            <label for="mcw_position">Position</label>
                            <select id="mcw_position" name="chatwoot_widget_settings[position]" class="mcw-input">
                                <option value="right" <?php selected($settings['position'], 'right'); ?>>Right</option>
                                <option value="left" <?php selected($settings['position'], 'left'); ?>>Left</option>
                            </select>
                            <p class="mcw-help-text">Which Side of The Screen The Widget Appears on</p>
                        </div>

                        <div class="mcw-form-group">
                            <label for="mcw_launcher_text">Launcher Text</label>
                            <input type="text" id="mcw_launcher_text" name="chatwoot_widget_settings[launcher_text]"
                                   value="<?php echo esc_attr($settings['launcher_text']); ?>"
                                   class="mcw-input" placeholder="Chat With Us">
                            <p class="mcw-help-text">Custom Text Shown on The Expanded Bubble. Leave Empty For Default.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Custom Toggle Button -->
        <div class="mcw-card">
            <div class="mcw-card-header">
                <span class="mcw-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                    <i class="fas fa-mouse-pointer"></i>
                </span>
                <h3>Custom Toggle Button</h3>
            </div>
            <div class="mcw-card-body">
                <div class="mcw-info-box">
                    <h4><i class="fas fa-info-circle"></i> Open or Close The Widget From Any Element</h4>
                    <p>No extra JavaScript is needed. Use either method below:</p>

                    <p style="margin-top: 12px;"><strong>Method 1:</strong> Link With <code>#chatbox-toggle</code> (Recommended For Links)</p>
                    <pre><code>&lt;a href="#chatbox-toggle"&gt;Chat With Us&lt;/a&gt;
&lt;a href="#chatbox-toggle"&gt;Need Help?&lt;/a&gt;</code></pre>

                    <p><strong>Method 2:</strong> CSS Class <code>chatbox-toggle</code> (For Buttons and Elements)</p>
                    <pre><code>&lt;button class="chatbox-toggle"&gt;Chat With Us&lt;/button&gt;
&lt;div class="chatbox-toggle"&gt;Support&lt;/div&gt;</code></pre>

                    <p>Both methods work with page builders — use <code>#chatbox-toggle</code> as the link URL
                    in menus, or add <code>chatbox-toggle</code> as a CSS class to any button or element
                    in Elementor, Gutenberg, WPBakery, etc.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mcw-footer">
            <strong>Chatwoot Widget</strong> v1.2.0
            &bull;
            Made With <span style="color: #ef4444;">&hearts;</span> By
            <a href="https://multilat.xyz" target="_blank">Multilat</a>
        </div>
    </div>

    <script>
    // Toggle Feature Card on Click
    function mcwToggleFeature(card) {
        var checkbox = card.querySelector('.mcw-toggle-input');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            mcwUpdateFeatureCard(checkbox);
        }
    }

    // Update Feature Card Icon Colors Based on Toggle State
    function mcwUpdateFeatureCard(checkbox) {
        var card = checkbox.closest('.mcw-feature-card');
        if (!card) return;
        var iconEl = card.querySelector('.mcw-feature-icon');
        var iconI = card.querySelector('.mcw-feature-icon i');
        if (!iconEl || !iconI) return;

        if (checkbox.checked) {
            var color = iconI.getAttribute('data-color') || iconI.style.color || '#94a3b8';
            iconEl.style.background = 'linear-gradient(135deg, ' + color + '33, ' + color + '1a)';
            iconI.style.color = color;
        } else {
            if (!iconI.getAttribute('data-color') && iconI.style.color && iconI.style.color !== 'rgb(148, 163, 184)') {
                iconI.setAttribute('data-color', iconI.style.color);
            }
            iconEl.style.background = '#f1f5f9';
            iconI.style.color = '#94a3b8';
        }
    }

    // Store Initial Colors on Load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.mcw-feature-card .mcw-feature-icon i').forEach(function(iconI) {
            if (iconI.style.color && iconI.style.color !== '#94a3b8' && iconI.style.color !== 'rgb(148, 163, 184)') {
                iconI.setAttribute('data-color', iconI.style.color);
            }
        });
    });
    </script>
    <?php
}

// Inject Widget Script In Frontend
function chatwoot_widget_enqueue_script() {
    if (is_admin()) {
        return;
    }

    $settings = get_option('chatwoot_widget_settings', []);

    if (empty($settings['enabled']) || $settings['enabled'] !== '1') {
        return;
    }

    if (empty($settings['base_url']) || empty($settings['website_token'])) {
        return;
    }

    // Logged-In Users Only Check
    if (!empty($settings['logged_in_only']) && $settings['logged_in_only'] === '1' && !is_user_logged_in()) {
        return;
    }

    $base_url      = esc_url($settings['base_url']);
    $website_token = esc_js($settings['website_token']);
    $dark_mode     = esc_js($settings['dark_mode'] ?? 'light');
    $defer         = !empty($settings['defer_load']) && $settings['defer_load'] === '1';
    $locale        = esc_js($settings['locale'] ?? '');
    $widget_type   = esc_js($settings['widget_type'] ?? 'standard');
    $position      = esc_js($settings['position'] ?? 'right');
    $launcher_text = esc_js($settings['launcher_text'] ?? '');

    // Build chatwootSettings Object
    $chatwoot_settings = "darkMode: '{$dark_mode}'";
    if (!empty($locale)) {
        $chatwoot_settings .= ", locale: '{$locale}'";
    }
    if ($widget_type !== 'standard') {
        $chatwoot_settings .= ", type: '{$widget_type}'";
    }
    if ($position !== 'right') {
        $chatwoot_settings .= ", position: '{$position}'";
    }
    if (!empty($launcher_text)) {
        $chatwoot_settings .= ", launcherTitle: '{$launcher_text}'";
    }

    // Shared: Toggle Listener For .chatbox-toggle Elements
    $toggle_script = <<<'JS'
  document.addEventListener('click', function(e) {
    var el = e.target.closest('.chatbox-toggle, a[href="#chatbox-toggle"]');
    if (el && window.$chatwoot) {
      e.preventDefault();
      window.$chatwoot.toggle();
    }
  });
JS;

    if ($defer) {
        // Defer: Load After Window.load Event
        $script = <<<JS
(function() {
  function loadChatwoot() {
    window.chatwootSettings = { {$chatwoot_settings} };
    var s = document.createElement('script');
    s.src = '{$base_url}/packs/js/sdk.js';
    s.defer = true;
    s.onload = function() {
      window.chatwootSDK.run({
        websiteToken: '{$website_token}',
        baseUrl: '{$base_url}'
      });
    };
    document.body.appendChild(s);
  }
  if (document.readyState === 'complete') {
    loadChatwoot();
  } else {
    window.addEventListener('load', loadChatwoot);
  }
{$toggle_script}
})();
JS;
    } else {
        // Immediate: Standard Chatwoot Embed
        $script = <<<JS
(function() {
  window.chatwootSettings = { {$chatwoot_settings} };
  var s = document.createElement('script');
  s.src = '{$base_url}/packs/js/sdk.js';
  s.async = true;
  s.onload = function() {
    window.chatwootSDK.run({
      websiteToken: '{$website_token}',
      baseUrl: '{$base_url}'
    });
  };
  document.head.appendChild(s);
{$toggle_script}
})();
JS;
    }

    wp_add_inline_script('jquery', $script, 'after');
}
add_action('wp_enqueue_scripts', 'chatwoot_widget_enqueue_script');

// Add Settings Link on Plugins Page
function chatwoot_widget_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=chatwoot-widget">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'chatwoot_widget_settings_link');
