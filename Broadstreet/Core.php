<?php
/**
 * This file acts as the 'Controller' of the application. It contains a class
 *  that will load the required hooks, and the callback functions that those
 *  hooks execute.
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
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
require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/Vendor/Broadstreet.php';

if (! class_exists('Broadstreet_Core')):

/**
 * This class contains the core code and callback for the behavior of Wordpress.
 *  It is instantiated and executed directly by the Broadstreet plugin loader file
 *  (which is most likely at the root of the Broadstreet installation).
 */
class Broadstreet_Core
{
    CONST KEY_API_KEY             = 'Broadstreet_API_Key';
    CONST KEY_NETWORK_ID          = 'Broadstreet_Network_Key';
    CONST KEY_BIZ_ENABLED         = 'Broadstreet_Biz_Enabled';
    CONST KEY_INSTALL_REPORT      = 'Broadstreet_Installed';
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
        'bs_images' => array(),
        'bs_yelp' => '',
        'bs_monday_open' => '', 'bs_monday_close' => '',
        'bs_tuesday_open' => '', 'bs_tuesday_close' => '',
        'bs_wednesday_open' => '', 'bs_wednesday_close' => '',
        'bs_thursday_open' => '', 'bs_thursday_close' => '',
        'bs_friday_open' => '', 'bs_friday_close' => '',
        'bs_saturday_open' => '', 'bs_saturday_close' => '',
        'bs_sunday_open' => '', 'bs_sunday_close' => '',
    );
    
    /**
     * The constructor
     */
    public function __construct()
    {
        Broadstreet_Log::add('debug', "Broadstreet initializing..");
    }

    /**
     * Get the Broadstreet environment loaded and register Wordpress hooks
     */
    public function execute()
    {
        $this->_registerHooks();
    }

    /**
     * Get a Broadstreet client 
     */
    public function getBroadstreetClient()
    {
        $key = Broadstreet_Utility::getOption(self::KEY_API_KEY);
        return new Broadstreet($key);
    }
    
    /**
     * Register Wordpress hooks required for Broadstreet
     */
    private function _registerHooks()
    {
        Broadstreet_Log::add('debug', "Registering hooks..");

        # -- Below is core functionality --
        add_action('admin_menu', 	array($this, 'adminCallback'     ));
        add_action('admin_init', 	array($this, 'adminInitCallback' ));
        add_action('admin_notices',     array($this, 'adminWarningCallback'));
        add_action('widgets_init', array($this, 'registerWidget'));
        add_shortcode('broadstreet', array($this, 'shortcode'));
        add_filter('image_size_names_choose', array($this, 'addImageSizes'));
        
        # -- Below are all business-related hooks
        if(Broadstreet_Utility::isBusinessEnabled())
        {
            add_action('init', array($this, 'createPostTypes'));
            add_action('wp_enqueue_scripts', array($this, 'addPostStyles'));
            add_action('pre_get_posts', array($this, 'modifyPostListing'));
            add_filter('the_content', array($this, 'postTemplate'), 100);
            add_filter('the_excerpt', array($this, 'postExcerpt'), 100);
            add_filter('the_posts', array($this, 'businessQuery'));
            add_filter('comment_form_defaults', array($this, 'commentForm'));
            add_action('save_post', array($this, 'savePostMeta'));
        }
        
        # - Below are partly business-related
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        
        # -- Below is administration AJAX functionality
        add_action('wp_ajax_save_settings', array('Broadstreet_Ajax', 'saveSettings'));
        add_action('wp_ajax_create_advertiser', array('Broadstreet_Ajax', 'createAdvertiser'));
        add_action('wp_ajax_import_facebook', array('Broadstreet_Ajax', 'importFacebook'));
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
     * Callback for adding an extra broadstreet-friendly image size
     * @param array $sizes
     * @return array
     */
    public function addImageSizes($sizes)
    {
        $sizes['bs-biz-size'] = __('Broadstreet Business');
        return $sizes;
    }
    
    /**
     * Handler for adding the Broadstreet business meta data boxes on the post
     * create/edit page 
     */
    public function addMetaBoxes()
    {
        add_meta_box( 
            'broadstreet_sectionid',
            __( 'Broadstreet Zone Info', 'broadstreet_textdomain' ),
            array($this, 'broadstreetInfoBox'),
            'post' 
        );
        add_meta_box(
            'broadstreet_sectionid',
            __( 'Broadstreet Zone Info', 'broadstreet_textdomain'), 
            array($this, 'broadstreetInfoBox'),
            'page'
        );
        
        if(Broadstreet_Utility::isBusinessEnabled())
        {
            add_meta_box(
                'broadstreet_sectionid',
                __( 'Business Details', 'broadstreet_textdomain'), 
                array($this, 'broadstreetBusinessBox'),
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
            wp_enqueue_style ('Broadstreet-styles-listings', Broadstreet_Utility::getCSSBaseURL() . 'listings.css');
        }
    }
    

    /**
     * A callback executed whenever the user tried to access the Broadstreet admin page
     */
    public function adminCallback()
    {
        $icon_url = Broadstreet_Utility::getImageBaseURL() . 'marty-icon.png';
                
        add_menu_page('Broadstreet', 'Broadstreet', 'edit_pages', 'Broadstreet', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Broadstreet', 'Settings', 'Account Setup', 'edit_pages', 'Broadstreet', array($this, 'adminMenuCallback'));
        add_submenu_page('Broadstreet', 'Help', 'How To Get Started', 'edit_pages', 'Broadstreet-Help', array($this, 'adminMenuHelpCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            $info = Broadstreet_Utility::getNetwork();

            if(!$info || !$info->cc_on_file)
                echo '<div class="updated"><p>You\'re <strong>almost ready</strong> to start using Broadstreet! Check the <a href="admin.php?page=Broadstreet">plugin page</a> to take care of the last steps. When that\'s done, this message will clear shortly after.</p></div>';
        }
    }

    /**
     * A callback executed when the admin page callback is a about to be called.
     *  Use this for loading stylesheets/css.
     */
    public function adminInitCallback()
    {
        add_image_size('bs-biz-size', 600, 450, true);
        
        # Only register javascript and css if the Broadstreet admin page is loading
        if(strstr($_SERVER['QUERY_STRING'], 'Broadstreet'))
        {
            wp_enqueue_style ('Broadstreet-styles',  Broadstreet_Utility::getCSSBaseURL() . 'broadstreet.css');
            wp_enqueue_script('Broadstreet-main'  ,  Broadstreet_Utility::getJSBaseURL().'broadstreet.js');
        }
        
        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Broadstreet-vendorcss-time', Broadstreet_Utility::getVendorBaseURL() . 'timepicker/css/timePicker.css');
            wp_enqueue_script('Broadstreet-main'  ,  Broadstreet_Utility::getJSBaseURL().'broadstreet.js');
            wp_enqueue_script('Broadstreet-vendorjs-time'  ,  Broadstreet_Utility::getVendorBaseURL().'timepicker/js/jquery.timePicker.min.js');
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
        Broadstreet_Log::add('debug', "Admin page callback executed");
        Broadstreet_Utility::sendInstallReportIfNew();
        
        $data = array();

        $data['service_tag']        = Broadstreet_Utility::getServiceTag();
        $data['api_key']            = Broadstreet_Utility::getOption(self::KEY_API_KEY);
        $data['business_enabled']   = Broadstreet_Utility::getOption(self::KEY_BIZ_ENABLED);
        $data['network_id']         = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
        $data['errors']             = array();
        $data['networks']           = array();
        $data['key_valid']          = false;
        $data['has_cc']             = false;
        
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
            $data['errors'][] = 'You dont have an API key set yet! Set it below. If you don\'t have an account with us, <a target="blank" href="http://my.broadstreetads.com/register">sign up here</a>.';
        } 
        else 
        {
            $api = new Broadstreet($data['api_key']);
            
            try
            {
                $data['networks']  = $api->getNetworks();
                $data['key_valid'] = true;
                $data['network']   = Broadstreet_Utility::getNetwork(true);
                
                if(!$data['network']->cc_on_file)
                    $data['errors'][] = 'Your account does not have a credit card on file for your selected network below. The "Magic Import" and "Updateable Message" features will not work until <a target="_blank" href="http://my.broadstreetads.com/networks/'. $data['network_id'] .'/payment_detail/edit">you add a card here.</a>';
            }
            catch(Exception $ex)
            {
                $data['networks'] = array();
                $data['key_valid'] = false;
            }
        }

        Broadstreet_View::load('admin/admin', $data);
    }
    
    public function adminMenuHelpCallback()
    {
        Broadstreet_View::load('admin/help');
    }
    
    /**
     * Handler for the broadstreet info box below a post or page
     * @param type $post 
     */
    public function broadstreetInfoBox($post) 
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'broadstreetnoncename');

        $zone_data = Broadstreet_Utility::getZoneCache();
        
        Broadstreet_View::load('admin/infoBox', array('zones' => $zone_data));
    }
    
    /**
     * Handler for the broadstreet info box below a post or page
     * @param type $post 
     */
    public function broadstreetBusinessBox($post) 
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'broadstreetnoncename');
        
        $meta = Broadstreet_Utility::getAllPostMeta($post->ID, self::$_businessDefaults);
        
        $network_id       = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
        $advertiser_id    = Broadstreet_Utility::getPostMeta($post->ID, 'bs_advertiser_id');
        $advertisement_id = Broadstreet_Utility::getPostMeta($post->ID, 'bs_advertisement_id');
        $network_info     = Broadstreet_Utility::getNetwork();
        
        $api = $this->getBroadstreetClient();
        
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
        
        Broadstreet_View::load('admin/businessMetaBox', array(
            'meta'        => $meta, 
            'advertisers' => $advertisers, 
            'network'     => $network_info
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

            $meta = Broadstreet_Model::getPostMeta($ids, self::$_businessDefaults);

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
            'description' => 'Businesses for inclusion in the Broadstreet business directory',
            'public' => true,
            'has_archive' => true,
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'rewrite' => array( 'slug' => self::BIZ_SLUG),
            'taxonomies' => array('business_category')
            )
        );
        
        $this->addBusinessTaxonomy();
        Broadstreet_Utility::flushRewrites();
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
        # Only do this for business posts, and don't do it
        #  for excerpts
        if(!Broadstreet_Utility::inExcerpt() 
                && get_post_type() == self::BIZ_POST_TYPE)
        {
            $meta = $GLOBALS['post']->meta;
            
            # Make sure the image meta is unserialized properly
            if(isset($meta['bs_images'])) 
                $meta['bs_images'] = maybe_unserialize($meta['bs_images']);

            if(is_single())
            {
                return Broadstreet_View::load('listings/single/default', array('content' => $content, 'meta' => $meta), true);
            }
            else
            {   
                return Broadstreet_View::load('listings/archive/default', array('content' => $content, 'meta' => $meta), true);
            }
        }
        
        return $content;
    }
    
    /**
     * Handler used for modifying the way business listings are displayed
     *  in exceprts, for themes that use excerpts
     * @param type $content
     * @return type 
     */
    public function postExcerpt($content)
    {
        if(get_post_type() == self::BIZ_POST_TYPE)
        {
            $meta = $GLOBALS['post']->meta;
            $this->_excerptRan = true;
            return Broadstreet_View::load('listings/archive/excerpt', array('content' => $content, 'meta' => $meta), true);
        }
    }
    
    /**
     * The callback used to register the widget
     */
    public function registerWidget()
    {
        register_widget('Broadstreet_Zone_Widget');
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
                    Broadstreet_Utility::setPostMeta($post_id, $key, is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]);
                elseif($key == 'bs_images')
                    Broadstreet_Utility::setPostMeta($post_id, $key, self::$_businessDefaults[$key]);
            }
            
            # Has an ad been created/set?
            if($_POST['bs_update_source'] !== '')
            {
                # Okay, one is being set, but does it already exist?
                $ad_id = Broadstreet_Utility::getPostMeta($post_id, 'bs_advertisement_id');
                $api   = $this->getBroadstreetClient();
                
                $network_id    = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
                $advertiser_id = $_POST['bs_advertiser_id'];

                if(!$ad_id)
                {
                    $name          = "Wordpress Profile Ad";
                    $type          = 'text';

                    $ad = $api->createAdvertisement($network_id, $advertiser_id, $name, $type, array(
                        'default_text' => 'Check back for updates!'
                    ));
                    
                    Broadstreet_Utility::setPostMeta($post_id, 'bs_advertisement_id', $ad->id);
                    Broadstreet_Utility::setPostMeta($post_id, 'bs_advertisement_html', $ad->html);
                    
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
        $zone_data = Broadstreet_Utility::getZoneCache();
        
        if(isset($attrs['zone'])
            && isset($zone_data[$attrs['zone']])) {
            return $zone_data[$attrs['zone']]->html;
        } else {
            return '';
        }   
    }    
}

