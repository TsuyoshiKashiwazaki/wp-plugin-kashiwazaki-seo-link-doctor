<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('ksv_version');

global $wpdb;

// プラグインオプションを削除
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ksv_%'");

// リンクステータスキャッシュ（transients）を削除
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        '_transient_ksv_link_%',
        '_transient_timeout_ksv_link_%'
    )
); 