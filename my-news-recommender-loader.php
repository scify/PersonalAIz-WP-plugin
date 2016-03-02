<?php

/*
  Plugin Name: My News Recommender
  Plugin URI: http://www.demokritos.gr/
  Description: A news article recommendation plugin.
  Version: 1.0
  License: GPL
  Author:
  Author URI:
 */

require_once('MyNewsRecommender/MyNewsRecommender.php');

// hook to plugin activation
register_activation_hook(__FILE__, array('MyNewsRecommender', 'plugin_activated'));

$mynewsrecommender = new MyNewsRecommender();
