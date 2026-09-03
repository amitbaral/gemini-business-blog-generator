<?php
/**
 * Plugin Name: Gemini Business SEO Blog Generator
 * Plugin URI: https://novacare.org.au
 * Description: AI-powered blog post generator using Google Gemini API (gemini-3.8-flash, gemini-2.5-flash, gemini-1.5-flash) tailored for business websites. Guarantees 100/100 SEO optimization with Rank Math/Yoast integration, Schema JSON-LD, TOC, FAQ blocks, and real-time SEO scoring.
 * Version: 1.0.0
 * Author: Nova Care AI Team
 * Text Domain: gemini-business-blog
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
        'business_name'        => 'Nova Care Australia',
        'business_niche'       => 'Aged Care & NDIS Disability Support Services',
        'business_location'    => 'Australia',
        'default_tone'         => 'Professional, Empathetic, Authoritative, Caring',
        'target_word_count'    => '1500-2500 words',
        'enable_rank_math'     => '1',
        'enable_yoast'         => '1',
        'enable_faq_schema'    => '1',
        'enable_article_schema'=> '1',
        'enable_toc'           => '1',
        'enable_key_takeaways' => '1',
        'business_website'     => get_bloginfo('url'),
        'primary_cta'          => 'Contact Nova Care Australia today to discuss our personalized aged care and NDIS support packages.',
        'service_links'        => "Home Care Packages | /services/home-care
NDIS Support Services | /services/ndis
Aged Care Accommodation | /services/aged-care
Contact Us | /contact"
    );

    foreach ($defaults as $key => $val) {
        if (get_option('gbbg_' . $key) === false) {
            update_option('gbbg_' . $key, $val);
        }
    }
});