<?php

class KSV_Loader {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        register_activation_hook(KSV_PLUGIN_DIR . 'kashiwazaki-seo-vitalcheck.php', array($this, 'activate'));
        register_deactivation_hook(KSV_PLUGIN_DIR . 'kashiwazaki-seo-vitalcheck.php', array($this, 'deactivate'));
    }
    
    public function activate() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        add_option('ksv_version', KSV_VERSION);
    }
    
    public function deactivate() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        delete_option('ksv_version');
    }
} 