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
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_API_URL, $_POST['api_url']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_ZIP_CODES, $_POST['zip_codes']);
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_USE_CUISINE_TYPES, $_POST['use_cuisine_types'] === 'true');
        Bizyhood_Utility::setOption(Bizyhood_Core::KEY_CATEGORIES, $_POST['categories']);
        die(json_encode(array('success' => true)));
    }
    
}