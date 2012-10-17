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
    CONST KEY_INSTALL_REPORT      = false;
    CONST BIZ_POST_TYPE           = 'broadstreet_biz';
    
    CONST BIZ_ENABLED             = false;

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
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_shortcode('broadstreet', array($this, 'shortcode'));
        add_action('init', array($this, 'createPostTypes'));

        # -- Below is administration AJAX functionality
        add_action('wp_ajax_save_settings', array('Broadstreet_Ajax', 'saveSettings'));
    }
    
    public function addMetaBoxes()
    {
        add_meta_box( 
            'broadstreet_sectionid',
            __( 'Broadstreet Zone Info', 'broadstreet_textdomain' ),
            array(__CLASS__, 'broadstreetInfoBox'),
            'post' 
        );
        add_meta_box(
            'broadstreet_sectionid',
            __( 'Broadstreet Zone Info', 'broadstreet_textdomain'), 
            array(__CLASS__, 'broadstreetInfoBox'),
            'page'
        );
        
        if(self::BIZ_ENABLED):
        add_meta_box(
            'broadstreet_sectionid',
            __( 'Business Details', 'broadstreet_textdomain'), 
            array(__CLASS__, 'broadstreetBusinessBox'),
            self::BIZ_POST_TYPE,
            'normal',
            'high'
        );
        endif;
    }

    /**
     * A callback executed whenever the user tried to access the Broadstreet admin page
     */
    public function adminCallback()
    {
        add_options_page('Broadstreet Settings', 'Broadstreet', 'edit_pages', 'Broadstreet/Broadstreet.php', array($this, 'adminMenuCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {

    }

    /**
     * A callback executed when the admin page callback is a about to be called.
     *  Use this for loading stylesheets/css.
     */
    public function adminInitCallback()
    {
        # Only register javascript and css if the Broadstreet admin page is loading
        if(strstr($_SERVER['QUERY_STRING'], 'Broadstreet') === FALSE) return;
        
        wp_enqueue_style ('Broadstreet-styles',  Broadstreet_Utility::getCSSBaseURL() . 'broadstreet.css');
        wp_enqueue_script('Broadstreet-main'  ,  Broadstreet_Utility::getJSBaseURL().'broadstreet.js');

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

        $data['service_tag'] = Broadstreet_Utility::getServiceTag();
        $data['api_key']     = Broadstreet_Utility::getOption(self::KEY_API_KEY);
        $data['network_id']  = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
        $data['errors']      = array();
        
        if(!$data['api_key']) 
        {
            $data['errors'][] = 'You dont have an API key set yet! Set it below.';
        } 
        else 
        {
            $api = new Broadstreet($data['api_key']);
            
            try
            {
                $data['networks']  = $api->getNetworks();
                $data['key_valid'] = true;
            }
            catch(Exception $ex)
            {
                $data['networks'] = array();
                $data['key_valid'] = false;
            }
        }

        Broadstreet_View::load('admin', $data);
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
        
        Broadstreet_View::load('infoBox', array('zones' => $zone_data));
    }
    
    /**
     * Handler for the broadstreet info box below a post or page
     * @param type $post 
     */
    public function broadstreetBusinessBox($post) 
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'broadstreetnoncename');
        
        Broadstreet_View::load('businessMetaBox', array());
    }
    
    public function createPostTypes()
    {
        if(self::BIZ_ENABLED):
        register_post_type( self::BIZ_POST_TYPE,
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
            'rewrite' => array('slug' => 'businesses'),
            'taxonomies' => array('category', 'post_tag')
            )
        );
        endif;
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
    
    /**
     * The callback used to register the widget
     */
    public function registerWidget()
    {
        register_widget('Broadstreet_Zone_Widget');
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
         
         echo $before_widget;

         echo $zone_data[$zone_id]->html;

         echo $after_widget;
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
                <option <?php if($instance['w_zone'] == $zone->id) echo "selected" ?> value="<?php echo $zone->id ?>"><?php echo $zone->name ?></option>
                <?php endforeach; ?>
            </select>
       </p>
        <?php endif; ?>
        </div>
       <?php
     }
}


endif;