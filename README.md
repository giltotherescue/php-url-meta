php-url-meta
============

Extract some basic metadata about a URL.
In most cases it favors Open Graph (OG) markup, and will fall back to standard meta tags if necessary.

parse() returns an object with title, description, keywords, author, and image.
All values are string EXCEPT keywords (which is its own object).

Example usage:

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