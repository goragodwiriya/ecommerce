<?php
if (defined("INSTALL_INIT")) {
  $php = array();
  $sql = "SELECT * FROM `".DB_LANGUAGE."`";
  foreach ($db->customQuery($sql) AS $item) {
    unset($item['id']);
    foreach ($languages AS $language) {
      $item[$language] = addslashes($item[$language]);
    }
    $php[$item['key']] = $item;
  }
  $db->query("TRUNCATE `".DB_LANGUAGE."`");

  function installLanguage($f, $l, $m)
  {
    global $php;
    $lng = array();
    include ($f);
    foreach ($lng AS $key => $value) {
      if (isset($php[$key])) {
        if ($php[$key]['type'] == 'array') {
          $php[$key][$l] = serialize($value);
        } elseif ($value !== '') {
          $php[$key][$l] = addslashes($value);
        }
      } else {
        $save = array();
        $save['key'] = $key;
        $save['owner'] = $m;
        if (is_array($value)) {
          $save['type'] = 'array';
          $save[$l] = serialize($value);
        } else {
          if (preg_match('/^[0-9]+$/', $value)) {
            $save['type'] = 'int';
          } else {
            $save['type'] = 'text';
          }
          $save[$l] = addslashes($value);
        }
        $save['js'] = 0;
        $php[$key] = $save;
      }
    }
  }

  function installLanguage2($f, $l, $m)
  {
    global $php;
    $patt = '/^([A-Z0-9_]+)[\s]{0,}=[\s]{0,}[\'"](.*)[\'"];$/';
    foreach (file($f) AS $item) {
      $item = trim($item);
      if ($item != '') {
        if (preg_match($patt, $item, $match)) {
          if (isset($php[$match[1]])) {
            $php[$match[1]][$l] = addslashes($match[2]);
          } else {
            $save = array();
            if (preg_match('/^[0-9]+$/', $value)) {
              $save['type'] = 'int';
            } else {
              $save['type'] = 'text';
            }
            $save['key'] = $match[1];
            $save['owner'] = $m;
            $save[$l] = addslashes($match[2]);
            $save['js'] = 1;
            $php[$match[1]] = $save;
          }
        }
      }
    }
  }
  // โหลดภาษาของโมดูล
  $dir = ROOT_PATH.'language/';
  if (is_dir($dir)) {
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
      if ($text != '.' && $text != '..' && is_dir("$dir$text/")) {
        foreach ($languages AS $language) {
          if (is_file("$dir$text/$language.php")) {
            installLanguage("$dir$text/$language.php", $language, $text);
          }
          if (is_file("$dir$text/$language.js")) {
            installLanguage2("$dir$text/$language.js", $language, $text);
          }
        }
      }
    }
    closedir($f);
  }
  // โหลดภาษาของ widgets
  $dir = ROOT_PATH.'widgets/';
  $f = opendir($dir);
  while (false !== ($text = readdir($f))) {
    if ($text != '.' && $text != '..' && is_dir("$dir$text/")) {
      foreach ($languages AS $language) {
        if (is_file("$dir$text/language/$language.php")) {
          installLanguage("$dir$text/language/$language.php", $language, $text);
        }
        if (is_file("$dir$text/language/$language.js")) {
          installLanguage2("$dir$text/language/$language.js", $language, $text);
        }
      }
    }
  }
  closedir($f);
  foreach ($languages AS $language) {
    if (is_file(ROOT_PATH."language/$language.php")) {
      installLanguage(ROOT_PATH."language/$language.php", $language, 'index');
    }
    if (is_file(ROOT_PATH."language/$language.js")) {
      installLanguage2(ROOT_PATH."language/$language.js", $language, 'index');
    }
  }
  foreach ($php AS $v) {
    $db->add(PREFIX."_language", $v);
  }
  unset($php);
}