<?php
/*
Plugin Name: Bizyhood
Plugin URI: https://bizyhood.com
Description: Integrates Bizyhood for Publishers engagement and discovery platform to your hyperlocal digital publishing site
Version: 1.2.1
Author: Bizyhood
Author URI: http://bizyhood.com
*/

require dirname(__FILE__) . '/Bizyhood/Core.php';


# Start the beast
$engine = new Bizyhood_Core;
$engine->execute();

register_activation_hook( __FILE__, array( 'Bizyhood_Core', 'install' ) );
register_deactivation_hook( __FILE__, array( 'Bizyhood_Core', 'uninstall' ) );
