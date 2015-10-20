<?php
/**
 * This file acts as the 'Controller' of the application. It contains a class
 *  that will load the required hooks, and the callback functions that those
 *  hooks execute.
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
    CONST KEY_SIGNUP_PAGE_ID      = 'Bizyhood_Signup_page_ID';
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
        $business_list_page = get_page_by_path( "business-directory" );
        if ( !$business_list_page )
        {
            $business_list_page = array(
                'post_title'     => 'Business Directory',
                'post_type'      => 'page',
                'post_name'      => 'business-directory',
                'post_content'   => '[bh-businesses]',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            wp_insert_post( $business_list_page );
        }

        // Create the view business page
        $business_view_page = get_page_by_path( "business-overview" );
        if ( !$business_view_page )
        {
            $business_view_page = array(
                'post_title'     => 'Business Overview',
                'post_type'      => 'page',
                'post_name'      => 'business-overview',
                'post_content'   => '',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_author'    => 1,
                'menu_order'     => 0,
            );
            wp_insert_post( $business_view_page );
        }

    }

    public function uninstall()
    {
        Bizyhood_Log::add('debug', "Bizyhood uninstalling");

        // Remove business list page
        $business_list_page = get_page_by_path( "business-directory" );
        if ($business_list_page)
        {
            Bizyhood_Log::add('info', "Removing business list page (post ID " . $business_list_page->ID . ")");
            wp_delete_post($business_list_page->ID);
        }

        // Remove business list page
        $business_view_page = get_page_by_path( "business-overview" );
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
        add_action('admin_menu', 	array($this, 'adminCallback'));
        add_action('admin_init', 	array($this, 'adminInitCallback'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_styles'));
        add_action('wp_enqueue_scripts', 	array($this, 'load_plugin_gallery'));
        add_shortcode('bh-businesses', array($this, 'businesses_shortcode'));
        add_filter('the_content', array($this, 'postTemplate'), 100);
        add_action('wp_ajax_save_settings', array('Bizyhood_Ajax', 'saveSettings'));
        
        
        // create rewrite rule for single business
        add_filter('rewrite_rules_array', array($this, 'bizyhood_add_rewrite_rules'));
        // hook add_query_vars function into query_vars
        add_filter('query_vars', array($this, 'bizyhood_add_query_vars'));
        // check if a flush is needed
        add_action( 'wp_loaded', array($this, 'bizyhood_flush_rules') );
        
        // Yoast SEO additions START
        
        add_action( 'init', array( $this, 'sitemap_init' ), 10 );
        add_action('wpseo_do_sitemap_bizyhood-sitemap', array($this, 'bizyhood_create_sitemap') );
        add_filter( 'wpseo_sitemap_index', array($this, 'bizyhood_addtoindex_sitemap') );
        
        // Yoast SEO additions END      

        // AIOSP START
        
        add_filter( 'aiosp_sitemap_extra', array( $this, 'aiosp_sitemap_init' ), 10 );
        add_filter( 'aiosp_sitemap_custom_bizyhood', array( $this, 'bizy_add_aioseo_pages' ), 10, 3 );
        add_filter( 'aiosp_sitemap_addl_pages', array( $this, 'bizy_add_aioseo_pages' ), 10, 1 );
        
        // AIOSP END
        
        
        // editor bizybutton START
        
        add_action('admin_head', array( $this, 'bizy_add_bizylink_button'));
        
        add_action( 'wp_ajax_bizylink_insert_dialog', array( $this, 'bizylink_insert_dialog' ));
        add_action( 'wp_ajax_bizylink_business_results', array( $this, 'bizylink_business_results' ));

        // editor bizybutton END
        
        
    }
    
    
    function bizylink_business_results() {

      $_GET['keywords']  = $_REQUEST['keywords'];
    
      
      $queryapi = $this->businesses_information(array('paged' => 1));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( get_page_by_path( 'business-overview' ) );
      $date = date("Y-m-d H:i");
      $count  = $queryapi['total_count']; // get the number of results // 492
      
      $out = '
        <div class="query-notice" id="query-notice-message">
          <em class="query-notice-default" style="display: block;">Results for: <b>'. $_GET['keywords'].'</b> ('. count($queryapi['businesses']) .')</em>
        </div>';
      
      
      
      
      if (count($queryapi['businesses']) > 0) {
        $out .= '<ul class="bizyres">';
        $i = 0;
        foreach ($queryapi['businesses'] as $business) {
          
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          
          $out .= '<li class="'. ($i%2 == false ? 'alternate' : '') .'"><a href="'. $urlbase.$urlarr[0].'/'.$urlarr[1].'/' .'" title="'. $business->name .'">'. $business->name .' - '. $business->address1 .', '. $business->region.', '. $business->postal_code .'</li>';
          $i++;
        }
        $out .= '</ul>';
      } else {
        $out = 'No results';
      }
      
      
      
      
      die( $out );
    }
    
    function bizylink_insert_dialog() {
      
      $out ='
          <div id="mceu_54" class="mce-container mce-first mce-formitem" hidefocus="1" tabindex="-1" style="margin: 20px; width: 458px; height: auto; oveflow: auto;">
            <div id="mceu_54-body" class="mce-container-body mce-abs-layout" style="width: 458px; height: 30px;">
              <div id="mceu_54-absend" class="mce-abs-end"></div>
              <label id="bizylink_title-l" class="mce-widget mce-label mce-first mce-abs-layout-item" for="bizylink_title" aria-disabled="false" style="line-height: 19px; left: 0px; top: 6px; width: 147px; height: 19px;">Call to Action Text</label>
              <input  placeholder="your bizybox text" id="bizylink_title" class="mce-textbox mce-last mce-abs-layout-item" value="" hidefocus="1" aria-labelledby="bizylink_title-l" style="left: 147px; top: 0px; width: 301px; height: 28px;">
            </div>
          </div>
          <div id="mceu_55" class="mce-container mce-last mce-formitem" hidefocus="1" tabindex="-1" style="margin: 20px; width: 458px; height: auto; oveflow: auto;">
            <div id="mceu_55-body" class="mce-container-body mce-abs-layout" style="width: 458px; height: 30px;">
              <div id="mceu_55-absend" class="mce-abs-end"></div>
              <label id="bizylink_link-l" class="mce-widget mce-label mce-first mce-abs-layout-item" for="bizylink_link" aria-disabled="false" style="line-height: 19px; left: 0px; top: 6px; width: 147px; height: 19px;">Business Link</label>
              <input placeholder="business overview link" id="bizylink_link" class="mce-textbox mce-last mce-abs-layout-item form-initialized" value="" hidefocus="1" aria-labelledby="bizylink_link-l" style="left: 147px; top: 0px; width: 301px; height: 28px;">
            </div>
          </div>
          <div id="mceu_56" class="mce-container mce-last mce-formitem" hidefocus="1" tabindex="-1" style="margin: 20px; width: 458px; height: auto; oveflow: auto;">
            <div id="mceu_55-body" class="mce-container-body mce-abs-layout" style="width: 458px; height: 30px;">
              <div id="mceu_56-absend" class="mce-abs-end"></div>
              <label id="bizylink_search-l" class="mce-widget mce-label mce-first mce-abs-layout-item" for="bizylink_search" aria-disabled="false" style="line-height: 19px; left: 0px; top: 6px; width: 147px; height: 19px;">Search Business</label>
              <input placeholder="search for business" id="bizylink_search" class="mce-textbox mce-last mce-abs-layout-item form-initialized" value="" hidefocus="1" aria-labelledby="bizylink_search-l" style="left: 147px; top: 0px; width: 301px; height: 28px;">
            </div>
          </div>
          <div id="bizylink_results" style="margin: 0 20px; height: 133px; overflow: auto;" >No results</div>
          
          
          <style>
            #bizylink-insert-dialog .query-notice {
                padding: 0;
                border-bottom: 1px solid #dfdfdf;
                background-color: #f7fcfe;
                color: #000;
            }
            #bizylink-insert-dialog .query-notice .query-notice-default {
                display: block;
                padding: 6px;
                border-left: 4px solid #00a0d2;
            }
            #bizylink-insert-dialog li {
                clear: both;
                margin-bottom: 0;
                border-bottom: 1px solid #f1f1f1;
                color: #32373c;
                padding: 4px 6px 4px 10px;
                cursor: pointer;
                position: relative;
            }
            .alternate, .striped>tbody>:nth-child(odd) {
                background-color: #f9f9f9;
            }
            #bizylink_results a { display: block; width: 100%; }
          </style>
      ';
    
    
      die( $out );

    }
    
    
    function bizy_add_bizylink_button() {
      if ( get_user_option('rich_editing') == 'true' && current_user_can('edit_posts')) {
        add_filter('mce_buttons', array( $this, 'bizy_register_buttons' ), 10);
        add_filter('mce_external_plugins', array( $this, 'bizy_register_tinymce_javascript' ), 10);
      }
      
      return;
      
    }
    
    
    function bizy_register_buttons($buttons) {
      array_push($buttons, 'separator', 'bizylink');
      return $buttons;
    }
    
    function bizy_register_tinymce_javascript($plugin_array) {
      $plugin_array['bizylink'] = plugins_url('/Public/js/bizybutton-plugin.js',__FILE__);
      return $plugin_array;
    }

    
    
    function bizy_add_aioseo_pages( $pages ) {
      
      // initialize array
      if ( empty( $pages ) ) $pages = Array();
      
      $queryapi = $this->businesses_information(array('paged' => 1));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( get_page_by_path( 'business-overview' ) );
      $date = date("Y-m-d H:i");
      $count  = $queryapi['total_count']; // get the number of results // 492
      
      $start = 1;
      
      // get first 12 urls to save an API request
      if ($start == 1) {
        foreach($queryapi['businesses'] as $business) {
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          $pages[] = Array( "loc" => $urlbase.$urlarr[0].'/'.$urlarr[1].'/', "lastmod" => $date, "changefreq" => "weekly", "priority" => "0.6" );
        }
      }
      
      // get the rest of the urls if they exist
      $i = $start + 1; // start  to query the API from the second batch
      while($i <= $numofpages) {
        $queryapi = $this->businesses_information(array('paged' => $i));
        foreach($queryapi['businesses'] as $business) {
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          $pages[] = Array( "loc" => $urlbase.$urlarr[0].'/'.$urlarr[1].'/', "lastmod" => $date, "changefreq" => "weekly", "priority" => "0.6" );
        }
        $i++;
      }
      
      

      
      return $pages;
    }
    
    
    function aiosp_sitemap_init($extra) {
            
      $extra[] = 'bizyhood';
      
      return $extra;
    }
    
    
    function bizyhood_add_query_vars($aVars) 
    {
      $aVars[] = "bizyhood_id"; // represents the id of the business
      $aVars[] = "bizyhood_name"; // represents the name of the business
      return $aVars;
    }
     
    function bizyhood_add_rewrite_rules($wr_rules)
    {
      
      
      $bizy_rules = array('business-overview/([^/]+)/([^/]+)/?$' => 'index.php?pagename=business-overview&bizyhood_name=$matches[1]&bizyhood_id=$matches[2]');
      $wr_rules = $bizy_rules + $wr_rules;
      
      return $wr_rules;
    }

    // flush_rules() if our rules are not yet included
    function bizyhood_flush_rules(){
      
      $rules = get_option( 'rewrite_rules' );
      
      // check if the rule already exits and if not then flush the rewrite rules
      if ( ! isset( $wr_rules['business-overview/([^/]+)/([^/]+)/?$'] ) ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
      }
    }
    
    
    // create Yoast sitemap
    
    public function sitemap_init() {
      if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
        $GLOBALS['wpseo_sitemaps']->register_sitemap( 'bizyhood', array( $this, 'sitemap_build' ) );
      }
    }
    
    
    public function sitemap_build() {
      $GLOBALS['wpseo_sitemaps']->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . preg_replace( '/(^http[s]?:)/', '', esc_url( home_url( 'main-sitemap.xsl' ) ) ) . ' "?>' );
      $GLOBALS['wpseo_sitemaps']->set_sitemap( $this->bizyhood_create_sitemap() );
    }
    
    
    public function bizyhood_create_all_urls() {
      $queryapi = $this->businesses_information(array('paged' => 1));
      $numofpages = floor($queryapi['total_count'] / $queryapi['page_size']);
      $urlbase = get_permalink( get_page_by_path( 'business-overview' ) );
      $date = date("Y-m-d H:i");
      
      
      
      // split sitemaps pages START
      
      $count  = $queryapi['total_count']; // get the number of results // 492
      $yoastoptions = WPSEO_Options::get_all();
      $max_entries  = $yoastoptions['entries-per-page']; // get the limit of urls per sitemap page
      $sitemapnum = get_query_var( 'sitemap_n' ); // get the sitemap number / page
      
      $maxsitemapnum = (int) ceil( $count / $max_entries ); // max number of pages
      
      if ($sitemapnum > $maxsitemapnum) { return; } // do not return any results if the sitemap query_var is bigger than the results we have to display
      
      
      $start  = (($sitemapnum - 1) * $max_entries / 12 == 0 ? 1 : ceil(($sitemapnum - 1) * $max_entries / 12));
      $end    = ceil($sitemapnum * $max_entries / 12);
      
      // split sitemaps pages  END
      
      
      // initialize array
      $urls = array();
      
      // get first 12 urls
      $urlindex = 0; // help me index
      if ($start == 1) {
        foreach($queryapi['businesses'] as $business) {
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          $urls[$urlindex]['url'] = $urlbase.$urlarr[0].'/'.$urlarr[1].'/';
          $urls[$urlindex]['date'] = $date; // this needs to be changed to the last modified when added to the API // TODO
          $urlindex++;
        }
      }
      
      // get the rest of the urls if they exist
      $i = $start + 1; // start  to query the API from the second batch
      while($i <= $numofpages && $end >= $i) {
        $queryapi = $this->businesses_information(array('paged' => $i));
        foreach($queryapi['businesses'] as $business) {
          $urlarr = array_slice(explode('/', $business->bizyhood_url), -3);
          $urls[$urlindex]['url'] = $urlbase.$urlarr[0].'/'.$urlarr[1].'/';
          $urls[$urlindex]['date'] = $date; // this needs to be changed to the last modified when added to the API // TODO
          $urlindex++;
        }
        $i++;
      }
      
      return $urls;
    }
    
    
    public function bizyhood_create_sitemap() {
      
      $api_url = Bizyhood_Utility::getApiUrl();
      $urls = $this->bizyhood_create_all_urls();
      $WPSEO_Sitemaps = new WPSEO_Sitemaps();
      
      
      $sitemap  = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ';
      $sitemap .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" ';
      $sitemap .= 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
      
      foreach($urls as $u) {      
        $sitemap .= $WPSEO_Sitemaps->sitemap_url(
            array(
              'loc' => $u['url'],
              'pri' => 0.6,
              'chf' => 'monthly',
              'mod' => $u['date']
            )
        );
      }
        
			$sitemap .= '</urlset>';
      
      return $sitemap;
      
    }
    
    
    // add sitemap to index
    function bizyhood_addtoindex_sitemap() {
      
      $getfirstpage = $this->businesses_information(array('paged' => 1));
      $count  = $getfirstpage['total_count'];
      $yoastoptions = WPSEO_Options::get_all();
      $max_entries  = $yoastoptions['entries-per-page'];
      $sitemap = '';
      
      // if we need to split the sitemaps
      if ($count > $max_entries) {
        
        $n = (int) ceil( $count / $max_entries );
        for ( $i = 1; $i <= $n; $i ++ ) {
          
          $sitemap  .= '<sitemap>' . "\n";
          $sitemap .= '<loc>' . wpseo_xml_sitemaps_base_url( 'bizyhood-sitemap' . $i . '.xml' ) . '</loc>' . "\n";
          $sitemap .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
          $sitemap .= '</sitemap>' . "\n";
          
        }
        
      } else { // create just one
      
        $sitemap  = '<sitemap>' . "\n";
        $sitemap .= '<loc>' . wpseo_xml_sitemaps_base_url( 'bizyhood-sitemap.xml' ) . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
        $sitemap .= '</sitemap>' . "\n";
        
      }
      return $sitemap;
    }
    
    function load_plugin_styles()
    {
        wp_enqueue_style ('bizyhood-plugin-styles',  Bizyhood_Utility::getCSSBaseURL() . 'plugin.css', array(), BIZYHOOD_VERSION);
    }
    
    function load_plugin_gallery()
    {
        wp_enqueue_style ('photoswipe-css',  Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/css/photoswipe.css', array(), BIZYHOOD_VERSION);
        wp_enqueue_style ('photoswipe-css-default-skin',  Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/css/default-skin/default-skin.css', array('photoswipe-css'), BIZYHOOD_VERSION);
        wp_enqueue_script('photoswipe-js', Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/js/photoswipe.min.js', array(), BIZYHOOD_VERSION, true);
        wp_enqueue_script('photoswipe-ui-js', Bizyhood_Utility::getVendorBaseURL() . 'photoswipe/js/photoswipe-ui-default.js', array('photoswipe-js'), BIZYHOOD_VERSION, true);
        wp_enqueue_script('bizyhood-gallery-js', Bizyhood_Utility::getJSBaseURL() . 'bizyhood-plugin-gallery.js', array(), BIZYHOOD_VERSION, true);
    }
    
    /**
     * A callback executed whenever the user tried to access the Bizyhood admin page
     */
    public function adminCallback()
    {
        $icon_url = null;
                
        add_menu_page('Bizyhood', 'Bizyhood', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'), $icon_url);
        add_submenu_page('Bizyhood', 'Settings', 'Account Setup', 'edit_pages', 'Bizyhood', array($this, 'adminMenuCallback'));
    }

    /**
     * Emit a warning that the search index hasn't been built (if it hasn't)
     */
    public function adminWarningCallback()
    {
        if(in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php')))
        {
            $info = Bizyhood_Utility::getNetwork();
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
        $data['signup_page_id']     = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
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
    
    public function businesses_information($atts)
    {
      
      $a = shortcode_atts( array(
        'paged' => null,
      ), $atts );

      
      $remote_settings = Bizyhood_Utility::getRemoteSettings();
      $api_url = Bizyhood_Utility::getApiUrl();
      $zip_codes = Bizyhood_Utility::getZipsEncoded();
      $use_cuisine_types = Bizyhood_Utility::getOption(self::KEY_USE_CUISINE_TYPES);
      $list_page_id = Bizyhood_Utility::getOption(self::KEY_MAIN_PAGE_ID);

      if ($use_cuisine_types) {
          $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/cuisine/?pc=" . $zip_codes . "&rad=15000&format=json" ) );
          $response_json = json_decode($response);
          $cuisines = $response_json->cuisines;
      }
      else {
          $categories = Bizyhood_Utility::getOption(self::KEY_CATEGORIES);
      }

      // get current page
      if (isset($a['paged']))
          $page = $a['paged'];
      elseif (get_query_var('paged'))
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
              $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/restaurant/?format=json&pc=$zip_codes&ps=12&pn=$page&rad=15000&cu=$category", $remote_settings ) );
          }
          elseif(isset($_GET['keywords'])) {
              $keywords = urlencode($_GET['keywords']);
              $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/restaurant/?format=json&pc=$zip_codes&ps=12&pn=$page&rad=15000&k=$keywords", $remote_settings ) );
          }
          else {
              $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/restaurant/?format=json&pc=$zip_codes&ps=12&pn=$page&rad=15000", $remote_settings ) );
          }
      }
      else {
          if (isset($category)) {
              $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/?format=json&pc=$zip_codes&ps=12&pn=$page&rad=15000&k=$category", $remote_settings ) );
          }
          elseif(isset($_GET['keywords'])) {
              $keywords = urlencode($_GET['keywords']);
              $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/?format=json&pc=$zip_codes&ps=12&pn=$page&rad=15000&k=$keywords", $remote_settings ) );
          }
          else {
              $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/?format=json&pc=$zip_codes&ps=12&pn=$page&rad=15000", $remote_settings ) );
          }
      }
      $response_json = json_decode( $response );
      
      // avoid throwing an error
      if ($response_json === null) { return; }
      
      $businesses = $response_json->businesses;
      $total_count = $response_json->total_count;
      $page_size = $response_json->page_size;
      
      $return = array(
        'remote_settings'   => $remote_settings,
        'api_url'           => $api_url,
        'zip_codes'         => $zip_codes,
        'use_cuisine_types' => $use_cuisine_types,
        'list_page_id'      => $list_page_id,
        'keywords'          => (isset($keywords) ? $keywords : ''),
        'cuisines'          => (isset($cuisines) ? $cuisines : ''),
        'categories'        => (isset($categories) ? $categories : ''),
        'category'          => (isset($category) ? $category : ''),
        'page'              => $page,
        'businesses'        => $businesses,
        'total_count'       => $total_count,
        'page_size'         => $page_size,
        'response'          => $response,
        'response_json'     => $response_json
      );
      
      return $return;
    }
    
    
    
    public function businesses_shortcode($attrs)
    {
        
        $q = $this->businesses_information($attrs);
        
        $list_page_id = $q['list_page_id'];
        $page = $q['page'];
       
        $businesses = $q['businesses'];
        $cuisines = $q['cuisines'];
        $categories = $q['categories'];
        $total_count = $q['total_count'];
        $page_size = $q['page_size'];
        $page_count = 0;
        if ($page_size > 0) {
            $page_count = ( $total_count / $page_size ) + ( ( $total_count % $page_size == 0 ) ? 0 : 1 );
        }
        $pagination_args = array(
            'total'              => $page_count,
            'current'            => $page,
            'type'               => 'list',
        );
        $view_business_page_id = get_page_by_path( "business-overview" )->ID;
       
        return Bizyhood_View::load( 'listings/index', array( 'cuisines' => (isset($cuisines) ? $cuisines : ''), 'categories' => (isset($categories) ? $categories : ''), 'list_page_id' => $list_page_id, 'pagination_args' => $pagination_args, 'businesses' => $businesses, 'view_business_page_id' => $view_business_page_id ), true );
    }

    /**
     * Handler used for modifying the way business listings are displayed
     * @param string $content The post content
     * @return string Content
     */
    public function postTemplate($content)
    {   
        global $post, $wp_query;
        $api_url = Bizyhood_Utility::getApiUrl();

        # Override content for the view business page        
        $post_name = $post->post_name;
        if ($post_name === 'business-overview')
        {
            $signup_page_id = Bizyhood_Utility::getOption(self::KEY_SIGNUP_PAGE_ID);
            
            // get the bizyhood_id
            if(isset($wp_query->query_vars['bizyhood_id'])) {
              
              $bizyhood_id = urldecode($wp_query->query_vars['bizyhood_id']);
            } else {
              $bizyhood_id = (isset($_REQUEST['bizyhood_id']) ? $_REQUEST['bizyhood_id'] : '');
            }
            
            
            $response = wp_remote_retrieve_body( wp_remote_get( $api_url . "/business/" . $bizyhood_id ) );
            $business = json_decode($response);
            
            
            return Bizyhood_View::load('listings/single/default', array('content' => $content, 'business' => $business, 'signup_page_id' => $signup_page_id), true);
        }

        return $content;
    }
    
}

endif;
