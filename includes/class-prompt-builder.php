<?php
if (!defined('ABSPATH')) {
    exit;
}

class GBBG_Prompt_Builder {

    public static function build_system_instruction() {
        $business_name     = get_option('gbbg_business_name', 'Apex SEO Agency');
        $business_niche    = get_option('gbbg_business_niche', 'SEO & Digital Marketing Agency');
        $business_location = get_option('gbbg_business_location', 'Global');
        $default_tone      = get_option('gbbg_default_tone', 'Authoritative, Persuasive, High-Converting, Professional');
        $primary_cta       = get_option('gbbg_primary_cta', 'Ready to get more clients and scale your organic rankings? Contact our SEO specialists today for a free growth strategy call.');
        $service_links     = get_option('gbbg_service_links', '');

        return "You are an elite SEO Strategist, Copywriter, and Lead Generation Expert specializing in high-ranking articles for $business_name ($business_niche serving $business_location).

YOUR GOAL: Write a comprehensive, high-converting, and 100/100 SEO-optimized blog post that passes ALL Rank Math & Yoast checks with 100/100 score, ranks #1 on Google, and converts readers directly into client leads.

CRITICAL RANK MATH 100/100 SEO RULES (MUST BE STRICTLY FOLLOWED):
1. TITLE (H1): Exactly 50-60 characters, MUST contain a number (e.g., '10 Proven...', '7 Essential...', '2026 Guide: 5 Steps...'), and MUST contain the primary focus keyword naturally.
2. META DESCRIPTION: Exactly 150-160 characters, persuasive, contains primary focus keyword and a clear Call To Action.
3. SLUG: Short & concise slug (3-5 words, max 60 characters) containing focus keyword (e.g., 'local-seo-client-acquisition').
4. KEYWORD PLACEMENT & DENSITY: Target 1.5% to 2.5% keyword density. Include focus keyword in:
   - The H1 Title
   - The first 100 words (Introduction)
   - At least 2 Subheadings (H2 or H3)
   - Image Alt Text
   - Naturally throughout body and concluding section
5. RICH MEDIA & IMAGES: Include at least 1 illustrative image element inside content_html: `<img src=\"https://picsum.photos/1200/630\" alt=\"{Focus Keyword} - Visual Guide\" class=\"gbbg-post-img\" />` with Focus Keyword in alt text.
6. OUTBOUND DOFOLLOW LINKS: Include at least 1-2 external dofollow links to high-authority reference sites (e.g. Google, Wikipedia, Forbes, Harvard Business Review, HubSpot) using `<a href=\"https://...\" target=\"_blank\" rel=\"noopener\">Anchor Text</a>`.
7. INTERNAL LINKS: Naturally reference relevant client service offerings where applicable (Service links: $service_links).
8. HEADING HIERARCHY & READABILITY: Logical H2 and H3 structure. Short punchy paragraphs (2-3 sentences max). Bullet points and bold key terms.
9. FAQ SECTION & SCHEMA: Include 3 to 5 frequently asked questions with direct, authoritative answers.
10. CLIENT CONVERSION CTA: End with a strong lead acquisition call to action ($primary_cta).
11. OUTPUT FORMAT: YOU MUST OUTPUT ONLY VALID RAW JSON matching the requested JSON structure. DO NOT wrap in markdown code blocks like ```json ... ``` unless requested, just pure JSON object.";
    }

    public static function build_blog_prompt($topic, $focus_keyword = '', $target_word_count = '1500-2500 words', $custom_instructions = '') {
        $business_name  = get_option('gbbg_business_name', 'Apex SEO Agency');
        $business_niche = get_option('gbbg_business_niche', 'SEO & Digital Marketing Agency');
        $tone           = get_option('gbbg_default_tone', 'Authoritative, Persuasive, High-Converting, Professional');

        if (empty($focus_keyword)) {
            $focus_keyword = $topic;
        }

        $prompt = "Create a full-length, 100/100 Rank Math SEO optimized, client-generating blog post for $business_name ($business_niche).

TOPIC: $topic
FOCUS KEYWORD: $focus_keyword
TARGET WORD COUNT: $target_word_count
BRAND TONE: $tone
ADDITIONAL INSTRUCTIONS: $custom_instructions

Return ONLY a valid JSON object with the following exact keys:
{
  \"title\": \"50-60 char H1 Title containing Focus Keyword AND a Number (e.g. 10 Proven...)\",
  \"meta_description\": \"Compelling 150-160 char Meta Description with Focus Keyword & CTA\",
  \"focus_keyword\": \"$focus_keyword\",
  \"secondary_keywords\": [\"keyword 1\", \"keyword 2\", \"keyword 3\"],
  \"slug\": \"short-slug-max-60-chars\",
  \"excerpt\": \"2-sentence engaging summary excerpt focused on value and lead capture.\",
  \"key_takeaways\": [
    \"Key takeaway 1 summary point\",
    \"Key takeaway 2 summary point\",
    \"Key takeaway 3 summary point\"
  ],
  \"table_of_contents\": [
    {\"title\": \"Section Heading 1\", \"anchor\": \"section-1\"},
    {\"title\": \"Section Heading 2\", \"anchor\": \"section-2\"}
  ],
  \"content_html\": \"<div class=\\\"gbbg-executive-summary\\\"><p><strong>Key Takeaways:</strong>...</p></div> <p>Intro paragraph containing $focus_keyword...</p> <p><img src=\\\"https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200\\\" alt=\\\"$focus_keyword - Agency Guide\\\" style=\\\"max-width:100%;height:auto;border-radius:8px;margin:20px 0;\\\" /></p> <h2 id=\\\"section-1\\\">1. Section Title with $focus_keyword</h2> <p>Body text featuring an authoritative outbound link to <a href=\\\"https://wikipedia.org\\\" target=\\\"_blank\\\" rel=\\\"noopener\\\">industry research</a>...</p>\",
  \"faq_list\": [
    {\"question\": \"Frequently Asked Question 1?\", \"answer\": \"Clear, concise answer to question 1.\"},
    {\"question\": \"Frequently Asked Question 2?\", \"answer\": \"Clear, concise answer to question 2.\"}
  ]
}";

        return $prompt;
    }

    public static function build_topic_ideation_prompt($niche = '') {
        if (empty($niche)) {
            $niche = get_option('gbbg_business_niche', 'SEO & Digital Marketing Agency');
        }
        $business_name = get_option('gbbg_business_name', 'Apex SEO Agency');

        return "Generate 6 high-converting, high-intent client acquisition blog topic ideas for $business_name operating in the $niche industry. Every topic title MUST contain a number (e.g. '10 Essential...', '7 Proven...').

Return ONLY a valid JSON array of objects:
[
  {
    \"topic\": \"Numbered High-Converting Blog Topic Title (e.g., 10 Proven SEO Strategies for 2026)\",
    \"focus_keyword\": \"Target Focus Keyword for Client Acquisition\",
    \"target_audience\": \"Ideal Client Profile / Business Decision Maker\",
    \"search_intent\": \"Commercial / High-Intent Transactional\"
  }
]";
    }
}