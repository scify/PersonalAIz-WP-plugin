<?php

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
