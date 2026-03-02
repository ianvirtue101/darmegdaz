# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

## Project Overview

**Dar Megdaz** is a guesthouse website for a family-run accommodation in Megdaz village, High Atlas Mountains, Morocco. The site is built on WordPress with Bricks Builder and serves as the primary booking and discovery channel for the property.

- **Local URL:** https://darmegdaz.local
- **Live URL:** https://darmegdaz.com
- **Host:** Hostinger
- **Stack:** WordPress, Bricks Builder 2.0.1, TranslatePress (FR active)
- **Repo scope:** `wp-content/` only — WordPress core and database are not tracked

---

## Architecture

### Bricks Child Theme (`themes/bricks-child/`)

Primary custom code location. Never modify the parent `themes/bricks/` — changes are lost on updates.

- **functions.php** — Style enqueuing, custom element registration, MCP REST endpoints
- **elements/** — Custom Bricks elements (PHP + Vue.js for builder panel)
- **style.css** — Frontend-only stylesheet (not loaded in builder)

### MCP REST API (`functions.php`)

Custom endpoints that allow AI agents to read and write Bricks page layouts:

- `GET /wp-json/mcp/v1/bricks/{id}` — Returns Bricks element tree for a page
- `POST /wp-json/mcp/v1/bricks/{id}` — Writes Bricks element tree (body: `{ "bricks": [...] }`)
- Requires `edit_posts` capability
- Bricks data lives in post meta key `_bricks_page_content_2` (serialized array)
- MCP Node.js server runs at `http://localhost:3000` — must be running for agent writes

### Key Plugins

| Plugin          | Purpose                    |
| --------------- | -------------------------- | --- |
| Rank Math SEO   | SEO meta, schema, sitemaps |
| TranslatePress  | Multilingual (FR active)   |
| LiteSpeed Cache | Performance/caching        |
| Fluent Forms    | Contact forms              |
| Google Site Kit | Analytics                  |
| WP Mail SMTP    | Transactional email        | k   |

Do not modify plugin files directly.

---

## Page Registry

| ID  | Page               | Slug               | Notes                                     |
| --- | ------------------ | ------------------ | ----------------------------------------- |
| 43  | Home               | home               | Hero video, testimonials, Mohamed section |
| 47  | Stay at Dar Megdaz | stay-at-dar-megdaz | Rooms overview, booking CTA               |
| 49  | Explore Megdaz     | explore-megdaz     | Hiking, activities                        |
| 45  | About              | about              | Mohamed's story                           |
| 53  | Contact            | contact            | Fluent Form                               |
| 51  | Our Impact         | our-impact         | Community/sustainability                  |
| 7   | Coming Soon        | coming-soon        | Legacy page, unpublish candidate          |

---

## Design System

### Colours

```css
--color-earth: #8b4513; /* Terracotta — primary */
--color-sand: #d4a96a; /* Atlas sand — secondary */
--color-dusk: #2c1810; /* Deep background */
--color-mist: #f5f0e8; /* Light background */
--color-atlas: #4a7c59; /* Mountain green — accent */
--color-white: #fdfaf5; /* Off-white */
```

### Typography

```css
--font-display: "Playfair Display", serif; /* Headlines */
--font-body: "Lato", sans-serif; /* Body */
--font-accent: "Cormorant Garamond", serif; /* Pull quotes */
```

### Tone & Voice

- Evocative, unhurried, immersive
- Speak to travellers seeking authenticity over comfort
- Avoid tourist-brochure language ("stunning views", "unique experience")
- Mohamed's voice is warm, personal, direct — preserve it in all copy

---

## SEO

Plugin: **Rank Math SEO**

Rank Math post meta keys:

- `rank_math_title` — SEO title
- `rank_math_description` — Meta description
- `rank_math_focus_keyword` — Primary keyword
- `rank_math_schema` — Schema markup (JSON-LD, serialized)

### Current SEO Meta

| Page (ID)    | Focus Keyword                   | SEO Title                                                                   |
| ------------ | ------------------------------- | --------------------------------------------------------------------------- |
| Home (43)    | guesthouse High Atlas Morocco   | Dar Megdaz — Guesthouse in the High Atlas Mountains, Morocco                |
| Stay (47)    | authentic Morocco accommodation | Stay at Dar Megdaz — Authentic Morocco Accommodation in the Atlas Mountains |
| Explore (49) | Tassaout Valley hiking Morocco  | Explore Megdaz — Tassaout Valley Hiking & Trails in Morocco                 |
| About (45)   | Mohamed Megdaz host High Atlas  | About Dar Megdaz — Mohamed Megdaz, Your Host in the High Atlas              |
| Contact (53) | book Dar Megdaz guesthouse      | Contact Dar Megdaz — Book Your Guesthouse Stay in Morocco                   |

### Schema Priority

Implement `LodgingBusiness` schema on the homepage with name, description, URL, address, geo coordinates, Mohamed as contact point, and booking.com URL.

---

## External Listings & Booking Links

### Dar Megdaz on OTAs

| Platform    | URL                                                                 | Rating     | Reviews |
| ----------- | ------------------------------------------------------------------- | ---------- | ------- |
| Booking.com | `https://www.booking.com/hotel/ma/dar-megdaz.html`                  | 9.7/10     | 66      |
| Airbnb      | `https://www.airbnb.com/rooms/1061615005740004557`                  | —          | —       |
| TripAdvisor | `https://www.tripadvisor.com/Hotel_Review-g33419032-d33414881-Reviews-Dar_Megdaz-Megdaz_Beni_Mellal_Khenifra.html` | — | — |

