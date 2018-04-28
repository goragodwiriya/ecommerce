<?php
// admin/report.php
if (MAIN_INIT == 'admin' && $canAdmin) {
  // ค่าที่ส่งมา
  $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d', $mmktime);
  list($y, $m, $d) = explode('-', $date);
  $counter_dat = DATA_PATH.'counter/'.(int)$y.'/'.(int)$m.'/'.(int)$d.'.dat';
  if (is_file($counter_dat)) {
    $datas = array();
    $agents = array();
    $ssid = array();
    $ips = array();
    foreach (file($counter_dat) AS $item) {
      list($sid, $sip, $sref, $sagent) = explode(chr(1), $item);
      $ssid[$sid] ++;
      $ips[$sip] ++;
      $k = $_GET['type'] == 'ip' ? $sip : $sref;
      $datas[$k]['ip'] = $sip;
      $datas[$k]['agent'] = $sagent;
      $datas[$k]['referer'] = $sref;
      if (preg_match('/.*(Googlebot|Baiduspider|bingbot|MJ12bot|yahoo).*/isu', $sagent, $match)) {
        $agents[$match[1]] ++;
      } else {
        $datas[$k]['total'] ++;
      }
    }
    if ($_GET['type'] == 'ip') {
      // เรียงลำดับตาม ip
      gcms::sortby($datas, 'ip');
    } else {
      // เรียงลำดับตาม referer
      gcms::sortby($datas, 'referer');
    }
    $i = 0;
    $graphs['Google Search'] = 0;
    $graphs['Google Cached'] = 0;
    $graphs['Inbound'] = 0;
    $graphs['Direct'] = 0;
    $graphs['Direct'] = 0;
    $graphs['other'] = 0;
    $list = array();
    foreach ($datas AS $item) {
      $i++;
      $total = $total + $item['total'];
      if (preg_match('/^(https?.*(www\.)?google(usercontent)?.*)\/.*[\&\?]q=(.*)($|\&.*)/iU', $item['referer'], $match) && $match[4] != '') {
        // จาก google
        $a = $match[1].'/search?q='.htmlspecialchars($match[4]);
        $text = gcms::cutstring($match[1].'/search?q='.htmlspecialchars(rawurldecode(rawurldecode($match[4]))), 170);
        $name = '<a href="'.$a.'" target=_blank>'.$text.'</a>';
        $graphs['Google Search'] += $item['total'];
      } elseif (preg_match('/^(https?:\/\/(www.)?google[\.a-z]+\/url\?).*&url=(.*)($|\&.*)/iU', $item['referer'], $match) && $match[3] != '') {
        // จาก google
        $a = rawurldecode(rawurldecode($match[3]));
        $text = gcms::cutstring($match[1].'url='.htmlspecialchars($a), 170);
        $name = '<a href="'.$a.'" target=_blank>'.$text.'</a>';
        $graphs['Google Cached'] += $item['total'];
      } elseif ($item['referer'] == '') {
        $name = 'localhost';
        $graphs['Direct'] += $item['total'];
      } elseif (preg_match('/'.preg_quote(WEB_URL, '/').'/', $item['referer'], $match)) {
        $graphs['Inbound'] += $item['total'];
        $text = gcms::cutstring(htmlspecialchars(rawurldecode(rawurldecode($item['referer']))), 170);
        $name = '<a href="'.htmlspecialchars($item['referer']).'" target=_blank>'.$text.'</a>';
      } else {
        $graphs['other'] += $item['total'];
        $text = gcms::cutstring(htmlspecialchars(rawurldecode(rawurldecode($item['referer']))), 170);
        $name = '<a href="'.htmlspecialchars($item['referer']).'" target=_blank>'.$text.'</a>';
      }
      $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
      $list[] = '<tr class='.$bg.'><td class="center mobile">'.$i.'</td><td class=mobile>'.$item['ip'].'</td><td class=tablet>'.$item['total'].'</td><td>'.$name.'</td></tr>';
    }
    // รวม bot
    foreach ($agents AS $a => $b) {
      $total = $total + $b;
    }
    $title = sprintf($lng['USERONLINE_REPORT_TITLE'], sql::sql_date2date($date));
    // แสดงผล
    $content[] = '<div class=breadcrumbs><ul><li><span class=icon-summary>'.$title.'</span></li></ul></div>';
    $content[] = '<section>';
    $content[] = '<header><h1 class=icon-stats>{LNG_TOTAL} '.$total.' {LNG_COUNT}, '.sizeof($ips).' Uniqe IP, '.sizeof($ips).' Uniqe Session</h1></header>';
    // ตารางข้อมูล
    $content[] = '<table id=report class="tbl_list fullwidth">';
    $content[] = '<thead>';
    $content[] = '<tr>';
    $content[] = '<th id=c0 scope=col class=mobile>&nbsp;</th>';
    $content[] = '<th id=c1 scope=col class=mobile><a href="index.php?module=report&amp;date='.$date.'&amp;type=ip">{LNG_IP}</a></th>';
    $content[] = '<th id=c2 scope=col class=tablet>{LNG_COUNT}</th>';
    $content[] = '<th id=c3 scope=col><a href="index.php?module=report&amp;date='.$date.'">{LNG_REFERER}</a></th>';
    $content[] = '</tr>';
    $content[] = '</thead>';
    $content[] = '<tbody>'.implode("\n", $list).'</tbody>';
    $content[] = "</table>";
    // graphs
    $content[] = '<div class="ggrid collapse">';
    $content[] = '<div class="block6 float-left">';
    $graphs = array_merge($graphs, $agents);
    $content[] = '<div id=online_graph class=ggraphs>';
    $content[] = '<canvas></canvas>';
    $content[] = '<table class=hidden>';
    $content[] = '<thead><tr><th></th>';
    foreach ($graphs AS $k => $v) {
      $content[] = '<td>'.$k.'</td>';
    }
    $content[] = '</tr></thead>';
    $content[] = '<tbody><tr><th>{LNG_USERONLINE_GRAPH_REPORT}</th>';
    foreach ($graphs AS $k => $v) {
      $content[] = '<td>'.$v.'</td>';
    }
    $content[] = '</tr></tbody>';
    $content[] = '</table>';
    $content[] = '</div>';
    $content[] = '</div>';
    $content[] = '<div class="block6 float-right">';
    $content[] = '<div id=uniqe_graph class=ggraphs>';
    $content[] = '<canvas></canvas>';
    $content[] = '<table class=hidden>';
    $content[] = '<thead><tr><th></th><td>Uniqe Session</td><td>Uniqe IP</td></tr></thead>';
    $content[] = '<tbody><tr><th>{LNG_USERONLINE_UNIQE_REPORT}</th><td>'.sizeof($ssid).'</td><td>'.sizeof($ips).'</td></tr></tbody>';
    $content[] = '</table>';
    $content[] = '</div>';
    $content[] = '</div>';
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    $content[] = 'new gGraphs("online_graph", {type:"hchart"});';
    $content[] = 'new gGraphs("uniqe_graph", {type:"pie",startColor:9,centerX:Math.round($G("uniqe_graph").getHeight() / 2)});';
    $content[] = '});';
    $content[] = '</script>';
    // หน้าปัจจุบัน
    $url_query['module'] = 'report';
  } else {
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
