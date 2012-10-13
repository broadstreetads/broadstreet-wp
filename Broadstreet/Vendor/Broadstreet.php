<?php
/**
 * This is the PHP client for Broadstreet
 * @link http://broadstreetads.com
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * This is the PHP client and class for Broadstreet
 * It requires cURL 
 */
class Broadstreet
{
    const API_VERSION = '0';
    
    /**
     * The API Key used for auth
     * @var string
     */
    protected $accessToken = null;
    
    /**
     * The hostname to point at
     * @var string
     */
    protected $host = 'my.broadstreetads.com';
    
    /**
     * The constructor
     * @param string $access_token A user's access token
     * @param string $host The API endpoint host. Optional. Defaults to
     *  my.broadstreetads.com
     */
    public function __construct($access_token, $host = null)
    {
        if($host !== null)
        {
            $this->host = $host;
        }
        
        $this->accessToken = $access_token;
    }
    
    /**
     * Get a list of networks this token has access to
     * @return array
     */
    public function getNetworks()
    {
        return $this->_get('/networks')->body->networks;
    }
    
    /**
     * Get a list of zones under a network
     * @return object
     */
    public function getNetworkZones($network_id)
    {
        return $this->_get("/networks/$network_id/zones")->body->zones;
    }
    
    /**
     * Gets a response from the server
     * @param type $uri
     * @return type
     * @throws Broadstreet_DependencyException 
     * @throws Broadstreet_AuthException 
     */
    protected function _get($uri, $options = array())
    {
        $url = $this->_buildRequestURL($uri);

        if(!function_exists('curl_exec'))
        {
            throw new Broadstreet_DependencyException("The cURL module must be installed");
        }

        $curl_handle = curl_init($url);
        $options    += array(CURLOPT_RETURNTRANSFER => true);

        curl_setopt_array($curl_handle, $options);

        $body   = curl_exec($curl_handle);
        $status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        
        if($status == '403')
        {
            throw new Broadstreet_AuthException("Broadstreet API Auth Denied (HTTP 403)");
        }

        return (object)(array('url' => $url, 'body' => @json_decode($body), 'status' => $status));
    }
    
    /**
     * Build a valid request URL from the URI given and the API key
     * @param string $uri
     * @return string 
     */
    protected function _buildRequestURL($uri)
    {
        $uri = ltrim($uri, '/');
        
        return "http://"
                . $this->host
                . '/api/'
                . self::API_VERSION
                . '/'
                . $uri
                . '?access_token='
                . $this->accessToken;      
    }
}

class Broadstreet_GeneralException extends Exception {}
class Broadstreet_DependencyException extends Broadstreet_GeneralException {}
class Broadstreet_AuthException extends Broadstreet_GeneralException {}