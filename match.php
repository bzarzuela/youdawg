<?php
require 'config.php';

// require 'lib/Zend/Loader.php';
require 'lib/Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();

$yt = new Zend_Gdata_YouTube(null, 'Hack-for-Fun-1.0', null, 'AI39si4E_GmwAJ71x0Hl4zI5HoXKseAmBt4sOtytWahDXOehvhReokFFhe_2JhGzg8wgYdgm8bdHR_fe8HodXYJ4kVpfRW69lw');

$terms = base64_decode($_POST['common_likes']);

$query = $yt->newVideoQuery();
$query->videoQuery = $terms;
$query->startIndex = 0;
$query->maxResults = 10;
$query->orderBy = 'relevance';


$videoFeed = $yt->getVideoFeed($query);

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
	  
	  <h1>Here's your Youtube playlist</h1>
	  <?php foreach ($videoFeed as $video): ?>
      <div class="video">
        <h2><?php echo $video->getVideoTitle() ?></h2>
        <?php if ($thumbnails = $video->getVideoThumbnails()): ?>
          <a href="<?php echo $video->getVideoWatchPageUrl() ?>"><img src="<?php echo $thumbnails[0]['url'] ?>"></a>
        <?php endif ?>
        <p><?php echo $video->getVideoDescription() ?></p>
      </div>
    <?php endforeach ?>
	</div>

	</div>
</div>
</body></html>