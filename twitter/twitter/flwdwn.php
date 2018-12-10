<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);


require './twitter/twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
require_once("controller.php");


//cron format cursor screen_name email

$path = $argv[0];
$format = $argv[1];
$int_cursor = $argv[2];
$screen_name = $argv[3];
$email = $argv[4];

// var_dump($argv);
// var_dump($argc);

// echo "test";
//  $path1 = $argv[0];
//  echo $path;

// $format=$argv[1];
// $int_cursor = $argv[2];
// $screen_name=$argv[3];
// $email = $argv[4];



    // $path = "/home/bme2kggy0iwu/public_html/twiproj/flwdwn.php";
    // $format="csv";
    // $int_cursor = "-1";
    // $screen_name= "aakashnagar9797";
    // $email = "niraj.visana@gmail.com";



shell_exec("echo '$argv[0] $argv[1] $argv[2] $argv[3] $argv[4]' >> /home/x4k54hildfhw/public_html/twitter/argv.txt");
    //shell_exec("sudo touch /var/www/html/rtcamp/tmp");


//die();

define("ID", "FollowerName");
define("VALUE", "FollowerScreenName");
define("FILE_NAME", $screen_name);
define("ROOT", "Follower");
$cursor = $int_cursor;


if (!isset($_SESSION['access_token']))
{
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
    $request_token = $connection->oauth('oauth/request_token', array('oauth_callback'=>OAUTH_CALLBACK));
    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
    $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
} else
{
    $accesstoken = $_SESSION['access_token'];
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $accesstoken['oauth_token'], $accesstoken['oauth_token_secret']);
}

if ($cursor != 0)
{

    // $flwdwn=$connection->get('followers/list',["screen_name"=>$_SESSION['flwdwn'],"count"=>200,"cursor"=>$cursor]);
    $td_t = array();
    for ($i = 1; $cursor != 0; $i++) { 
        $flwdwn = $connection->get('followers/list', ["screen_name"=>$screen_name, "count"=>200, "cursor"=>$cursor]);
        if (!isset($flwdwn->users))
        {
            $reading = fopen(__DIR__.'/cron.txt', 'r');
            $writing = fopen(__DIR__.'/cron.tmp', 'w');
            
            $replaced = false;
            
            while (!feof($reading)) {
            $line = fgets($reading);
            if (stristr($line, "*/15 * * * * php $path $format $int_cursor $screen_name $email ")) {
                $line = "*/15 * * * * php $path $format $cursor $screen_name $email \n";
                $replaced = true;
            }
            fputs($writing, $line);
            }
            fclose($reading); fclose($writing);
            // might as well not overwrite the file if we didn't replace anything
            if ($replaced) 
            {
            rename(__DIR__.'/cron.tmp', __DIR__.'/cron.txt');
            } else {
            unlink(__DIR__.'/cron.tmp');
            }
            shell_exec("chmod 777 ".__DIR__.'/cron.txt');
            $cmd = "sudo bash ".__DIR__."/cron.sh";
            shell_exec($cmd);
            break;
        }
        foreach ($flwdwn->users as $f) {
            $tmp = new stdClass;
            $tmp->id_str = $f->name;
            $tmp->text = $f->screen_name;
            array_push($td_t, [$tmp]);
        }
        $cursor = $flwdwn->next_cursor;
    }

    if ($format == "xml")
    {
        if ($int_cursor == -1)
        {
            $file = new SimpleXMLElement('<xml/>');
        } else
        {
            $file = simplexml_load_file(__DIR__."/".FILE_NAME.'.'.$format);
        }
        
        foreach ($td_t as $rows) {
            foreach ($rows as $row) {
                $tid = $file->addChild(ROOT);
                $tid->addChild(ID, $row->id_str);
                $tid->addChild(VALUE, $row->text);
            }
        }

        $file->saveXML(__DIR__."/".FILE_NAME.'.'.$format);
    } else if ($format == "json")
    {
        $file = fopen(__DIR__."/".FILE_NAME.'.'.$format, 'a');
        
        $result = json_decode(file_get_contents(__DIR__."/".FILE_NAME.'.'.$format), true);
        foreach ($td_t as $rows) {
            foreach ($rows as $row) {
                array_push($result, [ID=>$row->id_str, VALUE=>$row->text]);
            }
        }

        fwrite($file, json_encode($result));
        fclose($file);
    } else if ($format == "xls")
    {

        $file = fopen(__DIR__."/".FILE_NAME.'.'.$format, 'a');

        if ($int_cursor == -1)
        {
            fputcsv($file, array(ID, VALUE));
        }

        foreach ($td_t as $rows)
        {
            foreach ($rows as $row) {
                fputcsv($file, array($row->id_str, $row->text));
            }
        }

        fclose($file);
    } else
    {
        $file = fopen(__DIR__."/".FILE_NAME.'.'.$format, 'a');
        
        if ($int_cursor == -1)
        {
            fputcsv($file, array(ID, VALUE));
        }

        foreach ($td_t as $rows)
        {
            foreach ($rows as $row) {
                fputcsv($file, array($row->id_str, $row->text));
            }
        }

        fclose($file);
        
    }
}

if ($cursor == 0)
{
    require './PHPMailer-master/src/Exception.php';
    require './PHPMailer-master/src/PHPMailer.php';
    require './PHPMailer-master/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    $mail->IsMail();
    $mail->Host = 'relay-hosting.secureserver.net';
    $mail->Port = 25;
    $mail->SMTPAuth = false;
    $mail->SMTPSecure = false;
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->IsHTML(true);
    $mail->Username = "divudpatel89@gmail.com";  
    $mail->Password = "95175385245669495192";
    $mail->SetFrom('twitter@webdevia.xyz', 'Twitter-data');
    $mail->Subject = "Followers Data";
    $mail->AltBody = "";
    $mail->AddAddress($email);
    $mail->MsgHTML("Your requested follower data is in file attached below");
    $mail->AddAttachment(__DIR__."/".FILE_NAME.'.'.$format);

        if (!$mail->Send()) {
        echo "Mailer Error: ".$mail->ErrorInfo;
        } else {
        echo "Message has been sent";
        } 
       
    if ($mail->Send()) {
        $reading = fopen(__DIR__.'/cron.txt', 'r');
        $writing = fopen(__DIR__.'/cron.tmp', 'w');
        
        $replaced = false;
        
        while (!feof($reading)) {
        $line = fgets($reading);
        if (stristr($line, "*/15 * * * * php $path $format $int_cursor $screen_name $email ")) {
            $line = "";
            $replaced = true;
        }
        fputs($writing, $line);
        }
        fclose($reading); fclose($writing);
        // might as well not overwrite the file if we didn't replace anything
        if ($replaced) 
        {
        rename(__DIR__.'/cron.tmp', __DIR__.'/cron.txt');
        } else {
        unlink(__DIR__.'/cron.tmp');
        }
        shell_exec("chmod 777 ".__DIR__.'/cron.txt');
        $cmd = "sudo bash ".__DIR__."/cron.sh";
        shell_exec($cmd);
        unlink(__DIR__."/".FILE_NAME.'.'.$format);
    } else {
        echo "Error while sending mail : ".$mail->ErrorInfo;
    }

}
?>
