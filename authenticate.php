<?php

session_start();
require './config.php';
require './apiClient.php';

if (!isset($_REQUEST['code'])) {
    print 'code is required';
    die();
}
$code = $_REQUEST['code'];
$result = fetchToken($code);

$_SESSION['user_id'] = $result->user_id;
$_SESSION['token'] = $result->token;
$_SESSION['refresh_token'] = $result->refresh_token;

//redirect so we don't have the code in the url anymore
$user = makeAPICall('GET', 'me', $result);
if ($user == null) {
    print 'something went wrong';
    die();
}


header('Location: index.html');
