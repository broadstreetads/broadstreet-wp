<?php
/**
 * This file acts as the 'Controller' of the application. It contains a class
 *  that will load the required hooks, and the callback functions that those
 *  hooks execute.
 *
 * @author Bizyhood Ads <labs@bizyhoodads.com>
 */

require_once dirname(__FILE__) . '/Ajax.php';
require_once dirname(__FILE__) . '/Cache.php';
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Benchmark.php';
require_once dirname(__FILE__) . '/Log.php';
require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/Net.php';
require_once dirname(__FILE__) . '/Utility.php';
require_once dirname(__FILE__) . '/View.php';
require_once dirname(__FILE__) . '/Widget.php';
require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/Vendor/Bizyhood.php';

if (! class_exists('Bizyhood_Core')):

/**
 * This class contains the core code and callback for the behavior of Wordpress.
 *  It is instantiated and executed directly by the Bizyhood plugin loader file
 *  (which is most likely at the root of the Bizyhood installation).
 */
class Bizyhood_Core
{
    CONST KEY_API_KEY             = 'Bizyhood_API_Key';
    CONST KEY_API_URL             = 'Bizyhood_API_URL';
    CONST KEY_NETWORK_ID          = 'Bizyhood_Network_Key';
    CONST KEY_BIZ_ENABLED         = 'Bizyhood_Biz_Enabled';
    CONST KEY_INSTALL_REPORT      = 'Bizyhood_Installed';
    CONST KEY_SHOW_OFFERS         = 'Bizyhood_Offers';
    CONST KEY_ZIP_CODES           = 'Bizyhood_ZIP_Codes';
    CONST KEY_USE_CUISINE_TYPES   = 'Bizyhood_Use_Cuisine_Types';
    CONST KEY_CATEGORIES          = 'Bizyhood_Categories';
    CONST BIZ_POST_TYPE           = 'bs_business';
    CONST BIZ_TAXONOMY            = 'business_category';
    CONST BIZ_SLUG                = 'businesses';
    
    /**
     * Default values for the businesses meta fields
     * @var type 
     */
    public static $_businessDefaults = array (
        'bs_advertiser_id' => '',
        'bs_advertisement_id' => '',
        'bs_advertisement_html' => '',
        'bs_update_source' => '',
        'bs_facebook_id' => '',
        'bs_facebook_hashtag' => '',
        'bs_twitter_id' => '',
        'bs_twitter_hashtag' => '',
        'bs_phone_number' => '',
        'bs_address_1' => '',
        'bs_address_2' => '',
        'bs_city' => '',
        'bs_state' => '',
        'bs_postal' => '',
        'bs_latitude' => '',
        'bs_longitude' => '',
        'bs_phone'   => '',
        'bs_hours' => '',
        'bs_website' => '',
        'bs_menu' => '',
        'bs_publisher_review' => '',
        'bs_twitter' => '',
        'bs_facebook' => '',
        'bs_gplus' => '',
        'bs_images' => array(),
        'bs_yelp' => '',
        'bs_video' => '',
        'bs_offer' => '',
        'bs_offer_link' => '',
        'bs_monday_open' => '', 'bs_monday_close' => '',
        'bs_tuesday_open' => '', 'bs_tuesday_close' => '',
        'bs_wednesday_open' => '', 'bs_wednesday_close' => '',
        'bs_thursday_open' => '', 'bs_thursday_close' => '',
        'bs_friday_open' => '', 'bs_friday_close' => '',
        'bs_saturday_open' => '', 'bs_saturday_close' => '',
        'bs_sunday_open' => '', 'bs_sunday_close' => '',
        'bs_featured_business' => '0'
    );
    
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
                'post_content'   => '[bh-businesses]',
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

        // Create the category list page
        $category_list_page = get_page_by_title( "Bizyhood category list", "OBJECT", "page" );
        if ( !$category_list_page )
        {
            $category_list_page = array(
                'post_title'     => 'Bizyhood category list',
                'post_type'      => 'page',
                'post_name'      => 'bh-categories',
                'post_content'   => '',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
                'guid'          => site_url() . "/bh-categories"
            );
            wp_insert_post( $category_list_page );
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
            wp_trash_post($business_list_page->ID);
        }

