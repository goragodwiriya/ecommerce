<?php
// modules/payment/admin_config_save.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// referer, member
if (gcms::isReferer() && gcms::canConfig($config['payment_can_config'])) {
  if ($_SESSION['login']['account'] == 'demo') {
    $ret['error'] = 'EX_MODE_ERROR';
  } else {
    $error = false;
    $input = false;
    // โหลด config ใหม่
    $config = array();
    if (is_file(CONFIG)) {
      include CONFIG;
    }
    // payments_method
    $payments_method = array();
    foreach ($_POST['method'] AS $i => $item) {
      $item = $db->sql_quote($item);
      if ($item != '') {
        $file = $_FILES['method_file'];
        if ($file['tmp_name'][$i] != '') {
          // ตรวจสอบไฟล์อัปโหลด
          $info = gcms::isValidImage(array('jpg', 'gif', 'png'), array('tmp_name' => $file['tmp_name'][$i], 'name' => $file['name'][$i]));
          if (!$info) {
            $ret["ret_method_file_$i"] = 'INVALID_FILE_TYPE';
            $error = !$error ? 'INVALID_FILE_TYPE' : $error;
            $input = !$input ? "method_file_$i" : $input;
          } else {
            $f = DATA_FOLDER."payment/$i.$info[ext]";
            if (@move_uploaded_file($file['tmp_name'][$i], ROOT_PATH.$f)) {
              $payments_method[] = array($item, $f);
            } else {
              $ret["ret_method_file_$i"] = 'DO_NOT_UPLOAD';
              $error = !$error ? 'DO_NOT_UPLOAD' : $error;
              $input = !$input ? "method_file_$i" : $input;
            }
          }
        } else {
          $payments_method[] = array($item, $config['payments_method'][$i][1]);
        }
      }
    }
    if (sizeof($payments_method) == 0) {
      $ret['ret_method_0'] = 'DO_NOT_EMPTY';
      $input = !$input ? 'method_0' : $input;
      $error = !$error ? 'DO_NOT_EMPTY' : $error;
    } else if (!$error) {
      foreach ($_POST['method'] AS $i => $item) {
        if (isset($payments_method[$i])) {
          $ret["method_$i"] = rawurlencode(stripslashes($payments_method[$i][0]));
          if (is_file(ROOT_PATH.$payments_method[$i][1])) {
            $ret["method_img_$i"] = rawurlencode(WEB_URL.'/'.$payments_method[$i][1].'?'.$mmktime);
          } else {
            $ret["method_img_$i"] = rawurlencode(WEB_URL.'/modules/payment/img/bank.png');
          }
          $ret["ret_method_file_$i"] = '';
        } elseif ($i > 0) {
          $ret["remove$i"] = "pm_$i";
        }
      }
      $config['payments_method'] = $payments_method;
    }
    if (!$error) {
      $config['payment_saleman'] = $_POST['config_saleman'];
      $config['payment_saleman'][] = 1;
      $config['payment_can_config'] = $_POST['config_can_config'];
      $config['payment_can_config'][] = 1;
      // บันทึก config.php
      if (gcms::saveconfig(CONFIG, $config)) {
        $ret['error'] = 'SAVE_COMPLETE';
        $ret['location'] = 'reload';
      } else {
        $ret['error'] = 'DO_NOT_SAVE';
      }
    } else {
      if ($input) {
        $ret['input'] = $input;
      }
      $ret['error'] = $error;
    }
  }
} else {
  $ret['error'] = 'ACTION_ERROR';
}
// คืนค่าเป็น JSON
echo gcms::array2json($ret);
