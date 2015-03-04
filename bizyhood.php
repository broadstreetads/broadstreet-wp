<?php
/*
Plugin Name: Bizyhood
Plugin URI: https://bizyhood.com
Description: Integrate Bizyhood business directory on your site
Version: 0.1.1
Author: Bizyhood
Author URI: https://bizyhood.com
*/

require dirname(__FILE__) . '/Bizyhood/Core.php';


# Start the beast
$engine = new Bizyhood_Core;
$engine->execute();

register_activation_hook( __FILE__, array( 'Bizyhood_Core', 'install' ) );
register_deactivation_hook( __FILE__, array( 'Bizyhood_Core', 'uninstall' ) );
