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
  
  $id = $_GET['id'];
  
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $likes = $facebook->api("$id/likes");
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
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
    <h1>Stalk a friend</h1>
    <h2>Likes</h2>
    <ul>
      <?php foreach ($likes['data'] as $like): ?>
        <li><a href="videos.php?like=<?php echo urlencode($like['name']) ?>"><?php echo $like['name'] ?></li>
      <?php endforeach ?>
      <li></li>
    </ul>
  </body>
</html>
