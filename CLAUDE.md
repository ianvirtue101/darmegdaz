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

| ID  | Page               | Slug                 |
| --- | ------------------ | -------------------- |
| 43  | Home               | `home`               |
| 47  | Stay at Dar Megdaz | `stay-at-dar-megdaz` |
| 49  | Explore Megdaz     | `explore-megdaz`     |
| 45  | About              | `about`              |
| 53  | Contact            | `contact`            |

Other pages: Coming Soon (7), Our Impact (51).

### Room Pages (Custom Post Type `room`)

| ID  | Room         | Slug         | Image file           | Sleeps | Beds                |
| --- | ------------ | ------------ | -------------------- | ------ | ------------------- |
| 526 | Green Room   | green-room   | Green-Room-Hero.avif | 2      | 1 double            |
| 527 | Red Room     | red-room     | Red-Room.avif        | 1-2    | 2 singles           |
| 528 | Silver Room  | silver-room  | Silver-Room.avif     | 2-3    | 1 double + 1 single |
| 529 | Room 3 Green | room-3-green | Room-3-Green.avif    | 3      | 3 singles           |
| 530 | Purple Room  | purple-room  | purple-room.avif     | 4-5    | 5 singles           |

Room custom meta: `_room_sleeps`, `_room_beds`.

## SEO

The site uses **Rank Math SEO** (not Yoast). Rank Math stores meta in post meta:

- `rank_math_title` — custom SEO title
- `rank_math_description` — meta description
- `rank_math_focus_keyword` — primary keyword
- `rank_math_schema` — schema markup (JSON-LD, serialized)

### Current SEO meta (set via `update_post_meta`)

| Page (ID)    | Focus Keyword                   | SEO Title                                                                   |
| ------------ | ------------------------------- | --------------------------------------------------------------------------- |
| Home (43)    | guesthouse High Atlas Morocco   | Dar Megdaz — Guesthouse in the High Atlas Mountains, Morocco                |
| Stay (47)    | authentic Morocco accommodation | Stay at Dar Megdaz — Authentic Morocco Accommodation in the Atlas Mountains |
| Explore (49) | Tassaout Valley hiking Morocco  | Explore Megdaz — Tassaout Valley Hiking & Trails in Morocco                 |
| About (45)   | Mohamed Megdaz host High Atlas  | About Dar Megdaz — Mohamed Megdaz, Your Host in the High Atlas              |
| Contact (53) | book Dar Megdaz guesthouse      | Contact Dar Megdaz — Book Your Guesthouse Stay in Morocco                   |

## Local Environment

- **PHP binary**: `C:/Users/ianvi/AppData/Roaming/Local/lightning-services/php-8.2.27+1/bin/win64/php.exe`
- **php.ini** (with mysqli): `C:/Users/ianvi/AppData/Roaming/Local/run/1xSoHjwAw/conf/php/php.ini`
- **WordPress root**: `C:/Users/ianvi/Local Sites/darmegdaz/app/public`
- Run PHP scripts against WP: `"<PHP>" -c "<PHPINI>" script.php`
- Bootstrap WordPress in a script with `require_once __DIR__ . '/wp-load.php';` from the WP root
- WP-CLI is not pre-installed; download `wp-cli.phar` to WP root when needed, run with the PHP binary and php.ini above

## Bricks Builder — Critical Technical Requirements (v2.0.1)

These rules were confirmed by code inspection of `themes/bricks/includes/assets.php` and live debugging.

### 1. Required post meta for Bricks to render

Every post/page using Bricks **must** have these two meta keys, or Bricks silently falls back to the theme's block editor template:

```php
update_post_meta($id, '_bricks_editor_mode',  'bricks');
update_post_meta($id, '_bricks_template_type', 'content');
```

Content is stored in: `_bricks_page_content_2` (serialised PHP array).

### 2. Colour object format — ALL colours must be arrays with a `hex` key

Bricks' CSS generator (`assets.php` line 692) only reads `$color['hex']`. A plain hex string like `'#FFFFFF'` has no `['hex']` key and produces **no CSS output** — the colour is silently ignored.

**WRONG** (produces no CSS):

```php
'_background' => ['color' => '#4A7C59'],
'_typography' => ['color' => '#FFFFFF'],
```

**CORRECT**:

