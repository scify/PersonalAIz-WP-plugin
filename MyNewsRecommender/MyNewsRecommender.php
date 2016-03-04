<?php

require_once('SettingsManager.php');
require_once('RecommenderWidget.php');
require_once('JsonManager.php');
require_once('DbManager.php');
require_once('utility.php');

/**
 * 
 * Includes the logic of the plugin.
 * 
 */
class MyNewsRecommender {

    public $settingsmanager;

    public function __construct() {

        // add all necessary hooks
        $this->addHooks();

        // create new SettingsManager object
        $this->settingsmanager = new SettingsManager();

        // register the widget
        add_action('widgets_init', create_function('', 'return register_widget("RecommenderWidget");'));
    }

    /**
     * 
     * Add all required hooks.
     * 
     */
    private function addHooks() {

        // Hook when the menu bar on the admin panel starts loading and run function newsrec_admin_menu().
        add_action('admin_menu', array($this, 'newsrec_admin_menu'));

        // Hook when a post has been loaded.
        add_action('wp', array($this, 'post_loaded'));

        // register style on initialization
        add_action('init', array($this, 'register_style'));
        // register scripts on initialization
        add_action('init', array($this, 'register_script'));

        // use the registered style above
        add_action('wp_enqueue_scripts', array($this, 'enqueue_style'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));

        // hook after user regitration
        add_action('user_register', array($this, 'user_registration'));

        // hook after user has been deleted
        add_action('deleted_user', array($this, 'user_deleted'));
    }

    /**
     * 
     * Delete user from recommendation engine,called a user has been deleted from Wordpress.
     * 
     */
    public function user_deleted($user_id) {

        // delete user's records from database
        DbManager::delete_user($user_id);


        // get connection info and user credentials
        $ip = $this->settingsmanager->getIp();
        $port = $this->settingsmanager->getPort();
        $username = $this->settingsmanager->getUsername();
        $password = $this->settingsmanager->getPassword();

        // build request url
        $url = $ip . ":" . $port . "/PersonalAIz/api/recengine/" . $username . "%7c" . $password;
        $url .= "/user/delete/" . $user_id;

        // make post request
        $response = postRequest($url, NULL);

//        // check if user deletion failed
//        if($responsejson['outputMessage'] != "Delete User Complete")
//            echo "user deletion failed";
    }

    /**
     * 
     * Add user to recommendation engine.
     * 
     */
    public function user_registration($user_id) {

        // get connection info and user credentials
        $ip = $this->settingsmanager->getIp();
        $port = $this->settingsmanager->getPort();
        $username = $this->settingsmanager->getUsername();
        $password = $this->settingsmanager->getPassword();

        // build request url
        $url = $ip . ":" . $port . "/PersonalAIz/api/recengine/" . $username . "%7c" . $password;
        $url .= "/user/" . $user_id;

        // make post request
        $response = postRequest($url, NULL);
    }

    /**
     * 
     * Adds a sub menu to the admin Settings menu.
     * 
     */
    public function newsrec_admin_menu() {
        // add sub menu page to the Settings menu
        add_options_page("My News Recommender Settings", "My News Recommender Settings", 1, "MyNewsRecommenderSettings", array($this, 'newsrec_admin_settings'));
    }

    /**
     * 
     * Shows the plugin Settings page.
     * 
     */
    public function newsrec_admin_settings() {
        include('admin_settings.php');
    }

    /**
     * Register css file for widget.
     */
    public function register_style() {
        wp_register_style('widgetstyle', plugins_url('/css/widget-style.css', __FILE__), false, '1.0.0', 'all');
        wp_register_style('bootstrapstyle', plugins_url('/css/bootstrap.min.css', __FILE__), false, '1.0.0', 'all');
    }
    public function register_script() {
        wp_register_script('bootstrapscript', plugins_url('/js/bootstrap.min.js', __FILE__), false, '1.0.0', 'all');
        wp_register_script('jqueryscript', plugins_url('/js/jquery-2.2.1.min.js', __FILE__), false, '1.0.0', 'all');
    }

    /**
     * Add css file for widget.
     */
    public function enqueue_style() {
        wp_enqueue_style('widgetstyle');
        wp_enqueue_style('bootstrapstyle');
    }
    public function enqueue_script() {
        wp_enqueue_script('bootstrapscript');
        wp_enqueue_script('jqueryscript');
    }
    
    /**
     *
     * This function is called when a post has been loaded.
     * It gets post information and updates the recommendation engine.
     * 
     */
    public function post_loaded() {
        if (is_single() && get_post_type() == 'post' && is_user_logged_in()) {

            // feed recommendation engine
            $post = get_post();
            $this->feed($post);

            // save to the database that the current user has read the current post
            // and remove it from recommendations
            DbManager::add_user_post_history(wp_get_current_user()->ID, get_the_ID());
        }
    }

    /**
     * 
     * Feed Recommendation Engine with the given post.
     * 
     */
    private function feed($post) {

        //TODO: get lang from url
        // get post information as json encoded object
        $lang = get_bloginfo('language');
        $language = substr($lang, 0, strrpos($lang, "-"));
        $isrecommended = $this->isrecommended($post->ID);
        $json = JsonManager::encodeToJson($this->settingsmanager, $post, $language, $isrecommended);

        // get connection info and user credentials
        $ip = $this->settingsmanager->getIp();
        $port = $this->settingsmanager->getPort();
        $username = $this->settingsmanager->getUsername();
        $password = $this->settingsmanager->getPassword();
        $userid = wp_get_current_user()->ID;

        // build request url
        $url = $ip . ":" . $port . "/PersonalAIz/api/recengine/" . $username . "%7c" . $password . "/feed/" . $userid;

        // make post request
        $response = postRequest($url, array('JSONObject' => $json));


//        // check if feed has failed
//        if($response != "{\"outputMessage\":\"Feed User Complete\"}")
//            echo "Feed Completed!";
    }

    /**
     * 
     * Check if the post with the given id is has been clicked after recommendation.
     * 
     */
    private function isrecommended($postid) {
        // get recommendations from database
        $recommendations = DbManager::get_recommendations(wp_get_current_user()->ID);
        foreach ($recommendations as $pid) {
            if ($pid == $postid) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /*
     * 
     * Adds all users currently in Wordpress to recommendation engine.
     * It should be called after the plugin has been activated.
     * 
     */

    public static function plugin_activated() {


        //TODO: add as function to add all the existed users in the system
//        $users = get_users();
//        foreach ($users as $user) {
//            $userid = $user->ID;
//            $settingsmanager = new SettingsManager();
//
//
//            // get connection info and user credentials
//            $ip = $settingsmanager->getIp();
//            $port = $settingsmanager->getPort();
//            $username = $settingsmanager->getUsername();
//            $password = $settingsmanager->getPassword();
//
//            // build request url
//            $url = $ip . ":" . $port . "/PersonalAIz/api/recengine/" . $username . "%7c" . $password;
//            $url .= "/user/" . $userid;
//
//            // make post request
//            $response = postRequest($url, NULL);
//        }
        // create tables
        DbManager::create_tables();
    }

}
