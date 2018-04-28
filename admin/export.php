<?php
// admin/export.php
session_start();
// UTF-8
header("content-type: text/html; charset=UTF-8");
// load
include ('../bin/load.php');
// แอดมินเท่านั้น
if (gcms::isReferer() && gcms::isAdmin()) {
  $sqls = array();
  $rows = array();
  $database = array();
  $datas = array();
  foreach ($_POST AS $table => $values) {
    foreach ($values AS $k => $v) {
      $datas[$table][$v] ++;
    }
  }
  // ชื่อฐานข้อมูล
  $fname = "$config[db_name].sql";
  // memory limit
  ini_set('memory_limit', '512M');
  // ส่งออกเป็นไฟล์
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=$fname");
  // ตารางทั้งหมด
  $tables = $db->customQuery("SHOW TABLE STATUS");
  foreach ($tables AS $table) {
    if (preg_match('/^'.PREFIX.'(.*?)$/', $table['Name']) && isset($datas[$table['Name']])) {
      $fields = $db->customQuery("SHOW FULL FIELDS FROM ".$table['Name']);
      $primarykey = array();
      $rows = array();
      foreach ($fields AS $field) {
        if ($field['Key'] == 'PRI') {
          $primarykey[] = '`'.$field['Field'].'`';
        }
        $database[$table['Name']]['Field'][] = $field['Field'];
        $rows[] = '`'.$field['Field'].'` '.$field['Type'].($field['Collation'] != '' ? ' collate '.$field['Collation'] : '').($field['Null'] == 'NO' ? ' NOT NULL' : '').($field['Extra'] != '' ? ' '.$field['Extra'] : '');
      }
      if (sizeof($primarykey) > 0) {
        $rows[] = 'PRIMARY KEY ('.implode(',', $primarykey).')';
      }
      if (isset($datas[$table['Name']]['sturcture'])) {
        $sqls[] = 'DROP TABLE IF EXISTS `'.preg_replace('/^'.PREFIX.'/', '{prefix}', $table['Name']).'`;';
        $q = 'CREATE TABLE IF NOT EXISTS `'.preg_replace('/^'.PREFIX.'/', '{prefix}', $table['Name']).'` ('.implode(',', $rows).') ENGINE='.$table['Engine'];
        $q .= ' DEFAULT CHARSET='.preg_replace('/([a-zA-Z0-9]+)_.*?/Uu', '\\1', $table['Collation']).' COLLATE='.$table['Collation'];
        $q .= ($table['Create_options'] != '' ? ' '.strtoupper($table['Create_options']) : '').';';
        $sqls[] = $q;
      }
    }
  }
  // ข้อมูลในตาราง
  foreach ($tables AS $table) {
    if (preg_match('/^'.PREFIX.'(.*?)$/', $table['Name'])) {
      if ($table['Name'] == DB_LANGUAGE) {
        if (isset($_POST['language_lang']) && isset($_POST['language_owner'])) {
          $l = array_merge(array('key', 'type', 'owner', 'js'), $_POST['language_lang']);
          foreach ($_POST['language_owner'] AS $lang) {
            $languages[] = "'$lang'";
            if ($lang == 'index') {
              $languages[] = "''";
            }
          }
          $data = "INSERT INTO `".preg_replace('/^'.PREFIX.'/', '{prefix}', DB_LANGUAGE)."` (`".implode('`, `', $l)."`) VALUES ('%s');";
          $sql = "SELECT `".implode('`,`', $l)."` FROM `".DB_LANGUAGE."` WHERE `owner` IN (".implode(',', $languages).") ORDER BY `owner`,`key`,`js`";
          foreach ($db->customQuery($sql) AS $record) {
            foreach ($record AS $field => $value) {
              $record[$field] = ($field == 'owner' && $value == '') ? 'index' : addslashes($value);
            }
            $sqls[] = preg_replace(array('/[\r]/u', '/[\n]/u'), array('\r', '\n'), sprintf($data, implode("','", $record)));
          }
        }
      } elseif ($table['Name'] == DB_EMAIL_TEMPLATE) {
        if (isset($datas[DB_EMAIL_TEMPLATE]['datas'])) {
          if (($key = array_search('id', $database[DB_EMAIL_TEMPLATE]['Field'])) !== false) {
            unset($database[DB_EMAIL_TEMPLATE]['Field'][$key]);
          }
          $data = "INSERT INTO `".preg_replace('/^'.PREFIX.'/', '{prefix}', $table['Name'])."` (`".implode('`, `', $database[$table['Name']]['Field'])."`) VALUES ('%s');";
          $records = $db->customQuery("SELECT * FROM ".$table['Name']);
          foreach ($records AS $record) {
            foreach ($record AS $field => $value) {
              if (in_array($field, array('copy_to', 'from_email'))) {
                $record[$field] = $value == $_SESSION['login']['email'] ? '{WEBMASTER}' : '';
              } elseif ($field == 'id') {
                unset($record['id']);
              } else {
                $record[$field] = addslashes($value);
              }
            }
            $sqls[] = preg_replace(array('/[\r]/u', '/[\n]/u'), array('\r', '\n'), sprintf($data, implode("','", $record)));
          }
        }
      } elseif (isset($datas[$table['Name']]['datas'])) {
        $data = "INSERT INTO `".preg_replace('/^'.PREFIX.'/', '{prefix}', $table['Name'])."` (`".implode('`, `', $database[$table['Name']]['Field'])."`) VALUES ('%s');";
        $records = $db->customQuery("SELECT * FROM ".$table['Name']);
        foreach ($records AS $record) {
          foreach ($record AS $field => $value) {
            $record[$field] = addslashes($value);
          }
          $sqls[] = preg_replace(array('/[\r]/u', '/[\n]/u'), array('\r', '\n'), sprintf($data, implode("','", $record)));
        }
      }
    }
  }
  // คืนต่าข้อมูล
  echo preg_replace(array('/[\\\\]+/', '/\\\"/'), array('\\', '"'), implode("\r\n", $sqls));
} else {
  // ไม่สามารถดาวน์โหลดได้
  header("HTTP/1.0 404 Not Found");
}