```php
'_background' => ['color' => ['hex' => '#4A7C59', 'id' => 'clrsage', 'name' => 'Sage Green']],
'_typography' => ['color' => ['hex' => '#FFFFFF', 'id' => 'clrwhite', 'name' => 'White']],
```

The `id` and `name` keys are optional but recommended for consistency. Use a helper:

```php
function clr(string $hex, string $id = '', string $name = ''): array {
    $obj = ['hex' => $hex];
    if ($id)   $obj['id']   = $id;
    if ($name) $obj['name'] = $name;
    return $obj;
}
```

This applies to **every** colour value: `_background.color`, `_background.imageOverlay.color`, `_typography.color`, `_border.*.color`, `_shapeDividers[].fill`, etc.

### 3. Shape dividers — Bricks 2.0.1 format

The old Bricks 1.x keys `shapeTop` / `shapeBottom` are silently ignored. Use `_shapeDividers` array:

```php
'_shapeDividers' => [
    [
        'id'             => 'sd_unique_id',    // unique string
        'shape'          => 'wave-brush',       // shape slug
        'fill'           => clr('#F5F0E8', 'clrcream', 'Cream'),  // colour object
        'bottom'         => '0rem',             // OR 'top' => '0rem'
        'height'         => '8rem',
        'flipHorizontal' => true,               // optional
    ],
],
```

### 4. Cache busting after programmatic meta updates

The LiteSpeed object-cache drop-in (`wp-content/object-cache.php`) caches `get_post_meta()` in memory. After any `update_post_meta()` / `delete_post_meta()` / `add_post_meta()` call, bust all layers:

```php
wp_cache_delete($id, 'post_meta');
clean_post_cache($id);
if (class_exists('\LiteSpeed\Purge')) {
    \LiteSpeed\Purge::purge_post($id);
}
do_action('litespeed_purge_post', $id);
do_action('litespeed_purge_all');
```

This is already implemented in the MCP REST POST handler in `themes/bricks-child/functions.php`.

### 5. Element `children` array — REQUIRED on every element

Every element MUST include a `children` key, even leaf elements (`'children' => []`). Bricks uses this array to build the render tree. Without it, child content is silently dropped from HTML output.

```php
// WRONG — heading won't render
['id' => 'h1', 'name' => 'heading', 'parent' => 'c1', 'settings' => [...]]

// CORRECT
['id' => 'h1', 'name' => 'heading', 'parent' => 'c1', 'children' => [], 'settings' => [...]]
```

### 6. Force DB write with delete + add

`update_post_meta()` returns `false` (no-op) if the serialised value is identical to what is already stored. To guarantee a write, use:

```php
delete_post_meta($post_id, $meta_key);
$new_id = add_post_meta($post_id, $meta_key, $elements, true);
```

### 6. CSS generation modes

- **`cssLoading = 'file'`**: Bricks writes `wp-content/uploads/bricks/css/post-{id}.min.css` on `save_post`. This file must exist and be fresh for styles to load.
- **Inline mode** (default / not set): CSS is generated on every page load via `Assets::generate_inline_css()`, reading directly from the `_bricks_page_content_2` meta. No CSS file needed.
- Both modes require colour objects (rule 2 above) — plain strings produce empty CSS either way.

### 7. MCP REST API (local proxy at localhost:3000)

- `GET  http://localhost:3000/wp_get_bricks/{id}` — returns the element array
- `POST http://localhost:3000/wp_update_bricks/{id}` — body **must** be `{"bricks": [...]}` (not a raw array)
- Authenticated via WordPress nonce/cookie or Basic Auth
- Implemented in `themes/bricks-child/functions.php`

### 8. Animations — `_interactions` (scroll-triggered)

Bricks uses Animate.css + IntersectionObserver. Add to any element's settings:

```php
'_interactions' => [[
    'id' => 'anim-1', 'trigger' => 'enterView', 'action' => 'startAnimation',
    'target' => 'self', 'animationType' => 'fadeInUp',
    'animationDuration' => '0.8s', 'animationDelay' => '0.2s',
    'runOnce' => true,   // play once only
]]
```

Types: `fadeIn`, `fadeInUp`, `fadeInLeft`, `fadeInRight`, `slideInUp`, `zoomIn`, etc.

### 9. Custom CSS — `_cssCustom` (hover effects)

**IMPORTANT**: `%root%` only works in the Bricks visual editor (replaced via JS). For programmatic use, substitute the actual selector `#brxe-{elementId}`:

