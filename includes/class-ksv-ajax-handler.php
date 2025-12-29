<?php

class KSV_Ajax_Handler {
    
    public function __construct() {
        add_action('wp_ajax_ksv_get_content_list', array($this, 'get_content_list'));
        add_action('wp_ajax_ksv_check_page_links', array($this, 'check_page_links'));
        add_action('wp_ajax_ksv_extract_links', array($this, 'extract_links'));
        add_action('wp_ajax_ksv_check_single_link', array($this, 'check_single_link'));
        add_action('wp_ajax_ksv_preview_highlight', array($this, 'preview_highlight'));
        add_action('wp_ajax_ksv_clear_cache', array($this, 'clear_cache'));
    }
    
    public function get_content_list() {
        check_ajax_referer('ksv_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $scanner = new KSV_Content_Scanner();
        $content_list = $scanner->get_all_content();
        
        wp_send_json_success($content_list);
    }
    
    public function check_page_links() {
        check_ajax_referer('ksv_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => 'Unauthorized',
                'debug' => '権限がありません。'
            ]);
        }
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error([
                'message' => 'Invalid post ID',
                'debug' => 'post_idが不正です: ' . print_r($_POST, true)
            ]);
        }
        
        $scanner = new KSV_Content_Scanner();
        $link_checker = new KSV_Link_Checker();
        
        $content = $scanner->get_page_content($post_id);
        $page_url = get_permalink($post_id);
        
        if (!$content) {
            $debug = 'KSV: get_page_content failed for post_id ' . $post_id . "\n";
            $debug .= 'URL: ' . $page_url . "\n";
            wp_send_json_error([
                'message' => 'Content not found or fetch failed',
                'debug' => $debug
            ]);
        }
        
        $links = $link_checker->extract_links($content, $page_url);
        if (empty($links)) {
            $debug = 'KSV: No links found for post_id ' . $post_id . "\n";
            $debug .= 'URL: ' . $page_url . "\n";
            wp_send_json_error([
                'message' => 'リンクが見つかりません',
                'debug' => $debug
            ]);
        }
        $link_results = $link_checker->check_multiple_links($links);
        
        $post = get_post($post_id);
        $result = array(
            'post_title' => $post->post_title,
            'post_url' => $page_url,
            'links' => $link_results,
            'total_links' => count($link_results),
            'error_links' => array_values(array_filter($link_results, function($link) {
                return $link['status_code'] !== 200 || $link['is_error'];
            }))
        );
        
        wp_send_json_success($result);
    }

    public function extract_links() {
        check_ajax_referer('ksv_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        $scanner = new KSV_Content_Scanner();
        $link_checker = new KSV_Link_Checker();

        $content = $scanner->get_page_content($post_id);
        $page_url = get_permalink($post_id);

        if (!$content) {
            wp_send_json_error(['message' => 'Content not found']);
        }

        $links = $link_checker->extract_links($content, $page_url);
        $post = get_post($post_id);

        wp_send_json_success([
            'post_title' => $post->post_title,
            'post_url' => $page_url,
            'links' => $links,
            'total' => count($links)
        ]);
    }

    public function check_single_link() {
        check_ajax_referer('ksv_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        $skip_cache = isset($_POST['skip_cache']) && $_POST['skip_cache'] === 'true';

        if (empty($url)) {
            wp_send_json_error(['message' => 'Invalid URL']);
        }

        // キャッシュキーを生成
        $cache_key = 'ksv_link_' . md5($url);
        $cache_expiry = 24 * HOUR_IN_SECONDS; // 24時間

        // キャッシュを確認
        if (!$skip_cache) {
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                $cached['from_cache'] = true;
                wp_send_json_success($cached);
            }
        }

        // キャッシュがなければチェック
        $link_checker = new KSV_Link_Checker();
        $result = $link_checker->check_link_status($url);
        $result['from_cache'] = false;
        $result['cached_at'] = current_time('mysql');

        // キャッシュに保存
        set_transient($cache_key, $result, $cache_expiry);

        wp_send_json_success($result);
    }

    public function clear_cache() {
        check_ajax_referer('ksv_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        global $wpdb;

        // ksv_link_で始まるtransientsを削除
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_ksv_link_%',
                '_transient_timeout_ksv_link_%'
            )
        );

        wp_send_json_success([
            'message' => 'キャッシュをクリアしました',
            'deleted' => $deleted
        ]);
    }

    public function preview_highlight() {
        check_ajax_referer('ksv_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $post_id = intval($_POST['post_id']);
        $target_href = isset($_POST['href']) ? $_POST['href'] : '';
        $occurrence = isset($_POST['occurrence']) ? intval($_POST['occurrence']) : 1;

        if (!$post_id || empty($target_href)) {
            wp_send_json_error(['message' => 'Invalid parameters']);
        }

        $scanner = new KSV_Content_Scanner();
        $html = $scanner->get_page_content($post_id);

        if (!$html) {
            wp_send_json_error(['message' => 'Failed to fetch page']);
        }

        // ページのURLを取得（相対URL→絶対URL変換用）
        $page_url = get_permalink($post_id);

        // 該当リンクをハイライト（N番目のみ）
        $highlighted_html = $this->inject_highlight($html, $target_href, $occurrence, $page_url);

        wp_send_json_success(['html' => $highlighted_html]);
    }

    private function inject_highlight($html, $target_href, $target_occurrence = 1, $page_url = '') {
        // 正規化: URLデコード + HTMLエンティティデコード
        $target_normalized = html_entity_decode(urldecode($target_href), ENT_QUOTES, 'UTF-8');
        $highlight_style = 'outline: 4px solid #ff0000 !important; background: #ffff00 !important; box-shadow: 0 0 20px #ff0000 !important;';
        $url_counts = array(); // 絶対URLでカウント

        // 正規表現でaタグを検索して置換
        $html = preg_replace_callback(
            '/<a\s([^>]*href=["\']([^"\']*)["\'][^>]*)>/iu',
            function($matches) use ($target_normalized, $highlight_style, $target_occurrence, &$url_counts, $page_url) {
                $full_tag = $matches[0];
                $href_raw = $matches[2];
                // 正規化: URLデコード + HTMLエンティティデコード
                $href_normalized = html_entity_decode(urldecode($href_raw), ENT_QUOTES, 'UTF-8');

                // 絶対URLに変換（リンク抽出時と同じロジック）
                $href_absolute = $this->make_absolute_url_for_highlight($href_normalized, $page_url);

                $match = false;

                // 絶対URL同士で完全一致
                if ($href_absolute === $target_normalized) {
                    $match = true;
                }
                // アンカーリンク（#で始まる）の場合、末尾一致を許可
                elseif (strpos($href_normalized, '#') === 0 && substr($target_normalized, -strlen($href_normalized)) === $href_normalized) {
                    $match = true;
                }

                if ($match) {
                    // 絶対URLでoccurrenceをカウント
                    if (!isset($url_counts[$target_normalized])) {
                        $url_counts[$target_normalized] = 0;
                    }
                    $url_counts[$target_normalized]++;

                    // N番目の出現のみハイライト
                    if ($url_counts[$target_normalized] === $target_occurrence) {
                        // style属性を追加または更新
                        if (preg_match('/style=["\']([^"\']*)["\']/', $full_tag)) {
                            $full_tag = preg_replace('/style=["\']([^"\']*)["\']/', 'style="$1; ' . $highlight_style . '" data-ksv-highlighted="true"', $full_tag);
                        } else {
                            $full_tag = str_replace('<a ', '<a style="' . $highlight_style . '" data-ksv-highlighted="true" ', $full_tag);
                        }
                    }
                }

                return $full_tag;
            },
            $html
        );

        // 自動スクロールスクリプトを</body>の前に注入
        $scroll_script = '<script>
            setTimeout(function() {
                var el = document.querySelector("[data-ksv-highlighted]");
                if (el) {
                    el.scrollIntoView({ behavior: "smooth", block: "center" });
                }
            }, 300);
        </script>';

        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $scroll_script . '</body>', $html);
        } else {
            $html .= $scroll_script;
        }

        return $html;
    }

    private function make_absolute_url_for_highlight($href, $base_url) {
        if (empty($href) || empty($base_url)) {
            return $href;
        }

        // 既に絶対URL
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }

        // プロトコル相対URL
        if (strpos($href, '//') === 0) {
            return 'https:' . $href;
        }

        // ルート相対URL
        if (strpos($href, '/') === 0) {
            $parsed = parse_url($base_url);
            return $parsed['scheme'] . '://' . $parsed['host'] . $href;
        }

        // アンカーのみ
        if (strpos($href, '#') === 0) {
            return rtrim($base_url, '/') . $href;
        }

        // 相対URL
        return rtrim($base_url, '/') . '/' . ltrim($href, '/');
    }
} 