        // Remove business list page
        $business_view_page = get_page_by_title( "Bizyhood view business", "OBJECT", "page" );
        if ($business_view_page)
        {
            Bizyhood_Log::add('info', "Removing view business page (post ID " . $business_view_page->ID . ")");
            wp_trash_post($business_view_page->ID);
        }

        // Remove category list page
        $category_list_page = get_page_by_title( "Bizyhood category list", "OBJECT", "page" );
        if ($category_list_page)
        {
            Bizyhood_Log::add('info', "Removing category list page (post ID " . $category_list_page->ID . ")");
            wp_trash_post($category_list_page->ID);
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
     * Get a Bizyhood client 
     */
    public function getBizyhoodClient()
    {
        $key = Bizyhood_Utility::getOption(self::KEY_API_KEY);
        return new Bizyhood($key);
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
        add_action('init',          array($this, 'addZoneTag' ));
        add_action('init',          array($this, 'businessIndexSidebar' ));
        add_action('admin_notices',     array($this, 'adminWarningCallback'));
        add_action('widgets_init', array($this, 'registerWidget'));
        add_shortcode('bizyhood', array($this, 'shortcode'));
        add_shortcode('bh-businesses', array($this, 'businesses_shortcode'));
        add_filter('image_size_names_choose', array($this, 'addImageSizes'));
        add_action('wp_footer', array($this, 'addPoweredBy'));

        # -- Below are all business-related hooks
        if(Bizyhood_Utility::isBusinessEnabled())
        {
            add_action('init', array($this, 'createPostTypes'));
            add_action('wp_enqueue_scripts', array($this, 'addPostStyles'));
            add_action('pre_get_posts', array($this, 'modifyPostListing'));
            add_filter('the_content', array($this, 'postTemplate'), 100);
            add_filter('the_posts', array($this, 'businessQuery'));
            add_filter('comment_form_defaults', array($this, 'commentForm'));
            add_action('save_post', array($this, 'savePostMeta'));
        }
        
        # - Below are partly business-related
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        
        # -- Below is administration AJAX functionality
        add_action('wp_ajax_save_settings', array('Bizyhood_Ajax', 'saveSettings'));
        add_action('wp_ajax_create_advertiser', array('Bizyhood_Ajax', 'createAdvertiser'));
        add_action('wp_ajax_import_facebook', array('Bizyhood_Ajax', 'importFacebook'));
        add_action('wp_ajax_register', array('Bizyhood_Ajax', 'register'));
    }
        
    /**
     * Handler used for creating the business category taxonomy
     */
    public function addBusinessTaxonomy() {
        # Add new "Locations" taxonomy to Posts
        register_taxonomy(self::BIZ_TAXONOMY, self::BIZ_POST_TYPE, array(
            # Hierarchical taxonomy (like categories)
            'hierarchical' => true,
            # This array of options controls the labels displayed in the WordPress Admin UI
            'labels' => array(
                'name' => _x( 'Business Categories', 'taxonomy general name' ),
                'singular_name' => _x( 'Business Category', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Businesses by Category' ),
                'all_items' => __( 'All Business Categories' ),
                'parent_item' => __( 'Parent Category' ),
                'parent_item_colon' => __( 'Parent Category:' ),
                'edit_item' => __( 'Edit Business Category' ),
                'update_item' => __( 'Update Business Category' ),
                'add_new_item' => __( 'Add New Business Category' ),
                'new_item_name' => __( 'New Business Category Type' ),
                'menu_name' => __( 'Categories' ),
            ),
            # Control the slugs used for this taxonomy
            'rewrite' => array(
                'slug' => 'business-categories', // This controls the base slug that will display before each term
                'with_front' => false, // Don't display the category base before "/locations/"
                'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
            ),
        ));
    }
    
    /**
     * Callback for adding an extra bizyhood-friendly image size
     * @param array $sizes
     * @return array
     */
    public function addImageSizes($sizes)
    {
        $sizes['bs-biz-size'] = __('Bizyhood Business');
        return $sizes;
    }
    
    /**
     * Handler for adding the Bizyhood business meta data boxes on the post
     * create/edit page 
     */
    public function addMetaBoxes()
    {
        add_meta_box( 
            'bizyhood_sectionid',
            __( 'Bizyhood Zone Info', 'bizyhood_textdomain' ),
            array($this, 'bizyhoodInfoBox'),
            'post' 
        );
        add_meta_box(
            'bizyhood_sectionid',
            __( 'Bizyhood Zone Info', 'bizyhood_textdomain'), 
            array($this, 'bizyhoodInfoBox'),
            'page'
        );
        
        if(Bizyhood_Utility::isBusinessEnabled())
        {
            add_meta_box(
                'bizyhood_sectionid',
                __( 'Business Details', 'bizyhood_textdomain'), 
                array($this, 'bizyhoodBusinessBox'),
                self::BIZ_POST_TYPE,
                'normal',
                'high'
            );        
        }
    }
    
    public function addPostStyles()
    {
        if(get_post_type() == self::BIZ_POST_TYPE && !is_admin())
        {
            wp_enqueue_style ('Bizyhood-styles-listings', Bizyhood_Utility::getCSSBaseURL() . 'listings.css?v=' . BROADSTREET_VERSION);
        }
    }
    
    /**
     * Add powered-by notice 
     */
    public function addPoweredBy()
    {
        if(get_post_type() == self::BIZ_POST_TYPE && !is_admin())
        {
            echo '<style type="text/css">#bs-powered-by {display: none;}</style>';
            echo '<span id="bs-powered-by">';
            echo    'Powered by <a href="http://wordpress.org/extend/plugins/bizyhood/">Wordpress Business Directory</a>, ';
            echo    '<a href="http://bealocalpublisher.com">Start A Local News Site</a>,';
            echo    'and <a href="http://bizyhoodads.com">The Adserver for Local Publishers</a>.';
            echo '</span>';
        }
    }
    
    public function addZoneTag()
    {
        # Add Bizyhood ad zone CDN
        if(!is_admin()) 
        {
            if(is_ssl()) {
                wp_enqueue_script('Bizyhood-cdn', 'https://s3.amazonaws.com/street-production/init.js');
            } else {
                wp_enqueue_script('Bizyhood-cdn', 'http://cdn.bizyhoodads.com/init.js');
            }
        }
    }
    
    public function businessIndexSidebar() 
    {
        if(Bizyhood_Utility::isBusinessEnabled())
        {
            register_sidebar(array(
                'name' => __( 'Business Directory Listing Page' ),
                'id' => 'businesses-right-sidebar',
                'description' => __( 'The right rail displayed in the page when you use the [businesses] shortcode.' ),
                'before_widget' => '<div style="padding-bottom: 10px;" id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3>',
                'after_title' => '</h3>'
              ));
        }
    }
    

    /**
     * A callback executed whenever the user tried to access the Bizyhood admin page
     */
    public function adminCallback()
    {
        $icon_url = 'http://bizyhood-common.s3.amazonaws.com/bizyhood-blargo/bizyhood-icon.png';
                
        add_menu_page('Bizyhood', 'Bizyhood', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Bizyhood', 'Settings', 'Account Setup', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'));
        if(Bizyhood_Utility::isBusinessEnabled())
            add_submenu_page('Bizyhood', 'Business Settings', 'Business Settings', 'edit_pages', 'Bizyhood-Business', array($this, 'adminMenuBusinessCallback'));
        #add_submenu_page('Bizyhood', 'Advanced', 'Advanced', 'edit_pages', 'Bizyhood-Layout', array($this, 'adminMenuLayoutCallback'));
        add_submenu_page('Bizyhood', 'Help', 'How To Get Started', 'edit_pages', 'Bizyhood-Help', array($this, 'adminMenuHelpCallback'));
        add_submenu_page('Bizyhood', 'Editable Ads', 'Editable Ads&trade;', 'edit_pages', 'Bizyhood-Editable', array($this, 'adminMenuEditableCallback'));
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
            wp_enqueue_style ('Bizyhood-styles',  Bizyhood_Utility::getCSSBaseURL() . 'bizyhood.css?v='. BROADSTREET_VERSION);
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BROADSTREET_VERSION);
        }
        
        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Bizyhood-vendorcss-time', Bizyhood_Utility::getVendorBaseURL() . 'timepicker/css/timePicker.css');
            wp_enqueue_script('Bizyhood-main'  ,  Bizyhood_Utility::getJSBaseURL().'bizyhood.js?v='. BROADSTREET_VERSION);
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

