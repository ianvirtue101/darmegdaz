<?php
/**
 * Bricks Child Theme functions.
 */

/**
 * Register/enqueue custom scripts and styles
 */
add_action('wp_enqueue_scripts', function () {
	// Enqueue your files on the canvas & frontend, not the builder panel.
	// Otherwise custom CSS might affect builder.
	if (!function_exists('bricks_is_builder_main') || !bricks_is_builder_main()) {
		wp_enqueue_style(
			'bricks-child',
			get_stylesheet_uri(),
			['bricks-frontend'],
			filemtime(get_stylesheet_directory() . '/style.css')
		);
	}
});

/**
 * Register custom elements
 */
add_action('init', function () {
	$element_files = [
		__DIR__ . '/elements/title.php',
	];

	foreach ($element_files as $file) {
		if (file_exists($file)) {
			\Bricks\Elements::register_element($file);
		}
	}
}, 11);

/**
 * Add text strings to builder
 */
add_filter('bricks/builder/i18n', function ($i18n) {
	// For element category 'custom'
	$i18n['custom'] = esc_html__('Custom', 'bricks');
	return $i18n;
});

/**
 * Register 'room' custom post type
 */
add_action('init', function () {
	register_post_type('room', [
		'labels' => [
			'name'               => 'Rooms',
			'singular_name'      => 'Room',
			'add_new'            => 'Add New Room',
			'add_new_item'       => 'Add New Room',
			'edit_item'          => 'Edit Room',
			'new_item'           => 'New Room',
			'view_item'          => 'View Room',
			'search_items'       => 'Search Rooms',
			'not_found'          => 'No rooms found',
			'not_found_in_trash' => 'No rooms found in Trash',
			'all_items'          => 'All Rooms',
			'menu_name'          => 'Rooms',
		],
		'public'       => true,
		'has_archive'  => false,
		'show_in_rest' => true,
		'rewrite'      => ['slug' => 'rooms', 'with_front' => false],
		'supports'     => ['title', 'thumbnail', 'custom-fields'],
		'menu_icon'    => 'dashicons-building',
	]);
});

/**
 * Register room meta fields for REST API
 */
add_action('init', function () {
	$meta_args = [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	];

	register_post_meta('room', '_room_sleeps', $meta_args);
	register_post_meta('room', '_room_beds', $meta_args);
});

/**
 * MCP: Custom REST endpoints to read/write Bricks page data
 *
 * Endpoints:
 *  GET  /wp-json/mcp/v1/bricks/{id}
 *  POST /wp-json/mcp/v1/bricks/{id}   body: { "bricks": [ ... ] }
 *
 * Notes:
 * - Your DB shows Bricks content stored in _bricks_page_content_2 (serialized array)
 * - We return "bricks" as a normal JSON array/object
 * - We accept "bricks" as an array and write it back using update_post_meta (WP handles serialization)
 */
