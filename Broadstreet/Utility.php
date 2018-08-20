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

    protected static $_placementSettingsCache = NULL;

    /**
     * Get ad code for a specific ad
     * @param type $id
     */
    public static function getAdCode($id, $attrs = array()) {

        $instance_id = md5(uniqid());
        $config = false;

        $code = "<div instance-id=\"$instance_id\" street-address=\"$id\"></div><script async data-cfasync=\"false\" type=\"text/javascript\" src=\"//ad.broadstreetads.com/display/$id.js?sa=1\"></script>";

        if (@$attrs['config']) {
            $config = $attrs['config'];
        }

        if ($config) {
            $code .= "
            <script>
                // custom configuration
                (function () {
                    var selector = 'div[instance-id=\"$instance_id\"] iframe';
                    var to = setInterval(function () {
                        var el = document.querySelector(selector);
                        if (el) {
                            clearInterval(to);
                            el.contentWindow.postMessage({type: 'bsa.ad.configure', config: $config}, '*')
                        }
                    }, 100);
                })()
            </script>
            ";
        }

        return $code;
    }

    /**
     * Get code for a specific zone
     * @param type $id
     * @return type
     */
    public static function getZoneCode($id, $attrs = array()) {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();

        $old = false;
        if (property_exists($placement_settings, 'use_old_tags') && $placement_settings->use_old_tags) {
            $old = true;
        }


        if ($old) {
            $keywords = Broadstreet_Utility::getAllAdKeywordsString();
            return '<script data-cfasync="false" type="text/javascript">broadstreet.zone(' . $id . ', {responsive: true, softKeywords: true, keywords: [' . $keywords . ']});</script>';
        } else {
            $keywords = Broadstreet_Utility::getAllAdKeywordsString(true);
            if (!isset($attrs['zone-id']) && !isset($attrs['alt-zone-id'])) {
                $attrs['zone-id'] = $id;
            }

            $attrs['keywords'] = $keywords;
            $attrs['soft-keywords'] = 'true';

            $attr_string = join(' ', array_map(function($key) use ($attrs)
                {
                    if(is_bool($attrs[$key]))
                    {
                        return $attrs[$key]?$key:'';
                    }
                    return $key.'="'.$attrs[$key].'"';
                }, array_keys($attrs)));

            return "<broadstreet-zone $attr_string></broadstreet-zone>";
        }
    }

    /**
     * Get code for a specific zone, wrapped
     * @param type $id
     * @return type
     */
    public static function getWrappedZoneCode($config, $id) {

        $disabled = false;
        if(property_exists($config, 'avoid_categories') && count($config->avoid_categories)) {
            for($i = 0; $i < count($config->avoid_categories); $i++) {
                if(self::pageHasCategory($config->avoid_categories[$i]->id)) {
                    $disabled = true;
                }
            }
        }

        if (property_exists($config, 'avoid_urls') && count($config->avoid_urls)) {
            global $wp;
            $urls = preg_split ("/\r\n|\n|\r/", $config->avoid_urls);
            $url = home_url($wp->request);

            for ($i = 0; $i < count($urls); $i++) {
                $pattern = trim(str_replace('.', '.*', $urls[i]));
                if (!$pattern) continue;
                $pattern = "#$pattern#";

                if (@preg_match($pattern, $url)) {
                    $disabled = true;
                }
            }
        }

        if($disabled) return '';

        return '<div style="margin:5px auto; margin-bottom: 15px;">'
                .(property_exists($config, 'show_label') && trim($config->show_label)
                    ? "<div class='broadstreet-story-ad-text' style='font-size:11px; color:#ccc; margin-bottom: 5px;'>{$config->show_label}</div>"
                    : '')
                .self::getZoneCode($id).'</div>';
    }

    public static function getMaxWidthWrap($config, $content) {
        if(!property_exists($config, 'max_width') || !$config->max_width)
            $config->max_width = '100%';

        return "<div class='bs-max-width-wrap' style='max-width:{$config->max_width}; margin: 0 auto;'>".$content.'</div>';
    }

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
     * Get or set the featured business image
     * @param type $image_path
     * @return string
     */
    public static function featuredBusinessImage($image_path = null) {
        $default = Broadstreet_Utility::getImageBaseURL() . 'featured-biz.png';

        if($image_path !== null) {
            self::setOption('featured_business_image', $image_path);
            return $image_path;
        }

        $img = self::getOption('featured_business_image');

        if($img) return $img;

        return $default;
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
    public static function getNetwork($force_refresh = false)
    {
        $info = false;

        if(!$force_refresh)
            $info = Broadstreet_Cache::get('network_info');

        if($info) return $info;

        try
        {
            $broadstreet = new Broadstreet(self::getApiKey());
            $info = $broadstreet->getNetwork(self::getNetworkId());

            Broadstreet_Cache::set('network_info', $info, Broadstreet_Config::get('network_cache_ttl_seconds'));

            if($info)
                self::setOption(self::KEY_NET_INFO, $info);
        }
        catch(Exception $ex)
        {
            return false;
        }

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
            add_option($name, $value);
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


    public static function getPlacementSettings()
    {
        if (self::$_placementSettingsCache === NULL) {
            self::$_placementSettingsCache = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_PLACEMENTS, (object)array());
        }

        return self::$_placementSettingsCache;
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
     * Fix a malformed URL
     * @param string $url
     * @return string
     */
    public static function fixURL($url)
    {
        if(!strstr($url, 'http://'))
            $url = "http://$url";

        return $url;
    }

    /**
     * Resize a video embed snippet's dimensions to a given width and height
     *  Height is optional
     * @param string $url
     * @return string
     */
    public static function setVideoWidth($snippet, $new_width, $new_height = false, $keep_proportional = true)
    {
        if(preg_match('#width=[\\\'"](\d+)[\\\'"]#', $snippet, $matches))
        {
            $old_width = $matches[1];

            if(!$new_height && preg_match('#height=[\\\'"](\d+)[\\\'"]#', $snippet, $matches))
            {
                $height = $matches[1];
            }
            else
            {
                $height = $new_height;
            }

            if($keep_proportional)
            {
                $ratio   = $new_width / $old_width;
                $height  = round($height*$ratio);
                $width   = $new_width;
            }
            else
            {
                $width   = $new_width;
            }

            $width  = "width=\"$width\"";
            $height = "height=\"$height\"";

            $snippet = preg_replace('#width=[\\\'"]\d+[\\\'"]#', $width, $snippet);
            $snippet = preg_replace('#height=[\\\'"]\d+[\\\'"]#', $height, $snippet);
        }

        return $snippet;
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
     * Get a link to the Broadstreet interface
     * @param string $path
     * @return string
     */
    public static function broadstreetLink($path)
    {
        $path = ltrim($path, '/');
        $key = self::getOption(Broadstreet_Core::KEY_API_KEY);
        $url = "https://my.broadstreetads.com/$path?access_token=$key";
        return $url;
    }

    /**
     * Check the meta and see if we should show the times on the listing
     *  page.
     * @param type $meta
     */
    public static function shouldShowTimes($meta)
    {
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        $types= array('open', 'close');

        foreach($days as $day)
            foreach($types as $type)
                if($meta["bs_{$day}_{$type}"])
                    return true;

        return false;
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
        return (WP_PLUGIN_URL . '/broadstreet/Broadstreet/');
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

        uasort($zones, function($a, $b) {
            return strcasecmp($a->name, $b->name);
        });

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
                $zones = Broadstreet_Cache::get(self::KEY_ZONE_CACHE, FALSE, TRUE);
        }
        catch(Exception $ex)
        {
            $zones = Broadstreet_Cache::get(self::KEY_ZONE_CACHE, FALSE, TRUE);

            if(!is_array($zones))
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

        @wp_mail('plugin@broadstreetads.com', "Report: $message", $report);
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
                self::flushRewrites(true);
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

    public static function pageHasCategory($id) {
        global $post;

        if(is_single() || is_page()) {
            return has_category($id, $post->ID);
        }

        if(is_category() || is_archive()) {
            $cat = get_query_var('cat');
            $cat = get_category ($cat);
            return ($cat->cat_ID == $id);
        }

        return false;
    }

    /**
     * Get category slugs for use in keyword-dropping
     * @return type
     */
    public static function getAllAdSlugs() {
        global $post;

        $slugs = array();

        if (is_single() || is_page()) {

            $id = get_the_ID();
            $cats = wp_get_post_categories($id);

            if(!$cats) $cats = array();

            foreach($cats as $cat) {
                $c = get_category($cat);

                if (property_exists($c, 'slug')) {
                    $slugs[] = $c->slug;

                    # If there's a parent, go up one level and get that slug too
                    if ($c->category_parent > 0) {
                        $c = get_category($c->category_parent);
                        $slugs[] = $c->slug;
                    }
                }
            }

            $slugs[] = $post->post_name;
        }

        if (is_category() || is_archive()) {
            $cat = get_query_var('cat');
            $cat = get_category ($cat);

            if (property_exists($cat, 'slug')) {
                $slugs[] = $cat->slug;

                if ($cat->category_parent > 0) {
                    $cat = get_category($cat->category_parent);
                    $slugs[] = $cat->slug;
                }
            }
        }

        if(is_home()) {
            // No categories
            $slugs = array();
        }

        return $slugs;
    }

    public static function getTargets() {
        $targets = array();

        # page type
        if (is_single() && !is_page()) {
            $targets['pagetype'] = array('post');
        } elseif (is_single() && is_page()) {
            $targets['pagetype'] = array('page');
        } elseif (is_archive()) {
            $targets['pagetype'] = array('archive');
        } else {
            $targets['pagetype'] = array();
        }

        if (is_home()) {
            $targets['pagetype'][] = 'is_home_page';
        } else {
            $targets['pagetype'][] = 'not_home_page';
        }

        # categories
        $slugs = self::getAllAdSlugs();
        $categories = array ();
        foreach($slugs as $slug) {
            $categories[] = $slug;
        }

        $targets['category'] = $categories;

        # url
        $targets['url'] = basename(get_permalink());

        return $targets;
    }

    /**
     * Get all ad keyword slugs
     * @return string
     */
    public static function getAllAdKeywordsString($omit_quotes = false) {
         $keywords = array();

         /* Figure out which keywords are available */
         if(is_single() || is_page()) {
             $keywords = array('not_home_page', 'not_landing_page', 'is_article_page');
         }

         if(is_archive() || is_category()) {
             $keywords = array('not_home_page', 'not_landing_page', 'not_article_page');
         }

         if(is_home()) {
             $keywords = array('is_home_page', 'is_landing_page', 'not_article_page');
         }

        $slugs = self::getAllAdSlugs();

        foreach($slugs as $slug) {
            $keywords[] = $slug;
        }

        if ($omit_quotes) {
            $keywords_string = implode(",", $keywords);
        } else {
            $keywords_string = "'" . implode("','", $keywords) . "'";
        }

        return $keywords_string;
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