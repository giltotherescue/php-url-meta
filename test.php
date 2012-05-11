<?php

require('meta.php');

// bunch of random URLs I had open today. try your own!
$urls = array(
  'http://www.imdb.com/title/tt0120737/',
  'http://stackoverflow.com/questions/7454644/how-to-get-open-graph-protocol-of-a-webpage-by-php#comment9017431_7454737',
  'http://www.squidoo.com/teenage-movies-girls',
  'http://www.rottentomatoes.com/m/marvels_the_avengers/',
  'http://board.phpbuilder.com/board/showthread.php?t=10273232',
  'http://mixture.fm/',
  'http://collabfinder.com/',
  'http://sethgodin.typepad.com/',
  'http://techgil.tumblr.com/post/22785084186/aws-is-not-the-prime-enabler-of-scalability',
  'http://pinterest.com/pin/82190761919492297/',
  'http://www.php.net/manual/en/function.get-meta-tags.php'
);

foreach ($urls as $url) {
  print "$url\n";
  $m = new URLMeta($url);
  if ($resp = $m->parse()) {
    print_r($resp);
  } else {
    printf("FAILED\nERROR CODE:%s\nRESPONSE: %s", $m->error_code, $m->error_response);
  }
  print "\n\n";
}

?>