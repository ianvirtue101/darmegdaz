<?php
/*
 * Plugin Name:       BricksLabs Bricks Navigator
 * Plugin URI:        https://brickslabs.com/bricks-navigator/
 * Description:       Adds quick links in the WordPress admin bar for users of the Bricks theme.
 * Version:           1.1.2
 * Author:            Sridhar Katakam
 * Author URI:        https://brickslabs.com/
 * Text Domain:       brickslabs-bricks-navigator
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace BricksLabs\BricksNavigator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Plugin {
    const VERSION = '1.1.2';
    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        add_action('plugins_loaded', [$this, 'init']);
    }

    private function define_constants(): void {
        define( 'BRICKSLABS_BRICKS_NAVIGATOR_VERSION', self::VERSION );
        define( 'BRICKSLABS_BRICKS_NAVIGATOR_BASE', plugin_basename( __FILE__ ) );
        define( 'BRICKSLABS_BRICKS_NAVIGATOR_PATH', plugin_dir_path( __FILE__ ) );
        define( 'BRICKSLABS_BRICKS_NAVIGATOR_URL', plugin_dir_url( __FILE__ ) );
    }

    public function init(): void {
        $this->load_textdomain();
        add_action( 'init', [ $this, 'init_hooks' ], 0 );
        
        if ( is_admin() ) {
            require_once BRICKSLABS_BRICKS_NAVIGATOR_PATH . 'inc/settings.php';
            add_filter( 'plugin_action_links_' . BRICKSLABS_BRICKS_NAVIGATOR_BASE, [ $this, 'add_settings_link' ] );
        }
        
        if ( get_option( 'brickslabs_bricks_navigator_show_in_editor' ) ) {
            require_once BRICKSLABS_BRICKS_NAVIGATOR_PATH . 'inc/show-admin-bar-in-editor.php';
        }
        
        add_action( 'admin_init', [ $this, 'check_environment' ] );
    }

    public function init_hooks(): void {
        add_action( 'wp_loaded', function() {
            if ( $this->can_use_navigator() ) {
                add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 999 );
                add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
                add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
            }
        } );
    }

    public function check_environment(): bool {
        $errors = [];
        
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            $errors[] = sprintf(
                __( 'BricksLabs Bricks Navigator requires PHP version %s or higher. You are running version %s.', 'bricks-navigator' ),
                '7.4',
                PHP_VERSION
            );
        }
        
        if ( version_compare( $GLOBALS['wp_version'], '5.2', '<' ) ) {
            $errors[] = sprintf(
                __( 'BricksLabs Bricks Navigator requires WordPress version %s or higher. You are running version %s.', 'bricks-navigator' ),
                '5.2',
                $GLOBALS['wp_version']
            );
        }
        
        $parent_theme = wp_get_theme( get_template() );
        if ( 'Bricks' !== $parent_theme->get( 'Name' ) ) {
            $errors[] = __( 'BricksLabs Bricks Navigator requires Bricks theme to be active.', 'bricks-navigator' );
        }
        
        if ( ! empty( $errors ) ) {
            add_action( 'admin_notices', function() use ( $errors ) {
                echo '<div class="notice notice-error"><p>';
                echo implode( '</p><p>', $errors );
                echo '</p></div>';
            } );
            
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( BRICKSLABS_BRICKS_NAVIGATOR_BASE );
            
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
            
            return false;
        }
        
        return true;
    }

    public function load_textdomain(): void {
        load_plugin_textdomain( 'bricks-navigator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public function can_use_navigator(): bool {
        if ( ! function_exists( 'is_admin_bar_showing' ) || ! is_admin_bar_showing() ) {
            return false;
        }
        
        $parent_theme = wp_get_theme( get_template() );
        if ( 'Bricks' !== $parent_theme->get( 'Name' ) ) {
            return false;
        }
        
        if ( ! function_exists( 'bricks_is_builder' ) ) {
            return false;
        }
        
        // Use newer Builder_Permissions class if available, fallback to Capabilities
        if ( class_exists( '\Bricks\Builder_Permissions' ) ) {
            return \Bricks\Builder_Permissions::user_has_permission( 'access_builder_page' );
        }
        
        // Fallback to legacy method
        return class_exists( '\Bricks\Capabilities' ) && \Bricks\Capabilities::current_user_can_use_builder();
    }

    public function add_admin_bar_menu( $wp_admin_bar ): void {
        try {
            $iconhtml = sprintf(
                '<img src="%s" style="width: 16px; height: 16px; padding-right: 6px;" alt="" />',
                esc_url( BRICKSLABS_BRICKS_NAVIGATOR_URL . 'assets/images/bricks-logo.png' )
            );
            
            $wp_admin_bar->add_node( [
                'id'    => 'bn-bricks',
                'title' => $iconhtml . esc_html__( 'Bricks', 'bricks-navigator' ),
                'href'  => esc_url( admin_url( 'themes.php?page=bricks' ) ),
            ] );
            
            require_once BRICKSLABS_BRICKS_NAVIGATOR_PATH . 'inc/bricks.php';
            
            if ( get_option( 'brickslabs_bricks_navigator_show_community_menu' ) ) {
                require_once BRICKSLABS_BRICKS_NAVIGATOR_PATH . 'inc/community.php';
            }
            
            if ( get_option( 'brickslabs_bricks_navigator_show_thirdparty_plugins' ) ) {
                require_once BRICKSLABS_BRICKS_NAVIGATOR_PATH . 'inc/thirdpartyplugins.php';
            }
            
        } catch ( \Exception $e ) {
            error_log( 'Bricks Navigator Error: ' . $e->getMessage() );
        }
    }

    public function enqueue_assets(): void {
        if ( ! is_admin_bar_showing() ) {
            return;
        }
        
        wp_enqueue_style(
            'brickslabs-bricks-navigator',
            BRICKSLABS_BRICKS_NAVIGATOR_URL . 'assets/css/style.css',
            [],
            BRICKSLABS_BRICKS_NAVIGATOR_VERSION
        );
    }

    public function add_settings_link( array $links ): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=brickslabs-bricks-navigator' ) ),
            esc_html__( 'Settings', 'bricks-navigator' )
        );
        array_unshift( $links, $settings_link );
        return $links;
    }
}

Plugin::instance();