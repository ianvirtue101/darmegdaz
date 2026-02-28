# Bricks Builder Programmatic Skill — Claude Code Reference

Comprehensive guide for building WordPress pages with Bricks Builder v2.0.1 programmatically via PHP scripts or MCP API. Distilled from extensive debugging across 14 build iterations.

---

## 1. Element Tree Format

Bricks stores page content as a flat PHP array of element objects in `_bricks_page_content_2` post meta. Each element has:

```php
[
    'id'       => 'unique_id',       // Short string, must be unique within page
    'name'     => 'section',         // Element type (see §2)
    'parent'   => 'parent_id',       // Parent element ID (omit for root sections)
    'children' => ['child1', 'child2'], // CRITICAL: array of child IDs
    'settings' => [ ... ],           // Element-specific settings
]
```

### CRITICAL: `children` Array Required

**Every element MUST have a `children` key**, even if empty (`[]`). Bricks uses `children` to resolve the render tree. Without it, child elements are silently dropped from output. This was the #1 cause of "sections render but text is invisible" across 6 failed builds.

### Element Hierarchy

```
section (root, no parent)
  └─ container (layout)
       ├─ heading
       ├─ text
       ├─ button
       ├─ image
       └─ container (nested)
            └─ ...
```

Sections are always root elements (no `parent`). Everything else nests inside containers.

---

## 2. Element Types

| Element Name | Usage | Key Settings |
|---|---|---|
| `section` | Page section (root) | `_background`, `_padding`, `_shapeDividers`, `_heightMin` |
| `container` | Flex layout wrapper | `_direction`, `_alignItems`, `_justifyContent`, `_width`, `_flexWrap` |
| `heading` | h1-h6 headings | `text`, `tag`, `_typography` |
| `text` | Rich text (wraps in `<div>`) | `text` (HTML string), `_typography` |
| `button` | CTA button | `text`, `link`, `style`, `_background`, `_typography`, `_padding`, `_border` |
| `image` | Image element | `image` (see §7), `altText`, `_objectFit`, `_aspectRatio`, `_border` |

**Use `text` not `text-basic`** — the Home page uses `text` for all rich text. `text-basic` exists but `text` is the standard.

---

## 3. Colour Format — MUST Be Objects

Every colour value MUST be an array with `hex` key. Plain strings are silently ignored.

```php
// WRONG — produces NO CSS output
'color' => '#FFFFFF'

// CORRECT
'color' => ['hex' => '#FFFFFF']
```

Helper function:
```php
function clr(string $hex): array { return ['hex' => $hex]; }
```

This applies everywhere: `_background.color`, `_typography.color`, `_border.*.color`, `_shapeDividers[].fill`, `_background.imageOverlay.color`.

---

## 4. Settings Key Format

### Dimension values — plain strings, NOT arrays
```php
// WRONG
'_heightMin' => ['vh' => '100']
'_width'     => ['value' => '55', 'unit' => '%']

// CORRECT
'_heightMin' => '100vh'
'_width'     => '55%'
'_widthMax'  => '600px'
```

### Layout — underscore-prefixed keys
```php
'_direction'      => 'column',        // flex-direction
'_alignItems'     => 'center',        // align-items
'_justifyContent' => 'space-between', // justify-content
'_flexWrap'       => 'wrap',          // flex-wrap
```

### Padding/Margin — four-sided object
```php
'_padding' => ['top' => '2rem', 'bottom' => '2rem', 'left' => '2rem', 'right' => '2rem']
'_margin'  => ['bottom' => '1.5rem']  // can specify only some sides
```

### Border — radius + sides
```php
'_border' => [
    'radius' => ['top' => '8px', 'right' => '8px', 'bottom' => '8px', 'left' => '8px'],
    'top'    => ['width' => '1px', 'style' => 'solid', 'color' => clr('#F5F0E8')],
    // repeat for right, bottom, left
]
```

### Typography
```php
'_typography' => [
    'color'           => clr('#FFFFFF'),
    'font-size'       => '1.25rem',
    'font-weight'     => '700',
    'font-style'      => 'italic',
    'line-height'     => '1.6',
    'letter-spacing'  => '0.1em',
    'text-transform'  => 'uppercase',
    'text-align'      => 'center',
]
```

---

## 5. Shape Dividers (v2.0.1)

Old `shapeTop`/`shapeBottom` keys are ignored. Use `_shapeDividers` array:

```php
'_shapeDividers' => [[
    'id'             => 'unique_string',
    'shape'          => 'wave-brush',     // shape slug
    'fill'           => clr('#F5F0E8'),   // colour object
    'bottom'         => '0rem',           // position (or 'top')
    'height'         => '8rem',
    'flipHorizontal' => true,             // optional
]]
```

---

## 6. Animations & Interactions

### Scroll-triggered entrance animations

Uses Bricks' built-in Animate.css integration + IntersectionObserver:

```php
'_interactions' => [[
    'id'                => 'anim-unique',
    'trigger'           => 'enterView',
    'action'            => 'startAnimation',
    'target'            => 'self',
    'animationType'     => 'fadeInUp',    // any Animate.css name
    'animationDuration' => '0.8s',
    'animationDelay'    => '0.2s',
    'runOnce'           => true,          // don't replay on re-scroll
]]
```