        $data['service_tag']        = Bizyhood_Utility::getServiceTag();
        $data['api_key']            = Bizyhood_Utility::getOption(self::KEY_API_KEY);
        $data['api_url']            = Bizyhood_Utility::getApiUrl();
        $data['business_enabled']   = Bizyhood_Utility::getOption(self::KEY_BIZ_ENABLED);
        $data['network_id']         = Bizyhood_Utility::getOption(self::KEY_NETWORK_ID);
        $data['zip_codes']          = Bizyhood_Utility::getOption(self::KEY_ZIP_CODES);
        $data['use_cuisine_types']  = Bizyhood_Utility::getOption(self::KEY_USE_CUISINE_TYPES);
        $data['categories']         = Bizyhood_Utility::getOption(self::KEY_CATEGORIES);
        $data['errors']             = array();
        $data['networks']           = array();
        $data['key_valid']          = false;
        $data['has_cc']             = false;

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
        
        if(!$data['api_key']) 
        {
            $data['errors'][] = '<strong>You dont have an API key set yet!</strong><ol><li>If you already have a Bizyhood account, <a href="http://my.bizyhoodads.com/access-token">get your key here</a>.</li><li>If you don\'t have an account with us, <a target="blank" id="one-click-signup" href="#">then use our one-click signup</a>.</li></ol>';
        } 
        else 
        {
            $api = new Bizyhood($data['api_key']);
            
            try
            {
                $data['networks']  = $api->getNetworks();
                $data['key_valid'] = true;
                $data['network']   = Bizyhood_Utility::getNetwork(true);
                
                if(!$data['network']->cc_on_file)
                    $data['errors'][] = 'Your account does not have a credit card on file for your selected network below. The premium "Magic Import" and "Updateable Message" features, <strong>although entirely optional</strong>, will not work until <a target="_blank" href="'.Bizyhood_Utility::bizyhoodLink('/networks/'. $data['network']->id .'/accounts').'">you add a card here</a>. Your information is confidential, secure, and <em>never</em> shared.';
            }
            catch(Exception $ex)
            {
                $data['networks'] = array();
                $data['key_valid'] = false;
            }
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
    
    /**
     * Handler for the bizyhood info box below a post or page
     * @param type $post 
     */
    public function bizyhoodInfoBox($post) 
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'bizyhoodnoncename');

