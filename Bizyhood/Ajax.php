<?php
/**
 * This file contains a class which provides the AJAX callback functions required
 *  for Bizyhood.
 *
 * @author Bizyhood Ads <labs@bizyhoodads.com>
 */

/**
 * A class containing functions for the AJAX functionality in Bizyhood. These
 *  aren't executed directly by any Bizyhood code -- they are registered with
 *  the Wordpress hooks in Bizyhood_Core::_registerHooks(), and called as needed
 *  by the front-end and Wordpress. All of these methods output JSON.
 */
class Bizyhood_Ajax
{
    /**
     * Save a boolean value of whether to index comments on the next rebuild
     */
    public static function saveSettings()
    {
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_KEY, $_POST['api_key']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_URL, $_POST['api_url']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_NETWORK_ID, $_POST['network_id']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_BIZ_ENABLED, $_POST['business_enabled'] === 'true');
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_ZIP_CODES, $_POST['zip_codes']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_USE_CUISINE_TYPES, $_POST['use_cuisine_types'] === 'true');
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_CATEGORIES, $_POST['categories']);

        
        $api = new Bizyhood($_POST['api_key']);

        try
        {
            $networks  = $api->getNetworks();
            $key_valid = true;
            
            if($_POST['network_id'] == '-1')
            {
                Bizyhood_Utility::setOption(Bizyhood_Core::KEY_NETWORK_ID, $networks[0]->id);
            }
            
            //Bizyhood_Utility::refreshZoneCache();
        }
        catch(Exception $ex)
        {
            $networks = array();
            $key_valid = false;
            
            # Clear any options that aren't valid following the failed API key config
            Bizyhood_Utility::setOption(Bizyhood_Core::KEY_BIZ_ENABLED, FALSE);
        }
        
        die(json_encode(array('success' => true, 'key_valid' => $key_valid, 'networks' => $networks)));
    }
    
    public static function createAdvertiser()
    {
        $api_key    = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_API_KEY);
        $network_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_NETWORK_ID);
        
        $api        = new Bizyhood($api_key);
        $advertiser = $api->createAdvertiser($network_id, stripslashes($_POST['name']));
        
        die(json_encode(array('success' => true, 'advertiser' => $advertiser)));
    }
    
    public static function importFacebook()
    {
        try
        {
            $profile = Bizyhood_Utility::importBusiness($_POST['id'], $_POST['post_id']);
            die(json_encode(array('success' => (bool)$profile, 'profile' => $profile)));
        } 
        catch(Bizyhood_ServerException $ex)
        {
            die(json_encode(array('success' => false, 'message' => ($ex->error ? $ex->error->message : 'Server error. This issue has been reported to the folks at Bizyhood.'))));
        }
    }
    
    public static function register()
    {
        $api = new Bizyhood();
        
        try
        {
            # Register the user by email address
            $resp = $api->register($_POST['email']);
            Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_KEY, $resp->access_token);

            # Create a network for the new user
            $resp = $api->createNetwork(get_bloginfo('name'));
            Bizyhood_Utility::setOption(Bizyhood_Core::KEY_NETWORK_ID, $resp->id);

            die(json_encode(array('success' => true, 'network' => $resp)));
        }
        catch(Exception $ex)
        {
            die(json_encode(array('success' => false, 'error' => $ex->__toString())));
        }
    }
}