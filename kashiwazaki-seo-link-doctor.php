<?php
/**
 * Plugin Name: Kashiwazaki SEO Link Doctor
 * Plugin URI: https://github.com/kashiwazaki/kashiwazaki-seo-link-doctor
 * Description: サイト内の全コンテンツを一覧表示し、実際の出力ページから発リンクのステータスを可視化・エクスポートできるSEOツール
 * Version: 1.0.0
 * Author: Kashiwazaki
 * License: GPL v2 or later
 * Text Domain: kashiwazaki-seo-link-doctor
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KSV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KSV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KSV_VERSION', '1.0.0');

require_once KSV_PLUGIN_DIR . 'includes/class-ksv-loader.php';
require_once KSV_PLUGIN_DIR . 'includes/class-ksv-admin.php';
require_once KSV_PLUGIN_DIR . 'includes/class-ksv-content-scanner.php';
require_once KSV_PLUGIN_DIR . 'includes/class-ksv-link-checker.php';
require_once KSV_PLUGIN_DIR . 'includes/class-ksv-ajax-handler.php';

class KashiwazakiSEOLinkDoctor {
    private static $instance = null;
    private $loader;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->loader = new KSV_Loader();
        $this->init();
    }
    
    private function init() {
        add_action('plugins_loaded', array($this, 'load_plugin'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=kashiwazaki-seo-link-doctor') . '">設定</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function load_plugin() {
        if (is_admin()) {
            new KSV_Admin();
        }
        
        new KSV_Ajax_Handler();
    }
}

KashiwazakiSEOLinkDoctor::get_instance(); 