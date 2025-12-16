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

    public static $_disableAds = false;
    public static $_rssCount = 0;
    public static $_rssIndex = 0;

    /**
     * Default values for sponsored meta fields
     */
    public static $_sponsoredDefaults = array (
        'bs_sponsor_advertiser_id' => '',
        'bs_sponsor_advertisement_id' => '',
        'bs_sponsor_is_sponsored' => ''
    );

    /**
     * Default values for sponsored meta fields
     */
    public static $_visibilityDefaults = array (
        'bs_ads_disabled' => ''
    );

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
        return Broadstreet_Utility::getBroadstreetClient();
    }

    /**
     * Register Wordpress hooks required for Broadstreet
     */
    private function _registerHooks()
    {
        Broadstreet_Log::add('debug', "Registering hooks..");

        # -- Below is core functionality --
        add_action('admin_menu', 	array($this, 'adminCallback'     ));
        add_action('admin_enqueue_scripts', array($this, 'adminStyles'));
        add_action('admin_init', 	array($this, 'adminInitCallback' ));        
        add_action('wp_enqueue_scripts',          array($this, 'addCDNScript' ));
        add_filter('script_loader_tag',          array($this, 'finalizeZoneTag' ));
        add_action('init',          array($this, 'businessIndexSidebar' ));
        add_action('admin_notices',     array($this, 'adminWarningCallback'));
        add_action('widgets_init', array($this, 'registerWidget'));
        add_shortcode('broadstreet', array($this, 'shortcode'));
        add_filter('image_size_names_choose', array($this, 'addImageSizes'));
        add_action('wp_footer', array($this, 'addPoweredBy'));
        # -- Ad injection
        add_action('wp_body_open', array($this, 'addAdsPageTop' ));
        add_filter('the_content', array($this, 'addAdsContent'), 20);
        add_filter('the_content_feed', array($this, 'addRSSMacros'), 20);
        add_action('loop_end', array($this, 'addAdsLoopEnd'), 20);
        #add_action('comment_form_before', array($this, 'addAdsBeforeComments'), 1);
        add_filter('comments_template', array($this, 'addAdsBeforeComments'), 20);

        if (Broadstreet_Utility::getOption(self::KEY_API_KEY)) {
			add_action('post_updated', array($this, 'saveSponsorPostMeta'), 20);
			add_action('transition_post_status', array($this, 'monitorForScheduledPostStatus'), 20, 10, 3);
        }

        add_action('post_updated', array($this, 'saveAdVisibilityMeta'), 20);

        // only fires on newspack
        add_action('get_template_part_template-parts/header/entry', array($this, 'addNewspackAfterTitleAd'));
        add_action('after_header', array($this, 'addNewspackHeaderAd'));
        add_action('before_footer', array($this, 'addNewspackFooterAd'));
        add_filter('rest_pre_echo_response', array($this, 'addNewspackNewsletterMeta'));

        // only fires on wpp
        // add_action('get_template_part_loop-templates/content-single-camp', array($this, 'getTrackerContent'));

        # -- Below are all business-related hooks
        if(Broadstreet_Utility::isBusinessEnabled())
        {
            add_action('init', array($this, 'createPostTypes'));
            add_action('wp_enqueue_scripts', array($this, 'addPostStyles'));
            add_action('pre_get_posts', array($this, 'modifyPostListing'));
            add_filter('the_content', array($this, 'postTemplate'), 20);
            add_filter('the_posts', array($this, 'businessQuery'));
            add_filter('comment_form_defaults', array($this, 'commentForm'));
            add_action('save_post', array($this, 'savePostMeta'));
            add_shortcode('businesses', array($this, 'businesses_shortcode'));
        }

        # - Below are partly business-related
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));

        # RSS Zones, 1.0 and 2.0
        add_action('rss2_item', array($this, 'addRSSZone'));
        add_action('rss_item', array($this, 'addRSSZone'));



        # -- Below is administration AJAX functionality
        add_action('wp_ajax_bs_save_settings', array('Broadstreet_Ajax', 'saveSettings'));
        add_action('wp_ajax_create_advertiser', array('Broadstreet_Ajax', 'createAdvertiser'));
        add_action('wp_ajax_import_facebook', array('Broadstreet_Ajax', 'importFacebook'));
        add_action('wp_ajax_register', array('Broadstreet_Ajax', 'register'));
        add_action('wp_ajax_save_zone_settings', array('Broadstreet_Ajax', 'saveZoneSettings'));
        add_action('wp_ajax_get_sponsored_meta', array('Broadstreet_Ajax', 'getSponsorPostMeta'));

        add_action('rest_api_init', function () {
            # /wp-json/broadstreet/v1/targets
            register_rest_route('broadstreet/v1', '/targets', array(
              'methods' => 'GET',
              'callback' => function($request) {
                return Broadstreet_Utility::getAvailableTargets();
              },
              'permission_callback' => '__return_true', # public
            ));

            # /wp-json/broadstreet/v1/refresh
            register_rest_route('broadstreet/v1', '/refresh', array(
                'methods' => 'GET',
                'callback' => function($request) {
                  $info = Broadstreet_Utility::getNetwork(true);
                  return [
                      'success' => $info ? true : false
                  ];
                },
                'permission_callback' => '__return_true', #public
              ));            
        });
    }


    public function getTrackerContent($content = '') {
        $code = Broadstreet_Utility::getTrackerCode();

        if (!strstr($content, 'template-parts/')) {
            return $content . $code;
        } else {
            echo $code;
            return;
        }
    }

    public function addRSSZone() {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $home_url = get_home_url();

        $in_rss_feed = property_exists($placement_settings, 'in_rss_feed') && $placement_settings->in_rss_feed;
        if ($in_rss_feed) {
            $rss_interval = intval($placement_settings->in_rss_feed_interval);
            if (!$rss_interval) {
                $rss_interval = 1;
            }

            $index = self::$_rssIndex;
            $time = time();

            if ((self::$_rssCount + 1) % $rss_interval == 0) {
                echo "<source url=\"$home_url\"><![CDATA[{$index}?ds=true&seed=$time]]></source>";
                self::$_rssIndex++;
            } else {
                $index = '-1';
                echo "<source url=\"$home_url\"><![CDATA[{$index}?ds=true&seed=$time]]></source>";
            }

            self::$_rssCount++;
        }
    }

    public function addNewspackNewsletterMeta($data) {
        if (is_array($data) && isset($data['mjml'])) {
            $cachebuster = time();
            $data['mjml'] = preg_replace('/BROADSTREET_RANDOM/i', $cachebuster, $data['mjml']);
        }
        return $data;
    }

    public function addNewspackAfterTitleAd() {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (property_exists($placement_settings, 'newspack_before_title') && $placement_settings->newspack_before_title) {
            echo Broadstreet_Utility::getZoneCode($placement_settings->newspack_before_title);
        }
    }    

    public function addNewspackHeaderAd($slug) {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (property_exists($placement_settings, 'newspack_after_header') && $placement_settings->newspack_after_header) {
            $padding = '25';
            if (property_exists($placement_settings, 'newspack_after_header_padding') && $placement_settings->newspack_after_header_padding) {
                $padding = $placement_settings->newspack_after_header_padding;
            }
            echo '<section class="newspack-broadstreet-header" style="text-align:center; padding: ' . $padding . 'px;">' . Broadstreet_Utility::getZoneCode($placement_settings->newspack_after_header) . '</section>';
        }
    }

    public function addNewspackFooterAd($slug) {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (property_exists($placement_settings, 'newspack_before_footer') && $placement_settings->newspack_before_footer) {
            $padding = '25';
            if (property_exists($placement_settings, 'newspack_before_footer_padding') && $placement_settings->newspack_before_footer_padding) {
                $padding = $placement_settings->newspack_before_footer_padding;
            }
            echo '<section class="newspack-broadstreet-footer" style="text-align:center; padding: ' . $padding . 'px;">' . Broadstreet_Utility::getZoneCode($placement_settings->newspack_before_footer) . '</section>';
        }
    }

    public function addAdsPageTop() {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (property_exists($placement_settings, 'above_page') && $placement_settings->above_page) {
            echo Broadstreet_Utility::getZoneCode($placement_settings->above_page);
        }

        if (property_exists($placement_settings, 'amp_sticky') && $placement_settings->amp_sticky) {
            echo "<amp-sticky-ad layout='nodisplay'>" . Broadstreet_Utility::getZoneCode($placement_settings->amp_sticky, array('layout' => false)) . "</amp-sticky-ad>";
        }
        
    }

    public function addRSSMacros($content) {
        $content = str_replace('%%timestamp%%', time(), $content);
        return $content;
    }

    public function addAdsContent($content) {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $above_content = property_exists($placement_settings, 'above_content') && $placement_settings->above_content;
        $below_content = property_exists($placement_settings, 'below_content') && $placement_settings->below_content;
        $in_content = property_exists($placement_settings, 'in_content') && $placement_settings->in_content;

        $content = str_replace('%%timestamp%%', time(), $content);

        if (is_single()) {

            if ($in_content) {

                $in_content_paragraph = property_exists($placement_settings, 'in_content_paragraph') ? $placement_settings->in_content_paragraph : '4';
                if (!$in_content_paragraph) {
                    $in_content_paragraph = '4';
                }

                try {
                    $in_content_paragraph = array_map('intval', array_map('trim', explode(',', $in_content_paragraph)));
                } catch (Exception $e) {
                    // user error
                    $in_content_paragraph = array(4);
                }

                /* Now handle in-content */
                if (!stristr($content, '[broadstreet zone') && !stristr($content, '<broadstreet-zone') && !stristr($content, 'broadstreetads')) { # last one is for <amp-ad>
                    /* Split the content into paragraphs, clear out anything that is only whitespace */
                    /* The first lookbehind makes sure we don't match empty paragraphs,
                        the last lookahead makes sure we don't match special blocks that wrap a paragraph */
                    $pieces = preg_split('#(?<!<p>)</p>(?!\s*</div>)#', $content);

                    if (count($pieces) <= 1 && ($above_content || $below_content)) {
                        # One paragraph
                        #return "$content\n\n" . $in_story_zone;
                    }

                    # each insertion increases the offset of the next paragraph
                    $replacements = 0;
                    for ($i = 0; $i < count($in_content_paragraph); $i++) {
                        $in_story_zone = Broadstreet_Utility::getWrappedZoneCode($placement_settings, apply_filters('bs_ads_in_content_zone_id', $placement_settings->in_content), array('place' => $i));
                        if ((count($pieces) - $replacements) > $in_content_paragraph[$i]) {
                            array_splice($pieces, $in_content_paragraph[$i] + $replacements++, 0, "</p>" . $in_story_zone);
                        }
                    }
                    /* It's magic, :snort: :snort:
                    - Mr. Bean: https://www.youtube.com/watch?v=x0yQg8kHVcI */
                    $content = implode("\n\n", $pieces);
                }

            }

            if ($above_content) {
                $content = Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->above_content) . $content;
            }

            if ($below_content) {
                $content = $content . Broadstreet_Utility::getWrappedZoneCode($placement_settings, $placement_settings->below_content);
            }
        }

        $content = $this->getTrackerContent($content);

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

        $screens = get_post_types();
        if (Broadstreet_Utility::getOption(self::KEY_API_KEY)) {
            foreach ( $screens as $screen ) {
                add_meta_box(
                    'broadstreet_sposnor_sectionid',
                    __( '<span class="dashicons dashicons-performance"></span> Sponsored Content', 'broadstreet_textdomain'),
                    array($this, 'broadstreetSponsoredBox'),
                    $screen,
                    'side',
                    'high'
                );
            }
        }

        foreach ( $screens as $screen ) {
            add_meta_box(
                'broadstreet_visibility_sectionid',
                __( '<span class="dashicons dashicons-format-image"></span> Broadstreet Options', 'broadstreet_textdomain'),
                array($this, 'broadstreetAdVisibilityBox'),
                $screen,
                'side'
            );
        }

        if(Broadstreet_Utility::isBusinessEnabled())
        {
            add_meta_box(
                'broadstreet_sectionid',
                __( 'Business Details', 'broadstreet_textdomain'),
                array($this, 'broadstreetBusinessBox'),
                self::BIZ_POST_TYPE,
                'normal'
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

    // Update CSS within in Admin
    function adminStyles() {
        wp_enqueue_style('broadstreet-admin-styles', Broadstreet_Utility::getCSSBaseURL() . 'admin.css');
    }

    /**
     * Add powered-by notice
     */
    public function addPoweredBy()
    {
        if (self::$_disableAds || Broadstreet_Utility::isAMPEndpoint()) {
            return;
        }

        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (!property_exists($placement_settings, 'load_in_head')
            || !$placement_settings->load_in_head) {
            $code = Broadstreet_Utility::getInitCode();
            echo "<script data-cfasync='false'>$code</script>";
        }
    }

    public function writeInitCode()
    {
        $code = '';

        # while we're in the post, capture the disabled status of the ads
        if (is_singular()) {
            self::$_disableAds = Broadstreet_Utility::getPostMeta(get_queried_object_id(), 'bs_ads_disabled') == '1';
        }

        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        if (property_exists($placement_settings, 'use_old_tags') && $placement_settings->use_old_tags) {
            if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->adserver_whitelabel) > 0) {
                $code .= "broadstreet.setWhitelabel('//{$placement_settings->adserver_whitelabel}/');";
            }
        }

        if (self::$_disableAds) {
            return;
        }

        if (property_exists($placement_settings, 'load_in_head')
            && $placement_settings->load_in_head) {
            $code .= Broadstreet_Utility::getInitCode();
        }

        wp_add_inline_script('broadstreet-init', "$code", 'after');
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
        if (is_admin()) {
            return $tag;
        }

        // add cloudflare attrs. but seriously, f cloudflare
        if (strstr($tag, 'broadstreet-init')) {
            $tag = str_replace('<script', "<script async data-cfasync='false'", $tag);
        }

        return $tag;
    }

    public function addCDNScript()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();

        $old = false;
        if (property_exists($placement_settings, 'use_old_tags') && $placement_settings->use_old_tags) {
            $old = true;
        }

        # Add Broadstreet ad zone CDN
        if(!is_admin() && !Broadstreet_Utility::isAMPEndpoint())
        {
            $file = 'init-2.min.js?v=' . BROADSTREET_VERSION;
            if ($old) {
                $file = 'init.js';
            }

            $placement_settings = Broadstreet_Utility::getPlacementSettings();
            $host = 'cdn.broadstreetads.com';
            if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->cdn_whitelabel) > 0) {
                $host = $placement_settings->cdn_whitelabel;
            }
            # except for cdn whitelabels
            # if (is_ssl() && property_exists($placement_settings, 'cdn_whitelabel') && $placement_settings->cdn_whitelabel) {
            #     $host = 'street-production.s3.amazonaws.com';
            # }

	        # For site-wide analytics.
	        $adserver_host = 'flux.broadstreet.ai';
	        if (property_exists($placement_settings, 'adserver_whitelabel') && strlen($placement_settings->adserver_whitelabel) > 0) {
		        $adserver_host = $placement_settings->adserver_whitelabel;
	        }
	        if (property_exists($placement_settings, 'enable_analytics') && strlen($placement_settings->enable_analytics)) {
		        $network_id = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_NETWORK_ID);
		        wp_register_script('broadstreet-analytics', "//$adserver_host/emit/$network_id.js", array(), '1.0.0', array('strategy'  => 'async'));
		        wp_enqueue_script('broadstreet-analytics');
	        }

            wp_register_script('broadstreet-init', "//$host/$file");
            $this->writeInitCode();
            wp_enqueue_script('broadstreet-init');
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
        $icon_url = 'none';

        add_menu_page('Broadstreet', 'Broadstreet', 'edit_pages', 'Broadstreet', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Broadstreet', 'Settings', 'Account Setup', 'edit_pages', 'Broadstreet', array($this, 'adminMenuCallback'));
        add_submenu_page('Broadstreet', 'Zone Options', 'Zone Options', 'edit_pages', 'Broadstreet-Zone-Options', array($this, 'adminZonesMenuCallback'));
        if(Broadstreet_Utility::isBusinessEnabled())
            add_submenu_page('Broadstreet', 'Business Settings', 'Business Settings', 'edit_pages', 'Broadstreet-Business', array($this, 'adminMenuBusinessCallback'));
        #add_submenu_page('Broadstreet', 'Advanced', 'Advanced', 'edit_pages', 'Broadstreet-Layout', array($this, 'adminMenuLayoutCallback'));
        if(Broadstreet_Utility::isBusinessEnabled())
            add_submenu_page('Broadstreet', 'Help', 'Business Directory Help', 'edit_pages', 'Broadstreet-Help', array($this, 'adminMenuHelpCallback'));
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

            // Check for video security warning
            $security_warning = get_transient('broadstreet_video_security_warning_' . get_current_user_id());
            if ($security_warning) {
	                echo '<div class="notice notice-error is-dismissible"><p><strong>Broadstreet Security Notice:</strong> Potentially malicious content was detected and removed from the video embed field. JavaScript protocols, event handlers, and script tags are not allowed for security reasons.</p></div>';
                delete_transient('broadstreet_video_security_warning_' . get_current_user_id());
            }
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
            wp_enqueue_script('angular-js', Broadstreet_Utility::getJSBaseURL().'angular.min.js?v='. BROADSTREET_VERSION);
            wp_enqueue_script('isteven-multi-js', Broadstreet_Utility::getJSBaseURL().'isteven-multi-select.js');
            wp_enqueue_style ('isteven-multi-css',  Broadstreet_Utility::getCSSBaseURL() . 'isteven-multi-select.css');

            // Pass nonce to JavaScript for AJAX security
            wp_localize_script('Broadstreet-main', 'broadstreetAjax', [
                'nonce' => wp_create_nonce('broadstreet_ajax_nonce')
            ]);
        }

        # Only register on the post editing page
        if($GLOBALS['pagenow'] == 'post.php' || $GLOBALS['pagenow'] == 'post-new.php')
        {
            if (Broadstreet_Utility::isBusinessEnabled()) {
                wp_enqueue_style ('Broadstreet-vendorcss-time', Broadstreet_Utility::getVendorBaseURL() . 'timepicker/css/timePicker.css');
                wp_enqueue_script('Broadstreet-main'  ,  Broadstreet_Utility::getJSBaseURL().'broadstreet.js?v='. BROADSTREET_VERSION);
                wp_enqueue_script('Broadstreet-vendorjs-time'  ,  Broadstreet_Utility::getVendorBaseURL().'timepicker/js/jquery.timePicker.min.js');

                // Pass nonce to JavaScript for AJAX security
                wp_localize_script('Broadstreet-main', 'broadstreetAjax', [
                    'nonce' => wp_create_nonce('broadstreet_ajax_nonce')
                ]);
            }
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
            $api = $this->getBroadstreetClient();

            try
            {
                $data['networks']  = $api->getNetworks();
                $data['key_valid'] = true;
                $data['network']   = Broadstreet_Utility::getNetwork(true);
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

        if(!$data['api_key'])
        {
            $data['errors'][] = '<strong>You dont have an API key set yet!</strong><ol><li>If you already have a Broadstreet account, <a href="http://my.broadstreetads.com/access-token">get your key here</a>.</li><li>If you don\'t have an account with us, <a target="blank" id="one-click-signup" href="#">then use our one-click signup</a>.</li></ol>';
        }
        else
        {
            $api = $this->getBroadstreetClient();

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
    }

    public function broadstreetAdVisibilityBox($post) {
        wp_nonce_field(plugin_basename(__FILE__), 'broadstreetadvisibility');

        $meta = Broadstreet_Utility::getAllPostMeta($post->ID, self::$_visibilityDefaults);

        Broadstreet_View::load('admin/visibilityBox', array(
            'meta'        => $meta
        ));
    }

    /**
     * Handler for the broadstreet meta box to designate a post as sponsored
     *  content
     * @param type $post
     */
    public function broadstreetSponsoredBox($post)
    {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'broadstreetsponsored');

        $meta = Broadstreet_Utility::getAllPostMeta($post->ID, self::$_sponsoredDefaults);

        $network_id       = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
        $advertiser_id    = Broadstreet_Utility::getPostMeta($post->ID, 'bs_sponsor_advertiser_id');
        $advertisement_id = Broadstreet_Utility::getPostMeta($post->ID, 'bs_sponsor_advertisement_id');

        $api = $this->getBroadstreetClient();

        try
        {
            $advertisers = $api->getAdvertisers($network_id) ?? array();

            usort($advertisers, function($a, $b) {
                return strcmp($a->name, $b->name);
            });
        }
        catch(Exception $ex)
        {
            $advertisers = array();
        }

        Broadstreet_View::load('admin/sponsoredBox', array(
            'meta'        => $meta,
            'advertisers' => $advertisers,
            'network_id' => $network_id
        ));
    }

    /**
     * Allow user to enable and disable ads on a given page
     */
    public function saveAdVisibilityMeta($post_id) {
        if(isset($_POST['bs_ads_disabled_submit'])) {
            # save settings
            foreach(self::$_visibilityDefaults as $key => $value)
            {
                if(isset($_POST[$key])) {
                    Broadstreet_Utility::setPostMeta($post_id, $key, is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]);
                }
            }

            if (!isset($_POST['bs_ads_disabled'])) {
                Broadstreet_Utility::setPostMeta($post_id, 'bs_ads_disabled', '');
            }
        }
    }

	/**
	 * Handler for saving sponsor-specific meta data for scheduled posts. Scheduled posts sometimes don't have the correct permalink
	 * so this checks everytime a post's status is updated to `published` and executes an update to reflect the proper permalink in Broadstreet.
	 * @param type $new_status The new status of the post
	 * @param type $old_status The previous status of the post
	 * @param type $post The post
	 */
	public function monitorForScheduledPostStatus($new_status, $old_status, $post) {
		if (($old_status != 'publish') && ($new_status == 'publish')) {
			$meta = Broadstreet_Utility::getAllPostMeta($post->ID, self::$_businessDefaults);
			
			// Check if the keys exist in the meta array before accessing them
			$ad_id = isset($meta['bs_sponsor_advertisement_id']) ? $meta['bs_sponsor_advertisement_id'] : null;
			$advertiser_id = isset($meta['bs_sponsor_advertiser_id']) ? $meta['bs_sponsor_advertiser_id'] : null;
			
			if ($ad_id && $advertiser_id) {
				$api = $this->getBroadstreetClient();
				$network_id = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
				// Check if this post is a Yoast Republish
				$original_post_id = get_post_meta($post->ID, '_dp_original', true);
				$post_link = get_permalink($post->ID);

                if ($original_post_id) {
                    $post_link = get_permalink($original_post_id);
                }

                $params = array (
					'stencil_inputs' => array('url' => $post_link),
				);

				try {
					$api->updateAdvertisement($network_id, $advertiser_id, $ad_id, $params);
				} catch (Broadstreet_ServerException $ex) {
					if ($ex->code == 404) {
						# ad was deleted on bsa side, reset the post
						Broadstreet_Utility::setPostMeta($post->ID, 'bs_sponsor_is_sponsored', '');
						Broadstreet_Utility::setPostMeta($post->ID, 'bs_sponsor_advertiser_id', '');
						Broadstreet_Utility::setPostMeta($post->ID, 'bs_sponsor_advertisement_id', '');
					}
				} catch (\Exception $ex) {
					// hopefully a temporary server error, do nothing
				}
			}
		}
	}

    /**
     * Handler for saving sponsor-specific meta data
     * @param type $post_id The id of the post
     * @param type $content The post content
     */
    public function saveSponsorPostMeta($post_id, $content = false)
    {
        if(isset($_POST['bs_sponsor_submit']))
        {
            # hold on to this in case it changes
            $old_advertiser_id = $_POST['bs_sponsor_old_advertiser_id'];

            # save settings
            foreach(self::$_sponsoredDefaults as $key => $value)
            {
                if(isset($_POST[$key])) {
                    Broadstreet_Utility::setPostMeta($post_id, $key, is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]);
                }
            }

            if (!isset($_POST['bs_sponsor_is_sponsored'])) {
                /**
                 * You can check-in any time you like, but you can never leave.
                 * Trying to see if this fixes a client bug where this setting "turns off"
                 * Maybe it's two editors conflicting?
                 */
                return;
                # Broadstreet_Utility::setPostMeta($post_id, 'bs_sponsor_is_sponsored', '');
            }

            # Has an ad been created/set?
            if (isset($_POST['bs_sponsor_is_sponsored'])) {

                if(isset($_POST['bs_sponsor_advertiser_id']) && $_POST['bs_sponsor_advertiser_id'] !== '')
                {
                    # Okay, one is being set, but does it already exist?
                    $ad_id = $_POST['bs_sponsor_advertisement_id'];
                    $api   = $this->getBroadstreetClient();

                    $network_id    = Broadstreet_Utility::getOption(self::KEY_NETWORK_ID);
                    $advertiser_id = $_POST['bs_sponsor_advertiser_id'];

                    $status = get_post_status($post_id);
                    $post_link = get_the_permalink($post_id);

                    // Since the permalink is not registered for unpublished posts, we simulate what it should be
                    if (in_array($status, array("draft", "future", "pending"))) {
                        $post_link = preg_replace('/\%postname\%/', get_sample_permalink($post_id)[1], get_sample_permalink($post_id)[0]);
                    }

                    // Check if this post is a Yoast Republish
                    $original_post_id = get_post_meta($post_id, '_dp_original', true);
                    if ($original_post_id) {
                        $post_link = get_permalink($original_post_id);
                    }

                    # create the advertiser if it doesn't exist yet
                    if ($advertiser_id == 'new_advertiser') {
                        $advertiser_name = (isset($_POST['bs_sponsor_advertiser_name']) && $_POST['bs_sponsor_advertiser_name'])
                                            ? str_pad(stripslashes($_POST['bs_sponsor_advertiser_name']), 3, '*')
                                            : 'Untitled Advertiser';

                        $advertiser = $api->createAdvertiser($network_id, $advertiser_name);
                        $advertiser_id = $advertiser->id;
                        # before it was set to "new_advertiser" so let's correct that
                        Broadstreet_Utility::setPostMeta($post_id, 'bs_sponsor_advertiser_id', $advertiser_id);
                    }

                    if(!$ad_id)
                    {
                        $name          = substr(str_pad(get_the_title($post_id), 5, '*'), 0, 127);
                        $type          = 'tracker';

						// analytics_tracker if network has preference for sponsored_content_v3
	                    $use_tracker_v3 = $api->getNetwork($network_id)->use_tracker_v3;
	                    if ($use_tracker_v3) {
							$type = 'analytics_tracker';
	                    }

                        try {
                            $ad = $api->createAdvertisement($network_id, $advertiser_id, $name, $type, array(
                                'stencil_inputs' => array('url' => $post_link),
                                'post_id' => $post_id,
                            ));

                            Broadstreet_Utility::setPostMeta($post_id, 'bs_sponsor_advertisement_id', $ad->id);
                            $ad_id = $ad->id;
                        } catch (Broadstreet_ServerException $ex) {

                        } catch (\Exception $ex) {
                            // hopefully a temporary server error, do nothing
                        }
                    } else {
                        $params = array (
                            'name' => substr(str_pad(get_the_title($post_id), 5, '*'), 0, 127),
                            'stencil_inputs' => array('url' => $post_link),
                            'type' => 'tracker'
                        );

	                    # The case where they are using the new version of the sponsored content tracker
	                    $use_tracker_v3 = $api->getNetwork($network_id)->use_tracker_v3;
	                    if ($use_tracker_v3) {
		                    $params['type'] = 'analytics_tracker';
	                    }

						# The case where the advertiser has switched on WP side
                        if ($old_advertiser_id && $advertiser_id != $old_advertiser_id) {
                            $params['new_advertiser_id'] = $advertiser_id;
                            $advertiser_id = $old_advertiser_id;
                        }

                        try {
                            $api->updateAdvertisement($network_id, $advertiser_id, $ad_id, $params);
                        } catch (Broadstreet_ServerException $ex) {
                            if ($ex->code == 404) {
                                # ad was deleted on bsa side, reset the post
                                Broadstreet_Utility::setPostMeta($post_id, 'bs_sponsor_is_sponsored', '');
                                Broadstreet_Utility::setPostMeta($post_id, 'bs_sponsor_advertiser_id', '');
                                Broadstreet_Utility::setPostMeta($post_id, 'bs_sponsor_advertisement_id', '');
                            }
                        } catch (\Exception $ex) {
                            // hopefully a temporary server error, do nothing
                        }
                    }
                }
            }
        }
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
                // Special handling for video content - only allow video and iframe tags to prevent XSS attacks
                if(isset($_POST[$key]) && $key == 'bs_video') {
                    $original_content = $_POST[$key];
                    $security_warning = false;

                    // Create a whitelist of allowed tags
                    $allowed_tags = array(
                        'video' => array(
                            'width' => true,
                            'height' => true,
                            'controls' => true,
                            'autoplay' => true,
                            'loop' => true,
                            'muted' => true,
                            'poster' => true,
                            'preload' => true,
                            'src' => true,
                            'class' => true,
                            'id' => true
                        ),
                        'source' => array(
                            'src' => true,
                            'type' => true
                        ),
                        'iframe' => array(
                            'src' => true,
                            'width' => true,
                            'height' => true,
                            'frameborder' => true,
                            'allowfullscreen' => true,
                            'allow' => true,
                            'title' => true,
                            'class' => true,
                            'id' => true
                        )
                    );

                    // Define allowed protocols for URLs (prevent javascript:, data:, etc.)
                    $allowed_protocols = array('http', 'https');

                    // Strip all tags except those in the whitelist, with protocol validation
                    $video_content = wp_kses($_POST[$key], $allowed_tags, $allowed_protocols);

                    // Check for and remove dangerous patterns
                    // 1. Detect javascript: protocol
                    if (preg_match('/javascript:/i', $original_content)) {
                        $security_warning = true;
                        $video_content = preg_replace('/(<[^>]*[\s\'"](src|href|poster|data)\s*=\s*[\'"]?)javascript:/i', '$1', $video_content);
                    }

                    // 2. Detect data: protocol
                    if (preg_match('/data:/i', $original_content)) {
                        $security_warning = true;
                        $video_content = preg_replace('/(<[^>]*[\s\'"](src|href|poster|data)\s*=\s*[\'"]?)data:/i', '$1', $video_content);
                    }

                    // 3. Detect and remove event handlers (on*)
                    if (preg_match('/<[^>]*\s+on\w+\s*=/i', $original_content)) {
                        $security_warning = true;
                        $video_content = preg_replace('/<([^>]*)\s+on\w+\s*=\s*[\'"][^\'"]*[\'"]([^>]*)>/i', '<$1$2>', $video_content);
                    }

                    // 4. Detect <script> tags
                    if (preg_match('/<script/i', $original_content)) {
                        $security_warning = true;
                    }

                    // 5. Clean up any broken/suspicious src attributes after sanitization
                    // This removes leftover garbage from removed javascript:/data: protocols
                    $video_content = preg_replace('/(<(?:iframe|video|source)[^>]*\s+src\s*=\s*[\'"])(?![\'"]?https?:\/\/)[^\'"]*([\'"])/i', '$1$2', $video_content);

                    // Save the sanitized video content
                    Broadstreet_Utility::setPostMeta($post_id, $key, $video_content);

                    // Store security warning flag as transient for admin notice
                    if ($security_warning) {
                        set_transient('broadstreet_video_security_warning_' . get_current_user_id(), true, 45);
                    }

                    // Skip the default handling for this field
                    continue;
                }

                if(isset($_POST[$key]))
                    Broadstreet_Utility::setPostMeta($post_id, $key, is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]);
                elseif($key == 'bs_images')
                    Broadstreet_Utility::setPostMeta($post_id, $key, self::$_businessDefaults[$key]);
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
        $is_mobile = wp_is_mobile();
        if (function_exists('jetpack_is_mobile')) {
            $is_mobile = jetpack_is_mobile();
        }

        if(isset($attrs['mobile'])) {
            if (($attrs['mobile'] == 'true' && !$is_mobile) || ($attrs['mobile'] == 'false' && $is_mobile)) {
                return '';
            }
        }

        if(isset($attrs['ad'])) {
            $ad_id = (int) $attrs['ad'];
            if(isset($attrs['static'])) {
                return Broadstreet_Utility::getStaticAdCode($ad_id);
            } else {
                return Broadstreet_Utility::getAdCode($ad_id, $attrs);
            }
        }

        if(isset($attrs['zone'])) {
            $zone_id = (int) $attrs['zone'];
            $addl_attrs = array();

            if (isset($attrs['place'])) {
                $addl_attrs['place'] = $attrs['place'];
            }

            if (isset($attrs['class'])) {
                $addl_attrs['class'] = $attrs['class'];
            }

            if(isset($attrs['static'])) {
                return Broadstreet_Utility::getStaticZoneCode($zone_id);
            } else {
                return Broadstreet_Utility::getZoneCode($zone_id, $addl_attrs);
            }
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

        if (!function_exists('broadstreet_compare')) {
            function broadstreet_compare($a, $b) {
                return strtolower($a->post_title) > strtolower($b->post_title);
            }
        }

        foreach($cats_to_posts as $term_id => $data)
            usort($cats_to_posts[$term_id]['posts'], 'broadstreet_compare');

        return Broadstreet_View::load('listings/index', array('cats_to_posts' => $cats_to_posts), true);
    }
}

endif;
