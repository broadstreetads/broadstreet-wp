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
require_once dirname(__FILE__) . '/Widget.php';
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
    CONST KEY_SHOW_OFFERS         = 'Broadstreet_Offers';
    CONST KEY_PLACEMENTS          = 'Broadstreet_Placements';
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
        add_action('wp_enqueue_scripts',          array($this, 'addZoneTag' ));
        add_filter('script_loader_tag',          array($this, 'finalizeZoneTag' ));
        add_action('init',          array($this, 'businessIndexSidebar' ));
        add_action('admin_notices',     array($this, 'adminWarningCallback'));
        add_action('widgets_init', array($this, 'registerWidget'));
        add_shortcode('broadstreet', array($this, 'shortcode'));
        add_shortcode('businesses', array($this, 'businesses_shortcode'));
        add_filter('image_size_names_choose', array($this, 'addImageSizes'));
        add_action('wp_footer', array($this, 'addPoweredBy'));
        add_action('wp_head', array($this, 'setWhitelabel'));
        # -- Ad injection
        add_filter('the_content', array($this, 'addAdsContent'), 100);
        add_action('loop_end', array($this, 'addAdsLoopEnd'), 100);
        #add_action('comment_form_before', array($this, 'addAdsBeforeComments'), 1);
        add_filter('comments_template', array($this, 'addAdsBeforeComments'), 100);


        # -- Below are all business-related hooks
        if(Broadstreet_Utility::isBusinessEnabled())
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
        add_action('wp_ajax_bs_save_settings', array('Broadstreet_Ajax', 'saveSettings'));
        add_action('wp_ajax_create_advertiser', array('Broadstreet_Ajax', 'createAdvertiser'));
        add_action('wp_ajax_import_facebook', array('Broadstreet_Ajax', 'importFacebook'));
        add_action('wp_ajax_register', array('Broadstreet_Ajax', 'register'));
        add_action('wp_ajax_save_zone_settings', array('Broadstreet_Ajax', 'saveZoneSettings'));
    }

    public function addAdsContent($content) {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $above_content = property_exists($placement_settings, 'above_content') && $placement_settings->above_content;
        $below_content = property_exists($placement_settings, 'below_content') && $placement_settings->below_content;
        $in_content = property_exists($placement_settings, 'in_content') && $placement_settings->in_content;

        if (is_single()) {

            if ($in_content) {

                try {
                    $in_content_paragraph = property_exists($placement_settings, 'in_content_paragraph') ? $placement_settings->in_content_paragraph : '4';
                    $in_content_paragraph = array_map('trim', explode(',', $in_content_paragraph));
                } catch (Exception $e) {
                    // user error
                    $in_content_paragraph = array(4);
                }

                /* Now handle in-content */
                if (stristr($content, '[broadstreet zone'))
                    return $content;

                if (stristr($content, '<broadstreet-zone'))
                    return $content;

                $in_story_zone = Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->in_content);

                /* Split the content into paragraphs, clear out anything that is only whitespace */
                $pieces = preg_split('/(\r\n|\n|\r)+/', trim($content));
                $real_pieces = array();

                foreach($pieces as $piece) {
                    $piece = str_replace('&nbsp;', '', $piece);
                    $piece = str_replace('<p></p>', '', $piece);
                    if (strlen($piece)) $real_pieces[] = $piece;
                }

                $pieces = $real_pieces;

                if (count($pieces) <= 1 && ($above_content || $below_content)) {
                    # One paragraph
                    #return "$content\n\n" . $in_story_zone;
                }

                for ($i = 0; $i < count($in_content_paragraph); $i++) {
                    if (count($pieces) >= $in_content_paragraph[$i]) {
                        array_splice($pieces, $in_content_paragraph[$i], 0, $in_story_zone);
                    }
                }

                /* It's magic, :snort: :snort:
                   - Mr. Bean: https://www.youtube.com/watch?v=x0yQg8kHVcI */
                $content = implode("\n\n", $pieces);
            }

            if ($above_content) {
                $content = Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->above_content) . $content;
            }

            if ($below_content) {
                $content = $content . Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->below_content);
            }
        }

        return $content;
    }

    public function addAdsBeforeComments($template) {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (is_single()) {
            if (property_exists($placement_settings, 'before_comments') && $placement_settings->before_comments) {
                echo Broadstreet_Utility::getMaxWidthWrap($placement_settings, Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->before_comments));
            }
        }

        return $template;
    }

    public function addAdsLoopEnd() {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (is_archive() && in_the_loop()) {
            if (property_exists($placement_settings, 'inbetween_archive') && $placement_settings->inbetween_archive) {
                echo Broadstreet_Utility::getMaxWidthWrap($placement_settings, Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->inbetween_archive));
            }
        }
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
            wp_enqueue_style ('Broadstreet-styles-listings', Broadstreet_Utility::getCSSBaseURL() . 'listings.css?v=' . BROADSTREET_VERSION);
        }
    }

    /**
     * Add powered-by notice
     */
    public function addPoweredBy()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $network_id = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
        $args = '{}';
        if (property_exists($placement_settings, 'beta_tag_arguments') && strlen($placement_settings->beta_tag_arguments)) {
            $args = $placement_settings->beta_tag_arguments;
            $args = json_decode($args);
            if (!$args) {
                $args = new stdClass();
            }

            $args->networkId = $network_id;
            $args->targets = Broadstreet_Utility::getTargets();

            if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->adserver_whitelabel) > 0) {
                $args->domain = $placement_settings->adserver_whitelabel;
            }
            $args = json_encode($args);
        }

        echo "<script data-cfasync='false'>window.broadstreetKeywords = [" . Broadstreet_Utility::getAllAdKeywordsString() . "]</script>";
        echo "<script data-cfasync='false'>window.broadstreetTargets = " . json_encode(Broadstreet_Utility::getTargets()) . ";</script>";

        if (property_exists($placement_settings, 'defer_configuration') && strlen($placement_settings->defer_configuration)) {
            echo "<script data-cfasync='false'>if (window.broadstreet && window.broadstreet.loadNetworkJS) window.broadstreet.loadNetworkJS($network_id)</script>";
        } else {

            echo "<script data-cfasync='false'>if (broadstreet) broadstreet.watch($args);</script>";
        }
    }

    public function setWhitelabel()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (property_exists($placement_settings, 'use_old_tags') && $placement_settings->use_old_tags) {
            if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->adserver_whitelabel) > 0) {
                echo "<script data-cfasync='false'>broadstreet.setWhitelabel('//{$placement_settings->adserver_whitelabel}/')</script>";
            }
        }
    }

    /**
     * Manipulate our cdn tags to include anti-cloudflare stuff, because they think they own
     * all of the internet's javascript
     * @param $tag
     * @param string $handle
     * @param bool $src
     * @return mixed
     */
    public function finalizeZoneTag($tag, $handle = '', $src = false)
    {
        // add cloudflare attrs. but seriously, f cloudflare
        if (strstr($tag, 'init-2.min.js') || strstr($tag, 'init.js')) {
            $tag = str_replace('src', "data-cfasync='false' src", $tag);
        }

        return $tag;
    }

    public function addZoneTag()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $old = false;
        if (property_exists($placement_settings, 'use_old_tags') && $placement_settings->use_old_tags) {
            $old = true;
        }

        # Add Broadstreet ad zone CDN
        if(!is_admin())
        {
            $file = 'init-2.min.js';
            if ($old) {
                $file = 'init.js';
            }

            $placement_settings = Broadstreet_Utility::getPlacementSettings();
            $host = 'cdn.broadstreetads.com';
            if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->cdn_whitelabel) > 0) {
                $host = $placement_settings->cdn_whitelabel;
            }
            # except for cdn whitelabels
            if (is_ssl() && property_exists($placement_settings, 'cdn_whitelabel') && $placement_settings->cdn_whitelabel) {
                $host = 's3.amazonaws.com/street-production';
            }
            wp_enqueue_script('broadstreet-cdn', "//$host/$file");
        }
    }

    public function businessIndexSidebar()
    {
        if(Broadstreet_Utility::isBusinessEnabled())
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
     * A callback executed whenever the user tried to access the Broadstreet admin page
     */
    public function adminCallback()
    {
        $icon_url = 'http://broadstreet-common.s3.amazonaws.com/broadstreet-blargo/broadstreet-icon.png';

        add_menu_page('Broadstreet', 'Broadstreet', 'edit_pages', 'Broadstreet', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Broadstreet', 'Settings', 'Account Setup', 'edit_pages', 'Broadstreet', array($this, 'adminMenuCallback'));
        add_submenu_page('Broadstreet', 'Zone Options', 'Zone Options', 'edit_pages', 'Broadstreet-Zone-Options', array($this, 'adminZonesMenuCallback'));
        if(Broadstreet_Utility::isBusinessEnabled())
            add_submenu_page('Broadstreet', 'Business Settings', 'Business Settings', 'edit_pages', 'Broadstreet-Business', array($this, 'adminMenuBusinessCallback'));
        #add_submenu_page('Broadstreet', 'Advanced', 'Advanced', 'edit_pages', 'Broadstreet-Layout', array($this, 'adminMenuLayoutCallback'));
        add_submenu_page('Broadstreet', 'Help', 'Business Directory Help', 'edit_pages', 'Broadstreet-Help', array($this, 'adminMenuHelpCallback'));
        add_submenu_page('Broadstreet', 'Editable Ads', 'Editable Ads&trade;', 'edit_pages', 'Broadstreet-Editable', array($this, 'adminMenuEditableCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            $info = Broadstreet_Utility::getNetwork();

            //if(!$info || !$info->cc_on_file)
            //    echo '<div class="updated"><p>You\'re <strong>almost ready</strong> to start using Broadstreet! Check the <a href="admin.php?page=Broadstreet">plugin page</a> to take care of the last steps. When that\'s done, this message will clear shortly after.</p></div>';
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
        if(isset($_SERVER['QUERY_STRING']) && strstr($_SERVER['QUERY_STRING'], 'Broadstreet'))
        {
            wp_enqueue_style ('Broadstreet-styles',  Broadstreet_Utility::getCSSBaseURL() . 'broadstreet.css?v='. BROADSTREET_VERSION);
            wp_enqueue_script('Broadstreet-main'  ,  Broadstreet_Utility::getJSBaseURL().'broadstreet.js?v='. BROADSTREET_VERSION);
            wp_enqueue_script('angular-js', Broadstreet_Utility::getJSBaseURL().'angular.min.js');
            wp_enqueue_script('isteven-multi-js', Broadstreet_Utility::getJSBaseURL().'isteven-multi-select.js');
            wp_enqueue_style ('isteven-multi-css',  Broadstreet_Utility::getCSSBaseURL() . 'isteven-multi-select.css');
        }

        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php'
                || $GLOBALS['pagenow'] == 'post-new.php')
        {
            wp_enqueue_style ('Broadstreet-vendorcss-time', Broadstreet_Utility::getVendorBaseURL() . 'timepicker/css/timePicker.css');
            wp_enqueue_script('Broadstreet-main'  ,  Broadstreet_Utility::getJSBaseURL().'broadstreet.js?v='. BROADSTREET_VERSION);
            wp_enqueue_script('Broadstreet-vendorjs-time'  ,  Broadstreet_Utility::getVendorBaseURL().'timepicker/js/jquery.timePicker.min.js');
        }

        # Include thickbox on widgets page
        if($GLOBALS['pagenow'] == 'widgets.php'
                || (isset($_SERVER['QUERY_STRING']) && strstr($_SERVER['QUERY_STRING'], 'Broadstreet-Business')))
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
            $data['errors'][] = '<strong>You dont have an API key set yet!</strong><ol><li>If you already have a Broadstreet account, <a href="http://my.broadstreetads.com/access-token">get your key here</a>.</li><li>If you don\'t have an account with us, <a target="blank" id="one-click-signup" href="#">then use our one-click signup</a>.</li></ol>';
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
                    $data['errors'][] = 'Your account does not have a credit card on file for your selected network below. The premium "Magic Import" and "Updateable Message" features, <strong>although entirely optional</strong>, will not work until <a target="_blank" href="'.Broadstreet_Utility::broadstreetLink('/networks/'. $data['network']->id .'/accounts').'">you add a card here</a>. Your information is confidential, secure, and <em>never</em> shared.';
            }
            catch(Exception $ex)
            {
                $data['networks'] = array();
                $data['key_valid'] = false;
            }
        }

        Broadstreet_View::load('admin/admin', $data);
    }

    /**
     * The callback that is executed when the user is loading the admin page.
     *  Basically, output the page content for the admin page. The function
     *  acts just like a controller method for and MVC app. That is, it loads
     *  a view.
     */
    public function adminZonesMenuCallback()
    {
        Broadstreet_Log::add('debug', "Admin page callback executed");
        $data = array();

        $data['service_tag']        = Broadstreet_Utility::getServiceTag();
        $data['api_key']            = Broadstreet_Utility::getOption(self::KEY_API_KEY);
        $data['network_id']         = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
        $data['errors']             = array();
        $data['networks']           = array();
        $data['zones']              = array();
        $data['placements']              = array();
        $data['key_valid']          = false;
        $data['categories']         = get_categories(array('hide_empty' => false));
        $data['tags']               = get_tags(array('hide_empty' => false));

        if(!$data['api_key'])
        {
            $data['errors'][] = '<strong>You dont have an API key set yet!</strong><ol><li>If you already have a Broadstreet account, <a href="http://my.broadstreetads.com/access-token">get your key here</a>.</li><li>If you don\'t have an account with us, <a target="blank" id="one-click-signup" href="#">then use our one-click signup</a>.</li></ol>';
        }
        else
        {
            $api = new Broadstreet($data['api_key']);

            try
            {
                Broadstreet_Utility::refreshZoneCache();
                $data['key_valid'] = true;
                $data['zones'] = Broadstreet_Utility::getZoneCache();
                $data['placements'] = Broadstreet_Utility::getPlacementSettings();
            }
            catch(Exception $ex)
            {
                $data['networks'] = array();
                $data['key_valid'] = false;
            }
        }

        Broadstreet_View::load('admin/zones', $data);
    }

    public function adminMenuBusinessCallback() {

        if (isset($_POST['featured_business_image'])) {
            $featured_image = Broadstreet_Utility::featuredBusinessImage($_POST['featured_business_image']);
        } else {
            $featured_image = Broadstreet_Utility::featuredBusinessImage();
        }

        Broadstreet_View::load('admin/businesses', array('featured_image' => $featured_image));
    }

    public function adminMenuEditableCallback()
    {
        Broadstreet_View::load('admin/editable');
    }


    public function adminMenuHelpCallback()
    {
        Broadstreet_View::load('admin/help');
    }

    public function adminMenuLayoutCallback()
    {
        Broadstreet_View::load('admin/layout');
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
        $show_offers      = (Broadstreet_Utility::getOption(self::KEY_SHOW_OFFERS) == 'true');

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
                return $content;
            }
        }

        return $content;
    }

    /**
     * The callback used to register the widget
     */
    public function registerWidget()
    {
        register_widget('Broadstreet_Zone_Widget');
        register_widget('Broadstreet_SBSZone_Widget');
        register_widget('Broadstreet_Multiple_Zone_Widget');
        register_widget('Broadstreet_Business_Listing_Widget');
        register_widget('Broadstreet_Business_Profile_Widget');
        register_widget('Broadstreet_Business_Categories_Widget');
        register_widget('Broadstreet_Editable_Widget');
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

            if($_POST['bs_gplus'] == 'enableoffer')
                Broadstreet_Utility::setOption (self::KEY_SHOW_OFFERS, 'true');

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
        if(isset($attrs['ad'])) {
            return Broadstreet_Utility::getAdCode($attrs['ad'], $attrs);
        }

        if(isset($attrs['zone'])) {
            return Broadstreet_Utility::getZoneCode($attrs['zone']);
        }

        return '';
    }

    public function businesses_shortcode($attrs)
    {
        $ordering  = 'alpha'; #$instance['w_ordering'];
        $category  = 'all'; #$instance['w_category'];

        $args = array (
            'post_type' => Broadstreet_Core::BIZ_POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => 10000, #($is_random == 'no' ? intval($count) : 100),
            'ignore_sticky_posts'=> 0
        );

        if($category != 'all')
        {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => Broadstreet_Core::BIZ_TAXONOMY,
                    'field' => 'id',
                    'terms' => $category
                )
            );
        }

        if($ordering == 'alpha')
        {
            $args['order'] = 'ASC';
            $args['orderby'] = 'title';
        }

        if($ordering == 'mrecent')
        {
            $args['order'] = 'DESC';
            $args['orderby'] = 'ID';
        }

        if($ordering == 'lrecent')
        {
            $args['order'] = 'ASC';
            $args['orderby'] = 'ID';
        }

        $posts = get_posts($args);

        $cats_to_posts = array();
        $post_ids      = array();
        $id_to_posts   = array();

        foreach($posts as $post)
        {
            $post_ids[] = $post->ID;
            $id_to_posts[$post->ID] = $post;
        }

        $terms = wp_get_object_terms($post_ids, Broadstreet_Core::BIZ_TAXONOMY, array('fields' => 'all_with_object_id', 'orderby' => 'name'));

        foreach($terms as $term)
        {
            if(!isset($cats_to_posts[$term->term_id]))
            {
                $cats_to_posts[$term->term_id] = array();
                $cats_to_posts[$term->term_id]['name'] = $term->name;
                $cats_to_posts[$term->term_id]['slug'] = $term->slug;
                $cats_to_posts[$term->term_id]['posts'] = array ();
            }

            $cats_to_posts[$term->term_id]['posts'][] =
                $id_to_posts[$term->object_id];
        }

        function broadstreet_compare($a, $b) {
            return strtolower($a->post_title) > strtolower($b->post_title);
        }

        foreach($cats_to_posts as $term_id => $data)
            usort($cats_to_posts[$term_id]['posts'], 'broadstreet_compare');

        return Broadstreet_View::load('listings/index', array('cats_to_posts' => $cats_to_posts), true);
    }
}

endif;
