<?php
// admin/database.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_DATABASE_TITLE'];
  $a = array();
  $a[] = '<span class=icon-tools>{LNG_TOOLS}</span>';
  $a[] = '{LNG_DATABASE}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-database>{LNG_DATABASE_TITLE}</h1></header>';
  $content[] = '<div class=setup_frm>';
  $content[] = '<form id=export_frm class=paper method=post action=export.php target=_blank>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span class=icon-export>{LNG_DATABASE_BACKUP}</span></legend>';
  // backup database
  $content[] = '<div class=subtitle>'.str_replace('%s', $config['db_name'], $lng['LNG_DATABASE_EXPORT_TITLE']).'</div>';
  $content[] = '<div class=item>';
  $content[] = '<table class="responsive database fullwidth"><tbody id=language_tbl>';
  $content[] = '<tr><td class=tablet></td><td colspan=3 class=left><a href="javascript:setSelect(\'language_tbl\',true)">{LNG_SELECT_ALL}</a>&nbsp;|&nbsp;<a href="javascript:setSelect(\'language_tbl\',false)">{LNG_CLEAR_SELECTED}</a></td></tr>';
  // ตารางทั้งหมด
  $tables = $db->customQuery("SHOW TABLE STATUS");
  foreach ($tables AS $table) {
    if (preg_match('/^'.PREFIX.'(.*?)$/', $table['Name'], $match)) {
      $tr = '<tr>';
      $tr .= '<th>'.$table['Name'].'</th>';
      $tr .= '<td><label><input type=checkbox name='.$table['Name'].'[] value=sturcture checked>&nbsp;{LNG_STRUCTURE}</label></td>';
      if ($table['Name'] == DB_LANGUAGE) {
        $trs = array();
        $l = array('id', 'key', 'type', 'owner', 'js');
        foreach ($db->customQuery("SHOW FIELDS FROM ".DB_LANGUAGE) AS $item2) {
          if (!in_array($item2['Field'], $l)) {
            $trs[] = '<label><input type=checkbox name=language_lang[] value='.$item2['Field'].' checked>&nbsp;'.$item2['Field'].'</label>';
          }
        }
        $tr .= '<td>'.implode(', ', $trs).'</td>';
        $trs = array();
        $sql = "SELECT `owner` FROM `".DB_LANGUAGE."` GROUP BY `owner`";
        foreach ($db->customQuery($sql) AS $item3) {
          if ($item3['owner'] != '' && ($design_mode == 'design' || $item3['owner'] != 'sysadmin')) {
            $trs[] = '<label><input type=checkbox name=language_owner[] value="'.$item3['owner'].'" checked>&nbsp;'.$item3['owner'].'</label>';
          }
        }
        $tr .= '<td>'.implode(', ', $trs).'</td>';
      } else {
        $tr .= '<td><label><input type=checkbox name='.$table['Name'].'[] value=datas checked>&nbsp;{LNG_DATAS}</label></td>';
        $tr .= '<td>&nbsp;</td>';
      }
      $tr .= '</tr>';
      $content[] = $tr;
    }
  }
  $content[] = '<tr><td class=tablet></td><td colspan=3 class=left><a href="javascript:setSelect(\'language_tbl\',true)">{LNG_SELECT_ALL}</a>&nbsp;|&nbsp;<a href="javascript:setSelect(\'language_tbl\',false)">{LNG_CLEAR_SELECTED}</a></td></tr>';
  $content[] = '</tbody></table>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  $content[] = '<fieldset class=submit><input type=submit class="button large save" value="{LNG_DATABASE_SAVE}"></fieldset>';
  $content[] = '</form>';
  // restore database
  $content[] = '<form id=import_frm method=post action=index.php>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span class=icon-import>{LNG_DATABASE_RESTORE}</span></legend>';
  $content[] = '<div class=item>';
  $t = str_replace('%s', $config['db_name'], $lng['LNG_DATABASE_BROWSER_COMMENT']);
  $content[] = '<label>'.str_replace('%s', ini_get('upload_max_filesize'), $lng['LNG_IMPORT_BROWSER']).'</label>';
  $content[] = '<span class="g-input icon-upload"><input class=g-file type=file name=import_file placeholder="{LNG_BROWSE_FILE}" title="'.strip_tags($t).'"></span>';
  $content[] = '<div class=comment id=result_import_file>'.$t.'</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  $content[] = '<fieldset class=submit><input type=submit class="button large save" value="{LNG_DATABASE_UPLOAD}"></fieldset>';
  $content[] = '<aside class=warning>{LNG_DATABASE_COMMENT}</aside>';
  $content[] = '</form>';
  $content[] = '</div>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'new GForm("import_frm", "'.WEB_URL.'/admin/import.php").onsubmit(doFormSubmit);';
  $content[] = '});';
  $content[] = '</script>';
  $content[] = '</section>';
  // หน้าปัจจุบัน
  $url_query['module'] = 'database';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
