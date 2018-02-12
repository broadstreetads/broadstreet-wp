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
        Broadstreet_Utility::setOption(Broadstreet_Core::KEY_API_KEY, $_POST['api_key']);
        Broadstreet_Utility::setOption(Broadstreet_Core::KEY_NETWORK_ID, $_POST['network_id']);
        Broadstreet_Utility::setOption(Broadstreet_Core::KEY_BIZ_ENABLED, $_POST['business_enabled'] === 'true');
        
        $api = new Broadstreet($_POST['api_key']);
        $message = 'OK';

        try
        {
            $networks  = $api->getNetworks();
            $key_valid = true;
            
            if($_POST['network_id'] == '-1')
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
        $api_key    = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_API_KEY);
        $network_id = Broadstreet_Utility::getOption(Broadstreet_Core::KEY_NETWORK_ID);
        
        $api        = new Broadstreet($api_key);
        $advertiser = $api->createAdvertiser($network_id, stripslashes($_POST['name']));
        
        die(json_encode(array('success' => true, 'advertiser' => $advertiser)));
    }
    
    public static function importFacebook()
    {
        try
        {
            $profile = Broadstreet_Utility::importBusiness($_POST['id'], $_POST['post_id']);
            die(json_encode(array('success' => (bool)$profile, 'profile' => $profile)));
        } 
        catch(Broadstreet_ServerException $ex)
        {
            die(json_encode(array('success' => false, 'message' => ($ex->error ? $ex->error->message : 'Server error. This issue has been reported to the folks at Broadstreet.'))));
        }
    }
    
    public static function register()
    {
        $api = new Broadstreet();
        
        try
        {
            # Register the user by email address
            $resp = $api->register($_POST['email']);
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