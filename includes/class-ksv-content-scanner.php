<?php

class KSV_Content_Scanner {
    
    public function get_all_content() {
        $content_list = array();
        
        $post_types = get_post_types(array('public' => true), 'names');
        
        foreach ($post_types as $post_type) {
            $posts = get_posts(array(
                'post_type' => $post_type,
                'numberposts' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($posts as $post) {
                $content_list[] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'url' => get_permalink($post->ID),
                    'post_type' => $post->post_type,
                    'date' => $post->post_date
                );
            }
        }
        
        return $content_list;
    }
    
    public function get_page_content($post_id) {
        $url = get_permalink($post_id);
        if (!$url) {
            return false;
        }
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'redirection' => 5,
            'user-agent' => 'Mozilla/5.0 (compatible; Kashiwazaki SEO Link Doctor)'
        ));
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        return $body;
    }
    
    private function get_custom_fields_content($post_id) {
        $custom_fields = get_post_custom($post_id);
        $content = '';
        
        foreach ($custom_fields as $key => $values) {
            if (strpos($key, '_') !== 0) {
                foreach ($values as $value) {
                    $content .= ' ' . $value;
                }
            }
        }
        
        return $content;
    }
} 