        $zone_data = Bizyhood_Utility::getZoneCache();
        
        Bizyhood_View::load('admin/infoBox', array('zones' => $zone_data));
    }
    
    /**
     * Handler for the bizyhood info box below a post or page
     * @param type $post 
     */
    public function bizyhoodBusinessBox($post) 
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'bizyhoodnoncename');
        
        $meta = Bizyhood_Utility::getAllPostMeta($post->ID, self::$_businessDefaults);
        
        $network_id       = Bizyhood_Utility::getOption(self::KEY_NETWORK_ID);
        $advertiser_id    = Bizyhood_Utility::getPostMeta($post->ID, 'bs_advertiser_id');
        $advertisement_id = Bizyhood_Utility::getPostMeta($post->ID, 'bs_advertisement_id');
        $network_info     = Bizyhood_Utility::getNetwork();
        $show_offers      = (Bizyhood_Utility::getOption(self::KEY_SHOW_OFFERS) == 'true');
        
        $api = $this->getBizyhoodClient();
        
        if($network_id && $advertiser_id && $advertisement_id)
        {
            $meta['preferred_hash_tag'] = $api->getAdvertisement($network_id, $advertiser_id, $advertisement_id)
                                    ->preferred_hash_tag;
        }
        
        try
        {
            $advertisers = $api->getAdvertisers($network_id);
        } 
        catch(Exception $ex)
        {
            $advertisers = array();
        }
        
        Bizyhood_View::load('admin/businessMetaBox', array(
            'meta'        => $meta, 
            'advertisers' => $advertisers, 
            'network'     => $network_info,
            'show_offers' => $show_offers
        ));
    }

    /**
     * Handler used for attaching post meta data to post query results
     * @global object $wp_query
     * @param array $posts
     * @return array 
     */
    public function businessQuery($posts) 
    {
        global $wp_query;
        
        if(@$wp_query->query_vars['post_type'] == self::BIZ_POST_TYPE
            || @$wp_query->query_vars['taxonomy'] == self::BIZ_TAXONOMY)
        {
            $ids = array();
            foreach($posts as $post) $ids[] = $post->ID;

            $meta = Bizyhood_Model::getPostMeta($ids, self::$_businessDefaults);

            for($i = 0; $i < count($posts); $i++)
            {
                if(isset($meta[$posts[$i]->ID]))
                {
                    $posts[$i]->meta = $meta[$posts[$i]->ID];
                }
            }
        }
        
        return $posts;
    }
    
    /**
     * Handler used for changing the wording of the comment form for business
     * listings.
     * @param array $defaults
     * @return string 
     */
    public function commentForm($defaults)
    {
        $defaults['title_reply'] = 'Leave a Review or Comment';
        return $defaults;
    }
     
    public function createPostTypes()
    {        
        register_post_type(self::BIZ_POST_TYPE,
            array (
                'labels' => array(
                    'name' => __( 'Businesses'),
                    'singular_name' => __( 'Business'),
                    'add_new_item' => __('Add New Business Profile', 'your_text_domain'),
                    'edit_item' => __('Edit Business', 'your_text_domain'),
                    'new_item' => __('New Business Profile', 'your_text_domain'),
                    'all_items' => __('All Businesses', 'your_text_domain'),
                    'view_item' => __('View This Business', 'your_text_domain'),
                    'search_items' => __('Search Businesses', 'your_text_domain'),
                    'not_found' =>  __('No businesses found', 'your_text_domain'),
                    'not_found_in_trash' => __('No businesses found in Trash', 'your_text_domain'), 
                    'parent_item_colon' => '',
                    'menu_name' => __('Businesses', 'your_text_domain')
                ),
            'description' => 'Businesses for inclusion in the Bizyhood business directory',
            'public' => true,
            'has_archive' => true,
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'rewrite' => array( 'slug' => self::BIZ_SLUG),
            'taxonomies' => array('business_category')
            )
        );
        
        $this->addBusinessTaxonomy();
        Bizyhood_Utility::flushRewrites();
    }
    
    /**
     * Handler for modifying business/archive listings
     * @param type $query 
     */
    public function modifyPostListing($query)
    {
        if(is_post_type_archive(self::BIZ_POST_TYPE))
        {
            $query->query_vars['posts_per_page'] = 50;
            $query->query_vars['orderby'] = 'title';
            $query->query_vars['order'] = 'ASC';
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
            $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/" . $bizyhood_id . "?format=json" ) );
            $business = json_decode($response);
            return Bizyhood_View::load('listings/single/default', array('content' => $content, 'business' => $business), true);
        }

        # Override content for the list categories page
        if ($post->post_name === 'bh-categories')
        {
            $zips_encoded = Bizyhood_Utility::getZipsEncoded();
            $list_page_id = get_page_by_title( "Bizyhood business list", "OBJECT", "page" )->ID;
            if ($zips_encoded)
            {
                $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/cuisine/?pc=" . $zips_encoded . "&rad=15000&format=json" ) );
                $response_json = json_decode($response);
                $use_cuisine_types = Bizyhood_Utility::getOption(self::KEY_USE_CUISINE_TYPES, false);
                if ($use_cuisine_types) {
                    $cuisines = $response_json->cuisines;
                    return Bizyhood_View::load('categories/cuisines', array('content' => $content, 'cuisines' => $cuisines, 'list_page_id' => $list_page_id), true);
                }
                else {
                    $categories = Bizyhood_Utility::getOption(self::KEY_CATEGORIES);
                    return Bizyhood_View::load('categories/custom', array('content' => $content, 'categories' => $categories, 'list_page_id' => $list_page_id), true);
                }
            }
        }
        
        return $content;
    }

    /**
     * The callback used to register the widget
     */
    public function registerWidget()
    {
        register_widget('Bizyhood_Zone_Widget');
        register_widget('Bizyhood_SBSZone_Widget');
        register_widget('Bizyhood_Multiple_Zone_Widget');
        register_widget('Bizyhood_Business_Listing_Widget');
        register_widget('Bizyhood_Business_Profile_Widget');
        register_widget('Bizyhood_Business_Categories_Widget');
    }

    /**
     * Handler for saving business-specific meta data
     * @param type $post_id The id of the post
     * @param type $content The post content
     */
    public function savePostMeta($post_id, $content = false)
    {
        if(isset($_POST['bs_submit']))
        {
            foreach(self::$_businessDefaults as $key => $value)
            {
                if(isset($_POST[$key]))
                    Bizyhood_Utility::setPostMeta($post_id, $key, is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]);
                elseif($key == 'bs_images')
                    Bizyhood_Utility::setPostMeta($post_id, $key, self::$_businessDefaults[$key]);
            }
            
            if($_POST['bs_gplus'] == 'enableoffer')
                Bizyhood_Utility::setOption (self::KEY_SHOW_OFFERS, 'true');
            
            # Has an ad been created/set?
            if($_POST['bs_update_source'] !== '')
            {
                # Okay, one is being set, but does it already exist?
                $ad_id = Bizyhood_Utility::getPostMeta($post_id, 'bs_advertisement_id');
                $api   = $this->getBizyhoodClient();
                
                $network_id    = Bizyhood_Utility::getOption(self::KEY_NETWORK_ID);
                $advertiser_id = $_POST['bs_advertiser_id'];

                if(!$ad_id)
                {
                    $name          = "Wordpress Profile Ad";
                    $type          = 'text';

                    $ad = $api->createAdvertisement($network_id, $advertiser_id, $name, $type, array(
                        'default_text' => 'Check back for updates!'
                    ));
                    
                    Bizyhood_Utility::setPostMeta($post_id, 'bs_advertisement_id', $ad->id);
                    Bizyhood_Utility::setPostMeta($post_id, 'bs_advertisement_html', $ad->html);
                    
                    $ad_id = $ad->id;
                }
                
                $params   = array();
                $hash_tag = false;
                
                if($_POST['bs_update_source'] == 'facebook')
                {
                    $params['facebook_id'] = $_POST['bs_facebook_id'];
                    $hash_tag    = $_POST['bs_facebook_hashtag'];
                } 
                elseif($_POST['bs_update_source'] == 'twitter')
                {
                    $params['twitter_id'] = $_POST['bs_twitter_id'];
                    $hash_tag   = $_POST['bs_twitter_hashtag'];
                }
                elseif($_POST['bs_update_source'] == 'text_message')
                {
                    $params['phone_number'] = $_POST['bs_phone_number'];
                }
                
                # Update the ad
                if($hash_tag)
                {
                    $api->updateAdvertisement($network_id, $advertiser_id, $ad_id, array (
                        'hash_tag' => $hash_tag
                    ));
                }
                
                # Set the ad source
                $ad = $api->setAdvertisementSource($network_id, $advertiser_id, $ad_id, $_POST['bs_update_source'], $params);
            }
        }
    }

    /**
     * Handler for in-post shortcodes
     * @param array $attrs
     * @return string 
     */
    public function shortcode($attrs)
    {
        if(isset($attrs['ad'])) {
            return Bizyhood_Utility::getAdCode($attrs['ad']);
        }    
        
        if(isset($attrs['zone'])) {
            return Bizyhood_Utility::getZoneCode($attrs['zone']);
        }
        
        return '';
    }   
    
    public function businesses_shortcode($attrs)
    {
        $remote_settings = Bizyhood_Utility::getRemoteSettings();
        $api_url = Bizyhood_Utility::getApiUrl();
        $zip_codes = Bizyhood_Utility::getZipsEncoded();
        $page = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
        $query = isset( $_GET['k'] ) ? '&k=' . urlencode( $_REQUEST['k'] ) : '';

        $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/?format=json&pc=$zip_codes&ps=10&pn=$page&rad=15000$query", $remote_settings ) );
        $response_json = json_decode( $response );
        $businesses = $response_json->businesses;
        $pagination_args = array(
            'total'              => ( $response_json->total_count / $response_json->page_size ),
            'current'            => $page,
        );
        $view_business_page_id = get_page_by_title( "Bizyhood view business", "OBJECT", "page" )->ID;

        return Bizyhood_View::load( 'listings/index', array( 'pagination_args' => $pagination_args, 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id ), true );
    }
}

endif;
