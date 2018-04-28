<?php
// BingSiteAuth.php
// inint
include 'bin/inint.php';
// xml
if ($config['msvalidate'] != '') {
  header("content-type: text/xml; charset=UTF-8");
  echo '<'.'?xml version="1.0"?'.'>';
  echo "\n<users>";
  echo "\n\t<user>$config[msvalidate]</user>";
  echo "\n</users>";
} else {
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
}