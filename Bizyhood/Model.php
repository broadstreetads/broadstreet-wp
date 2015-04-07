<?php
/* 
 * 
 */

/**
 * Description of Model
 *
 */
class Bizyhood_Model
{
    public static function getPublishedDocument($document_id)
    {
        global $wpdb;

        $table = self::getTableName('posts');

        $sql = "SELECT *
                FROM $table
                WHERE ID = '$document_id'
                AND post_status = 'publish'
                LIMIT 1";

        $posts = $wpdb->get_results($sql);

        if(count($posts) == 0)
            return false;
        else
            return $posts[0];
    }

    public static function getPublishedDocuments($page = 0, $per_page = 10)
    {
        global $wpdb;

        $start = $page * $per_page;

        $table = self::getTableName('posts');

        $sql = "SELECT *
                FROM $table
                WHERE post_status = 'publish'
                LIMIT $start, $per_page";

        return $wpdb->get_results($sql);
    }

    public static function getPublishedPostCount()
    {
        global $wpdb;
        
        $table = self::getTableName('posts');

        $sql = "SELECT COUNT(*) AS 'count'
                FROM $table
                WHERE post_status = 'publish'";

        $results = $wpdb->get_results($sql);

        return $results[0]->count;
    }

    public static function getPostComments($post_id, $page = FALSE, $per_page = FALSE)
    {
        global $wpdb;

        $table = self::getTableName('comments');

        /* Process posts in batches of 20 from the db */
        $sql = "SELECT comment_author, comment_content
                FROM $table
                WHERE comment_approved = '1'
                AND comment_post_ID = '$post_id'";
                
        if($page && $per_page)
        {
            $start = $page * $per_page;
            $sql = "$sql LIMIT $start, $per_page";
        }

        return $wpdb->get_results($sql);
    }
    
    public static function getPostMeta($post_ids = array(), $defaults = array())
    {
        if(count($post_ids) == 0) return array();
        
        global $wpdb;

        $table = self::getTableName('postmeta');

        /* Process posts in batches of 20 from the db */
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

    public static function getCategories()
    {
        global $wpdb;

        $table = $this->getTableName('');
    }

    public static function getTableName($table_name)
    {
        global $wpdb;
        return "{$wpdb->prefix}{$table_name}";
    }
}