add_action('rest_api_init', function () {

	$permission = function () {
		return current_user_can('edit_posts');
	};

	/**
	 * Find the correct Bricks meta key on this install for a given post ID.
	 * Returns: [meta_key|null, raw_value|null]
	 */
	$get_bricks_meta = function (int $id): array {
		$possible_keys = [
			'_bricks_page_content_2',
			'_bricks_page_content',
		];

		foreach ($possible_keys as $key) {
			$val = get_post_meta($id, $key, true);
			if (!empty($val)) {
				return [$key, $val];
			}
		}

		return [null, null];
	};

	// GET Bricks data
	register_rest_route('mcp/v1', '/bricks/(?P<id>\d+)', [
		'methods'             => 'GET',
		'permission_callback' => $permission,
		'callback'            => function ($request) use ($get_bricks_meta) {
			$id = (int) $request['id'];

			$post = get_post($id);
			if (!$post) {
				return new WP_Error('not_found', 'Post not found', ['status' => 404]);
			}

			// Optional safety: restrict to pages only
			// if ($post->post_type !== 'page') {
			// 	return new WP_Error('invalid_type', 'Only pages supported', ['status' => 400]);
			// }

			[$meta_key, $raw] = $get_bricks_meta($id);

			if (empty($raw)) {
				return new WP_Error('no_data', 'No Bricks data found', ['status' => 404]);
			}

			// Bricks commonly stores a serialized PHP array, so decode safely
			$decoded = maybe_unserialize($raw);

			return rest_ensure_response([
				'success'  => true,
				'page_id'  => $id,
				'title'    => get_the_title($id),
				'type'     => $post->post_type,
				'meta_key' => $meta_key,
				'bricks'   => $decoded,
			]);
		},
	]);

	// POST update Bricks data
	register_rest_route('mcp/v1', '/bricks/(?P<id>\d+)', [
		'methods'             => 'POST',
		'permission_callback' => $permission,
		'callback'            => function ($request) use ($get_bricks_meta) {
			$id = (int) $request['id'];

			$post = get_post($id);
			if (!$post) {
				return new WP_Error('not_found', 'Post not found', ['status' => 404]);
			}

			$bricks = $request->get_param('bricks');

			// Must be an array (Bricks element tree)
			if (!is_array($bricks)) {
				return new WP_Error(
					'invalid_payload',
					'"bricks" must be an array (element tree)',
					['status' => 400]
				);
			}

			// Update whichever key exists on this site, otherwise default to _2
			[$meta_key, $raw] = $get_bricks_meta($id);
			if (!$meta_key) {
				$meta_key = '_bricks_page_content_2';
			}

			// update_post_meta will serialize arrays automatically
			update_post_meta($id, $meta_key, $bricks);

			// Bust all cache layers so the frontend reflects the new content immediately
			// 1. WordPress object cache (LiteSpeed drop-in caches post_meta in memory)
			wp_cache_delete($id, 'post_meta');
			clean_post_cache($id);

			// 2. LiteSpeed page cache
			if (class_exists('\LiteSpeed\Purge')) {
				\LiteSpeed\Purge::purge_post($id);
			}
			do_action('litespeed_purge_post', $id);

			// 3. WP Fastest Cache
			if (function_exists('wpfc_clear_post_cache_by_id')) {
				wpfc_clear_post_cache_by_id($id);
			}

			return rest_ensure_response([
				'success'  => true,
				'page_id'  => $id,
				'meta_key' => $meta_key,
			]);
		},
	]);

});

/**
 * Output HotelRoom schema for individual room pages (CPT: room)
 */
add_action('wp_head', function () {
	if (!is_singular('room')) {
		return;
	}

	$post_id = get_the_ID();
	$url     = get_permalink($post_id);

	// Room-specific data keyed by post ID
	$room_data = [
		526 => [
			'name'        => 'The Green Room',
			'description' => 'A peaceful double room (~18m²) overlooking the Tessaout Valley with private bathroom, traditional Moroccan breakfast included, and views of the High Atlas Mountains.',
			'bed'         => 'Double',
			'occupancy'   => 2,
			'image'       => '/wp-content/uploads/2025/11/Green-Room-Hero.avif',
		],
		527 => [
			'name'        => 'The Red Room',
			'description' => 'A warm, intimate room with rich textiles, mountain views, and private bathroom. Breakfast included.',
			'bed'         => 'Double',
			'occupancy'   => 2,
			'image'       => '/wp-content/uploads/2025/11/Red-Room.avif',
		],
		528 => [
			'name'        => 'The Silver Room',
			'description' => 'Light and airy with silver accents, valley views, and a private bathroom. Breakfast included.',
			'bed'         => 'Double',
			'occupancy'   => 2,
			'image'       => '/wp-content/uploads/2025/11/Silver-Room.avif',
		],
		529 => [
			'name'        => 'Room 3 Green',
			'description' => 'A spacious family-friendly room with garden access, private bathroom, and breakfast included.',
			'bed'         => 'Double',
			'occupancy'   => 3,
			'image'       => '/wp-content/uploads/2025/11/Room-3-Green.avif',
		],
		530 => [
			'name'        => 'The Purple Room',
			'description' => 'Bold colours and traditional craftsmanship meet modern comfort. Private bathroom and breakfast included.',
			'bed'         => 'Double',
			'occupancy'   => 2,
			'image'       => '/wp-content/uploads/2025/11/purple-room.avif',
		],
	];

	$data = $room_data[$post_id] ?? null;
	if (!$data) {
		return;
	}

	$site_url = home_url();

	$schema = [
		'@context'        => 'https://schema.org',
		'@type'           => 'HotelRoom',
		'name'            => $data['name'],
		'description'     => $data['description'],
		'url'             => $url,
		'image'           => $site_url . $data['image'],
		'bed'             => [
			'@type'        => 'BedDetails',
			'typeOfBed'    => $data['bed'],
			'numberOfBeds' => 1,
		],
		'occupancy'       => [
			'@type' => 'QuantitativeValue',
			'value' => $data['occupancy'],
		],
		'amenityFeature'  => [
			['@type' => 'LocationFeatureSpecification', 'name' => 'Private Bathroom', 'value' => true],
			['@type' => 'LocationFeatureSpecification', 'name' => 'Valley Views', 'value' => true],
			['@type' => 'LocationFeatureSpecification', 'name' => 'Free WiFi', 'value' => true],
			['@type' => 'LocationFeatureSpecification', 'name' => 'Breakfast Included', 'value' => true],
			['@type' => 'LocationFeatureSpecification', 'name' => 'Daily Housekeeping', 'value' => true],
		],
		'containedInPlace' => [
			'@type' => 'BedAndBreakfast',
			'@id'   => 'https://darmegdaz.com/#dar-megdaz',
		],
	];

	echo "\n<script type=\"application/ld+json\">\n" . wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n</script>\n";
}, 5);

