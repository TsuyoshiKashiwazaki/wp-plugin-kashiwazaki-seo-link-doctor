<?php

class KSV_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Kashiwazaki SEO Link Doctor',
            'Kashiwazaki SEO Link Doctor',
            'manage_options',
            'kashiwazaki-seo-link-doctor',
            array($this, 'admin_page'),
            'dashicons-chart-area',
            81
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_kashiwazaki-seo-link-doctor') {
            return;
        }
        $ver = date('YmdHis');
        wp_enqueue_script(
            'ksv-admin-js',
            KSV_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $ver,
            true
        );
        wp_enqueue_style(
            'ksv-admin-css',
            KSV_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $ver
        );
        wp_localize_script('ksv-admin-js', 'ksv_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ksv_nonce')
        ));
    }
    
    public function admin_page() {
        include KSV_PLUGIN_DIR . 'templates/admin-page.php';
    }
} 