### Key Contact Info

- **WhatsApp**: `https://wa.me/212667983588` (Mohamed)
- **Instagram**: `@dar_megdaz`
- **Languages spoken**: English, French, Amazigh (Berber), Arabic

### Pricing (current)

- From €24/night (all rooms), breakfast included
- EUR 24–30 range in schema.org structured data
- No per-room pricing differentiation on the website yet

---

## Competitor Analysis (March 2026)

### Competitor 1: Gite Megdaz Ouhadouch

- **Booking.com**: `https://www.booking.com/hotel/ma/gite-megdaz.fr.html`
- **Rating**: 7.9/10 (74 reviews) — more reviews than Dar Megdaz but lower score
- **Price**: From ~$30 USD/night (~€28)
- **Rooms**: 4 bedrooms, sleeps 11 max. Room types: Quadruple (14m², 4 twins), Double (6m², 1 full), Family (16m², 5 twins)
- **Bathrooms**: Shared only
- **Amenities**: Free WiFi, free parking, shared kitchen, sun terrace, garden, picnic area, pet-friendly, free cancellation
- **Listed on**: Booking.com, Agoda, Airbnb, Lodging World, Casai
- **Languages**: Arabic, French (no English)
- **Strengths**: Higher review volume, multi-platform presence, free cancellation, pet-friendly, shared kitchen for budget travellers
- **Weaknesses**: Lower rating, no English, shared bathrooms, no website, no storytelling/brand

### Competitor 2: Gite D'etape Ait Ali Nito Assounfou

- **Booking.com**: `https://www.booking.com/hotel/ma/gite-d-etape-ait-ali-nito-assounfou.html`
- **Rating**: 9.5/10 (6 reviews) — high but tiny sample
- **Price**: From ~$28 USD/night (~€26)
- **Rooms**: 7 bedrooms, sleeps 20 max. Family rooms (10–12m², 4–5 twins), Triple rooms (9m²)
- **Bathrooms**: Shared (1 total for the property)
- **Amenities**: Free parking, shared kitchen, sun terrace, 24-hour front desk, paid shuttle service, à la carte breakfast, pet-friendly, contactless check-in, restaurant on-site, CCTV security
- **Listed on**: Booking.com, Skyscanner, VillaSahara, A-Hotel, Lonely Planet
- **Languages**: Arabic, French (no English)
- **Strengths**: Lonely Planet feature, restaurant (dinner available), shuttle service, larger capacity for groups, 24-hour front desk
- **Weaknesses**: Only 6 reviews, shared bathroom (1 for 20 guests), no website, no English, non-refundable policy

### Dar Megdaz Competitive Advantages

1. **Highest rating in the area** — 9.7 vs 9.5 and 7.9
2. **Private en-suite bathrooms** — neither competitor offers this
3. **English-speaking host** — critical for international market
4. **Custom branded website** — competitors rely entirely on OTAs
5. **Compelling brand story** — Mohamed's narrative, Amazigh heritage, "hidden village"
6. **Detailed trek offerings** — Explore page with route descriptions (unique)
7. **Schema markup & SEO** — structured data for organic search
8. **On-site guest testimonials** — builds trust before OTA redirect

---

## Site Audit — Current Content & Gaps (March 2026)

### What the site currently has

