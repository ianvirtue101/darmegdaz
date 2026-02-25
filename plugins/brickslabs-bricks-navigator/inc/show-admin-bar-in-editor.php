<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if current user can use Bricks Builder.
 */
function brickslabs_bricks_navigator_user_can_use_bricks_builder(): bool {
    if ( ! function_exists( 'bricks_is_builder' ) ) {
        return false;
    }
    
    // Use newer Builder_Permissions class if available, fallback to Capabilities
    if ( class_exists( '\Bricks\Builder_Permissions' ) ) {
        return \Bricks\Builder_Permissions::user_has_permission( 'access_builder_page' );
    }
    
    // Fallback to legacy method
    return class_exists( '\Bricks\Capabilities' ) && 
           \Bricks\Capabilities::current_user_can_use_builder();
}

// Show WP admin bar in Bricks editor.
add_action( 'init', function () {
  // if this is not the outer frame, abort
  if ( ! function_exists( 'bricks_is_builder_main' ) || ! bricks_is_builder_main() || ! brickslabs_bricks_navigator_user_can_use_bricks_builder() ) {
    return;
  }

  add_filter( 'show_admin_bar', '__return_true' );
} );

// Add CSS to fix the admin bar.
add_action( 'wp_head', function() {
  if ( function_exists( 'bricks_is_builder_main' ) && bricks_is_builder_main() &&  brickslabs_bricks_navigator_user_can_use_bricks_builder() ) {
    echo '<style>body.admin-bar #bricks-panel,
	body.admin-bar #bricks-preview,
	body.admin-bar #bricks-structure {
		top: calc(var(--wp-admin--admin-bar--height) + var(--builder-toolbar-height));
		height: calc(100vh - var(--wp-admin--admin-bar--height) - var(--builder-toolbar-height));
	}</style>';
  }
} );