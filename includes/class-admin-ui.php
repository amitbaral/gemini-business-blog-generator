<?php
if (!defined('ABSPATH')) {
    exit;
}

class GBBG_Admin_UI {

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_gbbg_generate_blog', array($this, 'ajax_generate_blog'));
        add_action('wp_ajax_gbbg_suggest_topics', array($this, 'ajax_suggest_topics'));
        add_action('wp_ajax_gbbg_test_api', array($this, 'ajax_test_api'));
        add_action('wp_ajax_gbbg_publish_post', array($this, 'ajax_publish_post'));
        add_action('wp_head', array($this, 'inject_schema_json_ld'));
    }

    public function add_admin_menu() {
        add_menu_page(__('WP Pro Blogs', 'wp-pro-blogs'), __('WP Pro Blogs', 'wp-pro-blogs'), 'manage_options', 'gbbg-generator', array($this, 'render_generator_page'), 'dashicons-chart-line', 26);
        add_submenu_page('gbbg-generator', __('Generate Blog', 'wp-pro-blogs'), __('Generate Blog', 'wp-pro-blogs'), 'manage_options', 'gbbg-generator', array($this, 'render_generator_page'));
        add_submenu_page('gbbg-generator', __('Agency Settings', 'wp-pro-blogs'), __('Agency Settings', 'wp-pro-blogs'), 'manage_options', 'gbbg-settings', array($this, 'render_settings_page'));
    }

    public function register_settings() {
        $fields = array('gemini_api_key','gemini_model','gemini_temperature','gemini_max_tokens','gemini_top_p','gemini_top_k','business_name','business_niche','business_location','default_tone','target_word_count','business_website','primary_cta','service_links','enable_rank_math','enable_yoast','enable_faq_schema','enable_article_schema','enable_toc','enable_key_takeaways');
        foreach ($fields as $f) {
            register_setting('gbbg_settings', 'gbbg_' . $f);
        }
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'gbbg') === false) return;
        wp_enqueue_style('gbbg-admin-css', GBBG_PLUGIN_URL . 'assets/css/admin.css', array(), GBBG_VERSION);
        wp_enqueue_script('gbbg-admin-js', GBBG_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), GBBG_VERSION, true);
        wp_localize_script('gbbg-admin-js', 'gbbg_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('gbbg_nonce')));
    }

    public function sanitize_float($val) { return floatval($val); }
    public function render_settings_page() {
        if (!current_user_can('manage_options')) return;
        $o = array();
        $keys = array('gemini_api_key'=>'','gemini_model'=>'gemini-3.8-flash','gemini_temperature'=>'0.7','gemini_max_tokens'=>'8192','gemini_top_p'=>'0.95','gemini_top_k'=>'40','business_name'=>'Apex SEO Agency','business_niche'=>'SEO & Digital Marketing Agency','business_location'=>'Global','default_tone'=>'Authoritative, Persuasive, High-Converting, Professional','target_word_count'=>'1500-2500 words','business_website'=>get_bloginfo('url'),'primary_cta'=>'Ready to get more clients and scale your organic rankings? Contact our SEO specialists today for a free growth strategy call.','service_links'=>'SEO Audit Services | /services/seo-audit','enable_rank_math'=>'1','enable_yoast'=>'1','enable_faq_schema'=>'1','enable_article_schema'=>'1','enable_toc'=>'1','enable_key_takeaways'=>'1');
        foreach ($keys as $k => $d) { $o[$k] = get_option('gbbg_'.$k, $d); }
        ?>
        <div class="wrap gbbg-wrap">
        <div class="gbbg-header"><div class="gbbg-header-inner"><span class="dashicons dashicons-chart-line gbbg-header-icon"></span><h1>WP Pro Blogs</h1><span class="gbbg-version">v<?php echo esc_html(GBBG_VERSION); ?></span><span class="gbbg-badge gbbg-badge-agency">Agency Pro Edition</span></div></div>
        <div class="gbbg-settings-tabs">
            <button class="gbbg-tab-btn active" data-tab="gemini-api">&#9889; Gemini AI Engine</button>
            <button class="gbbg-tab-btn" data-tab="business-profile">&#127970; Agency & Client Profile</button>
            <button class="gbbg-tab-btn" data-tab="seo-integration">&#128200; SEO Integration & Schema</button>
        </div>
        <form method="post" action="options.php">
        <?php settings_fields('gbbg_settings'); ?>

        <div class="gbbg-tab-content active" id="tab-gemini-api"><div class="gbbg-card"><div class="gbbg-card-header"><span class="dashicons dashicons-cloud"></span><h2>Google Gemini AI Engine Configuration</h2></div>
        <table class="form-table gbbg-form-table">
        <tr><th><label for="gbbg_gemini_api_key">API Key</label></th><td>
            <div class="gbbg-input-group"><input type="password" id="gbbg_gemini_api_key" name="gbbg_gemini_api_key" value="<?php echo esc_attr($o['gemini_api_key']); ?>" class="regular-text gbbg-input" placeholder="Enter your Google AI Studio API Key" autocomplete="off" />
            <button type="button" class="gbbg-btn gbbg-btn-sm gbbg-toggle-visibility" data-target="gbbg_gemini_api_key"><span class="dashicons dashicons-visibility"></span></button>
            <button type="button" class="gbbg-btn gbbg-btn-sm gbbg-btn-accent" id="gbbg-test-api-btn"><span class="dashicons dashicons-yes-alt"></span> Test Connection</button></div>
            <p class="description">Get your free API key from <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a></p>
            <div id="gbbg-api-test-result" class="gbbg-notice-inline" style="display:none;"></div>
        </td></tr>
        <tr><th><label for="gbbg_gemini_model">AI Model</label></th><td>
            <select id="gbbg_gemini_model" name="gbbg_gemini_model" class="gbbg-select">
            <option value="gemini-3.8-flash" <?php selected($o['gemini_model'], 'gemini-3.8-flash'); ?>>Gemini 3.8 Flash (Recommended: Ultra-fast & Intelligent)</option>
            <option value="gemini-2.5-flash" <?php selected($o['gemini_model'], 'gemini-2.5-flash'); ?>>Gemini 2.5 Flash (Workhorse Model)</option>
            <option value="gemini-1.5-flash" <?php selected($o['gemini_model'], 'gemini-1.5-flash'); ?>>Gemini 1.5 Flash (Standard Fallback)</option>
            </select><p class="description">Select the Google Gemini AI model used for high-converting blog generation.</p></td></tr>
        <tr><th><label for="gbbg_gemini_temperature">Creativity (Temperature)</label></th><td>
            <div class="gbbg-range-group"><input type="range" id="gbbg_gemini_temperature" name="gbbg_gemini_temperature" min="0" max="2" step="0.1" value="<?php echo esc_attr($o['gemini_temperature']); ?>" class="gbbg-range" /><span class="gbbg-range-value"><?php echo esc_html($o['gemini_temperature']); ?></span></div>
            <p class="description">Controls creativity. Lower = structured & analytical. Higher = creative copy. (Recommended: 0.7)</p></td></tr>
        <tr><th><label for="gbbg_gemini_max_tokens">Max Output Tokens</label></th><td>
            <input type="number" id="gbbg_gemini_max_tokens" name="gbbg_gemini_max_tokens" value="<?php echo esc_attr($o['gemini_max_tokens']); ?>" class="small-text gbbg-input" min="1024" max="65536" step="256" />
            <p class="description">Maximum response token limit. 8192 is optimal for comprehensive long-form articles.</p></td></tr>
        <tr><th><label for="gbbg_gemini_top_p">Top P (Nucleus Sampling)</label></th><td>
            <div class="gbbg-range-group"><input type="range" id="gbbg_gemini_top_p" name="gbbg_gemini_top_p" min="0" max="1" step="0.05" value="<?php echo esc_attr($o['gemini_top_p']); ?>" class="gbbg-range" /><span class="gbbg-range-value"><?php echo esc_html($o['gemini_top_p']); ?></span></div>
            <p class="description">Controls token selection diversity. (Recommended: 0.95)</p></td></tr>
        <tr><th><label for="gbbg_gemini_top_k">Top K</label></th><td>
            <input type="number" id="gbbg_gemini_top_k" name="gbbg_gemini_top_k" value="<?php echo esc_attr($o['gemini_top_k']); ?>" class="small-text gbbg-input" min="1" max="100" />
            <p class="description">Limits top candidate tokens per step. (Recommended: 40)</p></td></tr>
        </table></div></div>
        <div class="gbbg-tab-content" id="tab-business-profile"><div class="gbbg-card"><div class="gbbg-card-header"><span class="dashicons dashicons-building"></span><h2>Agency & Client Target Profile</h2></div>
        <table class="form-table gbbg-form-table">
        <tr><th><label for="gbbg_business_name">Agency / Brand Name</label></th><td><input type="text" id="gbbg_business_name" name="gbbg_business_name" value="<?php echo esc_attr($o['business_name']); ?>" class="regular-text gbbg-input" placeholder="e.g. Apex Digital SEO Agency" /></td></tr>
        <tr><th><label for="gbbg_business_niche">Industry Niche / Target Market</label></th><td><input type="text" id="gbbg_business_niche" name="gbbg_business_niche" value="<?php echo esc_attr($o['business_niche']); ?>" class="regular-text gbbg-input" placeholder="e.g. SEO & Lead Generation for B2B Services" /></td></tr>
        <tr><th><label for="gbbg_business_location">Target Region / Location</label></th><td><input type="text" id="gbbg_business_location" name="gbbg_business_location" value="<?php echo esc_attr($o['business_location']); ?>" class="regular-text gbbg-input" placeholder="e.g. Global / USA / Australia" /></td></tr>
        <tr><th><label for="gbbg_default_tone">Brand Tone / Voice</label></th><td><input type="text" id="gbbg_default_tone" name="gbbg_default_tone" value="<?php echo esc_attr($o['default_tone']); ?>" class="regular-text gbbg-input" placeholder="e.g. Authoritative, High-Converting, Persuasive" /></td></tr>
        <tr><th><label for="gbbg_target_word_count">Default Content Length</label></th><td>
            <select id="gbbg_target_word_count" name="gbbg_target_word_count" class="gbbg-select">
            <option value="800-1200 words" <?php selected($o['target_word_count'], '800-1200 words'); ?>>Short Guide (800-1200 words)</option>
            <option value="1500-2500 words" <?php selected($o['target_word_count'], '1500-2500 words'); ?>>Standard SEO Article (1500-2500 words)</option>
            <option value="2500-4000 words" <?php selected($o['target_word_count'], '2500-4000 words'); ?>>Authority Pillar Post (2500-4000 words)</option>
            </select></td></tr>
        <tr><th><label for="gbbg_business_website">Agency Website URL</label></th><td><input type="url" id="gbbg_business_website" name="gbbg_business_website" value="<?php echo esc_attr($o['business_website']); ?>" class="regular-text gbbg-input" /></td></tr>
        <tr><th><label for="gbbg_primary_cta">Client Acquisition CTA</label></th><td><textarea id="gbbg_primary_cta" name="gbbg_primary_cta" class="large-text gbbg-textarea" rows="2" placeholder="e.g. Contact our SEO agency today for a complimentary organic search audit and growth strategy session."><?php echo esc_textarea($o['primary_cta']); ?></textarea><p class="description">Call To Action appended at the end of generated articles to capture client leads.</p></td></tr>
        <tr><th><label for="gbbg_service_links">Client Service Links</label></th><td>
            <textarea id="gbbg_service_links" name="gbbg_service_links" class="large-text gbbg-textarea" rows="4" placeholder="SEO Audit | /services/seo-audit&#10;Link Building | /services/link-building"><?php echo esc_textarea($o['service_links']); ?></textarea>
            <p class="description">One per line: Service Name | /url-path (Used for natural internal contextual linking)</p></td></tr>
        </table></div></div>

        <div class="gbbg-tab-content" id="tab-seo-integration"><div class="gbbg-card"><div class="gbbg-card-header"><span class="dashicons dashicons-chart-area"></span><h2>SEO Plugin Integration & Schema</h2></div>
        <table class="form-table gbbg-form-table">
        <tr><th>Rank Math SEO</th><td><label class="gbbg-toggle"><input type="checkbox" name="gbbg_enable_rank_math" value="1" <?php checked($o['enable_rank_math'], '1'); ?> /><span class="gbbg-toggle-slider"></span></label><span class="gbbg-toggle-label">Auto-populate Rank Math title, description & focus keyword</span></td></tr>
        <tr><th>Yoast SEO</th><td><label class="gbbg-toggle"><input type="checkbox" name="gbbg_enable_yoast" value="1" <?php checked($o['enable_yoast'], '1'); ?> /><span class="gbbg-toggle-slider"></span></label><span class="gbbg-toggle-label">Auto-populate Yoast SEO title, description & focus keyword</span></td></tr>
        <tr><th>FAQ Schema (JSON-LD)</th><td><label class="gbbg-toggle"><input type="checkbox" name="gbbg_enable_faq_schema" value="1" <?php checked($o['enable_faq_schema'], '1'); ?> /><span class="gbbg-toggle-slider"></span></label><span class="gbbg-toggle-label">Inject FAQPage JSON-LD structured data for rich snippets</span></td></tr>
        <tr><th>BlogPosting Schema</th><td><label class="gbbg-toggle"><input type="checkbox" name="gbbg_enable_article_schema" value="1" <?php checked($o['enable_article_schema'], '1'); ?> /><span class="gbbg-toggle-slider"></span></label><span class="gbbg-toggle-label">Inject BlogPosting JSON-LD schema</span></td></tr>
        <tr><th>Table of Contents</th><td><label class="gbbg-toggle"><input type="checkbox" name="gbbg_enable_toc" value="1" <?php checked($o['enable_toc'], '1'); ?> /><span class="gbbg-toggle-slider"></span></label><span class="gbbg-toggle-label">Auto-generate Table of Contents with smooth jump-links</span></td></tr>
        <tr><th>Key Takeaways Box</th><td><label class="gbbg-toggle"><input type="checkbox" name="gbbg_enable_key_takeaways" value="1" <?php checked($o['enable_key_takeaways'], '1'); ?> /><span class="gbbg-toggle-slider"></span></label><span class="gbbg-toggle-label">Include executive summary key takeaways at the top</span></td></tr>
        </table></div></div>

        <div class="gbbg-save-bar"><?php submit_button('Save All Settings', 'primary gbbg-btn gbbg-btn-primary gbbg-btn-lg', 'submit', false); ?></div>
        </form></div>
        <?php
    }
    public function render_generator_page() {
        if (!current_user_can('manage_options')) return;
        $has_key = !empty(get_option('gbbg_gemini_api_key', ''));
        ?>
        <div class="wrap gbbg-wrap">
        <div class="gbbg-header"><div class="gbbg-header-inner"><span class="dashicons dashicons-chart-line gbbg-header-icon"></span><h1>WP Pro Blogs - AI Generator</h1><span class="gbbg-version">v<?php echo esc_html(GBBG_VERSION); ?></span><span class="gbbg-badge gbbg-badge-agency">Agency Pro Edition</span></div></div>

        <?php if (!$has_key) : ?>
        <div class="gbbg-alert gbbg-alert-warning"><span class="dashicons dashicons-warning"></span><p>Please configure your Gemini API Key in <a href="<?php echo esc_url(admin_url('admin.php?page=gbbg-settings')); ?>">Agency Settings</a> before generating blog posts.</p></div>
        <?php endif; ?>

        <div class="gbbg-generator-grid">
        <div class="gbbg-panel gbbg-panel-input">
            <div class="gbbg-card"><div class="gbbg-card-header"><span class="dashicons dashicons-lightbulb"></span><h2>Client Acquisition Blog Strategy</h2></div>
            <div class="gbbg-form-group"><label for="gbbg-topic">Blog Topic / Article Title</label><input type="text" id="gbbg-topic" class="gbbg-input gbbg-input-lg" placeholder="e.g. 10 Essential Local SEO Strategies to Attract High-Paying Clients" /></div>
            <div class="gbbg-form-group"><label for="gbbg-focus-keyword">Focus Keyword</label><input type="text" id="gbbg-focus-keyword" class="gbbg-input" placeholder="e.g. local seo client acquisition" /></div>
            <div class="gbbg-form-group"><label for="gbbg-custom-instructions">Custom Instructions (Optional)</label><textarea id="gbbg-custom-instructions" class="gbbg-textarea" rows="3" placeholder="e.g. Target B2B business owners, highlight SEO ROI, include lead-generation CTA..."></textarea></div>
            <div class="gbbg-form-actions">
                <button type="button" id="gbbg-generate-btn" class="gbbg-btn gbbg-btn-primary gbbg-btn-lg" <?php echo !$has_key ? 'disabled' : ''; ?>><span class="dashicons dashicons-superhero-alt"></span> Generate Client-Acquisition Blog</button>
                <button type="button" id="gbbg-suggest-btn" class="gbbg-btn gbbg-btn-secondary" <?php echo !$has_key ? 'disabled' : ''; ?>><span class="dashicons dashicons-lightbulb"></span> Agency Topic Ideas</button>
            </div>
            <div id="gbbg-topic-suggestions" class="gbbg-suggestions-container" style="display:none;"></div>
            </div>

            <div id="gbbg-progress-container" class="gbbg-card" style="display:none;">
                <div class="gbbg-progress-header"><span class="gbbg-spinner"></span><span id="gbbg-progress-text">Generating client-converting blog post with Gemini AI...</span></div>
                <div class="gbbg-progress-bar"><div class="gbbg-progress-fill" id="gbbg-progress-fill"></div></div>
            </div>
        </div>

        <div class="gbbg-panel gbbg-panel-output">
            <div id="gbbg-seo-card" class="gbbg-card gbbg-seo-card" style="display:none;">
                <div class="gbbg-card-header"><span class="dashicons dashicons-awards"></span><h2>SEO Score Audit</h2></div>
                <div class="gbbg-seo-score-meter"><div class="gbbg-score-circle"><span id="gbbg-score-number">0</span><span class="gbbg-score-max">/100</span></div></div>
                <div id="gbbg-seo-checks" class="gbbg-seo-checks"></div>
            </div>

            <div id="gbbg-preview-card" class="gbbg-card" style="display:none;">
                <div class="gbbg-card-header"><span class="dashicons dashicons-visibility"></span><h2>Article Preview & Meta Audit</h2></div>
                <div class="gbbg-preview-meta">
                    <div class="gbbg-preview-meta-item"><strong>SEO Title:</strong> <span id="gbbg-preview-title"></span></div>
                    <div class="gbbg-preview-meta-item"><strong>Meta Description:</strong> <span id="gbbg-preview-meta-desc"></span></div>
                    <div class="gbbg-preview-meta-item"><strong>Focus Keyword:</strong> <span id="gbbg-preview-keyword" class="gbbg-badge gbbg-badge-primary"></span></div>
                    <div class="gbbg-preview-meta-item"><strong>Word Count:</strong> <span id="gbbg-preview-wordcount" class="gbbg-badge gbbg-badge-info"></span></div>
                </div>
                <div id="gbbg-preview-content" class="gbbg-preview-content-area"></div>
                <div class="gbbg-publish-bar">
                    <button type="button" id="gbbg-publish-btn" class="gbbg-btn gbbg-btn-success gbbg-btn-lg"><span class="dashicons dashicons-yes"></span> Publish Now</button>
                    <button type="button" id="gbbg-draft-btn" class="gbbg-btn gbbg-btn-secondary gbbg-btn-lg"><span class="dashicons dashicons-edit"></span> Save as Draft</button>
                </div>
            </div>
        </div>
        </div></div>
        <?php
    }
    public function ajax_generate_blog() {
        check_ajax_referer('gbbg_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error(array('message' => 'Insufficient permissions.')); }

        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $focus_keyword = sanitize_text_field($_POST['focus_keyword'] ?? '');
        $custom_instructions = sanitize_textarea_field($_POST['custom_instructions'] ?? '');
        $word_count = get_option('gbbg_target_word_count', '1500-2500 words');

        if (empty($topic)) { wp_send_json_error(array('message' => 'Please enter a blog topic.')); }

        $api = new GBBG_Gemini_API();
        $system_instruction = GBBG_Prompt_Builder::build_system_instruction();
        $prompt = GBBG_Prompt_Builder::build_blog_prompt($topic, $focus_keyword, $word_count, $custom_instructions);
        $result = $api->generate_content($prompt, $system_instruction);

        if (is_wp_error($result)) { wp_send_json_error(array('message' => $result->get_error_message())); }

        $cleaned = trim($result);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```\s*$/', '', $cleaned);
        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            wp_send_json_error(array('message' => 'AI returned invalid JSON. Retrying may help.', 'raw_response' => substr($result, 0, 500)));
        }

        $content_html = $this->build_full_content($data);
        $data['full_content'] = $content_html;
        $seo_result = GBBG_SEO_Analyzer::analyze(array_merge($data, array('content_html' => $content_html)));
        wp_send_json_success(array('blog_data' => $data, 'seo_result' => $seo_result));
    }

    private function build_full_content($data) {
        $html = '';
        $enable_takeaways = get_option('gbbg_enable_key_takeaways', '1');
        $enable_toc = get_option('gbbg_enable_toc', '1');
        $enable_faq = get_option('gbbg_enable_faq_schema', '1');
        $primary_cta = get_option('gbbg_primary_cta', '');

        if ($enable_takeaways && !empty($data['key_takeaways'])) {
            $html .= '<div class="gbbg-key-takeaways" style="background:#f0f7ff;border-left:4px solid #0073aa;padding:20px 24px;margin-bottom:28px;border-radius:4px;"><h2 style="margin-top:0;font-size:1.2em;">&#128161; Key Takeaways</h2><ul>';
            foreach ($data['key_takeaways'] as $kt) { $html .= '<li>' . esc_html($kt) . '</li>'; }
            $html .= '</ul></div>';
        }

        if ($enable_toc && !empty($data['table_of_contents'])) {
            $html .= '<div class="gbbg-toc" style="background:#fafafa;border:1px solid #e0e0e0;padding:20px 24px;margin-bottom:28px;border-radius:4px;"><h2 style="margin-top:0;font-size:1.1em;">&#128196; Table of Contents</h2><ul>';
            foreach ($data['table_of_contents'] as $toc) {
                $anchor = isset($toc['anchor']) ? $toc['anchor'] : sanitize_title($toc['title']);
                $html .= '<li><a href="#' . esc_attr($anchor) . '">' . esc_html($toc['title']) . '</a></li>';
            }
            $html .= '</ul></div>';
        }

        if (!empty($data['content_html'])) { $html .= wp_kses_post($data['content_html']); }

        if ($enable_faq && !empty($data['faq_list'])) {
            $html .= '<div class="gbbg-faq-section" style="margin-top:36px;"><h2>&#10067; Frequently Asked Questions</h2>';
            foreach ($data['faq_list'] as $faq) {
                $html .= '<div class="gbbg-faq-item" style="margin-bottom:16px;padding:16px;background:#f9f9f9;border-radius:4px;">';
                $html .= '<h3 style="margin-top:0;font-size:1em;">' . esc_html($faq['question']) . '</h3>';
                $html .= '<p>' . esc_html($faq['answer']) . '</p></div>';
            }
            $html .= '</div>';
        }

        if (!empty($primary_cta)) {
            $html .= '<div class="gbbg-cta-box" style="background:linear-gradient(135deg,#0073aa 0%,#005177 100%);color:#fff;padding:28px 32px;margin-top:36px;border-radius:8px;text-align:center;">';
            $html .= '<p style="font-size:1.15em;margin:0;">' . esc_html($primary_cta) . '</p></div>';
        }
        return $html;
    }
    public function ajax_suggest_topics() {
        check_ajax_referer('gbbg_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error(array('message' => 'Insufficient permissions.')); }

        $api = new GBBG_Gemini_API();
        $prompt = GBBG_Prompt_Builder::build_topic_ideation_prompt();
        $result = $api->generate_content($prompt, 'You are a business blog topic strategist. Always return valid JSON arrays only.');

        if (is_wp_error($result)) { wp_send_json_error(array('message' => $result->get_error_message())); }

        $cleaned = trim($result);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```\s*$/', '', $cleaned);
        $topics = json_decode($cleaned, true);

        if (!is_array($topics)) { wp_send_json_error(array('message' => 'Could not parse AI topic suggestions.')); }
        wp_send_json_success(array('topics' => $topics));
    }

    public function ajax_test_api() {
        check_ajax_referer('gbbg_nonce', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error(array('message' => 'Insufficient permissions.')); }
        $api = new GBBG_Gemini_API();
        if (!empty($_POST['api_key'])) { $api->set_api_key(sanitize_text_field($_POST['api_key'])); }
        if (!empty($_POST['model'])) { $api->set_model(sanitize_text_field($_POST['model'])); }
        $result = $api->test_connection();
        if (is_wp_error($result)) { wp_send_json_error(array('message' => $result->get_error_message())); }
        wp_send_json_success(array('message' => 'Connected successfully to Google Gemini API!'));
    }

    public function ajax_publish_post() {
        check_ajax_referer('gbbg_nonce', 'nonce');
        if (!current_user_can('publish_posts')) { wp_send_json_error(array('message' => 'Insufficient permissions.')); }

        $blog_data = isset($_POST['blog_data']) ? $_POST['blog_data'] : array();
        $post_status = sanitize_text_field($_POST['post_status'] ?? 'draft');

        if (empty($blog_data) || empty($blog_data['title'])) { wp_send_json_error(array('message' => 'No blog data found.')); }

        $title = sanitize_text_field($blog_data['title']);
        $content = wp_kses_post($blog_data['full_content'] ?? '');
        $excerpt = sanitize_text_field($blog_data['excerpt'] ?? '');
        $slug = sanitize_title($blog_data['slug'] ?? $title);
        $focus_keyword = sanitize_text_field($blog_data['focus_keyword'] ?? '');
        $meta_desc = sanitize_text_field($blog_data['meta_description'] ?? '');

        $post_id = wp_insert_post(array('post_title' => $title, 'post_content' => $content, 'post_excerpt' => $excerpt, 'post_name' => $slug, 'post_status' => $post_status, 'post_type' => 'post', 'post_author' => get_current_user_id()));
        if (is_wp_error($post_id)) { wp_send_json_error(array('message' => $post_id->get_error_message())); }

        if (!empty($blog_data['faq_list'])) { update_post_meta($post_id, '_gbbg_faq_list', $blog_data['faq_list']); }
        update_post_meta($post_id, '_gbbg_focus_keyword', $focus_keyword);
        update_post_meta($post_id, '_gbbg_generated', '1');

        if (get_option('gbbg_enable_rank_math', '1') === '1') {
            update_post_meta($post_id, 'rank_math_title', $title);
            update_post_meta($post_id, 'rank_math_description', $meta_desc);
            update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
        }
        if (get_option('gbbg_enable_yoast', '1') === '1') {
            update_post_meta($post_id, '_yoast_wpseo_title', $title);
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
        }

        wp_send_json_success(array(
            'message' => ($post_status === 'publish') ? 'Blog published successfully!' : 'Blog saved as draft!',
            'post_id' => $post_id,
            'edit_url' => get_edit_post_link($post_id, 'raw'),
            'view_url' => get_permalink($post_id),
        ));
    }

    public function inject_schema_json_ld() {
        if (!is_singular('post')) return;
        $post_id = get_the_ID();
        if (get_post_meta($post_id, '_gbbg_generated', true) !== '1') return;
        $post = get_post($post_id);

        if (get_option('gbbg_enable_article_schema', '1') === '1') {
            $schema = array('@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>get_the_title($post_id),'description'=>get_post_meta($post_id,'rank_math_description',true)?:wp_trim_words($post->post_content,30,'...'),'author'=>array('@type'=>'Organization','name'=>get_option('gbbg_business_name',get_bloginfo('name')),'url'=>get_option('gbbg_business_website',get_bloginfo('url'))),'datePublished'=>get_the_date('c',$post_id),'dateModified'=>get_the_modified_date('c',$post_id),'publisher'=>array('@type'=>'Organization','name'=>get_option('gbbg_business_name',get_bloginfo('name'))),'mainEntityOfPage'=>array('@type'=>'WebPage','@id'=>get_permalink($post_id)));
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }

        if (get_option('gbbg_enable_faq_schema', '1') === '1') {
            $faq_list = get_post_meta($post_id, '_gbbg_faq_list', true);
            if (!empty($faq_list) && is_array($faq_list)) {
                $entities = array();
                foreach ($faq_list as $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $entities[] = array('@type'=>'Question','name'=>$faq['question'],'acceptedAnswer'=>array('@type'=>'Answer','text'=>$faq['answer']));
                    }
                }
                if (!empty($entities)) {
                    echo '<script type="application/ld+json">' . wp_json_encode(array('@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$entities), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
                }
            }
        }
    }
}