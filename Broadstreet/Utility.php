<?php
/**
 * This file contains a class for utility methods and/or wrappers for built-in
 *  Wordpress API calls
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * The class contains a number of utility methods that may be needed by various
 *  parts of Broadstreet
 */
class Broadstreet_Utility
{
    const KEY_ZONE_CACHE = 'BROADSTREET_ZONE_CACHE';
    const KEY_RW_FLUSH   = 'BROADSTREET_RW_FLUSH';
    const KEY_NET_INFO   = 'BROADSTREET_NET_INFO';
    
    protected static $_zoneCache = NULL;
    protected static $_apiKeyValid = NULL;
    protected static $_businessEnabled = NULL;

    /**
     * Build an address from a meta array
     * @param type $meta The array of meta fields that come back for a business
     * @param type $single_line Whether the address should be on a single line
     * @param type $multi_html Whether multi-line addresses should be formatted
     *  in html
     * @return string The address
     */
    public static function buildAddressFromMeta($meta, $single_line = false, $multi_html = true)
    {
        $address = '';
        
        if($single_line)
        {
            if($meta['bs_address_1'])
                $address = "{$meta['bs_address_1']}"; 
            
            if($meta['bs_address_2'])
                $address .= ", {$meta['bs_address_2']}";
                
            $address .= ", {$meta['bs_city']}, {$meta['bs_state']}";
            
            if($meta['bs_postal'])
                $address .= ", {$meta['bs_postal']}";
        }
        else
        {
            if($meta['bs_address_1'])
                $address = "{$meta['bs_address_1']}"; 
            
            if($meta['bs_address_2'])
                $address .= "\n{$meta['bs_address_2']}";
                
            $address .= "\n{$meta['bs_city']}, {$meta['bs_state']}";
            
            if($meta['bs_postal'])
                $address .= " {$meta['bs_postal']}";
                
            if($multi_html)
                $address = nl2br($address);
        }
        
        return $address;
    }
    
    /**
     * Get the current user's Broadstreet API key
     * @return boolean 
     */
    public static function getApiKey()
    {
        $api_key = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_API_KEY);
        
