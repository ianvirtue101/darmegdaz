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