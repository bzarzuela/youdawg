<?php
require 'config.php';

require 'lib/facebook/src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => $config['facebook_app_id'],
  'secret' => $config['facebook_secret'],
));

$user = $facebook->getUser();

if (!$user) {
  // We shouldn't be here
  header('Location: fb-login.php');
  exit;
}

$me = $facebook->api('/me?fields=gender,name,likes');

$gender = $me['gender'];
$target = ($me['gender'] == 'male') ? 'female' : 'male';

// Build an easy lookup map of my likes.
$user_likes = array();
foreach ($me['likes']['data'] as $like) {
  $user_likes[$like['id']] = $like;
}

$tmp = $facebook->api('/me/friends?fields=gender,name,relationship_status,picture,likes');
$friends = $tmp['data']; // Because I don't have PHP 5.4

$log = array();

// Remove all unnecessary friends
foreach ($friends as $key => $friend) {
  if ((!isset($friend['gender'])) or
     (!isset($friend['relationship_status'])))
  {
    $log[] = $friend['name'] . ' cannot be analyzed';
    unset($friends[$key]);
    continue;
  }
  
  if (($friend['gender'] != $target) or
     ($friend['relationship_status'] != 'Single'))
  {
    $log[] = $friend['name'] . ' is incompatible';
    unset($friends[$key]);
    continue;
  }
  
  // Check if we're compatible.
  foreach ($friend['likes']['data'] as $like) {
    $friend['common_likes'] = array();
    if (isset($user_likes[$like['id']])) {
      $friend['common_likes'][] = $like['name'];
    }
    $friends[$key] = $friend;
  }
  
  // If we didn't find anything, well...
  if (empty($friend['common_likes'])) {
    $log[] = $friend['name'] . ' has nothing in common';
    unset($friends[$key]);
  }
}

?>

<!DOCTYPE html>
<html><head>
<title>Little Black Book</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<script type="text/javascript" src="js/prettify.js"></script>                                   <!-- PRETTIFY -->
<script type="text/javascript" src="js/kickstart.js"></script>                                  <!-- KICKSTART -->
<link rel="stylesheet" type="text/css" href="css/kickstart.css" media="all" />                  <!-- KICKSTART -->
<link rel="stylesheet" type="text/css" href="style.css" media="all" />                          <!-- CUSTOM STYLES -->
</head><body>

<div class="grid">
	<div id="wrap" class="clearfix">

	<div class="col_12">
	  
	  <h1>Good news, only one!</h1>
	  <img src="img/good-news.jpg">
    
    <?php foreach ($friends as $friend): ?>
      <div class="friend">
        <form action="match.php" method="post">
          <input type="hidden" name="common_likes" value="<?php echo serialize($friend['common_likes']) ?>">
          <img src="<?php echo $friend['picture']['data']['url'] ?>">
          <p><?php echo $friend['name'] ?></p>
          <ul class="checks">
            <?php foreach ($friend['common_likes'] as $like): ?>
            <li><?php echo $like ?></li>  
            <?php endforeach ?>
          </ul>
          <input type="submit" name="" value="I choose you!">
        </form>
      </div>
    <?php endforeach ?>
    
    <ul>
      <?php foreach ($log as $entry): ?>
      <li><?php echo $entry ?></li>  
      <?php endforeach ?>
    </ul>
    
	</div>

	</div>
</div>
</body></html>
