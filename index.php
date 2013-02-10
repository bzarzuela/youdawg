<?php
require 'config.php';

require 'lib/facebook/src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => $config['facebook_app_id'],
  'secret' => $config['facebook_secret'],
));

$user = $facebook->getUser();

$mc = new Memcached;
$mc->addServer('127.0.0.1', 11211);


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

if (!($tmp = $mc->get($user))) {
  $tmp = $facebook->api('/me/friends?fields=gender,name,relationship_status,picture,likes');
  $res = $mc->set($user, $tmp);
  if (!$res) {
    echo "Cannot store in memcached";
  }
}

$friends = $tmp['data']; // Because I don't have PHP 5.4

$log = array();

// Remove all unnecessary friends
foreach ($friends as $key => $friend) {
  if ((!isset($friend['gender'])))
  {
    $log[] = $friend['name'] . ' cannot be analyzed';
    unset($friends[$key]);
    continue;
  }
  
  if (($friend['gender'] != $target))
  {
    $log[] = $friend['name'] . ' is incompatible';
    unset($friends[$key]);
    continue;
  }
  
  // We're reducing the bar by settling for those who are overtly not single.
  if (isset($friend['relationship_status']) and ($friend['relationship_status'] != 'Single'))
  {
    $log[] = $friend['name'] . ' is not available';
    unset($friends[$key]);
    continue;
  }
  
  // Check if we're compatible.
  if (!isset($friend['likes'])) {
    $log[] = $friend['name'] . ' did not list likes';
    unset($friends[$key]);
    continue;
  }
  
  // I'm tired, don't ask.
  foreach ($friend['likes']['data'] as $like) {
    $friend['common_likes_string'] = '';
    $friend['common_likes'] = array();
    if (isset($user_likes[$like['id']])) {
      $friend['common_likes_string'] .= trim($like['name']) . ',';
      $friend['common_likes'][] = trim($like['name']);
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
	  
	  <h1>Good news, I found <?php echo count($friends) ?>!</h1>
	  <img src="img/good-news.jpg">
    
    <?php foreach ($friends as $friend): ?>
      <div class="friend">
        <form action="match.php" method="post">
          <input type="hidden" name="common_likes" value="<?php echo base64_encode($friend['common_likes_string']) ?>">
          <div class="center">
            <img style="width: 100px" src="<?php echo $friend['picture']['data']['url'] ?>">
          </div>
          
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
    
    <div class="clearfix"></div>
    
    <ul>
      <?php foreach ($log as $entry): ?>
      <li><?php echo $entry ?></li>  
      <?php endforeach ?>
    </ul>
    
	</div>

	</div>
</div>
</body></html>
