<?php
// modules/payment/address.php
if (defined('MAIN_INIT') && $user && $index) {
  // ประเทศที่เลือก
  $country = $user['country'] == '' ? 'TH' : $user['country'];
  // ประเทศและค่าขนส่ง
  $sql = "SELECT C.`iso`,C.`printable_name` FROM `".DB_COUNTRY."` AS C";
  $sql .= " ORDER BY C.`printable_name`";
  $list = $cache->get($sql);
  if (!$list) {
    $list = $db->customQuery($sql);
    $cache->save($sql, $list);
  }
  $countries = array();
  foreach ($list AS $i => $item) {
    $sel = $country == $item['iso'] ? ' selected' : '';
    $id = $i == 0 ? ' id=transport_method' : '';
    $countries[$item['iso']] = '<option value='.$item['iso'].$sel.$id.'>'.$item['printable_name'].'</option>';
  }
  // แสดงผล
  $patt = array('/{COUNTRIES}/', '/{WEBURL}/', '/{(LNG_[A-Z0-9_]+)}/e', '/{FNAME}/', '/{LNAME}/', '/{ADDRESS1}/',
    '/{ADDRESS2}/', '/{PROVINCE}/', '/{ZIPCODE}/', '/{PHONE1}/', '/{PHONE2}/', '/{COMPANY}/', '/{MODULE}/');
  $replace = array();
  $replace[] = implode('', $countries);
  $replace[] = WEB_URL;
  $replace[] = 'gcms::getLng';
  $replace[] = $user['fname'];
  $replace[] = $user['lname'];
  $replace[] = $user['address1'];
  $replace[] = $user['address2'];
  $replace[] = $user['province'];
  $replace[] = $user['zipcode'];
  $replace[] = $user['phone1'];
  $replace[] = $user['phone2'];
  $replace[] = $user['company'];
  $replace[] = $index['owner'];
  $content = gcms::pregReplace($patt, $replace, gcms::loadtemplate($index['owner'], 'payment', 'address'));
  // title
  $title = $lng['LNG_CONFIRM_YOUR_ADDRESS_TITLE'];
} else {
  $title = $lng['PAGE_NOT_FOUND'];
  $content = '<div class=error>'.$title.'</div>';
}
