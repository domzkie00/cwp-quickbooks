<?php
class Clients_WP_QuickBooks_Pages {

    public function __construct() {
        add_action('admin_init', array( $this, 'settings_options_init' ));
        add_action('admin_menu', array( $this, 'admin_menus'), 12 );
    }

    public function settings_options_init() {
        register_setting( 'cwpquickbooks_settings_options', 'cwpquickbooks_settings_options', '' );
    }

    public function admin_menus() {
        add_submenu_page ( 'edit.php?post_type=bt_client' , 'QuickBooks' , 'QuickBooks' , 'manage_options' , 'cwp-quickbooks' , array( $this , 'cwp_quickbooks' ));
    }

    public function cwp_quickbooks() {
        include_once(CWPQB_PATH_INCLUDES.'/cwp_quickbooks.php');
    }
}

new Clients_WP_QuickBooks_Pages();