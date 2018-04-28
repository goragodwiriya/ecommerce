<?php
// admin/savestatus.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../bin/inint.php';
// referer, admin
if (gcms::isReferer() && gcms::isAdmin()) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    // action
    $action = $_POST['action'];
    // โหลด config ใหม่
    $config = array();
    if (is_file(CONFIG)) {
      include CONFIG;
    }
    if ($action == 'config_status_add') {
      if (!isset($config['member_status'][0])) {
        $config['member_status'][0] = 'สมาชิก';
        $config['color_status'][0] = '#006600';
      }
      if (!isset($config['member_status'][1])) {
        $config['member_status'][1] = 'ผู้ดูแลระบบ';
        $config['color_status'][1] = '#FF0000';
      }
      // เพิ่มสถานะสมาชิกใหม่
      $config['member_status'][] = "$lng[LNG_CLICK_TO] $lng[LNG_EDIT]";
      $config['color_status'][] = '#000000';
      // id ของสถานะใหม่
      $i = sizeof($config['member_status']) - 1;
      // ข้อมูลใหม่
      $row = '<dd id="config_status_'.$i.'">';
      $row .= '<span class="icon-delete" id="config_status_delete_'.$i.'" title="'.$lng['LNG_DELETE'].' '.$lng['LNG_MEMBER_STATUS'].'"></span>';
      $row .= '<span id="config_status_color_'.$i.'" title="'.$config['color_status'][$i].'">&nbsp;</span>';
      $row .= '<span id="config_status_name_'.$i.'" title="'.$config['member_status'][$i].'">'.htmlspecialchars($config['member_status'][$i]).'</span>';
      $row .= '</dd>';
      // คืนค่าข้อมูลเข้ารหัส
      $ret['data'] = rawurlencode($row);
      $ret['newId'] = "config_status_$i";
    } elseif (preg_match('/^config_status_delete_([0-9]+)$/', $action, $match)) {
      // ลบ
      $save1 = array();
      $save2 = array();
      // ลบสถานะและสี
      for ($i = 0; $i < sizeof($config['member_status']); $i++) {
        if ($i < 2 || $i != $match[1]) {
          $save1[] = $config['member_status'][$i];
          $save2[] = $config['color_status'][$i];
        }
      }
      $config['member_status'] = $save1;
      $config['color_status'] = $save2;
      // รายการที่ลบ
      $ret['del'] = str_replace('delete_', '', $action);
    } elseif (preg_match('/^config_status_(name|color)_([0-9]+)$/', $action, $match)) {
      // แก้ไขชื่อสถานะหรือสี
      $value = trim(htmlspecialchars($_POST['value']));
      if ($value == '' && $match[1] == 'name') {
        $value = $config['member_status'][$match[2]];
      } elseif ($value == '' && $match[1] == 'color') {
        $value = $config['color_status'][$match[2]];
      } else {
        $name = $match[1] == 'name' ? 'member_status' : 'color_status';
        if (is_array($config[$name])) {
          foreach ($config[$name] AS $i => $item) {
            $config[$name][$i] = $i == $match[2] ? $value : $item;
          }
        } else {
          $config[$name][$i] = $i == $match[2] ? $value : $item;
        }
      }
      // ส่งข้อมูลใหม่ไปแสดงผล
      $ret['edit'] = rawurlencode(htmlspecialchars($value));
      $ret['editId'] = $action;
    } else {
      $ret['error'] = 'ACTION_ERROR';
    }
    if (!isset($ret['error'])) {
      // บันทึก config.php
      if (!gcms::saveconfig(CONFIG, $config)) {
        $ret['error'] = 'DO_NOT_SAVE';
      }
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