/**
 * This is an optional widget to display a broadstreet zone
 */
class Broadstreet_Zone_Widget extends WP_Widget
{
    /**
     * Set the widget options
     */
     function __construct()
     {
        $widget_ops = array('classname' => 'bs_zones', 'description' => 'A list of your Broadstreet zones');
        $this->WP_Widget('bs_zones', 'Broadstreet Ad Zone', $widget_ops);
     }

     /**
      * Display the widget on the sidebar
      * @param array $args
      * @param array $instance
      */
     function widget($args, $instance)
     {
         extract($args);
         
         $zone_id   = $instance['w_zone'];
         $zone_data = Broadstreet_Utility::getZoneCache();
         
         if($zone_data)
         {
            echo $before_widget;

            echo $zone_data[$zone_id]->html;

            echo $after_widget;
         }
     }

     /**
      * Update the widget info from the admin panel
      * @param array $new_instance
      * @param array $old_instance
      * @return array
      */
     function update($new_instance, $old_instance)
     {
        $instance = $old_instance;
        
        $instance['w_zone'] = $new_instance['w_zone'];

        return $instance;
     }

     /**
      * Display the widget update form
      * @param array $instance
      */
     function form($instance) 
     {

        $defaults = array('w_title' => 'Broadstreet Ad Zones', 'w_info_string' => '', 'w_opener' => '', 'w_closer' => '');
		$instance = wp_parse_args((array) $instance, $defaults);
        
        $zones = Broadstreet_Utility::refreshZoneCache();
        
       ?>
        <div class="widget-content">
       <?php if(count($zones) == 0): ?>
            <p style="color: green; font-weight: bold;">You either have no zones or
                Broadstreet isn't configured correctly. Go to 'Settings', then 'Broadstreet',
            and make sure your access token is correct, and make sure you have zones set up.</p>
        <?php else: ?>
        <input class="widefat" type="hidden" id="<?php echo $this->get_field_id('w_title'); ?>" name="<?php echo $this->get_field_name('w_title'); ?>" value="" />
       <p>
            <label for="<?php echo $this->get_field_id('w_info_string'); ?>">Zone</label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'w_zone' ); ?>" name="<?php echo $this->get_field_name('w_zone'); ?>" >
                <?php foreach($zones as $id => $zone): ?>
                <option <?php if(isset($instance['w_zone']) && $instance['w_zone'] == $zone->id) echo "selected" ?> value="<?php echo $zone->id ?>"><?php echo $zone->name ?></option>
                <?php endforeach; ?>
            </select>
       </p>
        <?php endif; ?>
        </div>
       <?php
     }
}


endif;