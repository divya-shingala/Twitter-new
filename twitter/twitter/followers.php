<?php
require './twitter/twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

$oauth_access_token = "1025696760574832641-lHHELCbCaox16QTt0Gube3ODWGZDNI";
$oauth_access_token_secret = "LfsE7QvRXWvL5qlU0GMIrwEwiFJDH3BghPGVqAusYlB2u";
$consumer_key = "FRjhLxejrkZYix6Dsbyo3sqON";
$consumer_secret = "xKoYzZiAD6PgGM8a2z4QNflMj2QAepVRFta22jFwkODrdO7aUM";

$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_access_token, $oauth_access_token_secret);	

if (isset($_SESSION['access_token']))
{
    $access_token = $_SESSION['access_token'];
    //$connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token['oauth_token'],$access_token['oauth_token_secret']);
    $follower = $connection->get('followers/list', ["count"=>200]);
    $follower_name = array();
    if (isset($follower->users))
    {
        foreach ($follower->users as $f) {
            array_push($follower_name, ["name"=>$f->name, "screen_name"=>$f->screen_name, "profile"=>$f->profile_image_url_https]);
        }
    }
    echo $follower_name = json_encode($follower_name);
} else
{
    echo "not set session";
}

file_put_contents('users.txt', $follower_name);
?>