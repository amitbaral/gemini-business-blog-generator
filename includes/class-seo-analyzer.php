<?php
if (!defined('ABSPATH')) {
    exit;
}

class GBBG_SEO_Analyzer {

    public static function analyze($data) {
        $title          = isset($data['title']) ? trim($data['title']) : '';
        $meta_desc      = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $focus_kw       = isset($data['focus_keyword']) ? strtolower(trim($data['focus_keyword'])) : '';
        $content_html   = isset($data['content_html']) ? $data['content_html'] : '';
        $slug           = isset($data['slug']) ? trim($data['slug']) : '';
        $faq_list       = isset($data['faq_list']) && is_array($data['faq_list']) ? $data['faq_list'] : array();
        $key_takeaways  = isset($data['key_takeaways']) && is_array($data['key_takeaways']) ? $data['key_takeaways'] : array();

        $plain_text = strtolower(wp_strip_all_tags($content_html));
        $words = preg_split('/\s+/', $plain_text);
        $total_words = count(array_filter($words));

        $checks = array();
        $total_score = 0;

        // 1. Title Length Check (10 pts)
        $title_len = mb_strlen($title);
        if ($title_len >= 45 && $title_len <= 65) {
            $checks[] = array('rule' => __('Title Length', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Title length is optimal (%d characters).', 'wp-pro-blogs'), $title_len));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Title Length', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 5, 'detail' => sprintf(__('Title length is %d chars (Target: 50-60 chars).', 'wp-pro-blogs'), $title_len));
            $total_score += 5;
        }

        // 2. Title Focus Keyword & Number Check (10 pts)
        $has_number = preg_match('/\d+/', $title);
        $has_kw = !empty($focus_kw) && strpos(strtolower($title), $focus_kw) !== false;
        if ($has_kw && $has_number) {
            $checks[] = array('rule' => __('Title Keyword & Number', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => __('H1 Title contains both the focus keyword and a number.', 'wp-pro-blogs'));
            $total_score += 10;
        } elseif ($has_kw) {
            $checks[] = array('rule' => __('Title Keyword & Number', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 7, 'detail' => __('Title contains focus keyword but missing a number (Rank Math rule).', 'wp-pro-blogs'));
            $total_score += 7;
        } else {
            $checks[] = array('rule' => __('Title Keyword & Number', 'wp-pro-blogs'), 'status' => 'fail', 'score' => 0, 'detail' => __('Focus keyword missing from title.', 'wp-pro-blogs'));
        }

        // 3. Meta Description Length (10 pts)
        $desc_len = mb_strlen($meta_desc);
        if ($desc_len >= 135 && $desc_len <= 165) {
            $checks[] = array('rule' => __('Meta Description Length', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Meta description length is optimal (%d characters).', 'wp-pro-blogs'), $desc_len));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Meta Description Length', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 6, 'detail' => sprintf(__('Meta description length is %d chars (Target: 150-160 chars).', 'wp-pro-blogs'), $desc_len));
            $total_score += 6;
        }

        // 4. Meta Description Focus Keyword (10 pts)
        if (!empty($focus_kw) && strpos(strtolower($meta_desc), $focus_kw) !== false) {
            $checks[] = array('rule' => __('Focus Keyword in Meta Description', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword present in meta description.', 'wp-pro-blogs'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Focus Keyword in Meta Description', 'wp-pro-blogs'), 'status' => 'fail', 'score' => 0, 'detail' => __('Focus keyword missing from meta description.', 'wp-pro-blogs'));
        }

        // 5. Keyword Density (10 pts)
        if ($total_words > 0 && !empty($focus_kw)) {
            $kw_count = substr_count($plain_text, $focus_kw);
            $density = round(($kw_count / $total_words) * 100, 2);
            if ($density >= 0.8 && $density <= 3.0) {
                $checks[] = array('rule' => __('Keyword Density', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Keyword density is optimal (%s%% - %d occurrences).', 'wp-pro-blogs'), $density, $kw_count));
                $total_score += 10;
            } else {
                $checks[] = array('rule' => __('Keyword Density', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 5, 'detail' => sprintf(__('Keyword density is %s%% (Target: 1.5%% - 2.5%%).', 'wp-pro-blogs'), $density));
                $total_score += 5;
            }
        } else {
            $checks[] = array('rule' => __('Keyword Density', 'wp-pro-blogs'), 'status' => 'fail', 'score' => 0, 'detail' => __('Unable to compute keyword density.', 'wp-pro-blogs'));
        }

        // 6. Focus Keyword in Intro (First 100 Words) (10 pts)
        $first_100_words = implode(' ', array_slice($words, 0, 100));
        if (!empty($focus_kw) && strpos($first_100_words, $focus_kw) !== false) {
            $checks[] = array('rule' => __('Keyword in First 100 Words', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword appears in the intro section.', 'wp-pro-blogs'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Keyword in First 100 Words', 'wp-pro-blogs'), 'status' => 'fail', 'score' => 0, 'detail' => __('Focus keyword missing from intro paragraphs.', 'wp-pro-blogs'));
        }

        // 7. Focus Keyword in Subheadings (10 pts)
        preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>/i', $content_html, $matches);
        $subheading_has_kw = false;
        if (!empty($matches[1]) && !empty($focus_kw)) {
            foreach ($matches[1] as $subheading) {
                if (strpos(strtolower(wp_strip_all_tags($subheading)), $focus_kw) !== false) {
                    $subheading_has_kw = true;
                    break;
                }
            }
        }
        if ($subheading_has_kw) {
            $checks[] = array('rule' => __('Keyword in Subheading (H2/H3)', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword found in H2/H3 subheading.', 'wp-pro-blogs'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Keyword in Subheading (H2/H3)', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 5, 'detail' => __('Focus keyword recommended in at least one subheading.', 'wp-pro-blogs'));
            $total_score += 5;
        }

        // 8. Image Alt Text & Rich Media (10 pts)
        preg_match_all('/<img\s+[^>]*alt=["\']([^"\']*)["\']/i', $content_html, $img_matches);
        $has_img = !empty($img_matches[0]);
        $img_has_kw = false;
        if (!empty($img_matches[1]) && !empty($focus_kw)) {
            foreach ($img_matches[1] as $alt) {
                if (strpos(strtolower($alt), $focus_kw) !== false) {
                    $img_has_kw = true;
                    break;
                }
            }
        }
        if ($has_img && $img_has_kw) {
            $checks[] = array('rule' => __('Image Alt Text with Focus Keyword', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => __('Includes rich media image with focus keyword in Alt text.', 'wp-pro-blogs'));
            $total_score += 10;
        } elseif ($has_img) {
            $checks[] = array('rule' => __('Image Alt Text with Focus Keyword', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 5, 'detail' => __('Image present but focus keyword missing from alt text.', 'wp-pro-blogs'));
            $total_score += 5;
        } else {
            $checks[] = array('rule' => __('Image Alt Text with Focus Keyword', 'wp-pro-blogs'), 'status' => 'fail', 'score' => 0, 'detail' => __('No image found with focus keyword alt text.', 'wp-pro-blogs'));
        }

        // 9. Outbound Dofollow Links (10 pts)
        $has_outbound = preg_match('/<a\s+[^>]*href=["\']https?:\/\/[^"\']+["\'][^>]*>/i', $content_html);
        if ($has_outbound) {
            $checks[] = array('rule' => __('Authoritative Outbound Links', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 10, 'detail' => __('Contains external dofollow link to authoritative resources.', 'wp-pro-blogs'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Authoritative Outbound Links', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 3, 'detail' => __('Include at least 1 outbound link to an authoritative site.', 'wp-pro-blogs'));
            $total_score += 3;
        }

        // 10. URL Slug Length (5 pts)
        $slug_len = mb_strlen($slug);
        if ($slug_len > 0 && $slug_len <= 65) {
            $checks[] = array('rule' => __('URL Slug Length', 'wp-pro-blogs'), 'status' => 'pass', 'score' => 5, 'detail' => sprintf(__('URL slug length is concise (%d chars).', 'wp-pro-blogs'), $slug_len));
            $total_score += 5;
        } else {
            $checks[] = array('rule' => __('URL Slug Length', 'wp-pro-blogs'), 'status' => 'warn', 'score' => 2, 'detail' => sprintf(__('Slug length is %d chars (Target: <= 65 chars).', 'wp-pro-blogs'), $slug_len));
            $total_score += 2;
        }

        return array(
            'score'       => min(100, $total_score),
            'word_count'  => $total_words,
            'checks'      => $checks
        );
    }
}