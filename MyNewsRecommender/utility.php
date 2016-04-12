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

function postRequest($url, $postfields) {

    // initialize a curl session
    $curl = curl_init($url);

    // set options
    curl_setopt($curl, CURLOPT_POST, 1);
    if (!is_null($postfields)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postfields));
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // send request to the given url ang get response
    $response = curl_exec($curl);

    // if there is an error, throw an exception
    if (curl_errno($curl) || strlen($response) == 0) {
        throw new Exception(curl_error($curl));
    }

    // close curl session
    curl_close($curl);


    return $response;
}