/**
 * Add meta description for room pages (only if Rank Math/Yoast haven't set one)
 */
add_action('wp_head', function () {
	if (!is_singular('room')) {
		return;
	}

	// Skip if Rank Math or Yoast is handling meta descriptions
	if (defined('RANK_MATH_VERSION') || defined('WPSEO_VERSION')) {
		return;
	}

	$post_id = get_the_ID();

	$descriptions = [
		526 => 'The Green Room at Dar Megdaz — a peaceful double room with valley views, private bathroom, and traditional Moroccan breakfast. Book your Atlas Mountains retreat.',
		527 => 'The Red Room at Dar Megdaz — warm textiles, mountain views, and Moroccan hospitality in the High Atlas. Private bathroom and breakfast included.',
		528 => 'The Silver Room at Dar Megdaz — light and airy with valley views, private bathroom, and daily breakfast. Your Atlas Mountains escape.',
		529 => 'Room 3 Green at Dar Megdaz — spacious family-friendly room with garden access in the High Atlas. Private bathroom and breakfast included.',
		530 => 'The Purple Room at Dar Megdaz — bold colours meet traditional craftsmanship. Private bathroom, valley views, and breakfast included.',
	];

	$desc = $descriptions[$post_id] ?? '';
	if ($desc) {
		echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	}
}, 1);
// LodgingBusiness Schema (structured data for Google)
function darmegdaz_lodging_schema() {
    if (!is_front_page()) return;

    $schema = array(
        "@context"    => "https://schema.org",
        "@type"       => "LodgingBusiness",
        "name"        => "Dar Megdaz Guesthouse",
        "description" => "Traditional Amazigh guesthouse in the High Atlas Mountains of Morocco, offering authentic cultural experiences and trekking adventures.",
        "url"         => home_url("/"),
        "address"     => array(
            "@type"           => "PostalAddress",
            "streetAddress"   => "Megdaz Village",
            "addressLocality" => "Megdaz",
            "addressRegion"   => "Azilal Province",
            "addressCountry"  => "MA",
        ),
        "geo" => array(
            "@type"     => "GeoCoordinates",
            "latitude"  => 31.6,
            "longitude" => -6.4,
        ),
        "aggregateRating" => array(
            "@type"       => "AggregateRating",
            "ratingValue" => "9.8",
            "bestRating"  => "10",
            "reviewCount" => "70",
        ),
        "priceRange"         => "EUR 24-30",
        "currenciesAccepted" => "EUR,MAD",
        "paymentAccepted"    => "Cash, Credit Card",
        "amenityFeature" => array(
            array("@type" => "LocationFeatureSpecification", "name" => "Free Breakfast", "value" => true),
            array("@type" => "LocationFeatureSpecification", "name" => "Free WiFi", "value" => true),
            array("@type" => "LocationFeatureSpecification", "name" => "Terrace", "value" => true),
            array("@type" => "LocationFeatureSpecification", "name" => "Heating", "value" => true),
        ),
        "checkinTime"  => "14:00",
        "checkoutTime" => "11:00",
        "image"        => wp_get_attachment_url(483),
        "sameAs"       => array(
            "https://www.booking.com/hotel/ma/dar-megdaz.html",
        ),
    );

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n</script>\n";
}
add_action('wp_head', 'darmegdaz_lodging_schema', 5);
// Sticky Mobile Booking CTA (visible only on mobile, slides up after scroll)
function darmegdaz_sticky_mobile_cta() {
    $booking_url = 'https://www.booking.com/hotel/ma/dar-megdaz.html';
    echo '<div id="mobile-booking-cta" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9999;background:#1C140C;border-top:2px solid #D4A96A;padding:0.75rem 1.25rem;box-shadow:0 -4px 20px rgba(0,0,0,0.3);">';
    echo '<div style="display:flex;align-items:center;justify-content:space-between;max-width:600px;margin:0 auto;gap:1rem;">';
    echo '<div style="flex:1;"><div style="color:#F5F0E8;font-size:0.95rem;font-weight:700;">From &euro;24/night</div><div style="color:#C4B8A8;font-size:0.75rem;">Breakfast included</div></div>';
    echo '<a href="' . esc_url($booking_url) . '" target="_blank" rel="noopener" style="display:inline-block;background:#D4A96A;color:#1C140C;font-weight:700;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;padding:0.7rem 1.5rem;border-radius:4px;text-decoration:none;white-space:nowrap;">Book Now</a>';
    echo '</div></div>';
    echo '<style>@media(max-width:768px){#mobile-booking-cta{display:block!important}body{padding-bottom:70px}}</style>';
    echo '<script>(function(){var c=document.getElementById("mobile-booking-cta");if(!c)return;c.style.transition="transform 0.3s ease";c.style.transform="translateY(100%)";window.addEventListener("scroll",function(){c.style.transform=(window.pageYOffset>300)?"translateY(0)":"translateY(100%)"})})();</script>';
}
add_action('wp_footer', 'darmegdaz_sticky_mobile_cta');

