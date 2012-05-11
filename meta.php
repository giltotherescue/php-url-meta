<?php

/**
 * php-url-meta
 *
 * Extract some basic metadata about a URL.
 * In most cases it favors Open Graph (OG) markup, and will fall back to standard meta tags if necessary.
 *
 * parse() returns an object with title, description, keywords, author, and image.
 * All values are string EXCEPT keywords (which is its own object).
 *
 * See test.php for example usage.
 *
 * @author Gil Hildebrand <gil@squidoo.com>
 * @copyright Squidoo.com
 * @url https://github.com/giltotherescue/php-url-meta
 * @credit http://stackoverflow.com/questions/7454644/how-to-get-open-graph-protocol-of-a-webpage-by-php#comment9017431_7454737
 *
 */

class URLMeta {

  var $url, $response, $standard, $og, $xpath, $error_code, $error_response;

  function __construct($url) {
    $this->url = $url;
  }

  function parse() {
    if (!$html = $this->crawl()) {
      return false;
    }

    $this->response = (object) array(
      'title' => '',
      'description' => '',
      'keywords' => (object) array(),
      'author' => '',
      'image' => '',
    );

    $this->standard = $this->og = array();

    libxml_use_internal_errors(true);
    $doc = new DomDocument();
    $doc->loadHTML($html);
    $this->xpath = new DOMXPath($doc);
    $query = '//*/meta';
    $metas = $this->xpath->query($query);
    if ($metas) {
      foreach ($metas as $meta) {
        $name = $meta->getAttribute('name');
        $property = $meta->getAttribute('property');
        $content = $meta->getAttribute('content');
        if (!empty($name)) {
          $this->standard[$name] = $content;
        } else if (!empty($property)) {
          // can be more than one article:tag
          if ($property == 'article:tag') {
            if (isset($this->og['article:tag'])) {
              $this->og['article:tag'][] = $content;
            } else {
              $this->og['article:tag'] = array($content);
            }
          }
          $this->og[$property] = $content;
        }
      }

      $this->get_title();
      $this->get_description();
      $this->get_keywords();
      $this->get_author();
      $this->get_image();
    } else {
      // at least try to get a title
      $this->get_title;
    }

    return $this->response;
  }

  function get_title() {
    if (isset($this->og['og:title'])) {
      $this->response->title = $this->og['og:title'];
    } else {
      $query = '//*/title';
      $titles = $this->xpath->query($query);
      if ($titles) {
        foreach ($titles as $title) {
          $this->response->title = $title->nodeValue;
          break;
        }
      }
    }
  }

  function get_description() {
    if (isset($this->og['og:description'])) {
      $this->response->description = $this->og['og:description'];
    } else if (isset($this->standard['description'])) {
      $this->response->description = $this->standard['description'];
    }
  }

  function get_keywords() {
    if (isset($this->standard['keywords'])) {
      $keywords = explode(',', $this->standard['keywords']);
      foreach ($keywords as $k => $v) {
        $keywords[$k] = trim($v);
      }
      $this->response->keywords = (object) $keywords;
    } else if (isset($this->og['article:tag'])) {
      $this->response->keywords = (object) $this->og['article:tag'];
    }
  }

  function get_author() {
    $query = '//*/a[starts-with(@rel, \'author\')]';
    $authors = $this->xpath->query($query);
    if ($authors) {
      foreach ($authors as $author) {
        $this->response->author = (object) array('name' => $author->nodeValue, 'href' => $author->getAttribute('href'));
        break;
      }
    } else if (isset($this->og['article:author'])) {
      $this->response->author = (object) array('name' => '', 'href' => $this->og['article:author']);
    }
  }

  function get_image() {
    if (isset($this->og['og:image'])) {
      $this->response->image = $this->og['og:image'];
    } else {
      // attempt to find an image on the page?
    }
  }

  /**
   * @param int $timeout
   * @param int $connect_timeout
   * @param int $num_tries
   * @param int $wait_between_tries_seconds
   * @param array $other_curl_options (eg. user-agent)
   * @param array $custom_fail_strings optionally search for strings that represent failure (such as "error")
   * @return bool|string
   */
  function crawl(
      $timeout = 10,
      $connect_timeout = 3,
      $num_tries = 3,
      $wait_between_tries_seconds = 1,
      $other_curl_options = array(),
      $custom_fail_strings = array()
  ) {

    for ($i = 0; $i < $num_tries; $i++) {
      $curl_handle = curl_init();
      curl_setopt($curl_handle, CURLOPT_USERAGENT, 'https://github.com/giltotherescue/php-url-meta');
      curl_setopt($curl_handle, CURLOPT_URL, $this->url);
      curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
      curl_setopt($curl_handle, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);

      if (count($other_curl_options) > 0) {
        foreach ($other_curl_options as $name => $value) {
          curl_setopt($curl_handle, $name, $value);
        }
      }

      $buffer   = curl_exec($curl_handle);
      $curlinfo = curl_getinfo($curl_handle);
      curl_close($curl_handle);

      $custom_fail = false;
      if (count($custom_fail_strings) > 0) {
        foreach ($custom_fail_strings as $custom_fail_string) {
          if (stristr($buffer, $custom_fail_string)) {
            $custom_fail = true;
            break;
          }
        }
      }

      if (($curlinfo['http_code'] < 400) && ($curlinfo['http_code'] != 0) && (!$custom_fail)) {
        return $buffer;
      }

      // only report error if this is the last try
      if ($i == ($num_tries - 1)) {
        // error condition
        $this->error_code = $curlinfo['http_code'];
        $this->error_response = $buffer;
        return false;
      }
    }

    return false;
  }

}

?>