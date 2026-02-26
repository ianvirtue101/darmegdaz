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

			return rest_ensure_response([
				'success'  => true,
				'page_id'  => $id,
				'meta_key' => $meta_key,
			]);
		},
	]);

});