<?php
/**
 * This is the PHP client for Broadstreet
 * @link http://broadstreetads.com
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

if(!class_exists('Broadstreet')):

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
    protected $host = 'api.broadstreetads.com';
    
    /**
     * Use SSL? You should.
     * @var bool 
     */
    protected $use_ssl = true;
    
    /**
     * The constructor
     * @param string $access_token A user's access token
     * @param string $host The API endpoint host. Optional. Defaults to
     *  api.broadstreetads.com
     */
    public function __construct($access_token = null, $host = null, $secure = true)
    {
        if($host !== null)
        {
            $this->host = $host;
        }
        
        $this->accessToken = $access_token;
        $this->use_ssl     = $secure;
        
        /* Define cURL constants if needed */
        if(!defined('CURLOPT_POST'))
        {
            define('CURLOPT_POST', 47);
            define('CURLOPT_POSTFIELDS', 10015);
            define('CURLOPT_RETURNTRANSFER', 19913);
            define('CURLOPT_CUSTOMREQUEST', 10036);
            define('CURLINFO_HTTP_CODE', 2097154);
        }
    }
    
    /**
     * Magically get back business data based off a seed URL
     * @param string $seed_url 
     * @param int    $network_id
     */
    public function magicImport($seed_url, $network_id)
    {
        return $this->_get("/networks/$network_id/import", array(), array('lookup' => $seed_url))->body;
    }
    
    /**
     * Create an advertiser
     * @param string $name The name of the advertiser
     * @return mixed
     */
    public function createAdvertiser($network_id, $name)
    {
        return $this->_post("/networks/$network_id/advertisers", array('name' => $name))->body->advertiser;
    }
    
    /**
     * Create an advertisement
     * @param string $name The name of the advertisement
     * @param string $type The type of advertisement
     * @return mixed
     */
    public function createAdvertisement($network_id, $advertiser_id, $name, $type, $options = array())
    {
        $params = array('name' => $name, 'type' => $type) + $options;
        
        return $this->_post("/networks/$network_id/advertisers/$advertiser_id/advertisements", $params)->body->advertisement;
    }
    
    /**
     * Create a network
     * @param string $name The name of the network
     * @param array $options An array of options
     */
    public function createNetwork($name, $options = array())
    {
        $options['name'] = $name;
        return $this->_post("/networks", $options)->body->network;
    }
    
    /**
     * Create a basic user
     * @param string $email 
     */
    public function createUser($email)
    {
        return $this->_post($email, $data);
    }
    
    /**
     * Create a proof
     * @param array $params 
     */
    public function createProof($params)
    {
        return $this->_post("/advertisements/proof", $params)->body->proof;
    }
    
    /**
     * Log in to the API, get an access token back
     * @param string $username
     * @param string $password 
     */
    public function login($email, $password)
    {
        $params   = array('email' => $email, 'password' => $password);
        $response = $this->_post("/sessions", $params)->body->user;
        
        $this->accessToken = $response->access_token;
        
        # Store access token
        return $response;
    }
    
    /**
     * Register a new user
     * @param string $username
     * @param string $password 
     */
    public function register($email)
    {
        $params   = array('email' => $email);
        $response = $this->_post("/users", $params)->body->user;
        
        $this->accessToken = $response->access_token;
        
        # Store access token
        return $response;
    }
    
    /**
     * Get a list of fonts supported by Broadstreet 
     */
    public function getFonts()
    {
        return $this->_get("/fonts")->body->fonts;
    }
    
    /**
     * Get base account information for a network, including whether a card is
     *  on file, the cost of an import (in cents), etc
     * @param int $network_id 
     */
    public function getNetwork($network_id)
    {
        return $this->_get("/networks/$network_id")->body->network;
    }
    
    /**
     * Update an advertisement
     * @param string $name The name of the advertisement
     * @param string $type The type of advertisement
     * @return mixed
     */
    public function updateAdvertisement($network_id, $advertiser_id, $advertisement_id, $params = array())
    {
        return $this->_put("/networks/$network_id/advertisers/$advertiser_id/advertisements/$advertisement_id", $params)->body->advertisement;
    }
    
    /**
     * Get a list of advertisers this token has access to 
     */
    public function getAdvertisers($network_id)
    {
        return $this->_get("/networks/$network_id/advertisers")->body->advertisers;
    }
    
    /**
     * Get information about a given advertisement
     * @param int $network_id
     * @param int $advertiser_id
     * @param int $advertisement_id
     * @return object
     */
    public function getAdvertisement($network_id, $advertiser_id, $advertisement_id)
    {
        return $this->_get("/networks/$network_id/advertisers/$advertiser_id/advertisements/$advertisement_id")
                    ->body->advertisement;
    }
    
    /**
     * Get information about a given advertisement source
     * @param int $network_id
     * @param int $advertiser_id
     * @param int $advertisement_id
     * @return object
     */
    public function getAdvertisementSource($network_id, $advertiser_id, $advertisement_id)
    {   
        return $this->_get("/networks/$network_id/advertisers/$advertiser_id/advertisements/$advertisement_id/source")
                    ->body->source;
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
     * The the update source of an advertisement
     * @param int $network_id
     * @param int $advertiser_id
     * @param int $advertisement_id
     * @param string $type
     * @param array $options
     * @return object 
     */
    public function setAdvertisementSource($network_id, $advertiser_id, $advertisement_id, $type, $options = array())
    {
        $params = array('type' => $type) + $options;

        return $this->_post("/networks/$network_id/advertisers/$advertiser_id/advertisements/$advertisement_id/source", $params)
                    ->body->advertisement;
    }
    
    /**
     * Gets a response from the server
     * @param string $uri
     * @param array $options
     * @param array $query_args
     * @return type
     * @throws Broadstreet_DependencyException 
     * @throws Broadstreet_AuthException 
     */
    protected function _get($uri, $options = array(), $query_args = array())
    {
        $url = $this->_buildRequestURL($uri, $query_args);

        # If the Wordpress HTTP library is loaded, use it
        if(function_exists('wp_remote_post'))
        {
            list($body, $status) = $this->_wpGet($url, $options);
        }
        else
        {
            # Fallback to cURL
            if(!function_exists('curl_exec'))
            {
                throw new Broadstreet_DependencyException("The cURL module must be installed");
            }
            
            list($body, $status) = $this->_curlGet($url, $options);
        }
        
        if($status == '403')
        {
            throw new Broadstreet_ServerException("Broadstreet API Auth Denied (HTTP 403)", @json_decode($body));
        }
        
        if($status == '500')
        {
            throw new Broadstreet_ServerException("Broadstreet API had a 500 error");
        }
        
        if($status[0] != '2')
        {
            throw new Broadstreet_ServerException("Server threw HTTP $status for call to $uri with cURL params " . print_r($options, true) . "; Response: " . $body, @json_decode($body));
        }

        return (object)(array('url' => $url, 'body' => @json_decode($body), 'status' => $status));
    }
    
    /**
     * Issue a network request using the built-in Wordpress libraries
     *  Intended for use within Wordpress for extra portability
     * @param string $url
     * @param array $options cURL options. Limited support
     * @return array(body, status_code)
     */
    protected function _wpGet($url, $options = array())
    {
        $params = array (
            'method'      => 'GET',
            'timeout'     => 25,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true
        );
        
        # Handle POST Requests
        if(isset($options[CURLOPT_POST]))
        {
            $params['method'] = 'POST';
            $params['body']   = $options[CURLOPT_POSTFIELDS];
        }
        
        # Handle PUT
        if(isset($options[CURLOPT_CUSTOMREQUEST])
            && $options[CURLOPT_CUSTOMREQUEST] == 'PUT')
        {
            $params['method'] = 'PUT';
            $params['body']   = $options[CURLOPT_POSTFIELDS];
        }
        
        $body     = '{}';
        $status   = false;
        $response = @wp_remote_post($url, $params);
        
        if(isset($response['response'])
                && isset($response['body'])
                && isset($response['response']['code']))
        {
            $body   = $response['body'];
            $status = (string)$response['response']['code'];
        }
        
        return array($body, $status);
    }
    
    /**
     * Issue a network request using cURL
     * @param string $url
     * @param array  $options
     * @return array(body, status_code)
     */
    protected function _curlGet($url, $options = array())
    {
        $curl_handle = curl_init($url);
        $options    += array(CURLOPT_RETURNTRANSFER => true);

        curl_setopt_array($curl_handle, $options);

        $body   = curl_exec($curl_handle);
        $status = (string)curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
        
        return array($body, $status);
    }
    
    
    /**
     * POST data to the server
     * @param string $uri
     * @param array $data Assoc. array of post data
     * @return mixed
     */
    protected function _post($uri, $data)
    {
        return $this->_get($uri, array(                                        
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $data)
        );
    }
    
    /**
     * PUT data to the server
     * @param string $uri
     * @param array $data Assoc. array of post data
     * @return mixed
     */
    public function _put($uri, $data = false, $options = array())
    {
        $data    = http_build_query($data);

        $options = array (
                        CURLOPT_CUSTOMREQUEST => 'PUT',
                        CURLOPT_POSTFIELDS    => $data
                        ) + $options;
        
        $result = $this->_get($uri, $options);
        
        return $result;
    }   
    
    /**
     * Build a valid request URL from the URI given and the API key
     * @param string $uri
     * @return string 
     */
    protected function _buildRequestURL($uri, $query_args = array())
    {
        $uri      = ltrim($uri, '/');

        return ($this->use_ssl ? 'http://' : 'http://')
                . $this->host
                . '/api/'
                . self::API_VERSION
                . '/'
                . $uri
                . (count($query_args) ? '?' . http_build_query($query_args) : '')
                . (count($query_args) ? '&' : '?')
                . ($this->accessToken ? "access_token={$this->accessToken}" : '');
    }
}

class Broadstreet_GeneralException extends Exception {}
class Broadstreet_DependencyException extends Broadstreet_GeneralException {}
class Broadstreet_AuthException extends Broadstreet_GeneralException {}
class Broadstreet_ServerException extends Broadstreet_GeneralException {
    /**
     * The error object
     * @var object 
     */
    public $error;
    public function __construct($message, $error = '') {
        $this->error = $error;
        parent::__construct($message);
    }
}

endif;