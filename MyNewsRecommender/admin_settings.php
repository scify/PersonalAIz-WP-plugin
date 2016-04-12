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
<?php
require_once('SettingsManager.php');
require_once('utility.php');
require_once('MyNewsRecommender.php');
?>

<?php
$settingsmanager = new SettingsManager();

// check if form has been submitted
if ($_POST['submit'] != NULL) {
    // get form field values
    $ip = $_POST['ip'];
    $port = $_POST['port'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $apikey = $_POST['apikey'];
    $authmethod = $_POST['authmethod'];
    $titlecheck = isset($_POST['titlecheck']);
    $contentcheck = isset($_POST['contentcheck']);
    $tagscheck = isset($_POST['tagscheck']);
    $categoriescheck = isset($_POST['categoriescheck']);
    $initCheck = isset($_POST['initCheck']);
    $articlescount = $_POST['articlescount'];
    $cacheDuration = $_POST['cacheDuration'];
    $lasthourscount = $_POST['lasthourscount'];

    //initUsers
    if ($initCheck){
        $MyNewsRecommender = new MyNewsRecommender();
        $MyNewsRecommender->initUsers();
        $initCheck=0;
    }

    //
    // MUST CHECK AUTHENTICATION USING API KEY
    //

        // update settings only if connection attributes,all weight values and recommendation settings are valid
    $connectionvalid = TRUE;
    if (is_numeric($articlescount) &&
            floatval($articlescount) == intval(floatval($articlescount)) &&
            is_numeric($lasthourscount) &&
            floatval($cacheDuration) == intval(floatval($cacheDuration)) &&
            floatval($lasthourscount) == intval(floatval($lasthourscount))) {
        $settingsmanager->setIp($ip);
        $settingsmanager->setPort($port);
        $settingsmanager->setUsername($username);
        $settingsmanager->setPassword($password);
        $settingsmanager->setApiKey($apikey);
        $settingsmanager->setAuthenticationMethod($authmethod);
        $settingsmanager->setTitleCheck($titlecheck);
        $settingsmanager->setContentCheck($contentcheck);
        $settingsmanager->setTagsCheck($tagscheck);
        $settingsmanager->setCategoriesCheck($categoriescheck);
        $settingsmanager->setArticlesCount($articlescount);
        $settingsmanager->setCacheDuration($cacheDuration);
        $settingsmanager->setLastHoursCount($lasthourscount);
    } else {
        $result = "<div id='setting-error-invalid_admin_email' class='error settings-error notice is-dismissible'> 
                        <p><strong>Non-integer values given for <i>Recommendation Settings</i>.</strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
    }


    // settings saved, set the appropriate result message
    if ($result == NULL) {
        $result = "<div id='setting-error-settings_updated' class='updated settings-error notice is-dismissible'> 
                    <p><strong>Settings saved.</strong></p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
    }
} else {
    // get stored values for all fields
    $ip = $settingsmanager->getIp();
    $port = $settingsmanager->getPort();
    $username = $settingsmanager->getUsername();
    $password = $settingsmanager->getPassword();
    $apikey = $settingsmanager->getApiKey();
    $authmethod = $settingsmanager->getAuthenticationMethod();
    $titlecheck = $settingsmanager->getTitleCheck();
    $contentcheck = $settingsmanager->getContentCheck();
    $tagscheck = $settingsmanager->getTagsCheck();
    $categoriescheck = $settingsmanager->getCategoriesCheck();
    $articlescount = $settingsmanager->getArticlesCount();
    $cacheDuration = $settingsmanager->getCacheDuration();
    $lasthourscount = $settingsmanager->getLastHoursCount();

    $result = "";
}

if ($authmethod == 1) {
    $userpasschecked = "";
    $apikeychecked = "checked";
} else {
    $userpasschecked = "checked";
    $apikeychecked = "";
}
?>


<div class="wrap">
    <div id="icon-plugins" class="icon32"></div>
    <h2>PersonalAIz Recommendation Plugin Settings</h2>

    <p><p>

        <?php echo $result; ?>

    <form method="post" action="<?= $_SERVER['REQUEST_URI']; ?> ">
        <h2 style="margin-top:50px;">Server Setup</h2>	
        <table class="form-table" style="margin-left:30px;">
            <tr><th>Ip</th><td><input type="text" value="<?php echo $ip; ?>" name="ip" /></td></tr>
            <tr><th>Port</th><td><input type="text" value="<?php echo $port; ?>" name="port" /></td></tr>
            <tr><th>Username</th><td><input type="text" value="<?php echo $username; ?>" name="username" /></td></tr>
            <tr><th>Password</th><td><input type="text" value="<?php echo $password; ?>" name="password" /></td></tr>
            <tr><th>API Key</th><td><input type="text" value="<?php echo $apikey; ?>" name="apikey" /></td></tr>
            <tr><th>Authentication method</th><td>
                    <input type="radio" <?php echo $userpasschecked ?> name="authmethod" id="userpassradio" value="0"/>
                    <label for="userpassradio">Username - Password</label><br>
                    <input type="radio" <?php echo $apikeychecked ?> name="authmethod" id="apikeyradio" value="1"/>
                    <label for="apikeyradio">Api Key</label>
                </td></tr>
        </table>
        <h2 style="margin-top:50px;">Feed Options</h2>	    	
        <table class="form-table" style="margin-left:30px;">
            <tr valign="top"><th scope="row"><label><input type="checkbox" <?php echo ($titlecheck == 1 ? "checked" : ""); ?> name="titlecheck">Title</label></th></tr>
            <tr valign="top"><th scope="row"><label><input type="checkbox" <?php echo ($contentcheck == 1 ? "checked" : ""); ?> name="contentcheck">Content</label></th></tr>
            <tr valign="top"><th scope="row"><label><input type="checkbox" <?php echo ($categoriescheck == 1 ? "checked" : ""); ?> name="categoriescheck">Categories</label></th></tr>
            <!--<tr valign="top"><th scope="row"><label><input type="checkbox"--> 
            <?php // echo ($tagscheck == 1 ? "checked" : ""); ?> 
            <!--name="tagscheck">Tags</label></th></tr>-->
        </table>
        <h2 style="margin-top:50px;">Recommendation Settings</h2>	    	
        <table class="form-table" style="margin-left:30px;">
            <tr valign="top"><th scope="row">Number of recommended articles</th><td><input type="text" name="articlescount" value="<?php echo $articlescount; ?>"/></td></tr>
            <tr valign="top"><th scope="row">Cache duration in minutes</th><td><input type="text" name="cacheDuration" value="<?php echo $cacheDuration; ?>"/></td></tr>
            <tr valign="top"><th scope="row">Number of last hours to show articles from</th><td><input type="text" name="lasthourscount" value="<?php echo $lasthourscount; ?>"/></td></tr>
        </table>
        <table class="form-table" style="margin-left:30px;">
            <tr valign="top"><th scope="row"><label><input type="checkbox" <?php echo ($initCheck == 1 ? "checked" : ""); ?> name="initCheck">Add Users</label></th></tr>
        </table>

        <input type='submit' name="submit" value="Save Changes" class='button-primary' id='submitbutton' style="margin-top:50px;margin-left:330px;"/>
    </form>
</div>