**Popular animation types:** `fadeIn`, `fadeInUp`, `fadeInDown`, `fadeInLeft`, `fadeInRight`, `slideInUp`, `slideInLeft`, `zoomIn`, `bounceIn`

### Custom CSS per element

Use `_cssCustom` with the **actual element selector** `#brxe-{id}`. The `%root%` placeholder only works in the Bricks visual editor (replaced by JavaScript) — it is NOT replaced server-side.

```php
// WRONG — %root% outputs literally in frontend CSS
'_cssCustom' => '%root% { transition: transform 0.3s; } %root%:hover { transform: scale(1.05); }'

// CORRECT — use actual element ID
'_cssCustom' => '#brxe-rm007 { transition: transform 0.3s; } #brxe-rm007:hover { transform: scale(1.05); }'
```

Helper function:
```php
function css(string $id, string $rules): string {
    return str_replace('%root%', "#brxe-$id", $rules);
}

// Usage: write with %root% for readability, auto-replace with actual selector
'_cssCustom' => css($element_id, '%root% { ... } %root%:hover { ... }')
```

### Hover effects that work well

```php
// Card lift + shadow
css($id, '%root% { transition: transform 0.35s ease, box-shadow 0.35s ease; }
          %root%:hover { transform: translateY(-8px); box-shadow: 0 16px 40px rgba(0,0,0,0.3); }')

// Button glow
css($id, '%root% { transition: transform 0.3s ease, filter 0.3s ease; }
          %root%:hover { transform: translateY(-2px); filter: brightness(1.1); }')

// Pill slide
css($id, '%root% { transition: transform 0.3s ease; }
          %root%:hover { transform: translateX(8px); }')

// Image zoom
css($id, '%root% { transition: transform 0.5s ease; }
          %root%:hover { transform: scale(1.03); }')

// Button color invert
css($id, '%root% { transition: background-color 0.3s ease, color 0.3s ease; }
          %root%:hover { background-color: #F5F0E8 !important; color: #2C1810 !important; }')
```

### Child element hover targeting

To change a child element when parent is hovered, put the rule in the parent's `_cssCustom`:

```php
// On card, change h3 color when card is hovered
css($card_id, '%root%:hover h3 { color: #FFFFFF !important; }')

// The h3 element also needs a transition
css($h3_id, '%root% { transition: color 0.35s ease; }')
```

---

## 7. Image Element

```php
// With WordPress attachment ID (preferred)
'image' => ['id' => 483, 'size' => 'large']

// External URL (no attachment)
'image' => ['external' => true, 'url' => 'https://example.com/photo.jpg']
```

Additional image settings:
```php
'altText'      => 'Description',
'_objectFit'   => 'cover',
'_aspectRatio' => '4/3',
'stretch'      => true,        // width: 100%
'loading'      => 'lazy',
```

---

## 8. Database Write Pattern

### Required meta keys
```php
update_post_meta($id, '_bricks_editor_mode',  'bricks');
update_post_meta($id, '_bricks_template_type', 'content');
```

### Force write (bypass equality check)
```php
delete_post_meta($post_id, '_bricks_page_content_2');
add_post_meta($post_id, '_bricks_page_content_2', $elements, true);
```

### Cache busting (required after every write)
```php
wp_cache_delete($post_id, 'post_meta');
clean_post_cache($post_id);
if (class_exists('\LiteSpeed\Purge')) \LiteSpeed\Purge::purge_post($post_id);
do_action('litespeed_purge_post', $post_id);
do_action('litespeed_purge_all');
```

---

## 9. Verification Pattern

Always verify after writing by fetching the page HTML:

```php
$ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
$html = @file_get_contents('https://darmegdaz.local/rooms/green-room/', false, $ctx);

// Check content presence
$checks = ['The Green Room', 'Book Now', 'Valley Views'];
foreach ($checks as $label) {
    echo "$label: " . (stripos($html, $label) !== false ? 'YES' : 'NO') . "\n";
}

// Check animations
echo "Interactions: " . (stripos($html, 'data-interactions') !== false ? 'YES' : 'NO') . "\n";
echo "%root% literal (BAD): " . (strpos($html, '%root%') !== false ? 'YES' : 'NO') . "\n";
```

Also validate element tree structure before writing:
```php
$ids = array_column($elements, 'id');
foreach ($elements as $el) {
    if (!empty($el['parent']) && !in_array($el['parent'], $ids)) {
        echo "ORPHAN: {$el['id']}\n";
    }
    foreach (($el['children'] ?? []) as $child_id) {
        if (!in_array($child_id, $ids)) {
            echo "MISSING CHILD: {$el['id']} -> $child_id\n";
        }
    }
}
```

---

## 10. Common Pitfalls (Ranked by Hours Wasted)

1. **Missing `children` array** — Elements without `children` key are silently dropped. Always include `'children' => []` even on leaf elements. (6 builds wasted)

2. **Plain colour strings** — `'#FFFFFF'` produces no CSS. Must be `['hex' => '#FFFFFF']`. (2 builds wasted)

