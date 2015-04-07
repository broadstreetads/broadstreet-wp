<?php
/**
 * This file acts as the 'Controller' of the application. It contains a class
 *  that will load the required hooks, and the callback functions that those
 *  hooks execute.
 *
 * @author Bizyhood <support@bizyhood.com>
 */

require_once dirname(__FILE__) . '/Ajax.php';
require_once dirname(__FILE__) . '/Cache.php';
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Benchmark.php';
require_once dirname(__FILE__) . '/Log.php';
require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/Utility.php';
require_once dirname(__FILE__) . '/View.php';
require_once dirname(__FILE__) . '/Exception.php';

if (! class_exists('Bizyhood_Core')):

/**
 * This class contains the core code and callback for the behavior of Wordpress.
 *  It is instantiated and executed directly by the Bizyhood plugin loader file
 *  (which is most likely at the root of the Bizyhood installation).
 */
class Bizyhood_Core
{
    CONST KEY_API_URL             = 'Bizyhood_API_URL';
    CONST KEY_MAIN_PAGE_ID        = 'Bizyhood_Main_page_ID';
    CONST KEY_INSTALL_REPORT      = 'Bizyhood_Installed';
    CONST KEY_ZIP_CODES           = 'Bizyhood_ZIP_Codes';
    CONST KEY_USE_CUISINE_TYPES   = 'Bizyhood_Use_Cuisine_Types';
    CONST KEY_CATEGORIES          = 'Bizyhood_Categories';
    
    public static $globals = null;

    /**
     * The constructor
     */
    public function __construct()
    {
        Bizyhood_Log::add('debug', "Bizyhood initializing");
    }

    static function install()
    {
        Bizyhood_Log::add('debug', "Bizyhood installing");
        
        // Create the business list page
        $business_list_page = get_page_by_title( "Bizyhood business list", "OBJECT", "page" );
        if ( !$business_list_page )
        {
            $business_list_page = array(
                'post_title'     => 'Bizyhood business list',
                'post_type'      => 'page',
                'post_name'      => 'bh-businesses',
                'post_content'   => '[bh-categories] [bh-businesses]',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
                'guid'          => site_url() . "/bh-businesses"
            );
            wp_insert_post( $business_list_page );
        }

        // Create the view business page
        $business_view_page = get_page_by_title( "Bizyhood view business", "OBJECT", "page" );
        if ( !$business_view_page )
        {
            $business_view_page = array(
                'post_title'     => 'Bizyhood view business',
                'post_type'      => 'page',
                'post_name'      => 'bh-business',
                'post_content'   => '',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
                'guid'          => site_url() . "/bh-business"
            );
            wp_insert_post( $business_view_page );
        }

    }

    public function uninstall()
    {
        Bizyhood_Log::add('debug', "Bizyhood uninstalling");

        // Remove business list page
        $business_list_page = get_page_by_title( "Bizyhood business list", "OBJECT", "page" );
        if ($business_list_page)
        {
            Bizyhood_Log::add('info', "Removing business list page (post ID " . $business_list_page->ID . ")");
            wp_delete_post($business_list_page->ID);
        }

        // Remove business list page
        $business_view_page = get_page_by_title( "Bizyhood view business", "OBJECT", "page" );
        if ($business_view_page)
        {
            Bizyhood_Log::add('info', "Removing view business page (post ID " . $business_view_page->ID . ")");
            wp_delete_post($business_view_page->ID);
        }

    }

    /**
     * Get the Bizyhood environment loaded and register Wordpress hooks
     */
    public function execute()
    {
        $this->_registerHooks();
    }
    
