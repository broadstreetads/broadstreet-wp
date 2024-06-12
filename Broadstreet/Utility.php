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
    const KEY_ZONE_CACHE        = 'BROADSTREET_ZONE_CACHE';
    const KEY_RW_FLUSH          = 'BROADSTREET_RW_FLUSH';
    const KEY_NET_INFO          = 'BROADSTREET_NET_INFO';
    const KEY_ADS_TXT_BACKUP    = 'BROADSTREET_ADS_TXT_BACKUP';

    protected static $_zoneCache = NULL;
    protected static $_apiKeyValid = NULL;
    protected static $_businessEnabled = NULL;

    protected static $_placementSettingsCache = NULL;

    /**
     * Get the initialization code (init.js)
     */
    public static function getInitCode() {
        if (Broadstreet_Utility::isAMPEndpoint()) return;

        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $network_id = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_NETWORK_ID);
        $args = '{}';

        if (Broadstreet_Utility::useLocalBSA()) {
            $args = '{"domain": "localhost:9090"}';
        }

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

        $code = "window.broadstreetKeywords = [" . Broadstreet_Utility::getAllAdKeywordsString() . "]\n";
        $code .= "window.broadstreetTargets = " . json_encode(Broadstreet_Utility::getTargets()) . ";\n";

        $code .= "\nwindow.broadstreet = window.broadstreet || { run: [] };window.broadstreet.run.push(function () {\n";
        if (property_exists($placement_settings, 'defer_configuration') && strlen($placement_settings->defer_configuration)) {
            if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->adserver_whitelabel) > 0) {
                $code.= "window.broadstreet.loadNetworkJS($network_id, { domain: '$placement_settings->adserver_whitelabel'});\n";
            } else {
                $code.= "window.broadstreet.loadNetworkJS($network_id, $args);\n";
            }
        } else {
            $code .= "window.broadstreet.watch($args);\n";
        }
        $code .= " });";

        return $code;
    }

    public static function getTrackerCode() {
        $code = '';
        $post_id = null;
        $ad_id = null;

        # check to see if this is a query for a post page and for the primary content
        if (is_singular() && is_main_query()) {
            $post_id = get_queried_object_id();
            $ad_id  = Broadstreet_Utility::getPostMeta($post_id, 'bs_sponsor_advertisement_id');
        } else if (in_the_loop()) { # or if we're in some loop somewhere
            $post_id = get_the_ID();
            $ad_id  = Broadstreet_Utility::getPostMeta($post_id, 'bs_sponsor_advertisement_id');
        }

        if ($post_id && $ad_id) {
            $is_sponsored = Broadstreet_Utility::getPostMeta($post_id, 'bs_sponsor_is_sponsored');

            if ($is_sponsored) {
                $code .= Broadstreet_Utility::getAdCode($ad_id);
            }
        }

        return $code;
    }

    /**
     * Get ad code for a specific ad
     * @param type $id
     */
    public static function getAdCode($id, $attrs = array()) {

        $instance_id = md5(uniqid());
        $config = false;

        //$code = "<div instance-id=\"$instance_id\" street-address=\"$id\"></div><script async data-cfasync=\"false\" type=\"text/javascript\" src=\"//localhost:9090/display/$id.js?sa=1\"></script>";
        $code = "<div instance-id=\"$instance_id\" street-address=\"$id\"></div><script async data-cfasync=\"false\" type=\"text/javascript\" src=\"". self::getAdserverURL() ."display/$id.js?sa=1\"></script>";

        if (isset($attrs['config'])) {
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

    public static function getStaticAdCode($id) {
        $base = self::getAdserverURL();
        $cb = time();
        return "<a href=\"{$base}click/{$id}\"><img src=\"{$base}display/{$id}?{$cb}\" style=\"max-width: 100%;\" /></a>";
    }


    public static function getStaticZoneCode($id, $index = 0) {
        $base = self::getAdserverURL();
        $cb = time();
        return "<a href=\"{$base}zone_static/{$id}/click/{$index}?ds=true&seed={$cb}\"><img src=\"{$base}zone_static/{$id}/image/{$index}?ds=true&seed={$cb}\" style=\"max-width: 100%;\" /></a>";
    }

    /**
     * Get code for a specific zone
     * @param type $id
     * @return type
     */
    public static function getZoneCode($id, $attrs = array()) {
        if (Broadstreet_Core::$_disableAds) {
            return '<!-- Broadstreet plugin: Ads disabled on this post -->';
        }

        if (self::isAMPEndpoint()) {
            return self::getAMPZoneCode($id, $attrs);
        }

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

            $cache = self::getZoneCache();

            $attrs['keywords'] = $keywords;
            $attrs['soft-keywords'] = 'true';

            // if we know the zone alias, add that
            if (isset($cache[$id])) {
                $attrs['zone-alias'] = $cache[$id]->alias;
            }

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
     * Get AMPHTML code for a specific zone
     * @param type $id
     * @return string
     */
    public static function getAMPZoneCode($id, $attrs = array()) {
        $network_id = (int) self::getNetworkId();
        $zone_id = (int) $id;
        $keywords = esc_attr(Broadstreet_Utility::getAllAdKeywordsString(true));

        // AMP ads require defined width and heights. Use standard 300x250 ad size as the default.
        $width = 300;
        $height = 250;

        $zones = self::getZoneCache();
        if (isset($zones[$zone_id])) {
            $zone = $zones[$zone_id];

            if (property_exists($zone, 'width') && !empty($zone->width)) {
                $parsed_width = intval($zone->width);
                if ($parsed_width) {
                    $width = $parsed_width;
                }
            }

            if (property_exists($zone, 'height') && !empty($zone->height)) {
                $parsed_height = intval($zone->height);
                if ($parsed_height) {
                    $height = $parsed_height;
                }
            }
        }

        $addl_attrs = '';
        if (isset($attrs['place'])) {
            $addl_attrs .= " data-place='{$attrs['place']}'";
        }

        // by default, the layout is responsive. But if we get a value, use that. If we don't, omit the layout.
        if (isset($attrs['layout'])) {
            if ($attrs['layout']) {
                $addl_attrs .= " layout='{$attrs['layout']}' ";
            }
        } else {
            $addl_attrs .= " layout='responsive' ";
        }

        $initurl = "https://cdn.broadstreetads.com/init-2.min.js?v=".BROADSTREET_VERSION;
        $addl_attrs .= " data-initurl='$initurl' ";

        return "<amp-ad type='broadstreetads' data-network='$network_id' data-zone='$zone_id' data-keywords='$keywords' $addl_attrs width='$width' height='$height'></amp-ad>";
    }

    /**
     * Get code for a specific zone, wrapped
     * @param type $id
     * @return type
     */
    public static function getWrappedZoneCode($config, $id, $attrs = array()) {

        $disabled = false;
        if(property_exists($config, 'avoid_categories') && count($config->avoid_categories)) {
            for($i = 0; $i < count($config->avoid_categories); $i++) {
                if(self::pageHasCategory($config->avoid_categories[$i]->id)) {
                    $disabled = true;
                }
            }
        }

        if (property_exists($config, 'avoid_urls')) {
            global $wp;

            if (!$config->avoid_urls) {
                $config->avoid_urls = '';
            }

            $urls = preg_split ("/\r\n|\n|\r/", $config->avoid_urls);
            $url = home_url($wp->request);

            if (count($urls)) {
                for ($i = 0; $i < count($urls); $i++) {
                    $pattern = trim(str_replace('.', '.*', $urls[$i]));
                    if (!$pattern) continue;
                    $pattern = "#$pattern#";

                    if (@preg_match($pattern, $url)) {
                        $disabled = true;
                    }
                }
            }
        }

        if($disabled) return '';

        $rand_fn = 'zone_load_' . rand();
        $js = "<script>window.$rand_fn = function(z, d) { if (!d.count) document.getElementById('$rand_fn').style.display = 'none'; };</script>";
        $attrs['callback'] = $rand_fn;
        
        return "<div style='margin:5px auto; margin-bottom: 15px;' id='$rand_fn'>"
                .(property_exists($config, 'show_label') && trim($config->show_label)
                    ? "<div class='broadstreet-story-ad-text' style='font-size:11px; color:#ccc; margin-bottom: 5px;'>{$config->show_label}</div>"
                    : '')
                .self::getZoneCode($id, $attrs).'</div>'.$js;
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

    public static function getBroadstreetDashboardURL()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $host = 'https://my.broadstreetads.com/';
        if (property_exists($placement_settings, 'use_local_bsa') && $placement_settings->use_local_bsa) {
            $host = 'http://localhost:3000/';
        }
        return $host;
    }

    public static function getAdserverURL()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $host = 'https://ad.broadstreetads.com/';
        if (property_exists($placement_settings, 'use_local_bsa') && $placement_settings->use_local_bsa) {
            $host = 'http://localhost:9090/';
        } else if (property_exists($placement_settings, 'cdn_whitelabel') && strlen($placement_settings->adserver_whitelabel)) {
            $host = "https://" . $placement_settings->adserver_whitelabel . "/";
        }
        return $host;
    }

    public static function useLocalBSA()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        return property_exists($placement_settings, 'use_local_bsa') && $placement_settings->use_local_bsa;
    }

    public static function getBroadstreetClient()
    {
        $placement_settings = Broadstreet_Utility::getPlacementSettings();
        $host = 'api.broadstreetads.com';
        $secure = true;

        if (property_exists($placement_settings, 'use_local_bsa') && $placement_settings->use_local_bsa) {
            $host = 'localhost:3000';
            $secure = false;
        }

        $key = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_API_KEY);
        return new Broadstreet($key, $host, $secure);
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
            $network_id = self::getNetworkId();

            if (!$network_id) {
                return false;
            }

            $broadstreet = self::getBroadstreetClient();
            $info = $broadstreet->getNetwork($network_id);

            Broadstreet_Cache::set('network_info', $info, Broadstreet_Config::get('network_cache_ttl_seconds'));

            if($info) {
                self::setOption(self::KEY_NET_INFO, $info);

                # if there's a demand partner on the account, make sure load_in_head is enabled for header bidding
                # and also defer_configuration so we can load the head bidding code
                if (property_exists($info, 'demand_partner') && property_exists($info->demand_partner, 'ads_txt')) {
                    $placement_settings = self::getPlacementSettings();
                    self::writeAdsTxt($info->demand_partner->ads_txt);
                    $placement_settings->load_in_head = true;
                    $placement_settings->defer_configuration = true;
                    Broadstreet_Utility::setOption(Broadstreet_Core::KEY_PLACEMENTS, $placement_settings);
                }
            }
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
            $api = self::getBroadstreetClient();

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
        if(!strstr($url, '://'))
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
        $broadstreet= self::getBroadstreetClient();

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
        # handle https
        $url = plugins_url( '/Broadstreet/', dirname(__FILE__) );
        return $url;
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
        $network_id  = self::getOption(Broadstreet_Core::KEY_NETWORK_ID);

        $api = self::getBroadstreetClient();

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

            $id = get_queried_object_id();
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
            $slugs[] = get_post_type();
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

        if(is_front_page()) {
            // No categories
            $slugs = array();
        }

        return $slugs;
    }

    public static function getAvailableTargets() {
        $categories = array();
        $cats = get_categories();
        foreach($cats as $cat) {
            $categories[] = array('name' => $cat->name, 'slug' => $cat->slug);
        }

        $post_types = array();
        $pts = get_post_types(array('show_ui' => true));
        foreach($pts as $name => $slug) {
            $post_types[] = array('name' => $name, 'slug' => $slug);
        }

        return array (
            'categories' => $categories,
            'post_types' => $post_types,
            'built_in_keywords' => array (
                'is_home_page', 'not_home_page', 'is_archive_page', 'is_article_page', 'not_article_page'
            )
        );
    }

    public static function getTargets() {
        $targets = array();

        # page type
        if (is_single() && !is_page()) {
            $targets['pagetype'] = array(get_post_type());
        } elseif (is_single() && is_page()) {
            $targets['pagetype'] = array(get_post_type());
        } elseif (is_archive()) {
            $targets['pagetype'] = array('archive');
        } else {
            $targets['pagetype'] = array();
        }

        if (is_front_page()) {
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

         if(is_front_page()) {
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
    public static function getServiceTag() {
        return md5($report['u'] = get_bloginfo('url'));
    }

    public static function isNewspack() {
        return function_exists('newspack_setup');
    }

    /**
     * Return whether an AMP page is requested.
     * @return bool True if AMP
     */
    public static function isAMPEndpoint() {
        $is_amp = function_exists('is_amp_endpoint') && is_amp_endpoint();

        if (self::isNewspack()) {
            $placement_settings = Broadstreet_Utility::getPlacementSettings();
            if (property_exists($placement_settings, 'newspack_ignore_amp') && $placement_settings->newspack_ignore_amp) {
                $is_amp = false;
            }
        }

        return $is_amp;
    }

    /**
     * Is Gutenberg active?
     */
    public static function isGutenberg() {
        // Gutenberg plugin is installed and activated.
        $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

        // Block editor since 5.0.
        $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

        if ( ! $gutenberg && ! $block_editor ) {
            return false;
        }

        if ( self::isClassicEditor() ) {
            $editor_option       = get_option( 'classic-editor-replace' );
            $block_editor_active = array( 'no-replace', 'block' );

            return in_array( $editor_option, $block_editor_active, true );
        }

        return true;
    }

    /**
     * Check if Classic Editor plugin is active.
     * @return bool
     */
    public static function isClassicEditor() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
            return true;
        }

        return false;
    }        

    public static function getAdsTxt() {
        $info = (object)['found' => false, 'type' => null, 'file' => null, 'content' => null, 'expected_location' => ''];

        # ads-txt by 10 gen installs https://plugins.trac.wordpress.org/browser/ads-txt/trunk/ads-txt.php
        # 10Gen's plugin: ADS_TXT_MANAGER_POST_OPTION = adstxt_post (currently, anyway)
        # make sure the plugin is actually active
        if (defined('ADS_TXT_MANAGER_POST_OPTION')) {
            $post_id = get_option(ADS_TXT_MANAGER_POST_OPTION);
            if (!empty($post_id)) {
                $post = get_post($post_id);
                if ($post instanceof WP_Post) {
                    $info->found = true;
                    $info->type = 'post';
                    $info->post = $post;
                    $info->content = $post->post_content;
                }
            }
        }
                
        # physical files will override 10gen's plugin, so look for those next
        # first traverse from current directory down - traditional installs
        $dirs = explode('/', dirname(__FILE__));
        while (count($dirs) > 0) {
          $dir = implode('/', $dirs);
          $file = $dir . '/ads.txt';
          if (file_exists($file)) {
            $info->found = true;
            $info->type = 'file';
            $info->file = $file;
            $info->content = file_get_contents($file);
          }
          if (is_dir($dir . '/wp-content')) {
            $info->expected_location = $dir;
          }
          array_pop($dirs);
        }

        # next try from the wordpress root - useful for symlinked installs
        $dirs = explode('/', ABSPATH);
        while (count($dirs) > 0) {
          $dir = implode('/', $dirs);
          $file = $dir . '/ads.txt';
          if (file_exists($file)) {
            $info->found = true;
            $info->type = 'file';
            $info->file = $file;
            $info->content = file_get_contents($file);
          }   
          if (is_dir($dir . '/wp-content')) {
            $info->expected_location = $dir;
          }                 
          array_pop($dirs);
        }

        return $info;
    }

    public static function writeAdsTxt($content) {
        $comment = "broadstreetadstxt";
        $comment_start = "# {$comment}start";
        $comment_end = "# {$comment}end";
        $timestamp = date('Y-m-d H:i:s');
        $content_to_insert = "$comment_start $timestamp\n$content\n$comment_end";

        $ads_txt = self::getAdsTxt();

        # if there's no ads.txt, make one and bail
        if (!$ads_txt->found) {
            file_put_contents("{$ads_txt->expected_location}/ads.txt", $content_to_insert);
            return;
        }

        # let's backup a previous ads_txt if we're going to write it... just in case
        $ads_txt_backup = get_option(self::KEY_ADS_TXT_BACKUP);
        if (!$ads_txt_backup) {
            update_option(self::KEY_ADS_TXT_BACKUP, $ads_txt->content);
        }        

        # if one exists, modify the content
        # first check if broadstreet adstxt is already in there, and modify it if needed
        if (preg_match("/$comment_start/", $ads_txt->content)) {
            $content = preg_replace("/$comment_start.*$comment_end/s", $content_to_insert, $ads_txt->content);            
        } else {
            # if broadstreet isn't in there, just add it
            $content = "{$ads_txt->content}\n\n" . $content_to_insert;
        }

        # and save it
        if ($ads_txt->type == 'file') {
            file_put_contents($ads_txt->file, $content);
        } else if ($ads_txt->type == 'post') {
            $ads_txt->post->post_content = $content;
            wp_update_post($ads_txt->post);
        }

    }

}