3. **`%root%` in `_cssCustom`** — Only replaced in the visual editor JS. Use `#brxe-{id}` in programmatic code. (1 build wasted)

4. **Wrong settings key format** — Dimensions must be plain strings (`'100vh'`), not arrays (`['vh'=>'100']`). Layout keys need underscore prefix (`_direction` not `direction`). (1 build wasted)

5. **`update_post_meta` no-op** — Returns false if value is identical. Use delete+add pattern. (1 build wasted)

6. **LiteSpeed cache** — Serves stale pages. Must bust cache after every meta write. (1 build wasted)

7. **Code elements** — `code` elements with `executeCode: true` require `signature: wp_hash($code)`. Even with valid signatures, code elements may not render in all contexts (double-verification in `sanitize_element_php_code` re-reads from DB). Use native elements (`text`, `heading`, `container`) instead. (2 builds wasted)

---

## 11. MCP Server Integration

Local MCP proxy at `localhost:3000`:

```
GET  http://localhost:3000/wp_get_bricks/{post_id}    → returns element array
POST http://localhost:3000/wp_update_bricks/{post_id}  → body: {"bricks": [...]}
```

The MCP endpoints in `themes/bricks-child/functions.php` handle cache busting automatically. However, for complex multi-element builds, PHP scripts run via CLI are more reliable because they allow structure validation and self-fetch verification.

---

## 12. Script Template

Complete boilerplate for a new page build:

```php
<?php
require_once __DIR__ . '/wp-load.php';

$post_id  = 526;
$meta_key = '_bricks_page_content_2';

function clr(string $hex): array { return ['hex' => $hex]; }

$elements = [];
$i = 0;
function eid(): string { global $i; return 'rm' . str_pad(++$i, 3, '0', STR_PAD_LEFT); }

$anim_counter = 0;
function scrollAnim(string $type = 'fadeInUp', string $dur = '0.8s', string $delay = '0s'): array {
    global $anim_counter;
    return [['id'=>'a'.(++$anim_counter),'trigger'=>'enterView','action'=>'startAnimation',
             'target'=>'self','animationType'=>$type,'animationDuration'=>$dur,
             'animationDelay'=>$delay,'runOnce'=>true]];
}

function css(string $id, string $rules): string {
    return str_replace('%root%', "#brxe-$id", $rules);
}

// Build elements here...

// Validate
$ids = array_column($elements, 'id');
$errs = 0;
foreach ($elements as $el) {
    if (!empty($el['parent']) && !in_array($el['parent'], $ids)) { $errs++; }
    foreach (($el['children'] ?? []) as $cid) { if (!in_array($cid, $ids)) $errs++; }
}
if ($errs) die("$errs structure errors\n");

// Write
delete_post_meta($post_id, $meta_key);
add_post_meta($post_id, $meta_key, $elements, true);
update_post_meta($post_id, '_bricks_editor_mode', 'bricks');
update_post_meta($post_id, '_bricks_template_type', 'content');
wp_cache_delete($post_id, 'post_meta');
clean_post_cache($post_id);
do_action('litespeed_purge_all');

// Verify
$html = @file_get_contents('https://darmegdaz.local/...', false,
    stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]));
// ... check content presence
```

---

## 13. Room Pages Reference

| ID  | Room         | Slug         | Image ID | Hero Image           |
|-----|-------------|-------------|----------|---------------------|
| 526 | Green Room  | green-room  | 483      | Green-Room-Hero.avif |
| 527 | Red Room    | red-room    | ?        | Red-Room.avif        |
| 528 | Silver Room | silver-room | ?        | Silver-Room.avif     |
| 529 | Room 3 Green| room-3-green| 487      | Room-3-Green.avif    |
| 530 | Purple Room | purple-room | ?        | purple-room.avif     |

CPT slug: `room`. URL pattern: `/rooms/{slug}/`

---

## 14. Design System

### Colour Palette
```
#1C140C / #2C1810  — Dark espresso (hero bg, CTA bg)
#F5F0E8            — Cream (details section bg)
#4A7C59            — Sage green (features bg, accent labels)
#3A6348            — Darker sage (feature cards)
#D4A96A            — Gold (accents, labels, button bg)
#E8E0D4            — Cream soft (body text on dark)
#C4B8A8            — Warm gray (subtitle text)
#4A3728            — Dark brown (body text on light)
#1A0F08            — Near-black (detail pills)
```

### Typography
- Headings: Playfair Display, italic, 700 weight
- Body: IBM Plex Sans (site default), 400 weight
- Labels: uppercase, 700 weight, letter-spacing 0.3em
- Body on light bg: `#4A3728`, 1.05rem, line-height 1.8
- Body on dark bg: `#E8E0D4`, cream soft

### Animation Timing
- Hero elements: staggered fadeInLeft 0.1s → 0.55s
- Hero image: fadeInRight 0.3s delay
- Section headings: fadeInUp, 0.15s delay after label
- Detail pills: fadeInRight, cascade 0.1s each
- Feature cards: fadeInUp, cascade 0.1s each
- All animations: `runOnce: true`
