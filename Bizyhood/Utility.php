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
     * Check the Bizyhood oAuth Data
     * @return boolean 
     */
    public static function checkoAuthData()
    {
      
      if ( get_transient(Bizyhood_Core::KEY_OAUTH_DATA) !== false ) {
        return true;
      }
      
      return false;
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