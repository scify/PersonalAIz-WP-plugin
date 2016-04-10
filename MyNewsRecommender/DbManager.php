<?php

/**
* Copyright 2016 IIT , NCSR Demokritos - http://www.iit.demokritos.gr,
                       SciFY NPO - http://www.scify.org
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

/**
 * 
 * Interact with the tables for history and recommendation list.
 * 
 */
class DbManager {

    /**
     * 
     * Create all database tables that are required for this plugin.
     * 
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $historytable = $wpdb->prefix . "recommendation_history";
        $listtable = $wpdb->prefix . "recommendation_list";
        $timestampstable = $wpdb->prefix . "recommendation_lasttimestamp";



        // query to create the history table
        $historysql = "CREATE TABLE IF NOT EXISTS " . $historytable . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
          user mediumint(9) NOT NULL,
          post mediumint(9) NOT NULL,
	  UNIQUE KEY id (id)
	) " . $charset_collate . ";";

        // query to create the recommendations list table
        $listsql = "CREATE TABLE IF NOT EXISTS " . $listtable . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
          user mediumint(9) NOT NULL,
          post mediumint(9) NOT NULL,
          score float(5,4) NOT NULL,
          lang VARCHAR(4) NOT NULL,
	  UNIQUE KEY id (id)
	) " . $charset_collate . ";";

        // query to create the last recommendation timestamp table
        $timestampssql = "CREATE TABLE IF NOT EXISTS " . $timestampstable . " (
          user mediumint(11) NOT NULL,
          last_recommendation bigint(15) NOT NULL,
	  UNIQUE KEY user (user)
	) " . $charset_collate . ";";


        // required for 'dbDelta()'
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );

        // create tables
        dbDelta($historysql);
        dbDelta($listsql);
        dbDelta($timestampssql);
    }

    /**
     * 
     * Delete all database tables created by this plugin.
     * 
     */
    public static function delete_tables() {
        global $wpdb;

        $historytable = $wpdb->prefix . "recommendation_history";
        $listtable = $wpdb->prefix . "recommendation_list";
        $timestampstable = $wpdb->prefix . "recommendation_lasttimestamp";


        // delete user's history from db
        $wpdb->query("DROP TABLE " . $historytable);

        // delete all user's recommendations from db
        $wpdb->query("DROP TABLE " . $listtable);

        // delete user's last recommendation timestamp from db
        $wpdb->query("DROP TABLE " . $timestampstable);
    }

    /**
     * 
     * Add to history table that the user with the given id has
     * read the post with the given id(if it does not already exist)
     * and update the recommendation table.
     * 
     */
    public static function add_user_post_history($userid, $postid) {
        global $wpdb;

        $historytable = $wpdb->prefix . "recommendation_history";
        $listtable = $wpdb->prefix . "recommendation_list";

        // create sql query
        // insert user-post relationship if it doesn't already exist
        $sql = "INSERT INTO " . $historytable . " (user,post)";
        $sql .= "SELECT * FROM (SELECT " . $userid . "," . $postid . ") AS tmp";
        $sql .= " WHERE NOT EXISTS (";
        $sql .= "SELECT user,post FROM " . $historytable . " WHERE user=" . $userid . " AND post=" . $postid;
        $sql .= ") LIMIT 1;";

        // insert to history table
        $wpdb->query($sql);


        // if the given post is in the given user's recommendation list table,remove it
        $wpdb->query("DELETE FROM " . $listtable . " WHERE user=" . $userid . " AND post=" . $postid);
    }

    /**
     * 
     * Get from database all the posts that the given user has read.
     * 
     */
    public static function get_history($userid) {
        global $wpdb;

        $historytable = $wpdb->prefix . "recommendation_history";


        // delete all user's recommendations from db
        $results = $wpdb->get_results("SELECT post FROM " . $historytable . " WHERE user=" . $userid);

        // put all post ids in an array
        $postsread = array();
        foreach ($results as $record) {
            array_push($postsread, $record->post);
        }


        return $postsread;
    }

    /**
     * 
     * Get all recommendations for the given user with the highest score being first.
     * 
     */
    public static function get_recommendations($userid, $lang) {
        global $wpdb;

        $listtable = $wpdb->prefix . "recommendation_list";


        //Get all user's recommendations from db
        $results = $wpdb->get_results("SELECT * FROM " . $listtable 
                . " WHERE user=" . $userid 
                . " AND lang LIKE '" . $lang."'" 
                . " ORDER BY score DESC");
        
        // put all post ids in an array
        $recommendations = array();
        foreach ($results as $record) {
            array_push($recommendations, $record->post);
        }


        return $recommendations;
    }

    /**
     * 
     * Add to recommendations list table that the given posts have been recommended.
     * 
     * @param int $userid
     * @param array $recommended Associative array with key being the recommendated post's id and value being the recommendation score.
     */
    public static function update_recommendations($userid, $recommended, $lang) {
        global $wpdb;

        $listtable = $wpdb->prefix . "recommendation_list";


        // delete all user's recommendations from db
        $wpdb->query("DELETE FROM " . $listtable . " WHERE user=" . $userid);

        // insert user's recommended posts
        foreach ($recommended as $postid => $score) {
            // create sql query
            $sql = "INSERT INTO " . $listtable 
                    . " (user,post,score,lang) VALUES (" 
                    . $userid . "," 
                    . $postid . "," 
                    . $score . "," 
                    ."'". $lang . "')";

            // insert recommendation
            $wpdb->query($sql);
        }
    }

    /**
     * 
     * Delete from all tables the given user's records.
     * 
     */
    public static function delete_user($userid) {
        global $wpdb;

        $historytable = $wpdb->prefix . "recommendation_history";
        $listtable = $wpdb->prefix . "recommendation_list";
        $timestampstable = $wpdb->prefix . "recommendation_lasttimestamp";


        // delete user's history from db
        $wpdb->query("DELETE FROM " . $historytable . " WHERE user=" . $userid);

        // delete all user's recommendations from db
        $wpdb->query("DELETE FROM " . $listtable . " WHERE user=" . $userid);

        // delete user's last recommendation timestamp
        $wpdb->query("DELETE FROM " . $timestampstable . " WHERE user=" . $userid);
    }

    /*
     * 
     * Get the last recommendation's timestamp for the given user.
     * If there is not timestamp for this user, NULL is returned.
     * 
     */

    public static function get_last_timestamp($userid) {
        global $wpdb;

        $timestampstable = $wpdb->prefix . "recommendation_lasttimestamp";

        // get last recommendation's timestamp
        $result = $wpdb->get_results($q = "SELECT * FROM " . $timestampstable . " WHERE user=" . $userid);

        return $result[0]->last_recommendation;
    }

    /**
     * 
     * Update last recommendation's timestamp for the given user.
     * 
     */
    public static function update_last_timestamp($userid, $timestamp) {
        global $wpdb;

        $timestampstable = $wpdb->prefix . "recommendation_lasttimestamp";


        // update timestamp query
        $sql = "INSERT INTO " . $timestampstable . " (user, last_recommendation) VALUES(" . $userid . "," . $timestamp . ") 
                ON DUPLICATE KEY UPDATE user=" . $userid . " , last_recommendation=" . $timestamp . ";";

        // insert or update timestamp
        $wpdb->query($sql);
    }

}
