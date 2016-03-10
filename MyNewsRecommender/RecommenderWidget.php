<?php
require_once('JsonManager.php');
require_once('DbManager.php');

class RecommenderWidget extends WP_Widget {

    private $settingsmanager;

    function __construct() {
        parent::__construct('RecommenderWidget', __('Recommended News', 'text_domain'), array('description' => __('Displays the recommended news.', 'text_domain')));

        // create new SettingsManager object
        $this->settingsmanager = new SettingsManager();
    }

    function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <?php
    }

    function update($new_instance, $old_instance) {
        // update widget settings
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    function widget($args, $instance) {

        // show widget in frontend
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        // show the menu of the recommended posts
        if (is_user_logged_in()) {

            // get an array of recommended posts
            $recommendations = $this->get_recommended_posts();

            // show menu
            if (count($recommendations) == 0) {
                echo "There are no recommended posts!";
            } else {

                echo '<div class="list-group">';

                foreach ($recommendations as $postid) {
                    $post = get_post($postid);
                    $category = get_the_category($postid);

                    echo '<a href="' . get_permalink($post->ID)
                    . '" class="list-group-item">'
                    . '<h5 class="list-group-item-heading">'
                    . $category[0]->name
                    . '</h5>'
                    . '<p class="list-group-item-text">'
                    . $post->post_title
                    . '</p>'
                    . '</a>';
                }
                echo '</div>';
            }
        }

        echo $after_widget;
    }

    /*
     * 
     * Returns an array of recommended posts or an empty array if no posts are recommended.
     * 
     */

    private function get_recommended_posts() {

        // check if a recommendation is required because the last one has expired
        $recommendationexpired = $this->recommendation_expired(wp_get_current_user()->ID);

        $lang = get_locale();
        if ($lang === "en_US") {
            $lang = "en";
        }

        // get recommendations from database
        $recommendations = DbManager::get_recommendations(wp_get_current_user()->ID, $lang);
        // if recommendation has expired or there are not recommendations in database, ask for one
        if ($recommendationexpired || count($recommendations) == 0) {

            // get the ids of the posts that the current user has read
            $postsread = DbManager::get_history(wp_get_current_user()->ID);


            // get recent posts and filter them with the posts the current user has read
            // as long as there are no posts keep filtering with older posts
            // until all the posts of up to 4 * recommendation_window ago have been tested
            for ($i = 1; $i <= 4; $i++) {

                // get how many hours ago to show posts from, from plugin's settings
                $lasthours = $this->settingsmanager->getLastHoursCount();

                // get the ids of the posts that published in the last '$lasthours' hours,
                // or get all posts if '$lasthours' is -1
                if ($lasthours == -1) {
                    $recentposts = $this->get_recent_posts(NULL);
                } else {
                    $recentposts = $this->get_recent_posts($i * $lasthours);
                }

                // from the recent posts filter those that have already been read by the current user
                $posts = array_diff($recentposts, $postsread);

                // if there are posts, break
                if (count($posts) > 0) {
                    break;
                }
            }

            // this user has already read all recent posts
            // there are no posts to recommend
            if (count($posts) == 0) {
                return array();
            }


            // get recommendations from Recommendation Engine
            $recommendations = $this->recommend($posts);


            // check if recommendation failed
            if (count($recommendations) == 0) {
                return array();
            }

            // update recommendations in databases
            DbManager::update_recommendations(wp_get_current_user()->ID, $recommendations, $lang);

            // update last recommendation's timestamp for this user
            DbManager::update_last_timestamp(wp_get_current_user()->ID, (int) current_time('timestamp'));

            // put all recommendations' ids in array
            $recommendations = array_keys($recommendations);

            // return recommendations' ids
            return array_slice($recommendations, 0, $this->settingsmanager->getArticlesCount());
        }

        // return the recommendations from database
        return array_slice($recommendations, 0, $this->settingsmanager->getArticlesCount());
    }

    /**
     * 
     * Check if the last recommendation has expired and a new one
     * is required regardless of the recommendations list state.
     * 
     */
    private function recommendation_expired($userid) {

        //get cache duration from settings
        $cacheDuration = $this->settingsmanager->getCacheDuration();

        //if cache Duration is >0
        if ($cacheDuration > 0) {
            // get user's last recommendation timestamp
            $lasttimestamp = DbManager::get_last_timestamp($userid);

            // if no recommendation has been done there is no need to check the timestamp
            if (!is_null($lasttimestamp)) {
                // get the current timestamp
                $currenttimestamp = (int) current_time('timestamp');

                // get cache window(in minutes) from wordpress options and convert it to seconds
                $cacheWindow = $cacheDuration * 60;
                // check if '$recommendationwindow' hours passed without a recommendation
                if ($currenttimestamp < $lasttimestamp + $cacheWindow) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     *
     * Get recommendation scores from Recommendation Engine.
     *  
     * @param array $post_ids Indexed array of the posts' ids to ask for recommendation
     * @return array Associative array of the recommended posts with the id of the post as the key and the recommendation score as the value.
     *
     */
    public function recommend($post_ids) {

        if (is_null($post_ids)) {
            return array();
        }


        // get all posts from the given post ids
        $posts = array();
        foreach ($post_ids as $id) {
            array_push($posts, get_post($id));
        }

        // current user's id
        $userid = wp_get_current_user()->ID;

        // get connection info and user credentials
        $ip = $this->settingsmanager->getIp();
        $port = $this->settingsmanager->getPort();
        $username = $this->settingsmanager->getUsername();
        $password = $this->settingsmanager->getPassword();

        // build request url
        $url = $ip . ":" . $port . "/PersonalAIz/api/recengine/" . $username . "%7c" . $password;
        $url .= "/getRecommendation/" . $userid;

        // get json object collection    
        $lang = get_locale();
        if ($lang === "en_US") {
            $lang = "en";
        }
        $jsonobjectcollection = JsonManager::encodeToJsonCollection($this->settingsmanager, $posts, $lang);


        // make post request
        $response = postRequest($url, array('JSONObjectList' => $jsonobjectcollection));

        // decode json
        $responsejson = json_decode($response, true);


        // check if recommendation failed
        if ($responsejson['outputMessage'] != "Get recommendation Complete") {
            return array();
        }


        // this is an associative array with the id of the post as the key
        // and the recommendation score as the value
        $postscores = $responsejson['output'];


        return $postscores;
    }

    /**
     * 
     * Get the posts that published in the last '$hours' hours.
     * Return all posts if the hours given is NULL.
     * 
     */
    private function get_recent_posts($hours) {

        if (is_null($hours)) {
            $args = 'numberposts=-1';
        } else {
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'post',
                'orderby' => 'date',
                'order' => 'DESC',
                'suppress_filters' => 0,
                'date_query' => array(array('after' => $hours . ' hours ago'))
            );
        }

        $posts = get_posts($args);
        wp_reset_query();

        // get the ids of the posts
        $recentposts = array();
        foreach ($posts as $post) {
            array_push($recentposts, $post->ID);
        }

        return $recentposts;
    }

}