        if(!$api_key) 
            return FALSE;
        else
            return $api_key;
    }
    
    /**
     * Get this publication's network ID
     * @return boolean 
     */
    public static function getNetworkId()
    {
        return Broadstreet_Utility::getOption(Broadstreet_Core::KEY_NETWORK_ID);
    }
    
    /**
     * Get info about the network this blog is registered as, and cache it
     * @return boolean 
     */
    public static function getNetwork()
    {
        //$info = self::getOption(self::KEY_NET_INFO);
        
        $info = Broadstreet_Cache::get('network_info');
        
        if($info) return $info;

        $broadstreet = new Broadstreet(self::getApiKey());
        $info = $broadstreet->getNetwork(self::getNetworkId());

        Broadstreet_Cache::set('network_info', $info, Broadstreet_Config::get('network_cache_ttl_seconds'));
        
        self::setOption(self::KEY_NET_INFO, $info);
        
        return $info;
    }

    /**
     * Check that the user's API key exists and is valid
     * @return boolean 
     */
    public static function checkApiKey($return_key = FALSE)
    {
        if(self::$_apiKeyValid !== NULL)
            return self::$_apiKeyValid;
        
        $api_key = self::getApiKey();
        
        if(!$api_key) 
        {
            self::$_apiKeyValid = FALSE;
            return FALSE;
        }
        else 
        {
            $api = new Broadstreet($api_key);
            
            try
            {
                $api->getNetworks();
                self::$_apiKeyValid = TRUE;
                
                if($return_key) 
                    return $api_key; 
                else 
                    return TRUE;
            }
            catch(Exception $ex)
            {
                self::$_apiKeyValid = TRUE;
                return FALSE;
            }
        }
    }
    
    /**
     * Check whether businesses are enabled. The API must be valid for this to
     *  be true
     * @return boolean 
     */
    public static function isBusinessEnabled()
    {
        if(self::$_businessEnabled === FALSE) return FALSE;
        if(self::$_apiKeyValid === FALSE) return FALSE;
        
        if(Broadstreet_Utility::getOption(Broadstreet_Core::KEY_BIZ_ENABLED))
        {
            self::$_businessEnabled = TRUE;
            return true;
        }
        else
        {
            self::$_businessEnabled = FALSE;
            return false;
        }
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
     * Sets a Wordpress meta value
     * @param string $name The name of the field to set
     * @param string $value The value of the field to set
     */
    public static function setPostMeta($post_id, $name, $value)
    {
        if (get_post_meta($post_id, $name, true) !== FALSE)
        {
            update_post_meta($post_id, $name, $value);
        }
        else
        {
            add_post_meta($post_id, $name, $value);
        }
    }

    /**
     * Gets a post meta value
     * @param string    $name The name of the field
     * @param mixed     $default The default value to return if one doesn't exist
     * @return string   The value if the field does exist
     */
    public static function getPostMeta($post_id, $name, $default = FALSE)
    {
        $value = get_post_meta($post_id, $name, true);
        if( $value !== FALSE ) return maybe_unserialize($value);
        return $default;
    }
    
    /**
     * Gets post meta values, cleaned up, singlefied (or not)
     * @param int       $post_id The id of the post
     * $param array     $defaults Assoc array of meta key names with value defaults
     * @param bool      $singles Whether to collapse value field to first value
     *  (default true)
     */
    public static function getAllPostMeta($post_id, $defaults = array(), $singles = true)
    {
        $meta = get_post_meta($post_id);
        
        foreach($defaults as $key => $value)
        {
            if(!isset($meta[$key])) {
                $meta[$key] = $value;
            }
        }
        
        if(!$singles) return $meta;
        
        $new_meta = array();
        
        # Meta fields come back nested in an array, fix that
        # unless the option is intended to be an array,
        # given the defaults
        foreach($meta as $key => $value) 
        {
            if(is_array(@$defaults[$key]) && count($value))
                $new_meta[$key] = maybe_unserialize($value[0]);
            else
                $new_meta[$key] = (is_array($value) && count($value)) ? $value[0] : $value;
        }
        
        return $new_meta;
    }
    
    /**
     * Figure out whether we're in a the_exceprt call stack
     * @return bool Whether we're in an excerpt 
     */
    public static function inExcerpt()
    {
        $stacktrace = debug_backtrace();
        
        foreach($stacktrace as $call)
            if($call['function'] == 'get_the_excerpt')
                return true;
            
        return false;
    }
    
    public static function toTime($time)
    {
        return date("g:i a", strtotime($time));
    }
    
    /**
     * Import data about a business based on a seed URL. Makes a call to the
     *  broadstreet backend
     * @param string $url The seed URL to start from
     * @param int $attach_post_id
     * @return type 
     */
    public static function importBusiness($url, $attach_post_id = 0)
    {
        $api_key    = self::getApiKey();
        $network_id = self::getNetworkId();
        $broadstreet= new Broadstreet($api_key);
        
        $import   = $broadstreet->magicImport($url, $network_id);
        
        $meta         = (array)$import->detail;
        $meta['charged'] = (bool)$import->cost;
        $meta['cost']    = number_format($import->cost / 100, 2);
        
        $defaults = Broadstreet_Core::$_businessDefaults;
        
        //print_r($meta); exit;
        
        foreach($defaults as $key => $value)
        {
            if(!isset($meta[$key]) || is_null($meta[$key]))
                $meta[$key] = $value;
        }
        
        # Improt the images locally
        $count  = 0;
        $images = array();
        
        foreach($meta['images'] as $image)
        {
            $img = Broadstreet_Utility::importImage($image, $attach_post_id, $meta['name'] . ' ' . ($count + 1));
            if($img) $images[] = $img;
            $count++;
        }
        
        $meta['images'] = $images;
                
        return $meta;
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
    public static function getBroadstreetBaseURL()
    {   
        return (get_bloginfo('url') . '/wp-content/plugins/broadstreet/Broadstreet/');
    }

    /**
     * Get the base URL for plugin images
     * @return string
     */
    public static function getImageBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/img/';
    }
    
    /**
     * Get the base url for plugin CSS
     * @return string
     */
    public static function getCSSBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/css/';
    }

    /**
     * Get the base URL for plugin javascript
     * @return string
     */
    public static function getJSBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/js/';
    }
    
    /**
     * Get the base URL for plugin javascript
     * @return string
     */
    public static function getVendorBaseURL()
    {
        return self::getBroadstreetBaseURL() . 'Public/vendor/';
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
     * Get the broadstreet zone cache
     * @return array
     */
    public static function getZoneCache()
    {
        if(self::$_zoneCache !== NULL) return self::$_zoneCache;
        
        $zones = Broadstreet_Cache::get(self::KEY_ZONE_CACHE, FALSE, FALSE);
        
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
        $api_key     = self::getOption(Broadstreet_Core::KEY_API_KEY);
        $network_id  = self::getOption(Broadstreet_Core::KEY_NETWORK_ID);
        
        $api = new Broadstreet($api_key);

        try
        {
            $zones  = $api->getNetworkZones($network_id);

            if(is_array($zones))
                Broadstreet_Cache::set(self::KEY_ZONE_CACHE, $zones, Broadstreet_Config::get('zone_cache_ttl_seconds'));
            else
                Broadstreet_Cache::get(self::KEY_ZONE_CACHE, FALSE, TRUE);
        }
        catch(Exception $ex)
        {
            $zones = array();
        }

        $kzones = array();
        foreach($zones as $zone)
            $kzones[$zone->id] = $zone;
        
        return $kzones;
    }

    /**
     * Set PHP to call Broadstreet's custom handlers for Exceptions and Erros.
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
        Broadstreet_Log::add('error', "Error [$errno]: '$errstr' in $errfile:$errline");
    }

    public static function handleException(Exception $ex)
    {
        Broadstreet_Log::add('error', "Exception: ".$ex->__toString());
    }

    /**
     * Makes a call to the Broadstreet service to collect information information
     *  on the blog in case of errors and other needs.
     */
    public static function sendReport($message = 'General')
    {
        
        $report = "$message\n";
        $report .= get_bloginfo('name'). "\n";
        $report .= get_bloginfo('url'). "\n";
        $report .= get_bloginfo('admin_email'). "\n";
        $report .= 'WP Version: ' . get_bloginfo('version'). "\n";
        $report .= 'Plugin Version: ' . BROADSTREET_VERSION . "\n";
        $report .= "$message\n";

        @wp_mail('plugin-help@broadstreetads.com', "Report: $message", $report);
    }

    /**
     * If this is a new installation and we've never sent a report to the
     * Broadstreet server, send a packet of basic info about this blog in case
     * issues should arise in the future.
     */
    public static function sendInstallReportIfNew()
    {
        $install_key = Broadstreet_Core::KEY_INSTALL_REPORT;
        $upgrade_key = Broadstreet_Core::KEY_INSTALL_REPORT .'_'. BROADSTREET_VERSION;
        
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
                self::sendReport("Upgrade");
                self::setOption($upgrade_key, 'true');
            }
        }
    }
    
    /**
     * Download an image from a URL an import it into the Media gallery
     * @param type $url The URL of the photo to fetch
     * @return string The new locally-hosted URL
     */
    public static function importImage($url, $post_id = 0, $desc = 'Business photo')
    {
        $tmp = download_url($url);

        # Set variables for storage
        # fix file filename for query strings
        preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
        
        if(!count($matches)) return false;
        
        $file_array['name'] = basename($matches[0]);
        $file_array['tmp_name'] = $tmp;

        # If error storing temporarily, unlink
        if (is_wp_error($tmp)) 
        {
            @unlink($file_array['tmp_name']);
            $file_array['tmp_name'] = '';
        }

        # Do the validation and storage stuff
        $id = media_handle_sideload($file_array, $post_id, $desc);

        # If error storing permanently, unlink
        if (is_wp_error($id)) 
        {
            @unlink($file_array['tmp_name']);
            return false;
        }

        return wp_get_attachment_url( $id );
    }

    /**
     * Get any reports / warnings / messages from the Broadstreet server.
     * @return mized A string if a message was found, FALSE if not
     */
    public static function getBroadstreetMessage()
    {
        return false;
        //self::setOption(Broadstreet_Core::KEY_LAST_MESSAGE_DATE, time() - 60*60*13);
        $date = self::getOption(Broadstreet_Core::KEY_LAST_MESSAGE_DATE);

        if($date !== FALSE && ($date + 12*60*60) > time())
            return self::getOption(Broadstreet_Core::KEY_LAST_MESSAGE);

        $driver = Broadstreet_Config::get('driver');
        $count  = Broadstreet_Model::getPublishedPostCount();

        $url     = "http://broadstreetads.com/messages?d=$driver&c=$count";
        $content = file_get_contents($url);

        self::setOption(Broadstreet_Core::KEY_LAST_MESSAGE, $content);
        self::setOption(Broadstreet_Core::KEY_LAST_MESSAGE_DATE, time());

        if(strlen($content) == 0 || $content == "0")
            return FALSE;

        return $content;
    }

    /**
     * Return a unique identifier for the site for use with future help requests
     * @return string A unique identifier
     */
    public static function getServiceTag()
    {
        return md5($report['u'] = get_bloginfo('url'));
    }
}