- **Home**: Full-screen hero, 4 value propositions, host bio, 6 guest reviews, Booking.com 9.8 badge
- **Stay page**: 5 room cards (text-only, no pricing), booking section linking to Booking.com
- **Room pages**: 5-section template (hero, about, what's included, cross-sell, CTA) built programmatically
- **Contact**: WhatsApp CTA, Booking.com link, Fluent Forms contact form
- **About**: Mohamed's story, Amazigh culture, invitation CTA
- **Explore**: 6 trek categories, featured "Lost Valley Route" with details
- **Header**: Logo, nav with Rooms dropdown, pricing badge on room pages ("From €24/night")
- **Footer**: Links, Instagram, WhatsApp, Booking.com rating badge
- **Mobile sticky CTA**: Scroll-triggered "From €24/night · Book Now" → Booking.com

### Booking paths on the site

1. Home hero "Book Your Stay" → Stay page
2. Room page "Book This Room" → **Contact page** (friction point)
3. Stay page "Stay in Megdaz" → Booking.com
4. Contact page WhatsApp link → wa.me
5. Contact page "Book Online" → Booking.com
6. Footer "Stay" link → Booking.com
7. Mobile sticky CTA → Booking.com
8. Fluent Forms on Contact page

### Critical gaps identified

1. **"Book This Room" CTAs go to Contact page** — adds friction for ready-to-book visitors
2. **No per-room pricing** — all rooms show "From €24" with no differentiation
3. **Only 1 photo per room** — no gallery (competitors show multiple on OTAs)
4. **No "How to Get Here" section** — remote village with no directions/map/transport info
5. **No dinner/meal info** — reviews mention dinners but site only says "breakfast included"
6. **Missing Airbnb link** — Stay page mentions Airbnb but doesn't link to actual listing
7. **No consolidated amenities page** — facilities scattered across room pages
8. **Languages spoken not displayed** — English fluency is a major advantage, not highlighted
9. **No cancellation policy visible** — competitors show this
10. **No seasonal guide** — when to visit, weather, what to pack
11. **About page duplicate content** — "House That Built Itself" repeats Amazigh hospitality copy
12. **Purple Room occupancy mismatch** — CLAUDE.md says sleeps 4-5, room page says 2

---

## Improvement Roadmap

### Phase 1 — Quick Wins (no new content from Mohamed needed)

| #  | Task                                                                 | Impact | Effort |
| -- | -------------------------------------------------------------------- | ------ | ------ |
| 1  | Fix "Book This Room" CTAs → Booking.com (or WhatsApp w/ room name)   | High   | Low    |
| 2  | Add per-room pricing to Stay page room cards and room pages          | High   | Low    |
| 3  | Add Airbnb link to Stay page and footer                              | Medium | Low    |
| 4  | Add "How to Get Here" section (Contact page or new section)          | High   | Medium |
| 5  | Add dinner/meal info ("Traditional dinner available on request ~€X") | Medium | Low    |
| 6  | Display languages spoken on About + Contact pages                    | Medium | Low    |
| 7  | Fix About page duplicate content                                     | Low    | Low    |
| 8  | Fix Purple Room occupancy (update to match actual capacity)          | Low    | Low    |
| 9  | Add TripAdvisor link to footer alongside Booking.com badge           | Low    | Low    |

### Phase 2 — Content Enhancements (need photos/info from Mohamed)

| #  | Task                                                                 | Impact    | Effort |
| -- | -------------------------------------------------------------------- | --------- | ------ |
| 10 | Photo gallery per room (4–6 photos: bed, bathroom, view, details)    | Very High | Medium |
| 11 | Shared spaces gallery (terrace, garden, dining area, rooftop)        | High      | Medium |
| 12 | Seasonal guide section (best times, weather, packing tips)           | Medium    | Medium |
| 13 | Consolidated amenities/facilities page or section                    | Medium    | Medium |
| 14 | Cancellation policy info visible on site                             | Medium    | Low    |

### Phase 3 — Competitive Positioning (strategic/ongoing)

| #  | Task                                                                 | Impact | Effort  |
| -- | -------------------------------------------------------------------- | ------ | ------- |
| 15 | Push for more Booking.com reviews (surpass competitor's 74)          | High   | Ongoing |
| 16 | Encourage TripAdvisor reviews from guests                            | Medium | Ongoing |
| 17 | Pursue Lonely Planet or travel blog features                         | High   | Hard    |
| 18 | Consider pet-friendly policy (both competitors allow pets)           | Low    | Low     |
| 19 | Add shuttle/transfer service from Marrakech (like Assounfou does)    | High   | Medium  |

---

## Local Environment

- **PHP binary:** `C:/Users/ianvi/AppData/Roaming/Local/lightning-services/php-8.2.27+1/bin/win64/php.exe`
- **php.ini:** `C:/Users/ianvi/AppData/Roaming/Local/run/1xSoHjwAw/conf/php/php.ini`
- **WordPress root:** `C:/Users/ianvi/Local Sites/darmegdaz/app/public`
- **Run PHP against WP:** `"<PHP>" -c "<PHPINI>" script.php`
- **Bootstrap WP in scripts:** `require_once __DIR__ . '/wp-load.php';` from WP root
- **WP-CLI:** Not pre-installed — download `wp-cli.phar` to WP root when needed

---

## Development Rules

1. **Never edit** `themes/bricks/` parent theme files
2. **Always back up** Bricks JSON before programmatic writes (`wp_get_bricks` before `wp_update_bricks`)
3. **Verify visually** after any Bricks element tree change — load https://darmegdaz.local and confirm
4. **Preserve Mohamed's voice** — do not rewrite copy without explicit instruction
5. **TranslatePress is active** — string changes may affect French translations
6. **MCP server must be running** at localhost:3000 for any Bricks write operations
