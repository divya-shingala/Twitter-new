<?php

session_start();

ini_set('display_errors', 1); 
error_reporting(E_ALL);
require './twitter/twitteroauth/autoload.php';
include 'common.inc.php';
use Abraham\TwitterOAuth\TwitterOAuth;

$today = time();

if (!isset($_SESSION['access_token'])) {
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET); //establishing connection

    //obtaining request token
    $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));

    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

    //above code is storing data into sessions

    $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
    // above is the authentication url
    echo "<a href='$url'><img src='twitter-login-blue.png' style='margin-left:4%; margin-top: 4%'></a>";
} 
else {

    $access_token = $_SESSION['access_token'];

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);	

    $login_user_details = $connection->get('account/verify_credentials'); //user details if we need any

        //print_r ($login_user_details);
		
            //getting user's data and storing them in variables	

            $login_user_id = $login_user_details->id;
            $login_user_name = $login_user_details->name;
            $login_user_screen_name = $login_user_details->screen_name;
            $login_user_location = $login_user_details->location;
            $login_user_friends_count = $login_user_details->friends_count;
            $login_user_profile_image_url = $login_user_details->profile_image_url;
            $login_user_profile_bgcolor = $login_user_details->profile_background_color;
            $login_user_profile_bgimage_url = $login_user_details->profile_background_image_url;
			


            //fetching tweets
            $login_user_homeline_tweets = $connection->get('statuses/home_timeline', array('screen_name' => $_SESSION['access_token']['screen_name'], 'count' => 10, 'include_rts' => true, 'include_entities' => true));


            //fetching followers details, scrren name and ids and storing them in array,change No_OF_FOLLOWERS constant in config file to change count


            $login_user_all_follower_lists = $connection->get('followers/ids');
            $login_user_all_follower_ids = $login_user_all_follower_lists->ids;

            if (count($login_user_all_follower_ids) >= 10) {
                $random_keys = array_rand($login_user_all_follower_ids, NO_OF_FOLLOWERS);
                foreach ($random_keys as $key) {
                    $login_user_follower_ids[] = $login_user_all_follower_ids[$key];								
                }
				

				
                if (isset($login_user_follower_ids) && count($login_user_follower_ids) > 0) {
                    foreach ($login_user_follower_ids as $follower_ids) {
                        $friend_realtion_details = array();
                        $friend_realtion_details = $connection->get('friendships/show', array('target_id' => $follower_ids));
	
                        if (isset($friend_realtion_details->relationship->target->screen_name)) {
                            $friend_screen_name_details[] = array('id'=>$follower_ids, 'screen_name'=>$friend_realtion_details->relationship->target->screen_name);
							
                            $friend_name_details[] = array('name'=>$follower_ids, 'screen_name'=>$friend_realtion_details->relationship->target->screen_name);

                        }
                    }
                }

				
                if (isset($friend_screen_name_details) && count($friend_screen_name_details) > 0) {
                    foreach ($friend_screen_name_details as $friend_screen_name) {						
                        $friend_details[] = $connection->get('users/show', array('user_id' => $friend_screen_name['id'], 'screen_name' => $friend_screen_name['screen_name']));
						 	
                    }
                }
            }


                ///////Following


            $login_user_all_following_lists = $connection->get('friends/ids');
            $login_user_all_following_lists = $login_user_all_following_lists->ids;

            if (count($login_user_all_following_lists) >= 10) {
                $random_keys = array_rand($login_user_all_following_lists, NO_OF_FOLLOWERS);
                foreach ($random_keys as $key) {
                    $login_user_following_ids[] = $login_user_all_following_lists[$key];								
                }
            }



				
                if (isset($login_user_following_ids) && count($login_user_following_ids) > 0) {
                    foreach ($login_user_following_ids as $following_ids) {
                        $friend_realtion_following_details = array();
                        $friend_realtion_following_details = $connection->get('friendships/show', array('target_id' => $following_ids));


						
                        if (isset($friend_realtion_following_details->relationship->target->screen_name)) {
                            $following_screen_name_details[] = array('id'=>$following_ids, 'screen_name'=>$friend_realtion_following_details->relationship->target->screen_name);

							
                            $friend_name_details[] = array('name'=>$following_ids, 'screen_name'=>$friend_realtion_following_details->relationship->target->screen_name);

                        }
                    }
                }
				
				
                if (isset($following_screen_name_details) && count($following_screen_name_details) > 0) {
                    foreach ($following_screen_name_details as $following_screen_name) {						
                        $following_details[] = $connection->get('users/show', array('user_id' => $following_screen_name['id'], 'screen_name' => $following_screen_name['screen_name']));
						 	
                    }
                }


				
                ////get followers 

    $follower = $connection->get('followers/list', ["count"=>200]);
    $follower_name = array();
    if (isset($follower->users))
    {
        foreach ($follower->users as $f) {
            array_push($follower_name, ["name"=>$f->name, "screen_name"=>$f->screen_name, "profile"=>$f->profile_image_url_https]);
        }
    }
    $follower_name = json_encode($follower_name);

		
?>
	

