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

// if (!$me = $mc->get($user . '-profile')) {
  $me = $facebook->api('/me?fields=gender,name,likes');
  // $mc->set($user . '-profile', $me);
// } 

$gender = $me['gender'];
$target = ($me['gender'] == 'male') ? 'female' : 'male';

// Build an easy lookup map of my likes.
$user_likes = array();
foreach ($me['likes']['data'] as $like) {
  $user_likes[$like['id']] = $like;
}

$friends = array();
if (!($friends = $mc->get($user))) {
  
  $query = 'fields=gender,name,relationship_status,picture,likes&limit=100';
  // echo $query, '<br/>';
  while (1) {
    $tmp = $facebook->api('/me/friends?' . $query);
    
    // echo count($tmp['data']);
    // var_dump($tmp['paging']);
    
    foreach ($tmp['data'] as $t) {
      $friends[] = $t;
    }
    if (count($tmp['data']) == 0) {
      break;
    }
    if (!isset($tmp['paging']['next'])) {
      break;
    }
    
    $query = $tmp['paging']['next'];
    $query = substr($query, strpos($query, '?')+1);
    // echo $query, '<br>';
  }
  
  
  // var_dump($tmp);
  $res = $mc->set($user, $friends);
  if (!$res) {
    echo "Cannot store in memcached";
  }
}

// $friends = $tmp['data']; // Because I don't have PHP 5.4

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
	  
	    <h1>Awoo! I've found <?php echo count($friends) ?> matches for you!</h1>
	  <img src="img/found.png" style="float: left; height: 240px;">
	  
    <!-- <div class="clearfix"></div> -->
    
    <div class="friends">
    <?php foreach ($friends as $friend): ?>
      <div class="col_2 friend">
        <form action="match.php" method="post">
          <input type="hidden" name="common_likes" value="<?php echo base64_encode($friend['common_likes_string']) ?>">
          <div class="center">
            <input type="image" style="width: 100px" src="<?php echo $friend['picture']['data']['url'] ?>">
          </div>
          
          <p><?php echo $friend['name'] ?></p>
          <ul class="checks">
            <?php foreach ($friend['common_likes'] as $like): ?>
            <li><?php echo $like ?></li>  
            <?php endforeach ?>
          </ul>
          
        </form>
      </div>
    <?php endforeach ?>
    </div>
    
    <div class="clearfix"></div>
    <?php if (count($friends) == 0): ?>
      <h1>Sorry, but it looks like you're forever alone this Valentine's day!</h1>
    <?php else: ?>
      <h1>Click on their photos and I'll sniff out some conversation pieces to help break the ice!</h1>
    <?php endif ?>
    
    
    <div class="log">
      <h4>I had to sniff <?php echo count($log) ?> other friends to find you those!</h4>
      <ul>
        <?php foreach ($log as $entry): ?>
        <li><?php echo $entry ?></li>  
        <?php endforeach ?>
      </ul>
    </div>
    
    <script type="text/javascript" charset="utf-8">
      $(function () {
        $('.log a').click(function () {
          $('.log ul').show();
          $('.log a').hide();
          return false;
        });
      });
    </script>
    
	</div>

	</div>
</div>
</body></html>
