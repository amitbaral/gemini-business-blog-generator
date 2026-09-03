<?php
/**
 * Plugin Name: WP Pro Blogs
 * Plugin URI: https://wpproblogs.com
 * Description: AI-powered client-generating SEO blog post platform for SEO agencies & growth marketers. Powered by Google Gemini API with 100/100 SEO optimization, Rank Math/Yoast integration, Schema JSON-LD, TOC, FAQ blocks, and client acquisition CTAs.
 * Version: 1.0.0
 * Author: WP Pro Blogs Team
 * Text Domain: wp-pro-blogs
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('GBBG_VERSION', '1.0.0');
define('GBBG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GBBG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require Core Classes
require_once GBBG_PLUGIN_DIR . 'includes/class-gemini-api.php';
require_once GBBG_PLUGIN_DIR . 'includes/class-prompt-builder.php';
require_once GBBG_PLUGIN_DIR . 'includes/class-seo-analyzer.php';
require_once GBBG_PLUGIN_DIR . 'includes/class-admin-ui.php';

// Initialize Plugin
function gbbg_init_plugin() {
    $admin_ui = new GBBG_Admin_UI();
    $admin_ui->init();
}
add_action('plugins_loaded', 'gbbg_init_plugin');

// Default options on activation
register_activation_hook(__FILE__, function() {
    $defaults = array(
        'gemini_api_key'       => '',
        'gemini_model'         => 'gemini-3.8-flash',
        'gemini_temperature'   => '0.7',
        'gemini_max_tokens'    => '8192',
        'gemini_top_p'         => '0.95',
        'gemini_top_k'         => '40',
        'business_name'        => 'Apex SEO Agency',
        'business_niche'       => 'SEO & Digital Marketing Agency',
        'business_location'    => 'Global',
        'default_tone'         => 'Authoritative, Persuasive, High-Converting, Professional',
        'target_word_count'    => '1500-2500 words',
        'enable_rank_math'     => '1',
        'enable_yoast'         => '1',
        'enable_faq_schema'    => '1',
        'enable_article_schema'=> '1',
        'enable_toc'           => '1',
        'enable_key_takeaways' => '1',
        'business_website'     => get_bloginfo('url'),
        'primary_cta'          => 'Ready to get more clients and scale your organic rankings? Contact our SEO specialists today for a free growth strategy call.',
        'service_links'        => "SEO Audit Services | /services/seo-audit
Content Strategy & Creation | /services/content-marketing
Link Building & Authority | /services/link-building
Book Strategy Call | /contact"
    );

    foreach ($defaults as $key => $val) {
        if (get_option('gbbg_' . $key) === false) {
            update_option('gbbg_' . $key, $val);
        }
    }
});