<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo SITE_NAME; ?></title>

        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
       
         <link rel="stylesheet" type="text/css" href="style.css" />
        <script type="text/javascript" src="./js/jquery-1.8.1.min.js"></script>

        <script src="./js/jquery-1.9.1.js"></script>
       
       	<meta name="viewport" content="width=device-width, initial-scale=1">
       
		<link rel="stylesheet" type="text/css" href="./css/jquery.bxslider.css" />
        <script type="text/javascript" src="./js/jquery.bxslider.js"></script>
  		 <script type="text/javascript">
  
 	 	var mainSlider;


 	 	$(document).ready(function(){

			  $('#txtflwdwn').blur(function(event) {
			    
			    
			    	$.ajax({
						url:"controller.php?flwdwn="+$(this).val(),
						type:"post",
						success:function(response){
							if(response == "Success")
							{
								$("#flwdwnbtn").removeClass("disabled");
							}
							else
							{
								$("#flwdwnbtn").addClass("disabled");
								alert("Not a valid screen name");

							}
						},
						failure:function(response){
							console.log(response);
						}
					});
			

			  });

		});
		
		$(document).ready(function(){		  
		    mainSlider = $('.slider4').bxSlider({
				slideWidth: 300,
				minSlides: 2,
				maxSlides: 3,
				moveSlides: 1,
				slideMargin: 10,
				auto: true
			  });
		});

		function tweetDownload(){
			if($.tirm($('select[name="download_format"]').val()) != ''){
				return true;
			}else{
				return false;
			}
		}

		function tmp(format){
			

		var person = prompt("All the Follower Data will be mailed on the given Email Address","Enter Email Address");
				if (person != "") {
					$.ajax({
						url:"controller.php?format="+format+"&email="+person,
						type:"post",
						success:function(response){
							console.log();
						},
						failure:function(response){
							console.log(response);
						}
					});			
				}
			}
		
			


		function getUserTweet(screen_name){
			if(screen_name != ''){
				$.ajax({
					url: "ajax-php/ajax-get-user-tweets.php",
					type: "POST",
					data: "screen_name="+screen_name,
					dataType: "json",
					async:false,
					success: function(resp){				
						 if(resp.ErrorCode == 0){
						 	$('#slider_tweet_content').html('Loading...');
							$('#slider_tweet_content').html(resp.Content);
							mainSlider.destroySlider();
							mainSlider = $('.slider4').bxSlider({
								slideWidth: 300,
								minSlides: 2,
								maxSlides: 3,
								moveSlides: 1,
								slideMargin: 10,
								auto: true
							});							
						 }
					}
				});
			}
			return false;
		}

	</script>
</head>
<body>
	
<div id="bodybg"></div>
<div class="header">
  <h1 style="color:green;">Twitter-Timeline Challenge</h1>

  <p><img src=<?php if (isset($login_user_profile_image_url)) { echo $login_user_profile_image_url; } ?> /></p>
  <p><?php if (isset($login_user_name)) { echo $login_user_name; } ?></p>
  <p>@<?php if (isset($login_user_screen_name)) { echo $login_user_screen_name; } ?></p>
  <a href="<?php echo ROOT_PATH.'signout.php'; ?>">Sign Out</a>
</div>

<div class="topnav">
 <hr />
</div>

<div class="row">
  <div class="column side">
  </div>
  
  <div class="column middle">
    <h2>Tweets Slider</h2>
      <?php
      if (count($login_user_homeline_tweets) > 0) {						
      ?>
      <div class="slider4" id="slider_tweet_content">
         	<?php						
                        foreach ($login_user_homeline_tweets as $homeline_tweet) {
                            $timeDiff = $func->dateDiff($today, $homeline_tweet->created_at, 1);
                            $tweet_text = $homeline_tweet->text;
                            # Turn URLs into links
                            $tweet_text = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\./-]*(\?\S+)?)?)?)@', '<a target="blank" title="$1" href="$1">$1</a>', $tweet_text);
				
                            #Turn hashtags into links
                                $tweet_text = preg_replace('/#([0-9a-zA-Z_-]+)/', "<a target='blank' title='$1' href=\"http://twitter.com/search?q=%23$1\">#$1</a>", $tweet_text);
				
                            #Turn @replies into links
                                $tweet_text = preg_replace("/@([0-9a-zA-Z_-]+)/", "<a target='blank' title='$1' href=\"http://twitter.com/$1\">@$1</a>", $tweet_text);
                        ?>
                        <div class="slide">
                            <div style="background-color:#CCC; height:140px; padding:5px;">
                                <div style="width:100%; margin:3px 0; height:50px;">
                                    <div style="width:48px; margin:0 3px 0 0; float:left; display:inline;">
                                    <img src="<?php echo $homeline_tweet->user->profile_image_url; ?>" alt=""  style="border:1px solid #000;" />
                                    </div>
                                    <div style="float:left; display:inline;">
                                    <?php echo $homeline_tweet->user->name.' - '.$timeDiff; ?>
                                    </div>
                                </div>
                                <div style="width:100%; margin:3px 0;">
                                <?php
                                echo $tweet_text;
                                ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <br clear="all" />
                    <?php
                    }
                    ?>
                    
                  
                                      
                  <h2>Download Follower</h2>



		<form>
		<div>
                  <label>Enter Screen name</label>
		  <input id="txtflwdwn" style="height:30px;width:300px;border-radius:4px; border:2px solid green; margin-bottom:20px;" type="text" placeholder="Enter screen name here" >
                 	<br />
                  <div><label>Select Format:</label>	
                       <select name="download_format" id="flwdwnbtn" style="height:30px;width:300px;border-radius:4px; border:2px solid green;" onchange="tmp(value);">
                            <option value="">--- Select Format ---</option>
                            <option value="csv">csv format</option>
                            <option value="json">json format</option>
                            <option value="xls">excel format</option>
                            <option value="xml">XML format</option>                            
                        </select> 
                        </div>
                </div>
                <br> 
	        </form>
          </div>
                </div>
            </div>
        </div>
	<br>
		<?php
                    }
                    ?>
  </div>
  
  <div class="column side">
  
  </div>
</div>
  

                    
                  

</body>
</html>		

           			