/**
 * Trek Hero Switcher — swaps hero SVG, title, subtitle, and stats
 * when clicking trek items in the selector bar on the Explore page.
 * Only loads on page ID 49.
 */
function darmegdaz_trek_hero_switcher() {
    if (!is_page(49)) return;

    echo <<<'TREKSWITCH'
<div id="trek-hero-svgs" style="display:none;">
<!-- 1. Takarout Loop (Medium) — irregular scenic loop with moderate switchbacks -->
<div data-trek="0" data-name="Takarout Loop" data-sub="A village viewpoint circuit high above Megdaz" data-dist="4.2 km" data-time="3 hours" data-diff="Medium" data-diff-color="#D4A96A" data-img="https://darmegdaz.local/wp-content/uploads/2026/03/takarout-loop-viewpoint.jpg">
<svg viewBox="0 0 400 600" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M200,540 C185,510 160,480 140,450 C115,410 90,370 80,320 C70,270 85,220 110,180 C135,140 170,115 210,105 C250,95 290,105 320,135 C350,165 365,210 360,260 C355,310 330,350 300,385 C270,420 240,445 225,480 C215,505 208,525 200,540 Z" stroke="#D4A96A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.8" stroke-dasharray="1200" stroke-dashoffset="1200"><animate attributeName="stroke-dashoffset" from="1200" to="0" dur="3.5s" begin="0.1s" fill="freeze"/></path>
  <circle cx="200" cy="540" r="7" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.8" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="200" cy="540" r="12" fill="none" stroke="#D4A96A" stroke-width="1.5" opacity="0"><animate attributeName="opacity" from="0" to="0.3" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="210" cy="105" r="5" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.6" dur="0.3s" begin="1.8s" fill="freeze"/></circle>
  <circle cx="360" cy="260" r="4" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.5" dur="0.3s" begin="2.5s" fill="freeze"/></circle>
  <text x="200" y="572" fill="#D4A96A" font-size="12" font-family="IBM Plex Sans, sans-serif" text-anchor="middle" opacity="0.7" font-weight="500">MEGDAZ</text>
  <text x="195" y="92" fill="#D4A96A" font-size="11" font-family="IBM Plex Sans, sans-serif" text-anchor="middle" opacity="0.3">Viewpoint</text>
</svg>
</div>
<!-- 2. Hiking Emgess (Medium) — meandering river-valley path with side branches -->
<div data-trek="1" data-name="Hiking Emgess" data-sub="Rock pools, waterfalls, and a shepherd village" data-dist="6.75 km" data-time="3 hours" data-diff="Medium" data-diff-color="#D4A96A" data-img="https://darmegdaz.local/wp-content/uploads/2026/03/hiking-emgess-autumn-trail.jpg">
<svg viewBox="0 0 400 600" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M200,555 C195,530 185,505 170,480 C150,445 130,420 120,390 C108,355 115,325 135,300 C155,275 180,260 190,235 C200,210 185,185 175,160 C165,135 170,110 185,90 C200,70 225,60 250,55 C275,50 300,58 310,75" stroke="#D4A96A" stroke-width="3" stroke-linecap="round" fill="none" opacity="0.8" stroke-dasharray="900" stroke-dashoffset="900"><animate attributeName="stroke-dashoffset" from="900" to="0" dur="3.2s" begin="0.1s" fill="freeze"/></path>
  <path d="M120,390 C100,380 85,365 75,345 C65,325 72,310 85,300" stroke="#4A7C59" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0" stroke-dasharray="120" stroke-dashoffset="120"><animate attributeName="opacity" from="0" to="0.35" dur="0.3s" begin="1.5s" fill="freeze"/><animate attributeName="stroke-dashoffset" from="120" to="0" dur="1s" begin="1.5s" fill="freeze"/></path>
  <path d="M175,160 C155,150 140,140 135,125" stroke="#4A7C59" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0" stroke-dasharray="60" stroke-dashoffset="60"><animate attributeName="opacity" from="0" to="0.3" dur="0.3s" begin="2.2s" fill="freeze"/><animate attributeName="stroke-dashoffset" from="60" to="0" dur="0.6s" begin="2.2s" fill="freeze"/></path>
  <circle cx="200" cy="555" r="7" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.8" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="200" cy="555" r="12" fill="none" stroke="#D4A96A" stroke-width="1.5" opacity="0"><animate attributeName="opacity" from="0" to="0.3" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="75" cy="345" r="5" fill="#4A7C59" opacity="0"><animate attributeName="opacity" from="0" to="0.6" dur="0.3s" begin="1.8s" fill="freeze"/></circle>
  <circle cx="310" cy="75" r="4" fill="#4A7C59" opacity="0"><animate attributeName="opacity" from="0" to="0.5" dur="0.3s" begin="3s" fill="freeze"/></circle>
  <text x="200" y="585" fill="#D4A96A" font-size="12" font-family="IBM Plex Sans" text-anchor="middle" opacity="0.7" font-weight="500">MEGDAZ</text>
  <text x="60" y="340" fill="#4A7C59" font-size="10" font-family="IBM Plex Sans" text-anchor="end" opacity="0.35">Pools</text>
  <text x="325" y="70" fill="#4A7C59" font-size="10" font-family="IBM Plex Sans" text-anchor="start" opacity="0.35">Oqan</text>
</svg>
</div>
<!-- 3. Irumin Waterfall (Easy) — smooth gentle arc, simple out-and-back -->
<div data-trek="2" data-name="Irumin Waterfall" data-sub="The largest waterfall in the region" data-dist="6.24 km" data-time="3 hours" data-diff="Easy" data-diff-color="#4A7C59" data-img="https://darmegdaz.local/wp-content/uploads/2026/03/irumin-waterfall-trek.jpg">
<svg viewBox="0 0 400 600" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M195,555 C190,510 185,460 180,410 C175,360 175,310 180,265 C185,220 195,180 210,145 C225,110 240,85 250,65" stroke="#4A7C59" stroke-width="3.5" stroke-linecap="round" fill="none" opacity="0.8" stroke-dasharray="600" stroke-dashoffset="600"><animate attributeName="stroke-dashoffset" from="600" to="0" dur="2.2s" begin="0.1s" fill="freeze"/></path>
  <path d="M250,65 C240,85 225,110 210,145 C195,180 185,220 180,265 C175,310 175,360 180,410 C185,460 190,510 195,555" stroke="#4A7C59" stroke-width="1.5" stroke-linecap="round" stroke-dasharray="6 10" fill="none" opacity="0" stroke-dasharray="600" stroke-dashoffset="600"><animate attributeName="opacity" from="0" to="0.2" dur="0.3s" begin="2.4s" fill="freeze"/><animate attributeName="stroke-dashoffset" from="600" to="0" dur="2.2s" begin="2.4s" fill="freeze"/></path>
  <circle cx="195" cy="555" r="7" fill="#4A7C59" opacity="0"><animate attributeName="opacity" from="0" to="0.8" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="195" cy="555" r="12" fill="none" stroke="#4A7C59" stroke-width="1.5" opacity="0"><animate attributeName="opacity" from="0" to="0.3" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="250" cy="65" r="6" fill="#4A7C59" opacity="0"><animate attributeName="opacity" from="0" to="0.7" dur="0.4s" begin="2.2s" fill="freeze"/></circle>
  <path d="M248,60 L250,42 L256,52" stroke="#4A7C59" stroke-width="1.5" fill="none" opacity="0"><animate attributeName="opacity" from="0" to="0.4" dur="0.3s" begin="2.4s" fill="freeze"/></path>
  <text x="195" y="585" fill="#4A7C59" font-size="12" font-family="IBM Plex Sans" text-anchor="middle" opacity="0.7" font-weight="500">MEGDAZ</text>
  <text x="270" y="55" fill="#4A7C59" font-size="11" font-family="IBM Plex Sans" text-anchor="start" opacity="0.4">Irumin Falls</text>
</svg>
</div>
<!-- 4. Megdaz to Iffolou (Medium, 25km) — complex multi-village angular circuit -->
<div data-trek="3" data-name="Megdaz to Iffolou" data-sub="A full-day loop through mountain villages" data-dist="25 km" data-time="8 hours" data-diff="Medium" data-diff-color="#D4A96A" data-img="https://darmegdaz.local/wp-content/uploads/2026/03/megdaz-to-iffolou-village.jpg">
<svg viewBox="0 0 400 600" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M185,555 C170,525 145,500 120,475 C90,445 60,410 40,370 C20,330 15,285 25,240 C35,195 60,158 95,130 C130,102 170,85 215,78 C260,71 300,80 335,105 C370,130 385,170 380,215 C375,260 350,300 325,335 C300,370 270,395 240,425 C215,450 200,485 195,520 L185,555 Z" stroke="#D4A96A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.8" stroke-dasharray="1600" stroke-dashoffset="1600"><animate attributeName="stroke-dashoffset" from="1600" to="0" dur="4.2s" begin="0.1s" fill="freeze"/></path>
  <circle cx="185" cy="555" r="7" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.8" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="185" cy="555" r="12" fill="none" stroke="#D4A96A" stroke-width="1.5" opacity="0"><animate attributeName="opacity" from="0" to="0.3" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="120" cy="475" r="4" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.4" dur="0.3s" begin="0.8s" fill="freeze"/></circle>
  <circle cx="40" cy="370" r="4" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.4" dur="0.3s" begin="1.4s" fill="freeze"/></circle>
  <circle cx="95" cy="130" r="4" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.4" dur="0.3s" begin="2.1s" fill="freeze"/></circle>
  <circle cx="335" cy="105" r="4" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.4" dur="0.3s" begin="2.8s" fill="freeze"/></circle>
  <circle cx="240" cy="425" r="4" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.4" dur="0.3s" begin="3.6s" fill="freeze"/></circle>
  <text x="185" y="585" fill="#D4A96A" font-size="12" font-family="IBM Plex Sans" text-anchor="middle" opacity="0.7" font-weight="500">MEGDAZ</text>
  <text x="22" y="365" fill="#D4A96A" font-size="9" font-family="IBM Plex Sans" text-anchor="end" opacity="0.3">Iffolou</text>
  <text x="95" y="120" fill="#D4A96A" font-size="9" font-family="IBM Plex Sans" text-anchor="middle" opacity="0.3">Ait Ali N'Ito</text>
  <text x="350" y="98" fill="#D4A96A" font-size="9" font-family="IBM Plex Sans" text-anchor="start" opacity="0.3">Tasselint</text>
  <text x="255" y="438" fill="#D4A96A" font-size="9" font-family="IBM Plex Sans" text-anchor="start" opacity="0.3">Tagousht</text>
</svg>
</div>
<!-- 5. Summit Tiglisst -->
<div data-trek="4" data-name="Summit Tiglisst" data-sub="The highest peak near Megdaz at 2,930m" data-dist="—" data-time="9 hours" data-diff="Medium" data-diff-color="#D4A96A" data-img="https://darmegdaz.local/wp-content/uploads/2026/03/summit-tiglisst-peak-scaled.jpg">
<svg viewBox="0 0 400 600" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M200,560 C195,535 175,510 185,488 C195,468 220,460 230,440 C240,420 215,400 210,378 C205,358 225,340 232,318 C238,298 218,278 215,255 C212,232 230,215 235,195 C240,175 222,158 218,138 C214,118 228,98 235,78 C240,62 238,48 240,35" stroke="#D4A96A" stroke-width="3" stroke-linecap="round" fill="none" opacity="0.8" stroke-dasharray="900" stroke-dashoffset="900"><animate attributeName="stroke-dashoffset" from="900" to="0" dur="3s" begin="0.1s" fill="freeze"/></path>
  <circle cx="200" cy="560" r="7" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.8" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="200" cy="560" r="12" fill="none" stroke="#D4A96A" stroke-width="1.5" opacity="0"><animate attributeName="opacity" from="0" to="0.3" dur="0.4s" begin="0.1s" fill="freeze"/></circle>
  <circle cx="240" cy="35" r="6" fill="#D4A96A" opacity="0"><animate attributeName="opacity" from="0" to="0.8" dur="0.4s" begin="3s" fill="freeze"/></circle>
  <circle cx="240" cy="35" r="10" fill="none" stroke="#D4A96A" stroke-width="1" opacity="0"><animate attributeName="opacity" from="0" to="0.3" dur="0.4s" begin="3s" fill="freeze"/></circle>
  <text x="200" y="590" fill="#D4A96A" font-size="12" font-family="IBM Plex Sans" text-anchor="middle" opacity="0.7" font-weight="500">MEGDAZ</text>
  <text x="260" y="30" fill="#D4A96A" font-size="12" font-family="IBM Plex Sans" text-anchor="start" opacity="0.5" font-weight="600">2,930m</text>
</svg>
</div>
</div>

<script>
(function() {
    var dataContainer = document.getElementById('trek-hero-svgs');
    if (!dataContainer) return;

    var svgDisplay = document.querySelector('#brxe-tr015');
    var titleEl = document.querySelector('#brxe-tr004');
    var subEl = document.querySelector('#brxe-tr005');
    var statsRow = document.querySelector('#brxe-tr006');
    var selectorBar = document.querySelector('#brxe-tr017');
    var heroSection = document.querySelector('#brxe-tr001');
    if (!selectorBar || !svgDisplay) return;

    var items = selectorBar.children;
    var currentIndex = 0;

    // Preload all trek images
    var dataItems = dataContainer.querySelectorAll('[data-trek]');
    for (var p = 0; p < dataItems.length; p++) {
        var imgUrl = dataItems[p].getAttribute('data-img');
        if (imgUrl) { var preload = new Image(); preload.src = imgUrl; }
    }

    function switchTrek(index) {
        if (index === currentIndex) return;
        currentIndex = index;

        var trekDiv = dataContainer.querySelector('[data-trek="' + index + '"]');
        if (!trekDiv) return;

        var name = trekDiv.getAttribute('data-name');
        var sub = trekDiv.getAttribute('data-sub');
        var dist = trekDiv.getAttribute('data-dist');
        var time = trekDiv.getAttribute('data-time');
        var diff = trekDiv.getAttribute('data-diff');
        var diffColor = trekDiv.getAttribute('data-diff-color');
        var bgImg = trekDiv.getAttribute('data-img');

        // Fade out
        svgDisplay.style.transition = 'opacity 0.4s ease';
        svgDisplay.style.opacity = '0';

        if (titleEl) { titleEl.style.transition = 'opacity 0.3s ease'; titleEl.style.opacity = '0'; }
        if (subEl) { subEl.style.transition = 'opacity 0.3s ease'; subEl.style.opacity = '0'; }
        if (statsRow) { statsRow.style.transition = 'opacity 0.3s ease'; statsRow.style.opacity = '0'; }

        setTimeout(function() {
            // Swap hero background image
            if (heroSection && bgImg) {
                heroSection.style.backgroundImage = 'url(' + bgImg + ')';
            }

            // Swap SVG
            svgDisplay.innerHTML = trekDiv.querySelector('svg').outerHTML;
            svgDisplay.style.opacity = '1';

            // Swap title
            if (titleEl) {
                titleEl.textContent = name;
                titleEl.style.opacity = '1';
            }

            // Swap subtitle
            if (subEl) {
                var p = subEl.querySelector('p');
                if (p) p.textContent = sub;
                else subEl.textContent = sub;
                subEl.style.opacity = '1';
            }

            // Swap stats
            if (statsRow) {
                var statEls = statsRow.querySelectorAll('.brxe-text');
                if (statEls.length >= 3) {
                    statEls[0].innerHTML = '<p><span style="color: #D4A96A; font-size: 0.8rem; margin-right: 6px;">&#9679;</span><span style="font-weight: 600;">' + dist + '</span></p>';
                    statEls[2].innerHTML = '<p><span style="color: #D4A96A; font-size: 0.8rem; margin-right: 6px;">&#9679;</span><span style="font-weight: 600;">' + time + '</span></p>';
                    statEls[4].innerHTML = '<p><span style="color: ' + diffColor + '; font-size: 0.8rem; margin-right: 6px;">&#9679;</span><span style="font-weight: 600;">' + diff + '</span></p>';
                }
                statsRow.style.opacity = '1';
            }

            // Update selector active states
            for (var i = 0; i < items.length; i++) {
                var headings = items[i].querySelectorAll('.brxe-heading');
                if (i === index) {
                    items[i].style.borderBottom = '2px solid #D4A96A';
                    items[i].style.opacity = '1';
                    if (headings[0]) headings[0].style.color = '#D4A96A';
                    if (headings[1]) headings[1].style.color = '#F5F0E8';
                } else {
                    items[i].style.borderBottom = '2px solid transparent';
                    items[i].style.opacity = '0.7';
                    if (headings[0]) headings[0].style.color = '#8A7E6B';
                    if (headings[1]) headings[1].style.color = '#8A7E6B';
                }
            }
        }, 400);
    }

    // Style all items and attach click handlers
    for (var i = 0; i < items.length; i++) {
        (function(idx) {
            items[idx].addEventListener('click', function(e) {
                e.preventDefault();
                switchTrek(idx);
            });
            items[idx].style.cursor = 'pointer';
            // Set initial state for all items
            var headings = items[idx].querySelectorAll('.brxe-heading');
            if (idx === 0) {
                items[idx].style.borderBottom = '2px solid #D4A96A';
                items[idx].style.opacity = '1';
                if (headings[0]) headings[0].style.color = '#D4A96A';
                if (headings[1]) headings[1].style.color = '#F5F0E8';
            } else {
                items[idx].style.borderBottom = '2px solid transparent';
                items[idx].style.opacity = '0.7';
                if (headings[0]) headings[0].style.color = '#8A7E6B';
                if (headings[1]) headings[1].style.color = '#8A7E6B';
            }
        })(i);
    }
})();
</script>
TREKSWITCH;
}
add_action('wp_footer', 'darmegdaz_trek_hero_switcher');