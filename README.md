# Gemini Business SEO Blog Generator

AI-powered WordPress plugin that generates 100/100 SEO-optimized, business-focused blog posts using Google Gemini API.

## Features

- **Google Gemini AI** — Supports gemini-3.8-flash, gemini-2.5-flash, gemini-1.5-flash
- **100/100 SEO Score** — Built-in 12-point SEO audit engine
- **Rank Math & Yoast SEO** — Auto-populates title, description & focus keyword
- **JSON-LD Schema** — BlogPosting + FAQPage structured data injection
- **Business-Focused** — Tailored prompts for your business niche, location & brand tone
- **AI Topic Ideation** — One-click AI-generated topic suggestions
- **Live Preview** — Real-time content preview with animated SEO score meter
- **One-Click Publish** — Publish or save as draft directly from the generator

## Installation

1. Download or clone this repository into `wp-content/plugins/`
2. Activate the plugin in WordPress Admin → Plugins
3. Navigate to **Gemini Blog AI → Settings** and enter your [Google AI Studio API Key](https://aistudio.google.com/app/apikey)
4. Go to **Gemini Blog AI → Generate Blog** and create your first post!

## Settings

All configuration is managed within the plugin Settings page:

| Tab | Settings |
|-----|----------|
| Gemini API | API Key, Model, Temperature, Max Tokens, Top P, Top K |
| Business Profile | Name, Niche, Location, Tone, Word Count, CTA, Service Links |
| SEO Integration | Rank Math, Yoast, FAQ Schema, Article Schema, TOC, Key Takeaways |

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Google Gemini API Key (free from [Google AI Studio](https://aistudio.google.com/app/apikey))

## License

GPLv2 or later