<?php
/**
 * Plugin Name: Optica Glasses Virtual Try-On
 * Plugin URI: https://github.com/galanakisbm/Optica
 * Description: Virtual try-on module for glasses using AI face detection
 * Version: 1.0.0
 * Author: Optica Team
 * Author URI: https://github.com/galanakisbm
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: optica-tryon
 * Domain Path: /languages
 *
 * @package Optica_Glasses_TryOn
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'OPTICA_TRYON_VERSION', '1.0.0' );
define( 'OPTICA_TRYON_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPTICA_TRYON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OPTICA_TRYON_ASSETS_URL', OPTICA_TRYON_PLUGIN_URL . 'assets/' );

// Include required files
require_once OPTICA_TRYON_PLUGIN_DIR . 'includes/class-woocommerce-integration.php';
require_once OPTICA_TRYON_PLUGIN_DIR . 'includes/class-glasses-manager.php';
require_once OPTICA_TRYON_PLUGIN_DIR . 'includes/class-face-detection.php';

/**
 * Main plugin class
 */
class Optica_Glasses_TryOn {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_shortcode( 'glasses_tryon', array( $this, 'render_tryon_shortcode' ) );
        
        // Initialize integrations
        new Optica_WooCommerce_Integration();
        new Optica_Glasses_Manager();
        new Optica_Face_Detection();
    }

    /**
     * Enqueue front-end assets
     */
    public function enqueue_assets() {
        // ML5.js library
        wp_enqueue_script(
            'ml5-js',
            OPTICA_TRYON_ASSETS_URL . 'js/ml5.min.js',
            array(),
            '0.12.0',
            true
        );

        // Main try-on script
        wp_enqueue_script(
            'optica-tryon-js',
            OPTICA_TRYON_ASSETS_URL . 'js/tryon.js',
            array( 'ml5-js' ),
            OPTICA_TRYON_VERSION,
            true
        );

        // CSS styling
        wp_enqueue_style(
            'optica-tryon-css',
            OPTICA_TRYON_ASSETS_URL . 'css/tryon.css',
            array(),
            OPTICA_TRYON_VERSION
        );

        // Localize script for AJAX
        wp_localize_script(
            'optica-tryon-js',
            'opticaTryOn',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'optica_tryon_nonce' ),
                'assetsUrl' => OPTICA_TRYON_ASSETS_URL,
            )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'optica-admin-css',
            OPTICA_TRYON_ASSETS_URL . 'css/tryon.css',
            array(),
            OPTICA_TRYON_VERSION
        );
    }

    /**
     * Render try-on shortcode
     */
    public function render_tryon_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'product_id' => get_the_ID(),
            ),
            $atts,
            'glasses_tryon'
        );

        ob_start();
        include OPTICA_TRYON_PLUGIN_DIR . 'templates/shortcode-template.php';
        return ob_get_clean();
    }
}

// Initialize plugin
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    new Optica_Glasses_TryOn();
}
?>