```php
function css(string $id, string $rules): string {
    return str_replace('%root%', "#brxe-$id", $rules);
}

'_cssCustom' => css($myId, '%root% { transition: transform 0.3s; } %root%:hover { transform: translateY(-6px); }')
```

### 10. Image element format

```php
'name' => 'image',
'settings' => [
    'image' => ['id' => 483, 'size' => 'large'],  // WP attachment ID
    'altText' => 'Description', '_objectFit' => 'cover',
    '_aspectRatio' => '4/3', 'stretch' => true,
]
```

### 11. Design system — confirmed colour palette

```php
$cream      = clr('#F5F0E8', 'clrcream',  'Cream');
$dark       = clr('#2C1810', 'clrdark',   'Dark Espresso');
$sage       = clr('#4A7C59', 'clrsage',   'Sage Green');
$gold       = clr('#D4A96A', 'clrgold',   'Gold');
$white      = clr('#FFFFFF', 'clrwhite',  'White');
$cream_soft = clr('#E8E0D4', 'clrcrmsoft','Cream Soft');
$dark_pill  = clr('#1A0F08', 'clrdkpill', 'Dark Pill');
$dark_body  = clr('#4A3728', 'clrdkbody', 'Dark Body');
```

Typography: Playfair Display (headings, italic), Lato (body/labels).

