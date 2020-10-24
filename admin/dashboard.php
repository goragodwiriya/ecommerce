<?php
// admin/dashboard.php
if (MAIN_INIT == 'admin' && $canAdmin) {
  // counter
  $sql1 = "SELECT COUNT(*) FROM `".DB_USER."`";
  $sql2 = "SELECT COUNT(*) FROM `".DB_USER."` WHERE `activatecode`<>''";
  $sql3 = "SELECT COUNT(*) FROM `".DB_USER."` WHERE `ban_count` > 0";
  $sql4 = "SELECT COUNT(*) FROM `".DB_USERONLINE."`";
  $sql = "SELECT C.`counter`,C.`visited`,($sql1) AS `members`,($sql2) AS `activate`,($sql3) AS `ban`,($sql4) AS `useronline` ";
  $sql .= "FROM `".DB_COUNTER."` AS C ";
  $sql .= "ORDER BY C.`id` DESC LIMIT 1";
  $datas = $db->customQuery($sql);
  $dashboard_menus[] = array('clock', '{LNG_COUNTER_TODAY}', 'index.php?module=report', (int)$datas[0]['visited']);
  $dashboard_menus[] = array('users', '{LNG_COUNTER_ONLINE}', '', (int)$datas[0]['useronline']);
  // สี สำหรับส่งให้ graphs
  $color = "['".implode("', '", $colors)."']";
  // title
  $title = $lng['LNG_DASHBOARD'];
  // breadcrumbs
  $content[] = '<div class=breadcrumbs><ul><li><span class=icon-home>{LNG_HOME}</span></li></ul></div>';
  // แสดงเมนูหลัก
  $content[] = '<div class="infobox clear">';
  $content[] = '<ul>';
  $l = sizeof($colors);
  foreach ($dashboard_menus AS $i => $items) {
    $z = $i % $l;
    $row = '<li class="table bdr-'.$z.'">';
    $row .= '<span class="td icon-'.$items[0].' bg-'.$z.'"></span>';
    $t = $items[3] == '' ? '' : '<span class=c-'.$i.'>'.$items[3].'</span>';
    if ($items[2] == '') {
      $row .= '<span class="detail td">'.$t.$items[1].'</span>';
    } else {
      $row .= '<a class="detail td" href="'.$items[2].'">'.$t.$items[1].'</a>';
    }
    $row .= '</li>';
    $content[] = $row;
  }
  $content[] = '</ul>';
  $content[] = '</div>';
  $content[] = '<div class="ggrid collapse dashboard">';
  $content[] = '<div class="block5 float-left">';
  // summary
  $sql1 = "SELECT COUNT(*) FROM `".DB_USER."`";
  $sql2 = "SELECT COUNT(*) FROM `".DB_USER."` WHERE `activatecode`<>''";
  $sql3 = "SELECT COUNT(*) FROM `".DB_USER."` WHERE `ban_count` > 0";
  $sql4 = "SELECT COUNT(*) FROM `".DB_USERONLINE."`";
  $sql = "SELECT C.`counter`,C.`visited`,($sql1) AS `members`,($sql2) AS `activate`,($sql3) AS `ban`,($sql4) AS `useronline` ";
  $sql .= "FROM `".DB_COUNTER."` AS C ";
  $sql .= "ORDER BY C.`id` DESC LIMIT 1";
  $datas = $db->customQuery($sql);
  $content[] = '<section class=section>';
  $content[] = '<header><h1 class=icon-summary>{LNG_SITE_REPORT}</h1></header>';
  $content[] = '<table class="summary fullwidth">';
  $content[] = '<caption>{LNG_REPORT_MEMBER}</caption>';
  $content[] = '<tbody>';
  $content[] = '<tr><th scope=row><a href="'.WEB_URL.'/admin/index.php?module=member&amp;order=0">{LNG_REPORT_MEMBER_ALL}</a></th><td class=right>'.(int)$datas[0]['members'].' {LNG_PEOPLE}</td></tr>';
  $content[] = '<tr class=bg2><th scope=row><a href="'.WEB_URL.'/admin/index.php?module=member&amp;order=2">{LNG_MEMBER_NOT_CONFIRM}</a></th><td class=right>'.(int)$datas[0]['activate'].' {LNG_PEOPLE}</td></tr>';
  $content[] = '<tr><th scope=row><a href="'.WEB_URL.'/admin/index.php?module=member&amp;order=9">{LNG_MEMBER_BAN}</a></th><td class=right>'.(int)$datas[0]['ban'].' {LNG_PEOPLE}</td></tr>';
  $content[] = '<tr class=bg2><th scope=row>{LNG_COUNTER_ALL}</th><td class=right>'.(int)$datas[0]['counter'].' {LNG_PEOPLE}</td></tr>';
  $content[] = '<tr><th scope=row>{LNG_COUNTER_ONLINE}</th><td class=right>'.(int)$datas[0]['useronline'].' {LNG_PEOPLE}</td></tr>';
  $content[] = '<tr class=bg2><th scope=row><a href="'.WEB_URL.'/admin/index.php?module=report">{LNG_COUNTER_TODAY}</a></th><td class=right>'.(int)$datas[0]['visited'].' {LNG_PEOPLE}</td></tr>';
  if (is_file(DATA_PATH.'index.php')) {
    $date = file_get_contents(DATA_PATH.'index.php');
    if (preg_match('/([0-9]+){0,2}-([0-9]+){0,2}-([0-9]+){0,4}\s([0-9]+){0,2}:([0-9]+){0,2}:([0-9]+){0,2}/', $date, $match)) {
      $cron_time = gcms::mktime2date(mktime($match[4], $match[5], $match[6], $match[2], $match[1], $match[3]));
    } else {
      $cron_time = '-';
    }
  } else {
    $cron_time = '-';
  }
  $content[] = '<tr><th scope=row>{LNG_CRON_CREATED}</th><td class="right data">'.$cron_time.'</td></tr>';
  $content[] = '</tbody>';
  $content[] = '<tfoot>';
  $content[] = '<tr><td colspan=2 class=right>{LNG_REPORT_VERSION}</td></tr>';
  $content[] = '</tfoot>';
  $content[] = '</table>';
  $content[] = '</section>';
  // gcms news
  $content[] = '<section class=section>';
  $content[] = '<header><h1 class=icon-rss>{LNG_GCMS_NEWS}</h1></header>';
  $content[] = '<ol id=news_div></ol>';
  $content[] = '<p class="bottom right padding-right">';
  $content[] = '<a class=icon-next href="http://gcms.in.th/news.html" target=_blank>{LNG_VIEW_ALL}</a>';
  $content[] = '</p>';
  $content[] = '</section>';
  $content[] = '</div>';
  $content[] = '<div class="block7 float-right">';
  // แสดงรายการ pagesview
  $y = (int)date('Y', $mmktime);
  $pages_view = 0;
  $pageview = array();
  $visited = array();
  $thead = array();
  $sql = "SELECT * FROM (SELECT `id`,MONTH(`date`) AS `month`,YEAR(`date`) AS `year`,SUM(`pages_view`) AS `pages_view`,SUM(`visited`) AS `visited`,`date`";
  $sql .= " FROM `".DB_COUNTER."` GROUP BY MONTH(`date`),YEAR(`date`) ORDER BY `date` DESC LIMIT 12) AS A ORDER BY A.`date`";
  $list = $db->customQuery($sql);
  $l = sizeof($list);
  foreach ($list AS $i => $item) {
    $c = $i > $l - 8 ? $i > $l - 4 ? '' : 'mobile' : 'tablet';
    $thead[] = '<td class="'.$c.'"><a href="'.WEB_URL.'/admin/index.php?module=pagesview&amp;date='.$item['year'].'-'.$item['month'].'">'.$lng['MONTH_SHORT'][$item['month'] - 1].'</a></td>';
    $pageview[] = '<td class="'.$c.'">'.$item['pages_view'].'</td>';
    $visited[] = '<td class="'.$c.'">'.$item['visited'].'</td>';
  }
  $content[] = '<section class=section>';
  $content[] = '<header><h1 class=icon-stats>{LNG_PAGE_VIEW_REPORT}</h1></header>';
  $content[] = '<div id=pageview_graph class=ggraphs>';
  $content[] = '<canvas></canvas>';
  $content[] = '<table class="data fullwidth border">';
  $content[] = '<thead><tr><th>{LNG_MONTHLY}</th>'.implode('', $thead).'</tr></thead>';
  $content[] = '<tbody>';
  $content[] = '<tr><th scope=row>{LNG_COUNTER_ALL}</th>'.implode('', $pageview).'</tr>';
  $content[] = '<tr class=bg2><th scope=row>{LNG_COUNTER_PAGES_VIEW}</th>'.implode('', $visited).'</tr>';
  $content[] = '</tbody>';
  $content[] = '</table>';
  $content[] = '</div>';
  $content[] = '</section>';
  // หน้ายอดนิยม
  $thead = array();
  $visited = array();
  $sql = "SELECT D.`topic`,I.`visited`";
  $sql .= " FROM `".DB_INDEX."` AS I";
  $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`owner`='document'";
  $sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
  $sql .= " ORDER BY I.`visited` DESC LIMIT 12";
  foreach ($db->customQuery($sql) AS $item) {
    $thead[] = '<td>'.$item['topic'].'</td>';
    $visited[] = '<td>'.$item['visited'].'</td>';
  }
  $content[] = '<section class=section>';
  $content[] = '<header><h1 class=icon-pie>{LNG_POPULAR_PAGE} ({LNG_DOCUMENT})</h1></header>';
  $content[] = '<div id=visited_graph class=ggraphs>';
  $content[] = '<canvas></canvas>';
  $content[] = '<table class=hidden>';
  $content[] = '<thead><tr><th>&nbsp;</th>'.implode('', $thead).'</tr></thead>';
  $content[] = '<tbody>';
  $content[] = '<tr><th>{LNG_VISITED}</th>'.implode('', $visited).'</tr>';
  $content[] = '</tbody>';
  $content[] = '</table>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '</div>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'new gGraphs("pageview_graph", {type:"line",colors:'.$color.'});';
  $content[] = 'new gGraphs("visited_graph", {type:"pie",colors:'.$color.',centerX:30+Math.round($G("visited_graph").getHeight()/2),labelOffset:35,pieMargin:30,strokeColor:null});';
  $content[] = "getNews('news_div');";
  //$content[] = "getUpdate();";
  $content[] = '});';
  $content[] = '</script>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'dashboard';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
