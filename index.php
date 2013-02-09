<?php
require 'config.php';

require 'lib/facebook/src/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => $config['facebook_app_id'],
  'secret' => $config['facebook_secret'],
));

// Get User ID
$user = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($user) {
  try {
    $me = $facebook->api('/me');
    // Proceed knowing you have a logged in user who's authenticated.
    $friends = $facebook->api('/me/friends?fields=gender,name,relationship_status,picture');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl(array(
    'scope' => 'friends_likes,friends_relationships',
  ));
}


?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <?php if ($user): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <?php if ($user): ?>
      <?php 
      $gender = $me['gender'];
      $target = ($me['gender'] == 'male') ? 'female' : 'male';
      ?>
      <h1>Friends</h1>
        <?php foreach ($friends['data'] as $friend): ?>
          <?php if (!isset($friend['gender'])) { continue; } ?>
          <?php if (!isset($friend['relationship_status'])) { continue; } ?>
          <?php if (($friend['gender'] == $target) and ($friend['relationship_status'] = 'Single')): ?>
            <div>
              <img src="<?php echo $friend['picture']['data']['url'] ?>">
              <p><a href="friend.php?id=<?php echo $friend['id'] ?>"><?php echo $friend['name'] ?></a></p>
            </div>
          <?php endif ?>
        
        <?php endforeach ?>
      
      
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

  </body>
</html>
