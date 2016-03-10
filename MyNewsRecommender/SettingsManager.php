<?php

/**
 * 
 * Includes logic for setting and updating settings.
 * 
 */
class SettingsManager {

    private $ip;
    private $port;
    private $username;
    private $password;
    private $apikey;
    private $authmethod;
    private $titlecheck;
    private $contentcheck;
    private $categoriescheck;
    private $tagscheck;
    private $articlescount;
    private $cacheDuration;
    private $lasthourscount;

    public function __construct() {
        $this->ip = get_option('ip');
        $this->port = get_option('port');
        $this->username = get_option('username');
        $this->password = get_option('password');
        $this->apikey = get_option('apikey');
        $this->authmethod = get_option('authmethod');
        $this->titlecheck = get_option('titlecheck');
        $this->contentcheck = get_option('contentcheck');
        $this->categoriescheck = get_option('categoriescheck');
        $this->tagscheck = get_option('tagscheck');
        $this->articlescount = get_option('articlescount');
        $this->cacheDuration = get_option('cacheDuration');
        $this->lasthourscount = get_option('lasthourscount');
    }

    public static function deleteAllOptions() {

        delete_option('ip');
        delete_option('port');
        delete_option('username');
        delete_option('password');
        delete_option('apikey');
        delete_option('authmethod');
        delete_option('titlecheck');
        delete_option('contentcheck');
        delete_option('categoriescheck');
        delete_option('tagscheck');
        delete_option('articlescount');
        delete_option('cacheDuration');
        delete_option('lasthourscount');
    }

    public function getIp() {
        return $this->ip;
    }

    public function getPort() {
        return $this->port;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getApiKey() {
        return $this->apikey;
    }

    public function getAuthenticationMethod() {
        return $this->authmethod;
    }

    public function getTitleCheck() {
        return $this->titlecheck;
    }

    public function getContentCheck() {
        return $this->contentcheck;
    }

    public function getCategoriesCheck() {
        return $this->categoriescheck;
    }

    public function getTagsCheck() {
        return $this->tagscheck;
    }

    public function getArticlesCount() {
        return $this->articlescount;
    }

    public function getCacheDuration() {
        return $this->cacheDuration;
    }

    public function getLastHoursCount() {
        return $this->lasthourscount;
    }

    public function setIp($ip) {
        $this->ip = $ip;
        update_option('ip', $ip);
    }

    public function setPort($port) {
        $this->port = $port;
        update_option('port', $port);
    }

    public function setUsername($username) {
        $this->username = $username;
        update_option('username', $username);
    }

    public function setPassword($password) {
        $this->password = $password;
        update_option('password', $password);
    }

    public function setApiKey($apikey) {
        $this->apikey = $apikey;
        update_option('apikey', $apikey);
    }

    public function setAuthenticationMethod($authmethod) {
        $this->authmethod = $authmethod;
        update_option('authmethod', $authmethod);
    }

    public function setTitleCheck($titlecheck) {
        $this->titlecheck = $titlecheck;
        update_option('titlecheck', $titlecheck);
    }

    public function setContentCheck($contentcheck) {
        $this->contentcheck = $contentcheck;
        update_option('contentcheck', $contentcheck);
    }

    public function setCategoriesCheck($categoriescheck) {
        $this->categoriescheck = $categoriescheck;
        update_option('categoriescheck', $categoriescheck);
    }

    public function setTagsCheck($tagscheck) {
        $this->tagscheck = $tagscheck;
        update_option('tagscheck', $tagscheck);
    }

    public function setArticlesCount($articlescount) {
        $this->articlescount = $articlescount;
        update_option('articlescount', $articlescount);
    }

    public function setCacheDuration($cacheDuration) {
        $this->cacheDuration = $cacheDuration;
        update_option('cacheDuration', $cacheDuration);
    }
    public function setLastHoursCount($lasthourscount) {
        $this->lasthourscount = $lasthourscount;
        update_option('lasthourscount', $lasthourscount);
    }

}