    /**
     * Register Wordpress hooks required for Bizyhood
     */
    private function _registerHooks()
    {
        Bizyhood_Log::add('debug', "Registering hooks..");

        # -- Below is core functionality --
        add_action('admin_menu', 	array($this, 'adminCallback'     ));
        add_action('admin_init', 	array($this, 'adminInitCallback' ));
        // add_action('init',          array($this, 'addZoneTag' ));
        // add_action('init',          array($this, 'businessIndexSidebar' ));
        // add_action('admin_notices',     array($this, 'adminWarningCallback'));
        // add_shortcode('bizyhood', array($this, 'shortcode'));
        add_shortcode('bh-businesses', array($this, 'businesses_shortcode'));
        add_shortcode('bh-categories', array($this, 'categories_shortcode'));
        // add_filter('image_size_names_choose', array($this, 'addImageSizes'));
        // add_action('wp_footer', array($this, 'addPoweredBy'));

        // add_action('init', array($this, 'createPostTypes'));
        // add_action('wp_enqueue_scripts', array($this, 'addPostStyles'));
        // add_action('pre_get_posts', array($this, 'modifyPostListing'));
        add_filter('the_content', array($this, 'postTemplate'), 100);
        // add_filter('the_posts', array($this, 'businessQuery'));
        // add_filter('comment_form_defaults', array($this, 'commentForm'));
        // add_action('save_post', array($this, 'savePostMeta'));
        
        # - Below are partly business-related
        // add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        
        # -- Below is administration AJAX functionality
        add_action('wp_ajax_save_settings', array('Bizyhood_Ajax', 'saveSettings'));
        // add_action('wp_ajax_create_advertiser', array('Bizyhood_Ajax', 'createAdvertiser'));
        // add_action('wp_ajax_import_facebook', array('Bizyhood_Ajax', 'importFacebook'));
        // add_action('wp_ajax_register', array('Bizyhood_Ajax', 'register'));
    }
     
    
    /**
     * A callback executed whenever the user tried to access the Bizyhood admin page
     */
    public function adminCallback()
    {
        $icon_url = 'http://bizyhood-common.s3.amazonaws.com/bizyhood-blargo/bizyhood-icon.png';
                
        add_menu_page('Bizyhood', 'Bizyhood', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Bizyhood', 'Settings', 'Account Setup', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'));
        // add_submenu_page('Bizyhood', 'Business Settings', 'Business Settings', 'edit_pages', 'Bizyhood-Business', array($this, 'adminMenuBusinessCallback'));
        // add_submenu_page('Bizyhood', 'Advanced', 'Advanced', 'edit_pages', 'Bizyhood-Layout', array($this, 'adminMenuLayoutCallback'));
        // add_submenu_page('Bizyhood', 'Help', 'How To Get Started', 'edit_pages', 'Bizyhood-Help', array($this, 'adminMenuHelpCallback'));
        // add_submenu_page('Bizyhood', 'Editable Ads', 'Editable Ads&trade;', 'edit_pages', 'Bizyhood-Editable', array($this, 'adminMenuEditableCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            $info = Bizyhood_Utility::getNetwork();

