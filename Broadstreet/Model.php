<?php
/* 
 * 
 *
 * @author Broadstreet Ads <labs@broadstreetads.com>
 */

/**
 * Description of Model
 *
 */
class Broadstreet_Model
{
    /**
     * There is a more modern way to do this, but this is also used very infrequently.
     * It's also pretty efficient: it grabs post meta at once so that a loop doesn't
     * have to be used later
     */
    public static function getPostMeta($post_ids = array(), $defaults = array())
    {
        if(count($post_ids) == 0) return array();
        
        global $wpdb;

        $table = self::getTableName('postmeta');

        $sql = "SELECT post_id, meta_key, meta_value
                FROM $table
                WHERE post_id IN (".implode(',', $post_ids).")";

        $results = $wpdb->get_results($sql);
        
        $meta = array();
        
        foreach($results as $result)
        {
            if(isset($meta[$result->post_id]) && !is_array($meta[$result->post_id]))
                $meta[$result->post_id] = $defaults;
            
            $meta[$result->post_id][$result->meta_key] = $result->meta_value;
        }
        
        return $meta;
    }

    public static function getTableName($table_name)
    {
        global $wpdb;
        return "{$wpdb->prefix}{$table_name}";
    }
}