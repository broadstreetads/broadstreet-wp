<?php
/**
 * This file contains a class for loading the presentation layer/files
 */

/**
 * This class contains methods for loading Bizyhood views
 */
class Bizyhood_View
{
    /**
     * Load a view file. The file should be located in Bizyhood/Views.
     * @param string $file The filename of the view without the extenstion (assumed
     *  to be PHP)
     * @param array $data An associative array of data that be be extracted and
     *  available to the view
     * @param bool $return Return the output instead of outputting it
     */
    public static function load($file, $data = array(), $return = false, $eval = true)
    {
        $file = dirname(__FILE__) . '/Views/' . $file . '.php';

        if(!file_exists($file))
        {
            Bizyhood_Log::add('fatal', "View '$file' was not found");
            throw new Exception("View '$file' was not found");
        }

        # Extract the variables into the global scope so the views can use them
        extract($data);

        if(!$return)
        {
            if($eval)
                include($file);
            else
                readfile($file);
        }
        else
        {
            ob_start();
            if($eval)
                include($file);
            else
                readfile($file);
            return ob_get_clean();
        }
    }
}