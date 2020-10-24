<?php
// admin/editprofile.php
if (MAIN_INIT == 'admin' && $canAdmin) {
  $user = false;
  // id ของสมาชิกที่เลือก
  $id = (int)$_GET['id'];
  if ($id > 0) {
    // query ข้อมูลสมาชิกที่เลือก
    $sql = "SELECT U.*,V.`email` AS `invite`";
    $sql .= " FROM `".DB_USER."` AS U";
    $sql .= " LEFT JOIN `".DB_USER."` AS V ON V.`id`=U.`invite_id`";
    $sql .= " WHERE U.`id`='$id' LIMIT 1";
    $user = $db->customQuery($sql);
    $user = sizeof($user) == 1 && ($isAdmin || $_SESSION['login']['id'] == $user[0]['id']) ? $user[0] : false;
  } elseif ($isAdmin) {
    // สมาชิกใหม่
    $user = array();
    $user['id'] = 0;
    $user['pname'] = '';
    $user['fname'] = '';
    $user['lname'] = '';
    $user['email'] = '';
    $user['displayname'] = '';
    $user['website'] = '';
    $user['company'] = '';
    $user['address1'] = '';
    $user['address2'] = '';
    $user['phone1'] = '';
    $user['phone2'] = '';
    $user['sex'] = 'f';
    $user['country'] = 'TH';
    $user['status'] = 0;
    $user['subscrib'] = 1;
    $user['invite'] = '';
    $user['admin_access'] = 0;
    $user['provinceID'] = '';
  }
  if (is_array($user)) {
    $title = $id == 0 ? '{LNG_REGISTER_TITLE}' : '{LNG_MEMBER_EDIT_TITLE}';
    $patt2 = array('/{TITLE}/', '/{(LNG_[A-Z0-9_]+)}/e', '/\%1/', '/\%2/', '/\%3/', '/{ADMIN}/', '/{ACCEPT}/');
    $replace2 = array();
    $replace2[] = $title;
    $replace2[] = 'gcms::getLng';
    $replace2[] = $config['user_icon_w'];
    $replace2[] = $config['user_icon_h'] == 0 ? $config['user_icon_w'] : $config['user_icon_h'];
    $replace2[] = $config['user_icon_typies'] == '' ? 'jpg' : implode(', ', $config['user_icon_typies']);
    $replace2[] = $isAdmin && $user['fb'] == 0 ? '' : 'disabled';
    $replace2[] = gcms::getEccept(array('jpg', 'png', 'gif'));
    foreach ($user AS $key => $value) {
      $patt2[] = '/{'.strtoupper($key).'}/';
      if ($key == 'sex') {
        // เพศ
        $datas = array();
        foreach ($lng['SEX'] AS $sex => $name) {
          $sel = $sex == $user['sex'] ? ' selected' : '';
          $datas[] = '<option value='.$sex.$sel.'>'.$name.'</option>';
        }
        $replace2[] = implode('', $datas);
      } elseif ($key == 'country') {
        // รายชื่อประเทศ
        $user['country'] = $user['country'] == '' ? 'TH' : $user['country'];
        $datas = array();
        $sql = "SELECT `iso`,`printable_name` FROM `".DB_COUNTRY."`";
        foreach ($db->customQuery($sql) AS $item) {
          $sel = $user['country'] == $item['iso'] ? ' selected' : '';
          $datas[] = '<option value='.$item['iso'].$sel.'>'.$item['printable_name'].'</option>';
        }
        $replace2[] = implode('', $datas);
      } elseif ($key == 'status') {
        // status
        $datas = array();
        foreach ($config['member_status'] AS $i => $value) {
          if ($isAdmin || $user['status'] == $i) {
            $sel = $i == $user['status'] ? ' selected' : '';
            $datas[] = '<option value='.$i.$sel.'>'.$value.'</option>';
          }
        }
        $replace2[] = implode('', $datas);
      } elseif ($key == 'subscrib') {
        $replace2[] = $user['subscrib'] == 1 ? 'checked' : '';
      } elseif ($key == 'admin_access') {
        $replace2[] = $user['admin_access'] == 1 ? 'checked' : '';
      } elseif ($key == 'provinceID') {
        // จังหวัด
        $provinces = array();
        $provinces[] = '<option value="">--- '.$lng['LNG_PLEASE_SELECT'].' ---</option>';
        $sql = "SELECT `id`, `name` FROM `".DB_PROVINCE."`";
        $datas = $cache->get($sql);
        if (!$datas) {
          $datas = $db->customQuery($sql);
          $cache->save($sql, $datas);
        }
        foreach ($datas AS $item) {
          $sel = $user['provinceID'] == $item['id'] ? ' selected' : '';
          $provinces[] = '<option value='.$item['id'].$sel.'>'.$item['name'].'</option>';
        }
        $replace2[] = implode('', $provinces);
      } else {
        $replace2[] = $value;
      }
    }
    $content[] = gcms::pregReplace($patt2, $replace2, gcms::loadfile(ROOT_PATH."admin/skin/$config[admin_skin]/editprofile.html"));
    // หน้าปัจจุบัน
    $url_query['module'] = 'editprofile';
  } else {
    // ไม่ได้กำหนด id มา หรือ ไม่พบ user ที่เรียก
    $title = $lng['LNG_DATA_NOT_FOUND'];
    $content[] = '<aside class=error>'.$title.'</aside>';
  }
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
