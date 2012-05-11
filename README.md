php-url-meta
============

Extract some basic metadata about a URL.
In most cases it favors Open Graph (OG) markup, and will fall back to standard meta tags if necessary.

`parse()` returns an object with title, description, keywords, author, and image.
Most values are string EXCEPT keywords and author (which are their own objects).

Usage:

```
<?php
require('meta.php');
$m = new URLMeta('http://www.imdb.com/title/tt0120737/');
if ($m->parse()) {
  var_dump($resp);
} else {
  print $m->error_code . "\n" . $m->error_response;
}
?>
```

Response:

```
stdClass Object
(
    [title] => The Lord of the Rings: The Fellowship of the Ring (2001)
    [description] => Directed by Peter Jackson. With Elijah Wood, Ian McKellen, Orlando Bloom, Sean Bean. An innocent hobbit of The Shire journeys with eight companions to the fires of Mount Doom to destroy the One Ring and the dark lord Sauron forever.
    [keywords] => stdClass Object
        (
            [0] => Reviews
            [1] => Showtimes
            [2] => DVDs
            [3] => Photos
            [4] => Message Boards
            [5] => User Ratings
            [6] => Synopsis
            [7] => Trailers
            [8] => Credits
        )

    [author] =>
    [image] => http://ia.media-imdb.com/images/M/MV5BNTEyMjAwMDU1OV5BMl5BanBnXkFtZTcwNDQyNTkxMw@@._V1._SX96_SY140_.jpg
)
```