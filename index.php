<?php
/* 
* NOTE(Tj):
- Currently this script successfully connects to the facebook graph API and request permission on the type of data that 
   it will be needing.
- Created a Dummy GMail, Facbook, and Instagram business account to test with.

* TODO(Tj):
 - The scipts needs to be run on an actual server with https connection to be able to redirect back to it self to see the 
   short and long lived access token.
 - Manage environmental variables properly, can't have API secrets littered in the code anyhow.
 - Authentication system for Pax to allow user to log in.
 - A user interface, don't know what yet.
*/


  session_start();
  require __DIR__ . '/vendor/autoload.php';
  
  define('FACEBOOK_APP_ID', '');
  define('FACEBOOK_APP_SECRET', '');
  define('FACEBOOK_REDIRECT_URI', '');

  $credentials = [
    "app_id" => FACEBOOK_APP_ID,
    "app_secret" => FACEBOOK_APP_SECRET,
    "default_graph_version" => 'v14.0',
    "persistent_data_handler" => 'session',
  ];

  $facebook = new \Facebook\Facebook($credentials);
  $helper = $facebook->getRedirectLoginHelper();
  $oauth = $facebook->getOAuth2Client();

  // Check if code exists in the URL, if it is get an access token
  // otherwise generate link with needed permissions.
  if(isset($_GET['code'])){
    try {
      $access_token = $helper->getAccessToken();    
    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph return an error: ' . $e->getMessage();
      exit;
    } catch(\Facebook\Exceptions\FacebookSDKException $e){
      echo 'Facebook SDK return an error: ' . $e->getMessage();
      exit;
    }
    echo '<h3>Short Lived Access Token</h3>';
    var_dump($access_token);
    // print_r($access_token);

    // Exchange access token for a long lived access token
    if(!$access_token->isLongLived()){
      try {
        $access_token = $oauth->getLongLivedAccessToken($access_token);
      } catch (Facebook\Exceptions\FacebookSDKException $error) {
        echo 'Error getting long lived access token: ' . $error->getMessage();
      }

      echo '<h3>Long Lived Access Token</h3>';
      var_dump($access_token);
      // print_r($access_token);
    }
  } else {
    $permission = ['public_profile', 'instagram_basic', 'pages_show_list'];
    $login_url =  $helper->getLoginUrl(FACEBOOK_REDIRECT_URI, $permission);

    echo '<a href="' . $login_url . '"> Login With Facebook </a>';
  }
?>
