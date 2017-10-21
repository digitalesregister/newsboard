<?php
session_start();
header('Content-Type: application/json');

require './config.php';
require './apiClient.php';

$response = (object) array(
    'error' => null,
    'data' => null,
);

function sendResponse ($response) {
    print json_encode($response);
    die();
}

if (!isset($_SESSION['token']) || !isset($_SESSION['refresh_token'])) {
    $response->error = 403;
    sendResponse($response);
}

$user = (object) array(
    'user_id' => $_SESSION['user_id'],
    'token' => $_SESSION['token'],
    'refresh_token' => $_SESSION['refresh_token'],
);

// get room bookings
$bookings = makeAPICall('GET', 'room/upcoming_bookings');

// get substitutions
$substitutions = makeAPICall('GET', 'lesson/upcoming_substitutions');

$response->data = (object) array(
    'substitutions' => $substitutions,
    'rooms' => $bookings,
);
sendResponse($response);
