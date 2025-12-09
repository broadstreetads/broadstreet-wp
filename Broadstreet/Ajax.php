<?php
/**
 * This file contains a class which provides the AJAX callback functions required
 *  for Broadstreet.
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * A class containing functions for the AJAX functionality in Broadstreet. These
 *  aren't executed directly by any Broadstreet code -- they are registered with
 *  the Wordpress hooks in Broadstreet_Core::_registerHooks(), and called as needed
 *  by the front-end and Wordpress. All of these methods output JSON.
 */
class Broadstreet_Ajax
{
    /**
     * Save a boolean value of whether to index comments on the next rebuild
     */
    public static function saveSettings()
    {
        // Verify user has admin permissions (fixes Broken Access Control vulnerability)
        if (!current_user_can('manage_options')) {
            die(json_encode(array('success' => false, 'error' => 'Permission denied')));
        }

        // Sanitize the API key before storing it
        $api_key = sanitize_text_field($_POST['api_key']);
        Broadstreet_Utility::setOption(Broadstreet_Core::KEY_API_KEY, $api_key);
        
        // Sanitize network_id as an integer
        $network_id = isset($_POST['network_id']) ? intval($_POST['network_id']) : -1;
        Broadstreet_Utility::setOption(Broadstreet_Core::KEY_NETWORK_ID, $network_id);
        
        // Sanitize business_enabled as a boolean
        $business_enabled = ($_POST['business_enabled'] === 'true');
        Broadstreet_Utility::setOption(Broadstreet_Core::KEY_BIZ_ENABLED, $business_enabled);

        $api = Broadstreet_Utility::getBroadstreetClient();
        $message = 'OK';

        try
        {
            $networks  = $api->getNetworks();
            $key_valid = true;

            if($network_id == -1)
            {
                Broadstreet_Utility::setOption(Broadstreet_Core::KEY_NETWORK_ID, $networks[0]->id);
            }

            //Broadstreet_Utility::refreshZoneCache();
        }
        catch(Exception $ex)
        {
            $networks = array();
            $key_valid = false;
            $message = $ex->__toString();

            # Clear any options that aren't valid following the failed API key config
            Broadstreet_Utility::setOption(Broadstreet_Core::KEY_BIZ_ENABLED, FALSE);
        }

        die(json_encode(array('success' => true, 'key_valid' => $key_valid, 'networks' => $networks, 'message' => $message)));
    }

    /**
     *
     */
    public static function saveZoneSettings()
    {
        // Verify user has admin permissions (fixes Broken Access Control vulnerability)
        if (!current_user_can('manage_options')) {
            die(json_encode(array('success' => false, 'error' => 'Permission denied')));
        }

        $settings = json_decode(file_get_contents("php://input"));

        if($settings)
        {
            Broadstreet_Utility::setOption(Broadstreet_Core::KEY_PLACEMENTS, $settings);
            $success = true;
        }
        else
        {
            $success = false;
        }

        die(json_encode(array('success' => true)));
    }

    public static function createAdvertiser()
    {
        // Verify user has admin permissions (fixes Broken Access Control vulnerability)
        if (!current_user_can('manage_options')) {
            die(json_encode(array('success' => false, 'error' => 'Permission denied')));
        }

        $api_key    = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_API_KEY);
        $network_id = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_NETWORK_ID);

        $api        = Broadstreet_Utility::getBroadstreetClient();
        // Sanitize advertiser name to prevent XSS
        $name       = sanitize_text_field(stripslashes($_POST['name']));
        $advertiser = $api->createAdvertiser($network_id, $name);

        die(json_encode(array('success' => true, 'advertiser' => $advertiser)));
    }

    public static function getSponsorPostMeta() {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

        // Verify user has permission to edit this post (fixes IDOR vulnerability)
        if (!current_user_can('edit_post', $post_id)) {
            die(json_encode(array('success' => false, 'error' => 'Permission denied')));
        }

        die(json_encode(array('success' => true, 'meta' => Broadstreet_Utility::getAllPostMeta($post_id))));
    }

    public static function importFacebook()
    {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Verify user has permission to edit this post (fixes Broken Access Control vulnerability)
        if (!current_user_can('edit_post', $post_id)) {
            die(json_encode(array('success' => false, 'error' => 'Permission denied')));
        }

        try
        {
            $profile = Broadstreet_Utility::importBusiness(sanitize_text_field($_POST['id']), $post_id);
            die(json_encode(array('success' => (bool)$profile, 'profile' => $profile)));
        }
        catch(Broadstreet_ServerException $ex)
        {
            die(json_encode(array('success' => false, 'message' => ($ex->error ? $ex->error->message : 'Server error. This issue has been reported to the folks at Broadstreet.'))));
        }
    }

    public static function register()
    {
        // Verify user has admin permissions (fixes Broken Access Control vulnerability)
        if (!current_user_can('manage_options')) {
            die(json_encode(array('success' => false, 'error' => 'Permission denied')));
        }

        $api = Broadstreet_Utility::getBroadstreetClient(true);

        try
        {
            # Register the user by email address
            $resp = $api->register(sanitize_email($_POST['email']));
            Broadstreet_Utility::setOption(Broadstreet_Core::KEY_API_KEY, $resp->access_token);

            # Create a network for the new user
            $resp = $api->createNetwork(get_bloginfo('name'));
            Broadstreet_Utility::setOption(Broadstreet_Core::KEY_NETWORK_ID, $resp->id);

            die(json_encode(array('success' => true, 'network' => $resp)));
        }
        catch(Exception $ex)
        {
            die(json_encode(array('success' => false, 'error' => $ex->__toString())));
        }
    }
}