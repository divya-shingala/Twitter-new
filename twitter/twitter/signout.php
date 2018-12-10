<?php


ini_set('display_errors', 1); 
error_reporting(E_ALL);

session_start();

// include common files

require './twitter/twitteroauth/autoload.php';
include 'common.inc.php';
use Abraham\TwitterOAuth\TwitterOAuth;
session_destroy();

$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET_KEY, TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET);

$connection->post('account/end_session');

header('Location: '.ROOT_PATH);
exit;

?>