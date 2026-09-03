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
            $checks[] = array('rule' => __('Title Length', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Title length is optimal (%d characters).', 'gemini-business-blog'), $title_len));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Title Length', 'gemini-business-blog'), 'status' => 'warn', 'score' => 5, 'detail' => sprintf(__('Title length is %d chars (Target: 50-60 chars).', 'gemini-business-blog'), $title_len));
            $total_score += 5;
        }

        // 2. Title Focus Keyword (10 pts)
        if (!empty($focus_kw) && strpos(strtolower($title), $focus_kw) !== false) {
            $checks[] = array('rule' => __('Focus Keyword in Title', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword is present in the H1 Title.', 'gemini-business-blog'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Focus Keyword in Title', 'gemini-business-blog'), 'status' => 'fail', 'score' => 0, 'detail' => __('Focus keyword missing from title.', 'gemini-business-blog'));
        }

        // 3. Meta Description Length (10 pts)
        $desc_len = mb_strlen($meta_desc);
        if ($desc_len >= 135 && $desc_len <= 165) {
            $checks[] = array('rule' => __('Meta Description Length', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Meta description length is optimal (%d characters).', 'gemini-business-blog'), $desc_len));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Meta Description Length', 'gemini-business-blog'), 'status' => 'warn', 'score' => 6, 'detail' => sprintf(__('Meta description length is %d chars (Target: 150-160 chars).', 'gemini-business-blog'), $desc_len));
            $total_score += 6;
        }

        // 4. Meta Description Focus Keyword (10 pts)
        if (!empty($focus_kw) && strpos(strtolower($meta_desc), $focus_kw) !== false) {
            $checks[] = array('rule' => __('Focus Keyword in Meta Description', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword present in meta description.', 'gemini-business-blog'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Focus Keyword in Meta Description', 'gemini-business-blog'), 'status' => 'fail', 'score' => 0, 'detail' => __('Focus keyword missing from meta description.', 'gemini-business-blog'));
        }

        // 5. Keyword Density (10 pts)
        if ($total_words > 0 && !empty($focus_kw)) {
            $kw_count = substr_count($plain_text, $focus_kw);
            $density = round(($kw_count / $total_words) * 100, 2);
            if ($density >= 0.8 && $density <= 3.0) {
                $checks[] = array('rule' => __('Keyword Density', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Keyword density is optimal (%s%% - %d occurrences).', 'gemini-business-blog'), $density, $kw_count));
                $total_score += 10;
            } else {
                $checks[] = array('rule' => __('Keyword Density', 'gemini-business-blog'), 'status' => 'warn', 'score' => 5, 'detail' => sprintf(__('Keyword density is %s%% (Target: 1.5%% - 2.5%%).', 'gemini-business-blog'), $density));
                $total_score += 5;
            }
        } else {
            $checks[] = array('rule' => __('Keyword Density', 'gemini-business-blog'), 'status' => 'fail', 'score' => 0, 'detail' => __('Unable to compute keyword density.', 'gemini-business-blog'));
        }

        // 6. Focus Keyword in Intro (First 100 Words) (10 pts)
        $first_100_words = implode(' ', array_slice($words, 0, 100));
        if (!empty($focus_kw) && strpos($first_100_words, $focus_kw) !== false) {
            $checks[] = array('rule' => __('Keyword in First 100 Words', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword appears in the intro section.', 'gemini-business-blog'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Keyword in First 100 Words', 'gemini-business-blog'), 'status' => 'fail', 'score' => 0, 'detail' => __('Focus keyword missing from intro paragraphs.', 'gemini-business-blog'));
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
            $checks[] = array('rule' => __('Keyword in Subheading (H2/H3)', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => __('Focus keyword found in H2/H3 subheading.', 'gemini-business-blog'));
            $total_score += 10;
        } else {
            $checks[] = array('rule' => __('Keyword in Subheading (H2/H3)', 'gemini-business-blog'), 'status' => 'warn', 'score' => 5, 'detail' => __('Focus keyword recommended in at least one subheading.', 'gemini-business-blog'));
            $total_score += 5;
        }

        // 8. Word Count (10 pts)
        if ($total_words >= 1000) {
            $checks[] = array('rule' => __('Content Word Count', 'gemini-business-blog'), 'status' => 'pass', 'score' => 10, 'detail' => sprintf(__('Comprehensive depth (%d words).', 'gemini-business-blog'), $total_words));
            $total_score += 10;
        } elseif ($total_words >= 600) {
            $checks[] = array('rule' => __('Content Word Count', 'gemini-business-blog'), 'status' => 'warn', 'score' => 7, 'detail' => sprintf(__('Good length (%d words), 1000+ recommended.', 'gemini-business-blog'), $total_words));
            $total_score += 7;
        } else {
            $checks[] = array('rule' => __('Content Word Count', 'gemini-business-blog'), 'status' => 'fail', 'score' => 2, 'detail' => sprintf(__('Short content (%d words).', 'gemini-business-blog'), $total_words));
            $total_score += 2;
        }

        // 9. Table of Contents / Heading Structure (5 pts)
        if (count($matches[0]) >= 3) {
            $checks[] = array('rule' => __('Heading Structure & Subsections', 'gemini-business-blog'), 'status' => 'pass', 'score' => 5, 'detail' => sprintf(__('Well-structured with %d subheadings.', 'gemini-business-blog'), count($matches[0])));
            $total_score += 5;
        } else {
            $checks[] = array('rule' => __('Heading Structure & Subsections', 'gemini-business-blog'), 'status' => 'warn', 'score' => 2, 'detail' => __('Add more subheadings to improve structural score.', 'gemini-business-blog'));
            $total_score += 2;
        }

        // 10. FAQ Section & Schema (5 pts)
        if (count($faq_list) >= 2) {
            $checks[] = array('rule' => __('FAQ Section & Schema', 'gemini-business-blog'), 'status' => 'pass', 'score' => 5, 'detail' => sprintf(__('Includes %d FAQ items for rich search snippet FAQ Schema.', 'gemini-business-blog'), count($faq_list)));
            $total_score += 5;
        } else {
            $checks[] = array('rule' => __('FAQ Section & Schema', 'gemini-business-blog'), 'status' => 'warn', 'score' => 2, 'detail' => __('Add FAQ questions for rich snippet eligibility.', 'gemini-business-blog'));
            $total_score += 2;
        }

        // 11. Key Takeaways Box (5 pts)
        if (count($key_takeaways) >= 2) {
            $checks[] = array('rule' => __('Executive Key Takeaways', 'gemini-business-blog'), 'status' => 'pass', 'score' => 5, 'detail' => __('Includes high-value key takeaways for Google AI Overview (GEO).', 'gemini-business-blog'));
            $total_score += 5;
        } else {
            $checks[] = array('rule' => __('Executive Key Takeaways', 'gemini-business-blog'), 'status' => 'warn', 'score' => 2, 'detail' => __('Add executive key takeaways box.', 'gemini-business-blog'));
            $total_score += 2;
        }

        // 12. Formatting & Lists (5 pts)
        if (strpos($content_html, '<ul') !== false || strpos($content_html, '<ol') !== false) {
            $checks[] = array('rule' => __('Readability & Lists', 'gemini-business-blog'), 'status' => 'pass', 'score' => 5, 'detail' => __('Uses bullet points or numbered lists for scannability.', 'gemini-business-blog'));
            $total_score += 5;
        } else {
            $checks[] = array('rule' => __('Readability & Lists', 'gemini-business-blog'), 'status' => 'warn', 'score' => 2, 'detail' => __('Include lists to improve scannability.', 'gemini-business-blog'));
            $total_score += 2;
        }

        return array(
            'score'       => min(100, $total_score),
            'word_count'  => $total_words,
            'checks'      => $checks
        );
    }
}