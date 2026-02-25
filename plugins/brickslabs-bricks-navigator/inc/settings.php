<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add settings page under Bricks menu.
 */
add_action( 'admin_menu', function() {
    add_submenu_page(
        'bricks',
        __( 'Bricks Navigator Settings', 'bricks-navigator' ),
        __( 'Bricks Navigator', 'bricks-navigator' ),
        'manage_options',
        'brickslabs-bricks-navigator',
        'brickslabs_bricks_navigator_settings_page'
    );
}, 99 );

/**
 * Render settings page.
 */
function brickslabs_bricks_navigator_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Show success message if settings were saved
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error(
            'brickslabs_bricks_navigator_messages',
            'brickslabs_bricks_navigator_message',
            __( 'Settings Saved', 'bricks-navigator' ),
            'updated'
        );
    }
    ?>
    <div class="wrap bricks-navigator-settings">
        <div class="settings-header">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p class="description">
                <?php _e( 'Configure how the Bricks Navigator menu appears in your admin bar in addition to the core menu items - Settings, Templates and Pages', 'bricks-navigator' ); ?>
            </p>
        </div>

        <?php settings_errors( 'brickslabs_bricks_navigator_messages' ); ?>

        <form action="options.php" method="post" class="settings-form">
            <?php
            settings_fields( 'brickslabs-bricks-navigator' );
            do_settings_sections( 'brickslabs-bricks-navigator' );
            submit_button( __( 'Save Settings', 'bricks-navigator' ) );
            ?>
        </form>
    </div>

    <style>
    .bricks-navigator-settings {
        max-width: 960px;
        margin: 40px 20px 20px 20px;
        padding: 30px 30px 10px 30px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .settings-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .settings-header h1 {
        margin: 0 0 10px;
        padding: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .settings-header .description {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .form-table {
        margin-top: 20px;
    }

    .form-table th {
        padding: 20px 10px 20px 0;
        width: 200px;
        font-weight: 500;
    }

    .form-table td {
        padding: 15px 10px;
        position: relative;
    }

    .form-table .description {
        margin-top: 8px;
        color: #666;
        font-style: normal;
    }

    .settings-form .submit {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    /* Bricks-style toggle switch styling */
    .bricks-navigator-settings input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        background-color: #eaecef;
        border: none;
        border-radius: 16px;
        box-shadow: none;
        cursor: pointer;
        height: 16px;
        margin: 0 4px 0 0;
        outline: none;
        padding: 0;
        width: 26px;
        vertical-align: middle;
    }

    .bricks-navigator-settings input[type="checkbox"]:before {
        -webkit-appearance: none;
        background-color: #9da8b2;
        border-radius: 12px;
        content: "";
        cursor: pointer;
        display: block;
        height: 12px;
        left: 2px;
        margin: 0;
        position: relative;
        top: 2px;
        transition: all 0.2s ease-out;
        width: 12px;
    }

    .bricks-navigator-settings input[type="checkbox"]:checked {
        background-color: #2271b1;
    }

    .bricks-navigator-settings input[type="checkbox"]:checked:before {
        background-color: #fff;
        left: 0;
        opacity: 1;
        transform: translateX(100%);
    }

    .bricks-navigator-settings input[type="checkbox"] + label {
        cursor: pointer;
    }

    .bricks-navigator-settings input[type="checkbox"]:focus {
        box-shadow: 0 0 0 1px #2271b1;
    }

    </style>
    <?php
}

/**
 * Register settings.
 */
add_action( 'admin_init', function() {
    // General Settings Section
    add_settings_section(
        'brickslabs_bricks_navigator_general',
        __( 'General Settings', 'bricks-navigator' ),
        null,
        'brickslabs-bricks-navigator'
    );

    // Show in Editor Setting
    register_setting( 'brickslabs-bricks-navigator', 'brickslabs_bricks_navigator_show_in_editor', [
        'default' => false
    ] );
    add_settings_field(
        'brickslabs_bricks_navigator_show_in_editor',
        __( 'Admin bar in Bricks Editor', 'bricks-navigator' ),
        'brickslabs_bricks_navigator_toggle_callback',
        'brickslabs-bricks-navigator',
        'brickslabs_bricks_navigator_general',
        [
            'label_for' => 'brickslabs_bricks_navigator_show_in_editor',
            'description' => __( 'Show the admin bar in the Bricks editor interface', 'bricks-navigator' )
        ]
    );

    // Menu Items Section
    add_settings_section(
        'brickslabs_bricks_navigator_menu',
        __( 'Menu Items', 'bricks-navigator' ),
        null,
        'brickslabs-bricks-navigator'
    );

    // Show Community Menu
    register_setting( 'brickslabs-bricks-navigator', 'brickslabs_bricks_navigator_show_community_menu', [
        'default' => false
    ] );
    add_settings_field(
        'brickslabs_bricks_navigator_show_community_menu',
        __( 'Community Menu', 'bricks-navigator' ),
        'brickslabs_bricks_navigator_toggle_callback',
        'brickslabs-bricks-navigator',
        'brickslabs_bricks_navigator_menu',
        [
            'label_for' => 'brickslabs_bricks_navigator_show_community_menu',
            'description' => __( 'Show the Community menu items', 'bricks-navigator' )
        ]
    );

    // Show Internal Bricks Links
    register_setting( 'brickslabs-bricks-navigator', 'brickslabs_bricks_navigator_show_bricks_internal', [
        'default' => false
    ] );
    add_settings_field(
        'brickslabs_bricks_navigator_show_bricks_internal',
        __( 'Internal Bricks Links', 'bricks-navigator' ),
        'brickslabs_bricks_navigator_toggle_callback',
        'brickslabs-bricks-navigator',
        'brickslabs_bricks_navigator_menu',
        [
            'label_for' => 'brickslabs_bricks_navigator_show_bricks_internal',
            'description' => __( 'Show internal Bricks links (Getting Started, Custom Fonts, Form Submissions, Sidebars, System Information, License)', 'bricks-navigator' )
        ]
    );

    // Show External Bricks Links
    register_setting( 'brickslabs-bricks-navigator', 'brickslabs_bricks_navigator_show_bricks_external', [
        'default' => false
    ] );
    add_settings_field(
        'brickslabs_bricks_navigator_show_bricks_external',
        __( 'External Bricks Links', 'bricks-navigator' ),
        'brickslabs_bricks_navigator_toggle_callback',
        'brickslabs-bricks-navigator',
        'brickslabs_bricks_navigator_menu',
        [
            'label_for' => 'brickslabs_bricks_navigator_show_bricks_external',
            'description' => __( 'Show external Bricks links (Idea Board, Roadmap, Changelog, Academy, Forum, Facebook Group, YouTube Channel, Bricks Experts)', 'bricks-navigator' )
        ]
    );

    // Show Plugin Settings
    register_setting( 'brickslabs-bricks-navigator', 'brickslabs_bricks_navigator_show_thirdparty_plugins', [
        'default' => true
    ] );
    add_settings_field(
        'brickslabs_bricks_navigator_show_thirdparty_plugins',
        __( 'Plugin Settings', 'bricks-navigator' ),
        'brickslabs_bricks_navigator_toggle_callback',
        'brickslabs-bricks-navigator',
        'brickslabs_bricks_navigator_menu',
        [
            'label_for' => 'brickslabs_bricks_navigator_show_thirdparty_plugins',
            'description' => __( 'Show third-party plugin settings in the menu', 'bricks-navigator' )
        ]
    );
} );

/**
 * Toggle switch callback.
 */
function brickslabs_bricks_navigator_toggle_callback( $args ) {
    // Verify we have required arguments
    if ( ! isset( $args['label_for'] ) ) {
        return;
    }

    $option = get_option( $args['label_for'] );
    ?>
    <input 
        type="checkbox" 
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        name="<?php echo esc_attr( $args['label_for'] ); ?>"
        value="1"
        <?php checked( $option, true ); ?>
    >
    <label for="<?php echo esc_attr( $args['label_for'] ); ?>"></label>
    <?php if ( isset( $args['description'] ) ) : ?>
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
    <?php endif;
}