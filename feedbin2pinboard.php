<?php
include 'pinboard-api.php';

// SETTINGS
$pinboardapi  = ''; // Pinboard apitoken username:random
$feedbinurl   = ''; //Feedbin starred entries URL
$localstorage = './storage.json'; // Local storage path


// initialise pinboard object
$pinboard = new PinboardAPI(null, $pinboardapi);
// $pinboard->enable_logging(function($str) { echo "$str\n"; });

// Parse Feedbin XML
$xml = new SimpleXMLElement($feedbinurl, LIBXML_NOCDATA, true);


$tempstorage = Array();
$handler = fopen($localstorage, "a+");

if (file_exists($localstorage)) {
  if (filesize($localstorage)) {
    $tempstorage = json_decode(fread($handler, filesize($localstorage)), true);
  }
}

foreach ($xml->channel->item as $item) {
  echo $item->title . ' -> ' . $item->link;
  echo "\n";

  $bookmark = new PinboardBookmark;
  $bookmark->url = (string)$item->link;
  $bookmark->title = (string)$item->title;
  $bookmark->is_unread = true;
  $bookmark->is_public = false;
  $bookmark->tags = array('customimport', 'Feedbin', 'dailyimport');
  $bookmark->replace = false;

  if (array_key_exists($bookmark->url, $tempstorage)) {
    echo "Already sent to Pinboard\n";
  } else {

    if ($bookmark->save()) {
      echo "Saved to Pinboard sucessfully! :D\n";
      $tempstorage[(string)$item->link] = 'sent';
    } else {
      echo "Did not save to Pinboard, because of: " . $pinboard->get_last_status() . "\n";
    }

  }
  // sleep(3);
}


// Save list of sent items to disk
if (file_put_contents($localstorage, json_encode($tempstorage))) {
  echo "Local storage updated\n";
} else {
  echo "Could not save to local storage!\n";
}

?>
