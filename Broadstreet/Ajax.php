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
        
        $api = new Broadstreet($_POST['api_key']);

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
        }
        
        die(json_encode(array('success' => true, 'key_valid' => $key_valid, 'networks' => $networks)));
    }
}