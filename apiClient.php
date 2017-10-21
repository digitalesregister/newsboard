<?php

function fetchToken ($code) {
    $result = makeSecretAPICall('POST', 'token', array(
        'code' => $code,
    ));

    return $result;
}

function refreshToken() {
    $result = makeSecretAPICall('POST', 'refresh_token', array(
        'user_id' => $_SESSION['user_id'],
        'refresh_token' => $_SESSION['refresh_token'],
    ));

    $_SESSION['token'] = $result->token;

    return $result;
}

function makeSecretAPICall($method, $url, $payload) {
    $data = json_encode($payload);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, API_URL.$url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'API-CLIENT-ID: '.CLIENT_ID,
        'API-SECRET: '.API_SECRET,
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $result = json_decode($response);

    curl_close($curl);
    return $result;
}

function makeAPICall($method, $url, $payload = null, $try=0) {

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, API_URL.$url);
    if ($method == 'POST') {
        curl_setopt($curl, CURLOPT_POST, 1);
        $data = json_encode($payload);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    } else {
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'API-CLIENT-ID: '.CLIENT_ID,
        'API-TOKEN: '.$_SESSION['token'],
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    $result = json_decode($result);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    // refresh the token if we get a 400 status code
    if ($status == 403) {
        if ($try == 0) {
            refreshToken();
            return makeAPICall($method, $url, $payload, 1);
        } else {
            return null;
        }
    }
    return $result;
}
