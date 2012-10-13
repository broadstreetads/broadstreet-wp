<?php
/**
 * This file contains a custom Exception class for Broadstreet
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * Whenever an Exception needs to be thrown in Broadstreet, use this class or an
 *  Exception dervided form it. This helps separating Wordpress exceptions from
 *  Broadstreet exceptions
 */
class Broadstreet_Exception extends Exception
{
    
}