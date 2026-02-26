# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Dar Megdaz is a WordPress site built with the **Bricks Builder** page builder theme (v2.0.1). The repository tracks the `wp-content` directory. The site runs locally via **Local by Flywheel** at `darmegdaz.local`.

## Architecture

### Bricks Child Theme (`themes/bricks-child/`)

This is the primary custom code location. Key components:

- **functions.php** — Style enqueuing, custom element registration, builder i18n strings, and MCP REST API endpoints
- **elements/** — Custom Bricks elements (extend `\Bricks\Element`). Elements use PHP for frontend rendering and Vue.js templates for the builder panel
- **style.css** — Child theme stylesheet (only loaded on frontend, not in builder)

### MCP REST API Integration

Custom REST endpoints in the child theme allow AI tools to read/write Bricks page content:

- `GET /wp-json/mcp/v1/bricks/{id}` — Retrieve Bricks element tree for a page
- `POST /wp-json/mcp/v1/bricks/{id}` — Update Bricks element tree (body: `{ "bricks": [...] }`)
- Requires `edit_posts` capability (authenticated requests)
- Bricks content is stored in post meta key `_bricks_page_content_2` (serialized PHP array)

### Bricks Parent Theme (`themes/bricks/`)

The commercial Bricks Builder theme. Do not modify files here — changes are overwritten on theme updates.

### Plugins (21 installed)

Notable plugins: LiteSpeed Cache, Fluent Forms, Rank Math SEO, TranslatePress (multilingual), Google Site Kit, WP Mail SMTP. Plugin code should not be modified directly.

## Development Patterns

- **Custom elements** are registered via `\Bricks\Elements::register_element()` at priority 11 on the `init` hook
- Element files go in `themes/bricks-child/elements/` and are listed in the `$element_files` array in functions.php
- Follow WordPress coding standards (hooks, escaping, `WP_Error` for REST errors)
- REST endpoints use closures registered on `rest_api_init`

## Git Convention

- The `.gitignore` excludes `uploads/`, `cache/`, `.env`, `.log`, and OS files
- Only wp-content is tracked; WordPress core and database are not in the repo

## Pages

Five main pages (by ID):

| ID | Page | Slug |
|----|------|------|
| 43 | Home | `home` |
| 47 | Stay at Dar Megdaz | `stay-at-dar-megdaz` |
| 49 | Explore Megdaz | `explore-megdaz` |
| 45 | About | `about` |
| 53 | Contact | `contact` |

Other pages: Coming Soon (7), Our Impact (51).

## SEO

The site uses **Rank Math SEO** (not Yoast). Rank Math stores meta in post meta:

- `rank_math_title` — custom SEO title
- `rank_math_description` — meta description
- `rank_math_focus_keyword` — primary keyword
- `rank_math_schema` — schema markup (JSON-LD, serialized)

### Current SEO meta (set via `update_post_meta`)

| Page (ID) | Focus Keyword | SEO Title |
|-----------|---------------|-----------|
| Home (43) | guesthouse High Atlas Morocco | Dar Megdaz — Guesthouse in the High Atlas Mountains, Morocco |
| Stay (47) | authentic Morocco accommodation | Stay at Dar Megdaz — Authentic Morocco Accommodation in the Atlas Mountains |
| Explore (49) | Tassaout Valley hiking Morocco | Explore Megdaz — Tassaout Valley Hiking & Trails in Morocco |
| About (45) | Mohamed Megdaz host High Atlas | About Dar Megdaz — Mohamed Megdaz, Your Host in the High Atlas |
| Contact (53) | book Dar Megdaz guesthouse | Contact Dar Megdaz — Book Your Guesthouse Stay in Morocco |

## Local Environment

- **PHP binary**: `C:/Users/ianvi/AppData/Roaming/Local/lightning-services/php-8.2.27+1/bin/win64/php.exe`
- **php.ini** (with mysqli): `C:/Users/ianvi/AppData/Roaming/Local/run/1xSoHjwAw/conf/php/php.ini`
- **WordPress root**: `C:/Users/ianvi/Local Sites/darmegdaz/app/public`
- Run PHP scripts against WP: `"<PHP>" -c "<PHPINI>" script.php`
- Bootstrap WordPress in a script with `require_once __DIR__ . '/wp-load.php';` from the WP root
- WP-CLI is not pre-installed; download `wp-cli.phar` to WP root when needed, run with the PHP binary and php.ini above
