<?php
require 'config.php';

// require 'lib/Zend/Loader.php';
require 'lib/Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance();

$yt = new Zend_Gdata_YouTube(null, 'Hack-for-Fun-1.0', null, 'AI39si4E_GmwAJ71x0Hl4zI5HoXKseAmBt4sOtytWahDXOehvhReokFFhe_2JhGzg8wgYdgm8bdHR_fe8HodXYJ4kVpfRW69lw');


$query = $yt->newVideoQuery();
$query->videoQuery = $_GET['like'];
$query->startIndex = 10;
$query->maxResults = 20;
$query->orderBy = 'viewCount';


echo $query->queryUrl . "\n";
$videoFeed = $yt->getVideoFeed($query);
// var_dump($videoFeed);
 


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
    <h1>Videos from Youtube</h1>
    <?php foreach ($videoFeed as $video): ?>
      <?php echo $video->getVideoTitle() ?>
    <?php endforeach ?>
  </body>
</html>
