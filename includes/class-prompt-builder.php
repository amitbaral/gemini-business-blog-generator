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

YOUR GOAL: Write a comprehensive, high-converting, and 100/100 SEO-optimized blog post that ranks #1 on Google, provides immense strategic value to prospects, and converts readers directly into high-paying client leads and sales inquiries.

CRITICAL SEO & QUALITY RULES FOR 100/100 SCORE:
1. TITLE: Exactly 50-60 characters, highly compelling, contains primary focus keyword naturally.
2. META DESCRIPTION: Exactly 150-160 characters, persuasive, contains primary focus keyword and a direct lead-generation Call To Action.
3. KEYWORD PLACEMENT & DENSITY: Target 1.5% to 2.5% keyword density. Include focus keyword in:
   - The H1 Title
   - The first 100 words (Introduction)
   - At least 2 Subheadings (H2 or H3)
   - Naturally throughout the body
   - The concluding section
4. HEADING HIERARCHY: Logical H2 and H3 structure. Never skip heading levels.
5. READABILITY & ENGAGEMENT:
   - Short, punchy paragraphs (2-3 sentences max).
   - Use bullet points, bold key terms, and visual callout boxes.
   - Include 3 to 5 Executive Key Takeaways at the start.
6. FAQ SECTION & SCHEMA: Include 3 to 5 frequently asked questions with direct, authoritative answers.
7. CLIENT CONVERSION CTA: End with a strong lead acquisition call to action ($primary_cta).
8. INTERNAL LINKS: Naturally reference relevant client service offerings where applicable (Service links available: $service_links).
9. OUTPUT FORMAT: YOU MUST OUTPUT ONLY VALID RAW JSON matching the requested JSON structure. DO NOT wrap in markdown code blocks like ```json ... ``` unless requested, just pure JSON object.";
    }

    public static function build_blog_prompt($topic, $focus_keyword = '', $target_word_count = '1500-2500 words', $custom_instructions = '') {
        $business_name  = get_option('gbbg_business_name', 'Apex SEO Agency');
        $business_niche = get_option('gbbg_business_niche', 'SEO & Digital Marketing Agency');
        $tone           = get_option('gbbg_default_tone', 'Authoritative, Persuasive, High-Converting, Professional');

        if (empty($focus_keyword)) {
            $focus_keyword = $topic;
        }

        $prompt = "Create a full-length, 100/100 SEO optimized, client-generating blog post for $business_name ($business_niche).

TOPIC: $topic
FOCUS KEYWORD: $focus_keyword
TARGET WORD COUNT: $target_word_count
BRAND TONE: $tone
ADDITIONAL INSTRUCTIONS: $custom_instructions

Return ONLY a valid JSON object with the following exact keys:
{
  \"title\": \"Catchy 50-60 char H1 Title containing Focus Keyword\",
  \"meta_description\": \"Compelling 150-160 char Meta Description with Focus Keyword & CTA\",
  \"focus_keyword\": \"$focus_keyword\",
  \"secondary_keywords\": [\"keyword 1\", \"keyword 2\", \"keyword 3\"],
  \"slug\": \"url-friendly-slug-with-focus-keyword\",
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
  \"content_html\": \"<div class=\\\"gbbg-executive-summary\\\"><p><strong>Key Takeaways:</strong>...</p></div> <p>Intro paragraph containing focus keyword...</p> <h2 id=\\\"section-1\\\">Section 1 Title</h2> <p>Detailed body content with short paragraphs and bullet points...</p>\",
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

        return "Generate 6 high-converting, high-intent client acquisition blog topic ideas for $business_name operating in the $niche industry. Focus on topic ideas that attract business decision-makers, generate qualified lead inquiries, and position the agency as the go-to expert.

Return ONLY a valid JSON array of objects:
[
  {
    \"topic\": \"Complete High-Converting Blog Topic Title\",
    \"focus_keyword\": \"Target Focus Keyword for Client Acquisition\",
    \"target_audience\": \"Ideal Client Profile / Business Decision Maker\",
    \"search_intent\": \"Commercial / High-Intent Transactional\"
  }
]";
    }
}