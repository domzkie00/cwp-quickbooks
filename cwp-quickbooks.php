<?php
/**
 * Plugin Name: Clients WP - QuickBooks
 * Plugin URI:  https://www.gravity2pdf.com
 * Description: Connect Quickbooks with Clients WP
 * Version:     1.0
 * Author:      gravity2pdf
 * Author URI:  https://github.com/raphcadiz
 * Text Domain: cl-wp-quickbooks
 */

if (!class_exists('Clients_WP_QuickBooks')):

    define( 'CWPQB_PATH', dirname( __FILE__ ) );
    define( 'CWPQB_PATH_INCLUDES', dirname( __FILE__ ) . '/includes' );
    define( 'CWPQB_PATH_CLASS', dirname( __FILE__ ) . '/class' );
    define( 'CWPQB_FOLDER', basename( CWPQB_PATH ) );
    define( 'CWPQB_URL', plugins_url() . '/' . CWPQB_FOLDER );
    define( 'CWPQB_URL_INCLUDES', CWPQB_URL . '/includes' );
    define( 'CWPQB_URL_CLASS', CWPQB_URL . '/class' );
    define( 'CWPQB_VERSION', 1.0 );

    register_activation_hook( __FILE__, 'clients_wp_quickbooks_activation' );
    function clients_wp_quickbooks_activation(){
        if ( ! class_exists('Clients_WP') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('Sorry, but this plugin requires the Restrict Content Pro and Clients WP to be installed and active.');
        }

    }

    add_action( 'admin_init', 'clients_wp_quickbooks_activate' );
    function clients_wp_quickbooks_activate(){
        if ( ! class_exists('Clients_WP') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

    /*
     * include necessary files
     */
    require_once(CWPQB_PATH.'/vendor/autoload.php');
    require_once(CWPQB_PATH_CLASS . '/cwp-quickbooks-main.class.php');
    require_once(CWPQB_PATH_CLASS . '/cwp-quickbooks-pages.class.php');

    /* Intitialize licensing
     * for this plugin.
     */
    if( class_exists( 'Clients_WP_License_Handler' ) ) {
        $cwp_quickbooks = new Clients_WP_License_Handler( __FILE__, 'Clients WP - QuickBooks', CWPQB_VERSION, 'gravity2pdf', null, null, 7554);
    }

    add_action( 'plugins_loaded', array( 'Clients_WP_QuickBooks', 'get_instance' ) );
endif;