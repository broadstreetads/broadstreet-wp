<?php
/**
 * This file contains a class for utility methods and/or wrappers for built-in
 *  Wordpress API calls
 */

/**
 * The class contains a number of utility methods that may be needed by various
 *  parts of Bizyhood
 */
class Bizyhood_Utility
{
    const KEY_ZONE_CACHE = 'BIZYHOOD_ZONE_CACHE';
    const KEY_RW_FLUSH   = 'BIZYHOOD_RW_FLUSH';
    const KEY_NET_INFO   = 'BIZYHOOD_NET_INFO';
    
    protected static $_zoneCache = NULL;

    /**
     * Build an address from a meta array
     * @param type $meta The array of meta fields that come back for a business
     * @param type $single_line Whether the address should be on a single line
     * @param type $multi_html Whether multi-line addresses should be formatted
     *  in html
     * @return string The address
     */
    public static function buildAddressFromMeta($business, $single_line = false, $multi_html = true)
    {
        $address = '';
        
        if($single_line)
        {
            if($business->address1)
                $address = "{$business->address1}"; 
            
            if($business->address2)
                $address .= ", {$business->address2}";
                
            $address .= ", {$business->locality}, {$business->region} {$business->postal_code}";
        }
        else
        {
            if($business->address1)
                $address = "{$business->address1}"; 
            
            if($business->address2)
                $address .= "\n{$business->address2}";
                
            $address .= "\n{$business->locality}, {$business->region} {$business->postal_code}";
                
            if($multi_html)
                $address = nl2br($address);
        }
        
        return $address;
    }
    
    
    /**
     * Checks if this is a bizyhood page
     * @return boolean
     */
    public function is_bizyhood_page() {
      
      if (
        is_page(self::getOption(Bizyhood_Core::KEY_SIGNUP_PAGE_ID)) || 
        is_page(self::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID)) || 
        is_page(self::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID)) || 
        is_page(self::getOption(Bizyhood_Core::KEY_PROMOTIONS_PAGE_ID)) || 
        is_page(self::getOption(Bizyhood_Core::KEY_EVENTS_PAGE_ID))
      ) {
        return true;
      }
        
      return false;
      
    }
    
    
    /**
     * Build date text from start and end date
     * @param string $start The starting date
     * @param string $end The ending date
     * @param string $single The text string that has to do with the date
     * @param string $plural The text string in plural form that has to do with the date
     * @return html template with the date
     */
    public static function buildDateText($start, $end, $single, $plural)
    {
      
      $dates = '';
      
      // check date
      $start_date = date('Y-m-d', strtotime($start)); // start date
      $tomorrow_date = date('Y-m-d', strtotime('+ 1 days')); // tomorrow date
      $end_date = date('Y-m-d', strtotime($end)); // end date
      
      $data = array(
        'single' => strtotime($single),
        'plural' => strtotime($plural),
        'str_start_date' => strtotime($start_date),
        'str_tomorrow_date' => strtotime($tomorrow_date),
        'str_end_date' => ($end !== NULL ? strtotime($end_date) : false),
        'wp_start_date' => date_i18n( get_option( 'date_format' ), strtotime($start_date)),
        'wp_tomorrow_date' => date_i18n( get_option( 'date_format' ), strtotime($tomorrow_date)),
        'wp_end_date' => ($end !== NULL ? date_i18n( get_option( 'date_format' ), strtotime($end_date)) : false)
      );
        
      return Bizyhood_View::load( 'snippets/dateText', $data, true );
    }
    
    /**
     * Build date text from start and end date with microdata
     * @param string $start The starting date
     * @param string $end The ending date
     * @param string $single The text string that has to do with the date
     * @param string $plural The text string in plural form that has to do with the date
     * @return html template with the date with microdata
     */
    public static function buildDateTextMicrodata($start, $end, $single, $plural)
    {

      $dates = '';
      
      // check date
      $start_date = date('Y-m-d', strtotime($start)); // start date
      $tomorrow_date = date('Y-m-d', strtotime('+ 1 days')); // tomorrow date
      $end_date = date('Y-m-d', strtotime($end)); // end date
      
      $data = array(
        'single' => strtotime($single),
        'plural' => strtotime($plural),
        'str_start_date' => strtotime($start_date),
        'str_tomorrow_date' => strtotime($tomorrow_date),
        'str_end_date' => ($end !== NULL ? strtotime($end_date) : false),
        'wp_start_date' => date_i18n( get_option( 'date_format' ), strtotime($start_date)),
        'wp_tomorrow_date' => date_i18n( get_option( 'date_format' ), strtotime($tomorrow_date)),
        'wp_end_date' => ($end !== NULL ? date_i18n( get_option( 'date_format' ), strtotime($end_date)) : false),
        'c_start_date' => date('c',strtotime($start_date)),
        'c_tomorrow_date' => date('c',strtotime($tomorrow_date)),
        'c_end_date' => ($end !== NULL ? date('c',strtotime($end_date)): false)
      );
      
      return Bizyhood_View::load( 'snippets/dateTextMicrodata', $data, true );
    }
    
    /**
     * @return http protocol
     */
    function get_protocol() {
      
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      
      return $protocol;
    }
    
    
    /**
     * Get the default logo for widgets and listings
     * @param string $filename The filename of the logo
     * @return array Includes default logo URL, width and height
     */
    public static function getDefaultLogo($filename='placeholder-logo.jpg')
    {
      $logo = array();
      
      // set the default
      $logo['image']['url'] = Bizyhood_Utility::getImageBaseURL().$filename;
      $logo['image_width']  = Bizyhood_Core::BUSINESS_LOGO_WIDTH;
      $logo['image_height'] = Bizyhood_Core::BUSINESS_LOGO_HEIGHT;
      
      return $logo;
    }
    
    
    /**
     * Get the Bizyhood API URL
     * @return string 
     */
    public static function getApiUrl()
    {
        $api_production = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_API_PRODUCTION, TRUE);
        
        if ($api_production == false) {
          return "https://sapi.bizyhood.com";
        }

        return "https://api.bizyhood.com";

    }
    
    /**
     * Get the Bizyhood API Production mode
     * @return boolean 
     */
    public static function getApiProduction()
    {
        return Bizyhood_Utility::getOption(Bizyhood_Core::KEY_API_PRODUCTION, TRUE);
        
    }
    
    /**
     * Get the Bizyhood API ID
     * @return string 
     */
    public static function getApiID()
    {
        return Bizyhood_Utility::getOption(Bizyhood_Core::KEY_API_ID);
    }
    
    /**
     * Get the Bizyhood API ID
     * @return string 
     */
    public static function getApiSecret()
    {
        return Bizyhood_Utility::getOption(Bizyhood_Core::KEY_API_SECRET);
    }

    /**
     * Get the default settings for the WP remote API
     * @return array 
     */
    public static function getRemoteSettings()
    {
        $settings = array('timeout' => 60);
        return $settings;
    }
    
    /**
     * Sets a Wordpress option
     * @param string $name The name of the option to set
     * @param string $value The value of the option to set
     */
    public static function setOption($name, $value)
    {
        if (get_option($name) !== FALSE)
        {
            update_option($name, $value);
        }
        else
        {
            $deprecated = ' ';
            $autoload   = 'no';
            add_option($name, $value, $deprecated, $autoload);
        }
    }

    /**
     * Gets a Wordpress option
     * @param string    $name The name of the option
     * @param mixed     $default The default value to return if one doesn't exist
     * @return string   The value if the option does exist
     */
    public static function getOption($name, $default = FALSE)
    {
        $value = get_option($name);
        if( $value !== FALSE ) return $value;
        return $default;
    }

    /**
     * If rewrite rules haven't been flushed, flush them.
     * @param $clear Force a flush
     */
    public static function flushRewrites($force = FALSE)
    {
        if($force || !self::getOption(self::KEY_RW_FLUSH))
        {
            flush_rewrite_rules();
            self::setOption(self::KEY_RW_FLUSH, 'TRUE');
        }
    }
    
    /**
     * Get a value from an associative array. The specified key may or may
     *  not exist.
     * @param array $array Array to grab the value from
     * @param mixed $key The key to check the array
     * @param mixed $default A value to return if the key doesn't exist int he array (default is FALSE)
     * @return mixed The value if the key exists, and the default if it doesn't
     */
    public static function arrayGet($array, $key, $default = FALSE)
    {
        if(array_key_exists($key, $array))
            return $array[$key];
        else
            return $default;
    }
    
    
    /**
     * Get a weighted random key from an (API) array result
     * @param array $MultiArray Array to grab the value from
     * @param string $dateField the date key of the MultiArray to sort
     * @return mixed (string or int) array key
     */
    public static function getRandomWeightedElementByDate(array $MultiArray, $dateField) 
    {
      
      $weights = array();
      foreach ($MultiArray as $index => $element) {
        $weights[strtotime($element[$dateField])] = $index;
      }
      
      // sort by date
      ksort($weights);
      
      $percentages = array();
      
      $count = count($weights); // Count the Number of Results
      $max = $count*($count+1)/2; // Sum the Integers from 1 to N
      foreach ($weights as $weight) {
        $percentages[$weight] = floor($count * 100 / $max);
        $count--;
      }
      
      
      return self::getRandomWeightedElement($percentages);
    }
    
    
    /**
   * getRandomWeightedElement()
   * Utility function for getting random values with weighting.
   * Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
   * An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
   * The return value is the array key, A, B, or C in this case.  Note that the values assigned
   * do not have to be percentages.  The values are simply relative to each other.  If one value
   * weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
   * chance of being selected.  Also note that weights should be integers.
   * 
   * @param array $weightedValues
   */
    public static function getRandomWeightedElement(array $weightedValues) 
    {
      $rand = mt_rand(1, (int) array_sum($weightedValues));

      foreach ($weightedValues as $key => $value) {
        $rand -= $value;
        if ($rand <= 0) {
          return $key;
        }
      }
    }
    
    
    /**
     * Get the site's base URL
     * @return string
     */
    public static function getSiteBaseURL()
    {
        return get_bloginfo('url');
    }

    /**
     * Get the base URL of the plugin installation
     * @return string the base URL
     */
    public static function getBizyhoodBaseURL()
    {   
        return (plugin_dir_url( __FILE__ ));
    }

    /**
     * Get the base URL for plugin images
     * @return string
     */
    public static function getImageBaseURL()
    {
        return self::getBizyhoodBaseURL() . 'Public/img/';
    }
    
    /**
     * Get the base url for plugin CSS
     * @return string
     */
    public static function getCSSBaseURL()
    {
        return self::getBizyhoodBaseURL() . 'Public/css/';
    }

    /**
     * Get the base URL for plugin javascript
     * @return string
     */
    public static function getJSBaseURL()
    {
        return self::getBizyhoodBaseURL() . 'Public/js/';
    }
    
    /**
     * Get the base URL for plugin javascript
     * @return string
     */
    public static function getVendorBaseURL()
    {
        return self::getBizyhoodBaseURL() . 'Public/vendor/';
    }

    /**
     * Close a connection with the client, but keep PHP execution alive.
     * @param string $data Any data to send to the client/browser.
     * @param int $time_limit
     */
    public static function killConnectionAndContinue($data = '', $time_limit = 0)
    {
        ignore_user_abort(true);
        set_time_limit($time_limit);

        header("Connection: close");
        header("Content-Length: " . strlen($data));
        echo $data;
        flush();
    }

    /**
     * Check to see if a process with a given PID is running
     * @param int $pid The PID of the process in question
     * @return bool True if the process is running, false if not
     */
    public static function isProcessRunning($pid)
    {
        $output = array();
        exec('ps -A -o pid', $output);
        $pid = intval($pid);

        foreach($output as $running_pid)
        {
            if($pid == intval(trim($running_pid)))
            {
                return TRUE;
            }
        }

        return FALSE;
    }
    
    /**
     * Get the bizyhood zone cache
     * @return array
     */
    public static function getZoneCache()
    {
        if(self::$_zoneCache !== NULL) return self::$_zoneCache;
        
        $zones = Bizyhood_Cache::get(self::KEY_ZONE_CACHE, FALSE, FALSE);
        
        if($zones === FALSE)
        {
            $zones = self::refreshZoneCache();
        }
        else
        {
            $kzones = array();
            foreach($zones as $zone)
                $kzones[$zone->id] = $zone;

            $zones = $kzones;
        }
        
        self::$_zoneCache = $zones;
        
        return self::$_zoneCache;
    }
    
    /**
     * Force a refresh of the zone cache
     * @return array 
     */
    public static function refreshZoneCache()
    {
        $api_key     = self::getOption(Bizyhood_Core::KEY_API_KEY);
        $network_id  = self::getOption(Bizyhood_Core::KEY_NETWORK_ID);
        
        $api = new Bizyhood($api_key);

        try
        {
            $zones  = $api->getNetworkZones($network_id);

            if(is_array($zones))
                Bizyhood_Cache::set(self::KEY_ZONE_CACHE, $zones, Bizyhood_Config::get('zone_cache_ttl_seconds'));
            else
                $zones = Bizyhood_Cache::get(self::KEY_ZONE_CACHE, FALSE, TRUE);
        }
        catch(Exception $ex)
        {
            $zones = Bizyhood_Cache::get(self::KEY_ZONE_CACHE, FALSE, TRUE);

            if(!is_array($zones))                
                $zones = array();
        }

        $kzones = array();
        foreach($zones as $zone)
            $kzones[$zone->id] = $zone;
        
        return $kzones;
    }

    /**
     * Set PHP to call Bizyhood's custom handlers for Exceptions and Erros.
     *  This is used mainly for when drivers will still be running in the
     *  background doing something like an index build
     */
    public static function registerLogErrorHandlers()
    {
        set_error_handler(array(__CLASS__, 'handleError'));
        set_exception_handler(array(__CLASS__, 'handleException'));
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        Bizyhood_Log::add('error', "Error [$errno]: '$errstr' in $errfile:$errline");
    }

    public static function handleException(Exception $ex)
    {
        Bizyhood_Log::add('error', "Exception: ".$ex->__toString());
    }

    /**
     * Makes a call to the Bizyhood service to collect information information
     *  on the blog in case of errors and other needs.
     */
    public static function sendReport($message = 'General')
    {
        
        $report = "$message\n";
        $report .= get_bloginfo('name'). "\n";
        $report .= get_bloginfo('url'). "\n";
        $report .= get_bloginfo('admin_email'). "\n";
        $report .= 'WP Version: ' . get_bloginfo('version'). "\n";
        $report .= 'Plugin Version: ' . BIZYHOOD_VERSION . "\n";
        $report .= "$message\n";

        @wp_mail('plugin@bizyhoodads.com', "Report: $message", $report);
    }

    /**
     * If this is a new installation and we've never sent a report to the
     * Bizyhood server, send a packet of basic info about this blog in case
     * issues should arise in the future.
     */
    public static function sendInstallReportIfNew()
    {
        $install_key = Bizyhood_Core::KEY_INSTALL_REPORT;
        $upgrade_key = Bizyhood_Core::KEY_INSTALL_REPORT .'_'. BIZYHOOD_VERSION;
        
        $installed = self::getOption($install_key);
        $upgraded  = self::getOption($upgrade_key);
 
        $sent = ($installed && $upgraded);
        
        if($sent === FALSE)
        {   
            if(!$installed)
            {
                self::sendReport("Installation");
                self::setOption($install_key, 'true');
                self::setOption($upgrade_key, 'true');
            }
            else
            {
                self::flushRewrites(true);
                self::sendReport("Upgrade");
                self::setOption($upgrade_key, 'true');
            }
        }
    }
    
}