            //if(!$info || !$info->cc_on_file)
            //    echo '<div class="updated"><p>You\'re <strong>almost ready</strong> to start using Bizyhood! Check the <a href="admin.php?page=Bizyhood">plugin page</a> to take care of the last steps. When that\'s done, this message will clear shortly after.</p></div>';
        }
    }

    /**
     * A callback executed when the admin page callback is a about to be called.
     *  Use this for loading stylesheets/css.
     */
    public function adminInitCallback()
    {
        add_image_size('bs-biz-size', 600, 450, true);
        
        # Only register javascript and css if the Bizyhood admin page is loading
        if(strstr($_SERVER['QUERY_STRING'], 'Bizyhood'))
        {
            wp_enqueue_style ('Bizyhood-styles',  Bizyhood_Utility::getCSSBaseURL() . 'bizyhood.css?v='. BIZYHOOD_VERSION);
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BIZYHOOD_VERSION);
        }
        
        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Bizyhood-vendorcss-time', Bizyhood_Utility::getVendorBaseURL() . 'timepicker/css/timePicker.css');
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BIZYHOOD_VERSION);
            wp_enqueue_script('Bizyhood-vendorjs-time'  ,  Bizyhood_Utility::getVendorBaseURL().'timepicker/js/jquery.timePicker.min.js');
        }
        
        # Include thickbox on widgets page
        if($GLOBALS['pagenow'] == 'widgets.php'
                || strstr($_SERVER['QUERY_STRING'], 'Bizyhood-Business'))
        {
            wp_enqueue_script('thickbox');
            wp_enqueue_style( 'thickbox' );
        }
    }

    /**
     * The callback that is executed when the user is loading the admin page.
     *  Basically, output the page content for the admin page. The function
     *  acts just like a controller method for and MVC app. That is, it loads
     *  a view.
     */
    public function adminMenuCallback()
    {
        Bizyhood_Log::add('debug', "Admin page callback executed");
        Bizyhood_Utility::sendInstallReportIfNew();
        
        $data = array();

        $data['api_url']            = Bizyhood_Utility::getApiUrl();
        $data['main_page_id']       = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);
        $data['zip_codes']          = Bizyhood_Utility::getOption(self::KEY_ZIP_CODES);
        $data['use_cuisine_types']  = Bizyhood_Utility::getOption(self::KEY_USE_CUISINE_TYPES);
        $data['categories']         = Bizyhood_Utility::getOption(self::KEY_CATEGORIES);
        $data['errors']             = array();

        if(!function_exists('curl_exec'))
        {
            $data['errors'][] = 'Bizyhood requires the PHP cURL module to be enabled. You may need to ask your web host or developer to enable this.';
        }
        
        if(get_page_by_path('businesses'))
        {
            $data['errors'][] = 'You have a page named "businesses", which will interfere with the business directory if you plan to use it. You must delete that page.';
        }
        
        if(get_category_by_slug('businesses'))
        {
            $data['errors'][] = 'You have a category named "businesses", which will interfere with the business directory if you plan to use it. You must delete that category.';
        }

        Bizyhood_View::load('admin/admin', $data);
    }
    
    public function adminMenuBusinessCallback() {        
        
        if (isset($_POST['featured_business_image'])) {
            $featured_image = Bizyhood_Utility::featuredBusinessImage($_POST['featured_business_image']);
        } else {
            $featured_image = Bizyhood_Utility::featuredBusinessImage();
        }
        
        Bizyhood_View::load('admin/businesses', array('featured_image' => $featured_image));
    }
    
    public function adminMenuEditableCallback()
    {
        Bizyhood_View::load('admin/editable');
    }
    
    
    public function adminMenuHelpCallback()
    {
        Bizyhood_View::load('admin/help');
    }
    
    public function adminMenuLayoutCallback()
    {
        Bizyhood_View::load('admin/layout');
    }
    
    public function businesses_shortcode($attrs)
    {
        $remote_settings = Bizyhood_Utility::getRemoteSettings();
        $api_url = Bizyhood_Utility::getApiUrl();
        $zip_codes = Bizyhood_Utility::getZipsEncoded();
        $use_cuisine_types = Bizyhood_Utility::getOption(self::KEY_USE_CUISINE_TYPES);

        if ($zip_codes) {
            // get current page
            if (get_query_var('paged'))
                $page = get_query_var('paged');
            elseif (isset($_GET['paged']))
                $page = $_GET['paged'];
            else
                $page = 1;

            // get category filter
            if (get_query_var('k'))
                $category = urlencode( get_query_var('k') );
            elseif (isset($_GET['k']))
                $category = urlencode( $_GET['k'] );

            if ($use_cuisine_types) {
                if (isset($category)) {
                    $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/restaurant/?format=json&pc=$zip_codes&ps=10&pn=$page&rad=15000&cu=$category", $remote_settings ) );
                }
                else {
                    $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/restaurant/?format=json&pc=$zip_codes&ps=10&pn=$page&rad=15000", $remote_settings ) );
                }
            }
            else {
                if (isset($category)) {
                    $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/?format=json&pc=$zip_codes&ps=10&pn=$page&rad=15000&k=$category", $remote_settings ) );
                }
                else {
                    $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/?format=json&pc=$zip_codes&ps=10&pn=$page&rad=15000", $remote_settings ) );
                }
            }
            $response_json = json_decode( $response );
            $businesses = $response_json->businesses;
            $pagination_args = array(
                'total'              => ( $response_json->total_count / $response_json->page_size ) + ( ( $response_json->total_count % $response_json->page_size == 0 ) ? 0 : 1 ),
                'current'            => $page,
            );
            $view_business_page_id = get_page_by_title( "Bizyhood view business", "OBJECT", "page" )->ID;

            return Bizyhood_View::load( 'listings/index', array( 'pagination_args' => $pagination_args, 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id ), true );
        }
    }

    public function categories_shortcode($attrs)
    {
        $api_url = Bizyhood_Utility::getApiUrl();
        $zips_encoded = Bizyhood_Utility::getZipsEncoded();
        $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);
        if ($zips_encoded && $list_page_id)
        {
            $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/cuisine/?pc=" . $zips_encoded . "&rad=15000&format=json" ) );
            $response_json = json_decode($response);
            $use_cuisine_types = Bizyhood_Utility::getOption(self::KEY_USE_CUISINE_TYPES, false);
            if ($use_cuisine_types) {
                $cuisines = $response_json->cuisines;
                return Bizyhood_View::load('categories/cuisines', array('cuisines' => $cuisines, 'list_page_id' => $list_page_id), true);
            }
            else {
                $categories = Bizyhood_Utility::getOption(self::KEY_CATEGORIES);
                return Bizyhood_View::load('categories/custom', array('categories' => $categories, 'list_page_id' => $list_page_id), true);
            }
        }
    }


    /**
     * Handler used for modifying the way business listings are displayed
     * @param string $content The post content
     * @return string Content
     */
    public function postTemplate($content)
    {   
        global $post;
        $api_url = Bizyhood_Utility::getApiUrl();

        # Override content for the view business page
        if ($post->post_name === 'bh-business')
        {
            $bizyhood_id = $_REQUEST['bizyhood_id'];
            $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/" . $bizyhood_id ) );
            $business = json_decode($response);
            return Bizyhood_View::load('listings/single/default', array('content' => $content, 'business' => $business), true);
        }

        return $content;
    }
}

endif;
