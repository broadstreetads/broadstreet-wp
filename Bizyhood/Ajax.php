<?php
/**
 * This file contains a class which provides the AJAX callback functions required
 *  for Bizyhood.
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
        delete_transient('bizyhood_oauth_data');
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_PRODUCTION, $_POST['api_production'] === 'true');
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_ID, $_POST['api_id']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_SECRET, $_POST['api_secret']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_MAIN_PAGE_ID, $_POST['main_page_id']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_SIGNUP_PAGE_ID, $_POST['signup_page_id']);
        die(json_encode(array('success' => true)));
    }
    
}