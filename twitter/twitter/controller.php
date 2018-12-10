<?php
ini_set("display_errors", 1);
session_start();
//require "autoload.php";
//include_once "googleloginfunc.php";
include 'common.inc.php';
require './twitter/twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
    $user = $flwdwn = null;

if (isset($_REQUEST['flwdwn']))
{
    $token = $_SESSION['access_token'];
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $token['oauth_token'], $token['oauth_token_secret']);
    $flwdwn = $connection->get('users/lookup', ["screen_name"=>$_REQUEST['flwdwn']]);

    if (isset($flwdwn->errors))
    {
        echo "No User Found";
    } else
    {
        echo "Success";
        $_SESSION['flwdwn'] = $flwdwn[0]->screen_name;
    }
}

if (isset($_REQUEST['format']))
{
    $file = fopen("cron.txt", "a");
    $email = $_REQUEST['email'];
    $format = $_REQUEST['format'];
    $str = "*/15 * * * * /usr/local/bin/php ".getcwd()."/flwdwn.php ".$format." -1 ".$_SESSION['flwdwn']." ".$email." \n";
    $result = fwrite($file, $str);
    
    if ($result == true)
    {
            echo shell_exec('sh ./cron.sh');
            $output = exec('crontab -l');
            echo "cron Running output ==> ".$output;
    }
}

//}

?>	