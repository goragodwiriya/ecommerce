<?php
// admin/pagesview.php
if (MAIN_INIT == 'admin' && $canAdmin) {
  // ค่าที่ส่งมา
  if (isset($_GET['date'])) {
    list($y, $m) = explode('-', $_GET['date']);
    $y = (int)$y;
    $m = (int)$m;
  } else {
    // วันนี้
    $m = (int)date('m', $mmktime);
    $y = (int)date('Y', $mmktime);
  }
  $total = 0;
  $thead = array();
  $tbody = array();
  $sql = "SELECT * FROM `".DB_COUNTER."` WHERE MONTH(`date`)=$m AND YEAR(`date`)=$y ORDER BY `date` ASC";
  $datas = $db->customQuery($sql);
  $l = sizeof($datas);
  foreach ($datas AS $i => $item) {
    list($y, $m, $d) = explode('-', $item['date']);
    $d = (int)$d;
    if (is_file(DATA_PATH.'counter/'.(int)$y.'/'.(int)$m.'/'.$d.'.dat')) {
      $d = '<a href="index.php?module=report&amp;date='.$item['date'].'">'.$d.'</a>';
    }
    $c = $i > $l - 13 ? $i > $l - 7 ? '' : 'mobile' : 'tablet';
    $thead[] = '<td class='.$c.'>'.$d.'</td>';
    $tbody[] = '<td class='.$c.'>'.$item['pages_view'].'</td>';
    $total = $total + $item['pages_view'];
  }
  // title
  $title = str_replace('%s', $lng['MONTH_LONG'][$m - 1], $lng['USERONLINE_PAGE_VIEW_TITLE']);
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li><span class=icon-summary>'.$title.'</span></li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-stats>'.sprintf($lng['USERONLINE_PAGE_VIEW_SUB_TITLE'], $lng['MONTH_LONG'][$m - 1], $total, sizeof($tbody)).'</h1></header>';
  $content[] = '<div id=pageview_graph class=ggraphs>';
  $content[] = '<canvas></canvas>';
  $content[] = '<table class="data fullwidth">';
  $content[] = '<thead><tr><th>{LNG_DATE}</th>'.implode('', $thead).'</tr></thead>';
  $content[] = '<tbody><tr><th>{LNG_COUNTER_PAGES_VIEW}</th>'.implode('', $tbody).'</tr></tbody>';
  $content[] = '</table>';
  $content[] = '</div>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'new gGraphs("pageview_graph", {type:"chart",startColor:10});';
  $content[] = '});';
  $content[] = '</script>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'pagesview';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
