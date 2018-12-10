 <?php
//keys and tokens

$consumer_key = 'VYjyS0kyIAQtO9wMmH9Xcc73k';
$consumer_secret = '5fEwBA8OO3mjSkKhbeTfu6W4tXY81gGaO8EBUi5FWi7xGEGX92';
$access_token = '531047460-Hk4mlH6pyVmqjtdKzkqFgOW5TD51XYBAKN3poVhI';
$access_token_secret = 'JnVODhhW1beKVYE4fCeKg2hbBZFPmh5Px4pCOo9YHLqnS';

//including librray
require "twitter/twitteroauth/autoload.php";
Use Abraham\TwitterOAuth\TwitterOAuth;

//connect to API
$connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

$content = $connection->get("account/verify_credentials");

//get tweets

$statuses = $connection->get("statuses/home_timeline", ["count" => 1, "exclude_replies" =>true]);

print_r($statuses);


    ?>