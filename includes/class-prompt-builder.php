<?php
if (!defined('ABSPATH')) {
    exit;
}

class GBBG_Prompt_Builder {

    public static function build_system_instruction() {
        $business_name     = get_option('gbbg_business_name', 'Nova Care Australia');
        $business_niche    = get_option('gbbg_business_niche', 'Aged Care & NDIS Support Services');
        $business_location = get_option('gbbg_business_location', 'Australia');
        $default_tone      = get_option('gbbg_default_tone', 'Professional, Empathetic, Authoritative, Caring');
        $primary_cta       = get_option('gbbg_primary_cta', 'Contact Nova Care Australia today to discuss our personalized aged care and NDIS support packages.');
        $service_links     = get_option('gbbg_service_links', '');

        return "You are an elite SEO Content Strategist, Copywriter, and Subject Matter Expert specializing in high-ranking business articles for $business_name ($business_niche located in $business_location).

YOUR GOAL: Write a comprehensive, highly engaging, and 100/100 SEO-optimized blog post that ranks #1 on Google, provides immense value to potential clients, and converts readers into inquiries.

CRITICAL SEO & QUALITY RULES FOR 100/100 SCORE:
1. TITLE: Exactly 50-60 characters, catchy, contains primary focus keyword naturally.
2. META DESCRIPTION: Exactly 150-160 characters, compelling, contains primary focus keyword and a clear Call To Action.
3. KEYWORD PLACEMENT & DENSITY: Target 1.5% to 2.5% keyword density. Include the focus keyword in:
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
6. FAQ SECTION & SCHEMA: Include 3 to 5 frequently asked questions with direct, clear answers.
7. CALL TO ACTION: End with a strong conversion prompt ($primary_cta).
8. INTERNAL LINKS: Naturally reference business services where relevant (Service links available: $service_links).
9. OUTPUT FORMAT: YOU MUST OUTPUT ONLY VALID RAW JSON matching the requested JSON structure. DO NOT wrap in markdown code blocks like ```json ... ``` unless requested, just pure JSON object.";
    }

    public static function build_blog_prompt($topic, $focus_keyword = '', $target_word_count = '1500-2500 words', $custom_instructions = '') {
        $business_name  = get_option('gbbg_business_name', 'Nova Care Australia');
        $business_niche = get_option('gbbg_business_niche', 'Aged Care & NDIS Support Services');
        $tone           = get_option('gbbg_default_tone', 'Professional, Empathetic, Authoritative, Caring');

        if (empty($focus_keyword)) {
            $focus_keyword = $topic;
        }

        $prompt = "Create a full-length, 100/100 SEO optimized blog post for $business_name.

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
  \"excerpt\": \"2-sentence engaging summary excerpt for blog archive.\",
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
            $niche = get_option('gbbg_business_niche', 'Aged Care & NDIS Support Services');
        }
        $business_name = get_option('gbbg_business_name', 'Nova Care Australia');

        return "Generate 6 high-converting, trending business blog topic ideas for $business_name in the $niche industry in Australia.

Return ONLY a valid JSON array of objects:
[
  {
    \"topic\": \"Complete Blog Topic Title\",
    \"focus_keyword\": \"Target Primary Focus Keyword\",
    \"target_audience\": \"Target Audience Profile\",
    \"search_intent\": \"Informational / Commercial / Transactional\"
  }
]";
    }
}