<?php if ( ! defined( 'ABSPATH' ) ) exit;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(CWPQB_PATH.'/vendor/quickbooks/v3-php-sdk/src/config.php');

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;

class Clients_WP_QuickBooks{
    
    private static $instance;

    public static function get_instance()
    {
        if( null == self::$instance ) {
            self::$instance = new Clients_WP_QuickBooks();
        }

        return self::$instance;
    }

    function __construct(){
        add_action('admin_init', array($this, 'register_integration'));
        add_action('admin_init', array($this, 'get_access_token'));
        add_action('admin_init', array($this, 'sync_clients_list'));
        add_action('admin_enqueue_scripts', array( $this, 'cwp_quickbooks_add_admin_scripts' ));
        add_action('wp_enqueue_scripts', array($this, 'cwp_quickbooks_add_wp_scripts'), 20, 1);
        add_action('wp_ajax_get_quickbooks_client_list', array($this, 'get_quickbooks_client_list_ajax'));
        add_filter('the_content', array($this, 'transactions_content_table'));
    }

    public function cwp_quickbooks_add_admin_scripts() {
        wp_register_script('cwp_quickbooks_admin_scripts', CWPQB_URL . '/assets/js/cwp-quickbooks-admin-scripts.js', '1.0', true);
        $cwpqb_admin_script = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        );
        wp_localize_script('cwp_quickbooks_admin_scripts', 'cwpqb_admin_script', $cwpqb_admin_script );
        wp_enqueue_script('cwp_quickbooks_admin_scripts');
    }

    public function cwp_quickbooks_add_wp_scripts() {
        wp_register_script('cwp_quickbooks_wp_scripts', CWPQB_URL . '/assets/js/cwp-quickbooks-scripts.js', '1.0', true);
        wp_enqueue_script('cwp_quickbooks_wp_scripts');

        wp_register_style('cwp_quickbooks_wp_styles', CWPQB_URL . '/assets/css/cwp-quickbooks-styles.css', '1.0', true);
        wp_enqueue_style('cwp_quickbooks_wp_styles');
    }

    public function register_integration($array) {
        $quickbooks = array(
            'quickbooks' => array(
                'key'       => 'quickbooks',
                'label'     => 'QuickBooks'
            )
        );

        $clients_wp_integrations = get_option('clients_wp_integrations');

        if(is_array($clients_wp_integrations)) {
            $merge_integrations = array_merge($clients_wp_integrations, $quickbooks);
            update_option('clients_wp_integrations', $merge_integrations);
        } else {
            update_option('clients_wp_integrations', $quickbooks);
        }
        
    }

    public function get_access_token(){
        if (isset($_REQUEST['cwpintegration']) && $_REQUEST['cwpintegration'] == 'quickbooks' && !isset($_REQUEST['sync_clients'])):
            $cwpquickbooks_settings_options = get_option('cwpquickbooks_settings_options');
            $app_key    = isset($cwpquickbooks_settings_options['app_key']) ? $cwpquickbooks_settings_options['app_key'] : '';
            $app_secret = isset($cwpquickbooks_settings_options['app_secret']) ? $cwpquickbooks_settings_options['app_secret'] : '';
            $app_token  = isset($cwpquickbooks_settings_options['app_token']) ? $cwpquickbooks_settings_options['app_token'] : '';

            if(!empty($app_key) && !empty($app_secret)) {
                $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $app_key,
                    'ClientSecret' => $app_secret,
                    'scope' => 'com.intuit.quickbooks.accounting',
                    'RedirectURI' => admin_url( 'edit.php?post_type=bt_client&page=cwp-quickbooks&cwpintegration=quickbooks' )
                ));

                $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

                if (!isset($_GET['code'])) {
                    $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
                    header('Location: '.$authUrl);
                } else {
                    $code = $_GET['code'];
                    $realmId = $_GET['realmId'];

                    $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
                    $dataService->updateOAuth2Token($accessToken);
                    $dataService->throwExceptionOnError(true);

                    $cwpquickbooks_settings_options['app_token'] = serialize($accessToken);
                    update_option( 'cwpquickbooks_settings_options', $cwpquickbooks_settings_options );
                    header('Location: ' . admin_url( 'edit.php?post_type=bt_client&page=cwp-quickbooks' ));
                }
            }
            
        endif;
    }

    public function sync_clients_list() {
        if (isset($_REQUEST['cwpintegration']) && $_REQUEST['cwpintegration'] == 'quickbooks' && isset($_REQUEST['sync_clients'])):
            $cwpquickbooks_settings_options = get_option('cwpquickbooks_settings_options');
            $app_key    = isset($cwpquickbooks_settings_options['app_key']) ? $cwpquickbooks_settings_options['app_key'] : '';
            $app_secret = isset($cwpquickbooks_settings_options['app_secret']) ? $cwpquickbooks_settings_options['app_secret'] : '';
            $app_token = isset($cwpquickbooks_settings_options['app_token']) ? $cwpquickbooks_settings_options['app_token'] : '';

            if(!empty($app_key) && !empty($app_secret) && !empty($app_token)) {
                $token = unserialize($app_token);
                $token_array = $this->object_to_array($token);

                $baseURL = "development";
                //$baseURL = "production";

                if($baseURL == "development") {
                    $qb_url = "sandbox.qbo.intuit.com";
                } else {
                    $qb_url = "#";
                }

                $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $app_key,
                    'ClientSecret' => $app_secret,
                    'accessTokenKey' =>  $token_array['accessTokenKey'],
                    'refreshTokenKey' => $token_array['refresh_token'],
                    'QBORealmID' => $token_array['realmID'],
                    'baseUrl' => $baseURL
                ));

                $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
                $accessToken = $OAuth2LoginHelper->refreshToken();
                $dataService->updateOAuth2Token($accessToken);

                $query_customer = "SELECT * FROM Customer";
                $resultingCustomerObj = $dataService->Query($query_customer);
                $error_customer = $dataService->getLastError();

                if(!$error_customer) {
                    unset($_SESSION['quickbooks_error_msg']);
                    $clients = array();
                    foreach($resultingCustomerObj as $cusObj) {
                        $customer = $this->object_to_array($cusObj);
                        $c['id'] = $customer['Id'];
                        $c['fname'] = $customer['GivenName'];
                        $c['lname'] = $customer['FamilyName'];
                        $clients[] = $c;
                    }

                    $cwpquickbooks_settings_options['clients_list'] = serialize($clients);
                } else {
                    $_SESSION['quickbooks_error_msg'] = $error_customer;
                    $cwpquickbooks_settings_options['clients_list'] = '';
                }

                update_option( 'cwpquickbooks_settings_options', $cwpquickbooks_settings_options );
                header('Location: ' . admin_url( 'edit.php?post_type=bt_client&page=cwp-quickbooks' ));
            }
        endif;
    }

    public function get_quickbooks_client_list_ajax() {
        $cwpquickbooks_settings_options = get_option('cwpquickbooks_settings_options');
        $app_key    = isset($cwpquickbooks_settings_options['app_key']) ? $cwpquickbooks_settings_options['app_key'] : '';
        $app_secret = isset($cwpquickbooks_settings_options['app_secret']) ? $cwpquickbooks_settings_options['app_secret'] : '';
        $app_token = isset($cwpquickbooks_settings_options['app_token']) ? $cwpquickbooks_settings_options['app_token'] : '';
        $clients_list = isset($cwpquickbooks_settings_options['clients_list']) ? $cwpquickbooks_settings_options['clients_list'] : '';

        if(!empty($app_key) && !empty($app_secret) && !empty($app_token) && !empty($clients_list)) {
            echo json_encode(unserialize($clients_list));
        }
        die();
    }

    public function object_to_array($object) {
        $reflectionClass = new ReflectionClass(get_class($object));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }
        return $array;
    }

    public function transactions_content_table() {
        global $pages;

        foreach($pages as $page) {
            if (strpos($page, '[clientswp_user_register_form]') !== FALSE) {
                return nl2br($page);
            }

            if (strpos($page, '[clientswp_group_add_user_form]') !== FALSE) {
                return nl2br($page);
            }
            
            if (strpos($page, '[cwp_') !== FALSE) {
                $args = array(
                    'meta_key' => '_clients_page_shortcode',
                    'meta_value' => $page,
                    'post_type' => 'bt_client_page',
                    'post_status' => 'any',
                    'posts_per_page' => -1
                );
                $posts = get_posts($args);

                foreach($posts as $post) {
                    echo $post->post_content;

                    $integration = get_post_meta($post->ID, '_clients_page_integration', true);
                    $qb_client_id = get_post_meta($post->ID, '_clients_page_integration_folder', true);

                    if (isset($integration)) {
                        if((!empty($integration) && $integration == 'quickbooks')) {
                            $cwpquickbooks_settings_options = get_option('cwpquickbooks_settings_options');
                            $app_key    = isset($cwpquickbooks_settings_options['app_key']) ? $cwpquickbooks_settings_options['app_key'] : '';
                            $app_secret = isset($cwpquickbooks_settings_options['app_secret']) ? $cwpquickbooks_settings_options['app_secret'] : '';
                            $app_token = isset($cwpquickbooks_settings_options['app_token']) ? $cwpquickbooks_settings_options['app_token'] : '';

                            $linked_client_id = get_post_meta($post->ID, '_clients_page_client', true);
                            $client_email = get_post_meta($linked_client_id, '_bt_client_group_owner', true);

                            if (!is_user_logged_in()) {
                                echo 'You are not allowed to see this contents.';
                                return;
                            }

                            $user_groups = cwp_get_current_user_groups();
                            if (empty($user_groups)) {
                                echo 'You are not allowed to see this contents.';
                                return;
                            }

                            if(!empty($app_token)) {
                                $token = unserialize($app_token);
                                $token_array = $this->object_to_array($token);

                                $baseURL = "development";
                                //$baseURL = "production";

                                if($baseURL == "development") {
                                    $qb_url = "sandbox.qbo.intuit.com";
                                } else {
                                    $qb_url = "#";
                                }

                                $dataService = DataService::Configure(array(
                                    'auth_mode' => 'oauth2',
                                    'ClientID' => $app_key,
                                    'ClientSecret' => $app_secret,
                                    'accessTokenKey' =>  $token_array['accessTokenKey'],
                                    'refreshTokenKey' => $token_array['refresh_token'],
                                    'QBORealmID' => $token_array['realmID'],
                                    'baseUrl' => $baseURL
                                ));

                                $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
                                $accessToken = $OAuth2LoginHelper->refreshToken();
                                $dataService->updateOAuth2Token($accessToken);

                                $query_invoice = "SELECT * FROM Invoice WHERE CustomerRef = '".$qb_client_id."'";
                                $resultingInvoiceObj = $dataService->Query($query_invoice);
                                $error_invoice = $dataService->getLastError();

                                if(!$error_invoice) {
                                    $invoices = $resultingInvoiceObj;
                                }

                                $query_estimate = "SELECT * FROM Estimate WHERE CustomerRef = '".$qb_client_id."'";
                                $resultingEstimateObj = $dataService->Query($query_estimate);
                                $error_estimate = $dataService->getLastError();

                                if(!$error_estimate) {
                                    $estimates = $resultingEstimateObj;
                                }

                                include_once(CWPQB_PATH_INCLUDES . '/cwp-quickbooks-table.php');
                            }
                        }
                    }
                }
            } else {
                return nl2br($page);
            }
        }
    }
}