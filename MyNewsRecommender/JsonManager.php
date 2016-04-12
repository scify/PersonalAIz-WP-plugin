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

require_once('JSONObject.php');

class JsonManager {

    /**
     * 
     * Get a json encoded JSONObject. 
     * 
     * @param SettingsManager $settingsmanager
     * @param WP_Post $post
     * @param string $language
     * @param boolean $recommended
     * @return json
     */
    public static function encodeToJson($settingsmanager, $post, $language, $recommended) {

        // create JSONObject
        $timestamp = ((int) current_time('timestamp'));
        $jsonobject = JsonManager::getJSONObject($settingsmanager, $post, $language, $recommended, $timestamp);

        // filter empty or null values
        $jsonobject = JsonManager::filterNullOrEmpty($jsonobject);

        // return json encoded object
        return json_encode($jsonobject);
    }

    /**
     * 
     * Get a json encoded JSONObject collection.
     * 
     * @param SettingsManager $settingsmanager
     * @param array $posts Array of WP_Posts.
     * @param string $language
     * @return json
     */
    public static function encodeToJsonCollection($settingsmanager, $posts, $language) {

        $objectscollection = array();

        foreach ($posts as $post) {
            // create JSONObject
            $jsonobject = JsonManager::getJSONObject($settingsmanager, $post, $language, NULL, NULL);

            // filter empty or null values
            $jsonobject = JsonManager::filterNullOrEmpty($jsonobject);   //(object)array_filter((array)$jsonobject,function($value){return !is_null($value);});
            // add to collection
            array_push($objectscollection, $jsonobject);
        }

        // encode to json
        $jsonobjectcollection = json_encode($objectscollection);

        return $jsonobjectcollection;
    }

    /**
     * 
     * Return a JSONObject from the given values and plugin settings.
     * 
     * @param SettingsManager $settingsmanager
     * @param WP_Post $post
     * @param string $language
     * @param boolean $recommended
     * @param long $timestamp
     * @return JSONObject
     */
    private static function getJSONObject($settingsmanager, $post, $language, $recommended, $timestamp) {

        // this array will hold the title and the content of the post
        $text = array();

        // get post title if it is enabled from the feed options 
        if ($settingsmanager->getTitleCheck()) {
            array_push($text, strip_tags($post->post_title));
        }

        // get post content if it is enabled from the feed options
        if ($settingsmanager->getContentCheck()) {
            array_push($text, strip_tags($post->post_content));
        }

        // get tag names if they are enabled from the feed options
        $posttags = wp_get_post_tags($post->ID);
        $tags = array();
        if ($posttags) {
            foreach ($posttags as $tag) {
                array_push($tags, $tag->name);
            }
        }
        if (!$settingsmanager->getTagsCheck()) {
            $tags = NULL;
        }

        // get categories if they are enabled from the feed options
        $categories = array();


        if ($settingsmanager->getCategoriesCheck()) {
            $cats = get_the_category($post->ID);
            foreach ($cats as $c) {
                array_push($categories, $c->name);
            }
        } else {
            $categories = NULL;
        }

        // create JSONObject
        return new JSONObject($post->ID . "", $language, $recommended, $timestamp, $text, $categories, $tags);
    }

    private static function filterNullOrEmpty($value) {
        return (object) array_filter((array) $value, function($value) {
                    if (is_array($value) && count($value) == 0) {
                        return FALSE;
                    }
                    return !is_null($value);
                });
    }

}