Additional notes:

    -webkit-text-size-adjust: 100%;
    --bricks-transition: all 0.2s;
    --bricks-color-primary: #ffd64f;
    --bricks-color-secondary: #fc5778;
    --bricks-text-dark: #212121;
    --bricks-text-medium: #616161;
    --bricks-text-light: #9e9e9e;
    --bricks-text-info: #00b0f4;
    --bricks-text-success: #11b76b;
    --bricks-text-warning: #ffa100;
    --bricks-text-danger: #fa4362;
    --bricks-bg-info: #e5f3ff;
    --bricks-bg-success: #e6f6ed;
    --bricks-bg-warning: #fff2d7;
    --bricks-bg-danger: #ffe6ec;
    --bricks-bg-dark: #263238;
    --bricks-bg-light: #f5f6f7;
    --bricks-border-color: #dddedf;
    --bricks-border-radius: 4px;
    --bricks-tooltip-bg: #23282d;
    --bricks-tooltip-text: #eaecef;
    --wp--preset--aspect-ratio--square: 1;
    --wp--preset--aspect-ratio--4-3: 4/3;
    --wp--preset--aspect-ratio--3-4: 3/4;
    --wp--preset--aspect-ratio--3-2: 3/2;
    --wp--preset--aspect-ratio--2-3: 2/3;
    --wp--preset--aspect-ratio--16-9: 16/9;
    --wp--preset--aspect-ratio--9-16: 9/16;
    --wp--preset--color--black: #000000;
    --wp--preset--color--cyan-bluish-gray: #abb8c3;
    --wp--preset--color--white: #ffffff;
    --wp--preset--color--pale-pink: #f78da7;
    --wp--preset--color--vivid-red: #cf2e2e;
    --wp--preset--color--luminous-vivid-orange: #ff6900;
    --wp--preset--color--luminous-vivid-amber: #fcb900;
    --wp--preset--color--light-green-cyan: #7bdcb5;
    --wp--preset--color--vivid-green-cyan: #00d084;
    --wp--preset--color--pale-cyan-blue: #8ed1fc;
    --wp--preset--color--vivid-cyan-blue: #0693e3;
    --wp--preset--color--vivid-purple: #9b51e0;
    --wp--preset--gradient--vivid-cyan-blue-to-vivid-purple: linear-gradient(135deg,rgb(6,147,227) 0%,rgb(155,81,224) 100%);
    --wp--preset--gradient--light-green-cyan-to-vivid-green-cyan: linear-gradient(135deg,rgb(122,220,180) 0%,rgb(0,208,130) 100%);
    --wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange: linear-gradient(135deg,rgb(252,185,0) 0%,rgb(255,105,0) 100%);
    --wp--preset--gradient--luminous-vivid-orange-to-vivid-red: linear-gradient(135deg,rgb(255,105,0) 0%,rgb(207,46,46) 100%);
    --wp--preset--gradient--very-light-gray-to-cyan-bluish-gray: linear-gradient(135deg,rgb(238,238,238) 0%,rgb(169,184,195) 100%);
    --wp--preset--gradient--cool-to-warm-spectrum: linear-gradient(135deg,rgb(74,234,220) 0%,rgb(151,120,209) 20%,rgb(207,42,186) 40%,rgb(238,44,130) 60%,rgb(251,105,98) 80%,rgb(254,248,76) 100%);
    --wp--preset--gradient--blush-light-purple: linear-gradient(135deg,rgb(255,206,236) 0%,rgb(152,150,240) 100%);
    --wp--preset--gradient--blush-bordeaux: linear-gradient(135deg,rgb(254,205,165) 0%,rgb(254,45,45) 50%,rgb(107,0,62) 100%);
    --wp--preset--gradient--luminous-dusk: linear-gradient(135deg,rgb(255,203,112) 0%,rgb(199,81,192) 50%,rgb(65,88,208) 100%);
    --wp--preset--gradient--pale-ocean: linear-gradient(135deg,rgb(255,245,203) 0%,rgb(182,227,212) 50%,rgb(51,167,181) 100%);
    --wp--preset--gradient--electric-grass: linear-gradient(135deg,rgb(202,248,128) 0%,rgb(113,206,126) 100%);
    --wp--preset--gradient--midnight: linear-gradient(135deg,rgb(2,3,129) 0%,rgb(40,116,252) 100%);
    --wp--preset--font-size--small: 13px;
    --wp--preset--font-size--medium: 20px;
    --wp--preset--font-size--large: 36px;
    --wp--preset--font-size--x-large: 42px;
    --wp--preset--spacing--20: 0.44rem;
    --wp--preset--spacing--30: 0.67rem;
    --wp--preset--spacing--40: 1rem;
    --wp--preset--spacing--50: 1.5rem;
    --wp--preset--spacing--60: 2.25rem;
    --wp--preset--spacing--70: 3.38rem;
    --wp--preset--spacing--80: 5.06rem;
    --wp--preset--shadow--natural: 6px 6px 9px rgba(0, 0, 0, 0.2);
    --wp--preset--shadow--deep: 12px 12px 50px rgba(0, 0, 0, 0.4);
    --wp--preset--shadow--sharp: 6px 6px 0px rgba(0, 0, 0, 0.2);
    --wp--preset--shadow--outlined: 6px 6px 0px -3px rgb(255, 255, 255), 6px 6px rgb(0, 0, 0);
    --wp--preset--shadow--crisp: 6px 6px 0px rgb(0, 0, 0);
    --bricks-color-587bf1: #f5f5f5;
    --bricks-color-9137cc: #e0e0e0;
    --bricks-color-a4fbf5: #9e9e9e;
    --bricks-color-0e03e4: #616161;
    --bricks-color-0446b3: #424242;
    --bricks-color-5bb9f4: #212121;
    --bricks-color-af35ec: #ffeb3b;
    --bricks-color-32ed43: #ffc107;
    --bricks-color-9b55a2: #ff9800;
    --bricks-color-16da90: #ff5722;
    --bricks-color-e66cf3: #f44336;
    --bricks-color-7e6a67: #9c27b0;
    --bricks-color-1cab84: #2196f3;
    --bricks-color-ad8971: #03a9f4;
    --bricks-color-4e4ba7: #81D4FA;
    --bricks-color-696955: #4caf50;
    --bricks-color-907a7b: #8bc34a;
    --bricks-color-1647b1: #cddc39;
    --bricks-color-rduedy: #a63e2f;
    --bricks-color-dyuoeo: #b88b5c;
    --bricks-color-elwmfz: #f5f1e9;
    --bricks-color-tgyvnf: #33281f;
    --bricks-color-whbggm: #9b6449;
    --bricks-color-lvggho: #1e1e1e;
    --bricks-color-jsagoy: #b88b5c;
    --bricks-color-cqhtdq: #4a5b40;
    --bricks-color-ocpdkh: #738665;
    --bricks-color-luyjuj: #8a3b2a;
    --bricks-vh: 23.66px;
    -webkit-font-smoothing: antialiased;
    color: #363636;
    font-family: "IBM Plex Sans";
    font-weight: 400;
    font-size: 1rem;
    line-height: 1.6;
    box-sizing: border-box;
    display: block;
    flex: 1;
    position: relative;
    width: 100%;
    border-color: var(--bricks-color-lvggho);
