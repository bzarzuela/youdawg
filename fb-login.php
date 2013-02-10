<?php
require 'config.php';

require 'lib/facebook/src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => $config['facebook_app_id'],
  'secret' => $config['facebook_secret'],
));

$loginUrl = $facebook->getLoginUrl(array(
  'scope' => 'friends_likes,friends_relationships,user_likes',
  'redirect_uri' => 'http://127.0.0.1:8080/globelabs/index.php',
));

?>

<!DOCTYPE html>
<html><head>
<title>Facebook Login</title>
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
	  <div class="center">
	   
	   <h1>Allow me to find you<br> your perfect match!</h1>
	   
     
     <div><img src="img/man-woman.png"></div>
     <a href="<?php echo $loginUrl ?>" class="button blue large">Login with Facebook</a>
	  </div>
	  
    
    
	</div>

	</div>
</div>
</body></html>