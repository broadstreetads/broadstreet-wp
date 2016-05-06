<?php
/**
 * This file contains a class for oAuth methods
 */

/**
 * The class contains a number of oAuth methods that may be needed by various
 *  parts of Bizyhood
 */
class Bizyhood_oAuth
{

    CONST OAUTH_CURLOPT_CONNECTTIMEOUT  = 10;
    CONST OAUTH_CURLOPT_TIMEOUT         = 10;

    /**
     * Build oAuth provider parameters
     * @return array The provider
     */
    public static function setoAuthProvider()
    {        
        $provider = array (
          'clientId'                =>  Bizyhood_Utility::getApiID(),
          'clientSecret'            =>  Bizyhood_Utility::getApiSecret(),
          'redirectUri'             =>  '', // no need for 2-legged auth
          'urlAuthorize'            =>  Bizyhood_Utility::getApiUrl().'/o/authorize/',
          'urlAccessToken'          =>  Bizyhood_Utility::getApiUrl().'/o/token/',
          'urlResourceOwnerDetails' =>  Bizyhood_Utility::getApiUrl().'/o/resource'
        );

        return $provider;
    }
    
    
    /**
     * Build oAuth provider parameters
     * @param array $provider The provider array to create the oAuth client
     * @return mixed The client oAuth object or WP_error
     */
    public static function oAuthClient($provider=array())
    { 
      
      $client = '';
      
      // no reason to continue if we do not have oAuth token
      if (self::checkoAuthData() === false) {
        $error = new WP_Error( 'bizyhood_error', __( 'Invalid oAuth credentials.', 'bizyhood' ) );
        return $error;
      }
    
      if (empty($provider)) {
        $provider = self::setoAuthProvider();
      }
    
      try {
        $client = new OAuth2\Client($provider['clientId'], $provider['clientSecret']);
      } catch (Exception $e) {
        $error = new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Request timed out.', 'bizyhood' ) );
        return $error;
      }
    
      $client->setAccessTokenType($client::ACCESS_TOKEN_BEARER);
      $client->setAccessToken(get_transient('bizyhood_oauth_data'));
      $curl_timeout = array(
        CURLOPT_CONNECTTIMEOUT => self::OAUTH_CURLOPT_CONNECTTIMEOUT,
        CURLOPT_TIMEOUT => self::OAUTH_CURLOPT_TIMEOUT
      );
      $client->setCurlOptions($curl_timeout);    
      
      return $client;
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
     * Set the Bizyhood oAuth Data transient
     * @return mixed boolean or WP_error
     */
    public function set_oauth_temp_data() {
      
      $provider = Bizyhood_oAuth::setoAuthProvider();
      

     // if the oAuth data does not exist
     if (get_transient('bizyhood_oauth_data') === false ) {
        $params = array();
        $client = new OAuth2\Client($provider['clientId'], $provider['clientSecret']);
        $response = $client->getAccessToken($provider['urlAccessToken'], 'client_credentials', $params);

        
        if ( is_array($response) && !empty($response) && isset($response['code']) && $response['code'] == 200 && (isset($response['result']['access_token']) && strlen($response['result']['access_token']) > 0)) {
          
          $client->setAccessToken($response['result']['access_token']);
          set_transient('bizyhood_oauth_data', $response['result']['access_token'], $response['result']['expires_in']);
                    
        } else {
          delete_transient('bizyhood_oauth_data');
          if (Bizyhood_Utility::is_bizyhood_page()) {
            return new WP_Error( 'bizyhood_error', __( 'Service is currently unavailable! Error code: '. $response['code'] .'; '.$response['result']['error'], 'bizyhood' ) );
          } else {
            return false;
          }
        }
      }
      
      return true;
    }
    
    
}