<?php

class KSV_Link_Checker {
    
    public function extract_links($content, $page_url) {
        $links = array();
        $url_counts = array(); // URL出現回数を追跡

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        $link_elements = $xpath->query('//a[@href]');

        foreach ($link_elements as $link) {
            $href = $link->getAttribute('href');
            $text = trim($link->textContent);

            if (!empty($href) && $href !== '#' && !$this->is_non_http_scheme($href)) {
                $absolute_url = $this->make_absolute_url($href, $page_url);
                $line_no = $link->getLineNo();
                $highlight_url = $this->build_highlight_url($page_url, $text);

                // 出現番号を計算
                if (!isset($url_counts[$absolute_url])) {
                    $url_counts[$absolute_url] = 0;
                }
                $url_counts[$absolute_url]++;
                $occurrence = $url_counts[$absolute_url];

                $links[] = array(
                    'url' => $absolute_url,
                    'text' => $text,
                    'is_internal' => $this->is_internal_link($absolute_url),
                    'line_no' => $line_no,
                    'highlight_url' => $highlight_url,
                    'occurrence' => $occurrence
                );
            }
        }

        return $links;
    }
    
    public function check_link_status($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Kashiwazaki SEO Link Doctor)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return array(
            'status_code' => $http_code,
            'is_error' => !empty($error),
            'error_message' => $error
        );
    }
    
    public function check_multiple_links($links) {
        $results = array();
        
        foreach ($links as $link) {
            $status = $this->check_link_status($link['url']);
            $results[] = array_merge($link, $status);
        }
        
        return $results;
    }
    
    private function make_absolute_url($href, $base_url) {
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }
        
        if (strpos($href, '//') === 0) {
            return 'https:' . $href;
        }
        
        if (strpos($href, '/') === 0) {
            $parsed = parse_url($base_url);
            return $parsed['scheme'] . '://' . $parsed['host'] . $href;
        }
        
        return rtrim($base_url, '/') . '/' . ltrim($href, '/');
    }
    
    private function is_internal_link($url) {
        $site_url = get_site_url();
        $parsed_site = parse_url($site_url);
        $parsed_link = parse_url($url);

        return $parsed_site['host'] === $parsed_link['host'];
    }

    private function is_non_http_scheme($href) {
        $exclude = array('tel:', 'mailto:', 'javascript:', 'data:', 'ftp:', 'file:', 'sms:', 'skype:');
        foreach ($exclude as $scheme) {
            if (stripos($href, $scheme) === 0) {
                return true;
            }
        }
        return false;
    }

    private function build_highlight_url($page_url, $text) {
        if (empty($text)) {
            return $page_url;
        }
        // Text Fragments用にエンコード
        $encoded_text = rawurlencode($text);
        return $page_url . '#:~:text=' . $encoded_text;
    }
} 