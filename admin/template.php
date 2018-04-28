<?php
// admin/template.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // จำนวนแถวที่ต้องการ (3 column)
  $rows = 3;
  // path ของ skin
  $dir = ROOT_PATH.'skin/';
  if (isset($_GET['action'])) {
    if ($_SESSION['login']['account'] == 'demo') {
      $message = '<aside class=error>{LNG_DATA_NOT_FOUND}</aside>';
    } else {
      $action = $_GET['action'];
      $theme = preg_replace('/[\/\\\\]/ui', '', $db->sql_trim_str($_GET['theme']));
      if (is_dir($dir.$theme)) {
        if ($action == 'use') {
          $save = $config;
          // โหลด config ใหม่
          $config = array();
          if (is_file(CONFIG)) {
            include CONFIG;
          }
          // skin ที่กำหนด
          $config['skin'] = $theme;
          // บันทึก config.php
          if (gcms::saveconfig(CONFIG, $config)) {
            $message = '<aside class=message>{LNG_TEMPLATE_SELECT_SUCCESS}</aside>';
          } else {
            $message = '<aside class=error>'.sprintf($lng['ERROR_FILE_READ_ONLY'], 'bin/config.php').'</aside>';
          }
          $config = array_merge($save, $config);
        } elseif ($action == 'delete') {
          // ลบ skin
          gcms::rm_dir($dir.$theme.'/');
          $message = '<aside class=message>{LNG_TEMPLATE_REMOVE_SUCCESS}</aside>';
        }
      }
    }
  }
  // อ่าน Theme ทั้งหมด
  $themes = array();
  $f = opendir($dir);
  while (false !== ($text = readdir($f))) {
    if ($text !== $config['skin'] && $text !== "." && $text !== "..") {
      if (is_dir($dir.$text.'/') && is_file($dir.$text.'/style.css')) {
        $themes[] = $text;
      }
    }
  }
  closedir($f);
  // theme ทั้งหมด
  $count = sizeof($themes);
  // จำนวนรายการต่อหน้า
  $list_per_page = $rows * 3;
  // จำนวนหน้าทั้งหมด
  $totalpage = round($count / $list_per_page);
  $totalpage += ($totalpage * $list_per_page < $count) ? 1 : 0;
  // หน้าที่เลือก
  $page = (int)$_GET['page'];
  $page = ($page < 1) ? 1 : $page;
  $page = max(1, $page > $totalpage ? $totalpage : $page);
  // แบ่งหน้า
  $maxlink = 9;
  $url = '<a href="index.php?module=template&amp;page=%d" title="{LNG_DISPLAY_PAGE} %d">%d</a>';
  if ($totalpage > $maxlink) {
    $start = $page - floor($maxlink / 2);
    if ($start < 1) {
      $start = 1;
    } elseif ($start + $maxlink > $totalpage) {
      $start = $totalpage - $maxlink + 1;
    }
  } else {
    $start = 1;
  }
  $splitpage = ($start > 2) ? str_replace('%d', 1, $url) : '';
  for ($i = $start; $i <= $totalpage && $maxlink > 0; $i++) {
    $splitpage .= ($i == $page) ? '<strong title="{LNG_DISPLAY_PAGE} '.$i.'">'.$i.'</strong>' : str_replace('%d', $i, $url);
    $maxlink--;
  }
  $splitpage .= ($i < $totalpage) ? str_replace('%d', $totalpage, $url) : '';
  $splitpage = $splitpage == '' ? '<strong title="{LNG_DISPLAY_PAGE} '.$i.'">1</strong>' : $splitpage;
  // title
  $title = $lng['LNG_TEMPLATE_SETTINGS'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_TEMPLATE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-template>'.$title.'</h1></header>';
  if ($message !== '') {
    $content[] = $message;
  }
  $info = gcms::parse_theme($dir.$config['skin'].'/style.css');
  $content[] = '<div id=admin_template>';
  $content[] = '<article class="current clear">';
  $content[] = '<h2>{LNG_TEMPLATE_CURRENT}</h2>';
  $content[] = '<article class="item bg-0">';
  if (isset($info['responsive'])) {
    $content[] = '<span title=Responsive class=icon-responsive></span>';
  }
  $content[] = '<span class=preview style="background-image:url('.WEB_URL.'/'.SKIN.'screenshot.jpg)" title="{LNG_TEMPLATE_CURRENT}"></span>';
  $content[] = '<h3>'.$info['name'].'</h3>';
  $content[] = '<p class=detail>'.$info['description'].'</p>';
  $content[] = '<p class=folder>'.str_replace('%s', SKIN, $lng['LNG_TEMPLATE_FOLDER']).'</p>';
  $content[] = '</article>';
  $content[] = '</article>';
  $content[] = '<article class="list clear">';
  $content[] = '<h2>{LNG_TEMPLATE_OTHER}</h2>';
  // รายการแรกที่แสดง
  $l = sizeof($colors);
  $c = 1;
  $n = 1;
  $start = $list_per_page * ($page - 1);
  $max = min($count, $start + $list_per_page);
  for ($r = $start; $r < $max; $r++) {
    $text = $themes[$r];
    $info = gcms::parse_theme($dir.$text.'/style.css');
    $row = '<article class=item>';
    $n++;
    if (isset($info['responsive'])) {
      $row .= '<span title=Responsive class=icon-responsive></span>';
    }
    $c = $c == 4 ? 0 : $c + 1;
    $row .= '<span class=preview style="background-image:url('.WEB_URL.'/skin/'.$text.'/screenshot.jpg)" title="{LNG_THUMBNAIL}"></span>';
    $row .= '<h3>'.$info['name'].'</h3>';
    $row .= '<p class=detail>'.$info['description'].'</p>';
    $row .= '<p class=folder>'.str_replace('%s', 'skin/'.$text.'/', $lng['LNG_TEMPLATE_FOLDER']).'</p>';
    $row .= '<p>';
    $row .= '<a href="index.php?module=template&amp;page='.$page.'&amp;action=use&amp;theme='.$text.'">{LNG_TEMPLATE_SELECT}</a>&nbsp;|&nbsp;';
    $row .= '<a href="index.php?module=template&amp;page='.$page.'&amp;action=delete&amp;theme='.$text.'">{LNG_DELETE}</a>';
    $row .= '</p>';
    $row .= '</article>';
    $content[] = $row;
  }
  $content[] = '</article>';
  $content[] = '<p class=splitpage>'.$splitpage.'</p>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = "inintTemplate('admin_template');";
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'template';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
