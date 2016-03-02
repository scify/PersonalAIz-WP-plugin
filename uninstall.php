<?php

require_once('MyNewsRecommender/SettingsManager.php');
require_once('MyNewsRecommender/DbManager.php');

//if uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// delete options
SettingsManager::deleteAllOptions();

// delete plugin's database tables
DbManager::delete_tables();
