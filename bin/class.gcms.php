<?php
// bin/class.gcms.php
mb_internal_encoding('utf-8');
// เวลาปัจจุบัน
$mmktime = mktime(date("H") + $config['hour']);
$myear = (int)date('Y', $mmktime);
$mmonth = (int)date('m', $mmktime);
$mtoday = (int)date('d', $mmktime);

// gcms class
class gcms
{

  // แปลงเวลา (mktime) เป็นวันที่ตามรูปแบบที่กำหนด
  public static function mktime2date($mmktime, $format = false)
  {
    global $lng;
    if (preg_match_all('/(.)/u', $format == false ? $lng['DATE_FORMAT'] : $format, $match)) {
      $ret = '';
      foreach ($match[0] AS $item) {
        switch ($item) {
          case 'D':
            $ret .= $lng['DATE_SHORT'][date('j', $mmktime) - 1];
            break;
          case 'l':
            $ret .= $lng['DATE_LONG'][date('j', $mmktime) - 1];
            break;
          case 'M':
            $ret .= $lng['MONTH_SHORT'][date('n', $mmktime) - 1];
            break;
          case 'F':
            $ret .= $lng['MONTH_LONG'][date('n', $mmktime) - 1];
            break;
          case 'Y':
            $ret .= date('Y', $mmktime) + $lng['YEAR_OFFSET'];
            break;
          default:
            $ret .= date($item, $mmktime);
        }
      }
      return $ret;
    } else {
      return $format == false ? $lng['DATE_FORMAT'] : $format;
    }
  }

  // ฟังก์ชั่น สุ่มตัวอักษร
  public static function rndname($count, $chars = 'abcdefghjkmnpqrstuvwxyz')
  {
    srand((double)microtime() * 10000000);
    $ret = "";
    $num = strlen($chars);
    for ($i = 0; $i < $count; $i++) {
      $ret .= $chars[rand() % $num];
    }
    return $ret;
  }

  /**
   * cutstring($str, $len)
   * ฟังก์ชั่น ตัดสตริงค์ตามความยาวที่กำหนด (utf8)
   * $str (string) ข้อความที่ต้องการตัด
   * $len (int) ความยาวที่ต้องการ หากข้อความที่นำมาตัดยาวกว่าที่กำหนด
   * จะตัดข้อความที่เกินออก และเติม .. ข้างท้าย
   * (จำนวนตัวอักษรรวมจุด จะเท่ากับ ความยาวที่กำหนด)
   *
   * @return string
   */
  public static function cutstring($str, $len)
  {
    $len = (int)$len;
    if ($len == 0) {
      return $str;
    } else {
      return (mb_strlen($str) <= $len || $len < 3) ? $str : mb_substr($str, 0, $len - 2)."..";
    }
  }

  /**
   * getMimeTypies($typies)
   * อ่าน mimetype จาก file type แบบ ออนไลน์
   * $typies (array) ชนิดของไฟล์ที่ยอมรับ เช่น jpg gif png
   *
   * คืนค่า แอเรย์ ของ mimetype ที่พบ เช่น $s['php'] = 'text/html';
   *
   * @return array
   */
  public static function getMimeTypies($typies)
  {
    global $config;
    $s = array();
    $es = array();
    if (is_array($config['mimeTypes'])) {
      foreach ($typies AS $ext) {
        if ($config['mimeTypes'][$ext] != '') {
          $s[$ext] = $config['mimeTypes'][$ext];
        } else {
          $es[] = $ext;
        }
      }
    } else {
      $es = $typies;
    }
    if (sizeof($es) > 0) {
      $content = '';
      if (is_file(DATA_PATH.'cache/mime.types')) {
        $content = trim(@file_get_contents(DATA_PATH.'cache/mime.types'));
      }
      if ($content == '') {
        // ตรวจสอบ mimetype ออนไลน์
        $content = trim(@file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'));
        if ($content != '') {
          // cache
          $f = @fopen(DATA_PATH.'cache/mime.types', 'wb');
          if ($f) {
            fwrite($f, $content);
            fclose($f);
          }
        }
      }
      if ($content != '') {
        foreach (explode("\n", $content) AS $x) {
          if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = sizeof($out[1])) > 1) {
            for ($i = 1; $i < $c; $i++) {
              if (in_array($out[1][$i], $typies)) {
                $s[$out[1][$i]] = $out[1][0];
              }
            }
          }
        }
      }
    }
    return $s;
  }

  /**
   * checkMIMEType($typies, $filetype)
   * ตรวจสอบ mimetype ที่ต้องการ
   * $typies (array) ชนิดของไฟล์ที่ยอมรับ เช่น jpg gif png
   * $mime (string) ชนิดของไฟล์ที่ต้องการตรวจสอบ เช่น image/png ซึ่งปกติจะได้จากการอัปโหลด
   *
   * คืนค่า true ถ้าพบ
   *
   * @return boolean
   */
  public static function checkMIMEType($typies, $mime)
  {
    global $config;
    foreach ($typies AS $t) {
      if ($mime == $config['mimeTypes'][$t]) {
        return true;
      }
    }
    return false;
  }

  /**
   * getEccept($typies)
   * คืนค่า mimetype ของไฟล์ สำหรับส่งให้ input ชนิด file
   * $typies (array) ชนิดของไฟล์ เช่น jpg gif png
   *
   * คืนค่า mimetype ของไฟล์ คั่นแต่ละรายการด้วย , เช่น image/jpeg,image/png,image/gif
   *
   * @return string
   */
  public static function getEccept($typies)
  {
    global $config;
    $accept = array();
    foreach ($typies AS $ext) {
      if (isset($config['mimeTypes'][$ext])) {
        $accept[] = $config['mimeTypes'][$ext];
      }
    }
    return implode(',', $accept);
  }

  // ตรวจสอบไฟล์อัปโหลด
  public static function isValidImage($typies, $files)
  {
    $imageinfo = array();
    // ext
    $imageinfo['ext'] = strtolower(end(explode('.', $files['name'])));
    if (!in_array($imageinfo['ext'], $typies)) {
      return false;
    } else {
      // Exif
      $info = getImageSize($files['tmp_name']);
      if ($info[0] == 0 || $info[1] == 0 || !gcms::checkMIMEType($typies, $info['mime'])) {
        return false;
      } else {
        $imageinfo['width'] = $info[0];
        $imageinfo['height'] = $info[1];
        $imageinfo['mime'] = $info['mime'];
        return $imageinfo;
      }
    }
  }

  // ฟังก์ชั่นอ่านข้อมูลรูปภาพ
  public static function imageInfo($img)
  {
    // Exif
    $info = getImageSize($img);
    $imageinfo['width'] = $info[0];
    $imageinfo['height'] = $info[1];
    $imageinfo['mime'] = $info['mime'];
    return $imageinfo;
  }

  // ฟังก์ชั่น ตัดรูปภาพ ตามขนาดที่กำหนด
  public static function cropImage($source, $target, $info, $thumbwidth, $thumbheight, $watermark = '')
  {
    switch ($info['mime']) {
      case 'image/gif':
        $o_im = imageCreateFromGIF($source);
        break;
      case 'image/jpg':
      case 'image/jpeg':
      case 'image/pjpeg':
        $o_im = gcms::orientImage($source);
        break;
      case 'image/png':
      case 'image/x-png':
        $o_im = imageCreateFromPNG($source);
        break;
      default:
        return false;
    }
    $wm = $info['width'] / $thumbwidth;
    $hm = $info['height'] / $thumbheight;
    $h_height = $thumbheight / 2;
    $w_height = $thumbwidth / 2;
    $t_im = ImageCreateTrueColor($thumbwidth, $thumbheight);
    $int_width = 0;
    $int_height = 0;
    $adjusted_width = $thumbwidth;
    $adjusted_height = $thumbheight;
    if ($info['width'] > $info['height']) {
      $adjusted_width = ceil($info['width'] / $hm);
      $half_width = $adjusted_width / 2;
      $int_width = $half_width - $w_height;
      if ($adjusted_width < $thumbwidth) {
        $adjusted_height = ceil($info['height'] / $wm);
        $half_height = $adjusted_height / 2;
        $int_height = $half_height - $h_height;
        $adjusted_width = $thumbwidth;
        $int_width = 0;
      }
    } elseif (($info['width'] < $info['height']) || ($info['width'] == $info['height'])) {
      $adjusted_height = ceil($info['height'] / $wm);
      $half_height = $adjusted_height / 2;
      $int_height = $half_height - $h_height;
      if ($adjusted_height < $thumbheight) {
        $adjusted_width = ceil($info['width'] / $hm);
        $half_width = $adjusted_width / 2;
        $int_width = $half_width - $w_height;
        $adjusted_height = $thumbheight;
        $int_height = 0;
      }
    }
    ImageCopyResampled($t_im, $o_im, -$int_width, -$int_height, 0, 0, $adjusted_width, $adjusted_height, $info['width'], $info['height']);
    if ($watermark != '') {
      $t_im = gcms::watermarkText($t_im, $watermark);
    }
    $ret = @ImageJPEG($t_im, $target);
    imageDestroy($o_im);
    imageDestroy($t_im);
    return $ret;
  }

  // ฟังก์ชั่นปรับขนาดของภาพ รักษาอัตราส่วนของภาพตามความกว้างที่ต้องการ
  public static function resizeImage($source, $target, $name, $info, $width, $watermark = '')
  {
    if ($info['width'] > $width || $info['height'] > $width) {
      if ($info['width'] <= $info['height']) {
        $h = $width;
        $w = round($h * $info['width'] / $info['height']);
      } else {
        $w = $width;
        $h = round($w * $info['height'] / $info['width']);
      }
      switch ($info['mime']) {
        case 'image/gif':
          $o_im = imageCreateFromGIF($source);
          break;
        case 'image/jpg':
        case 'image/jpeg':
        case 'image/pjpeg':
          $o_im = gcms::orientImage($source);
          break;
        case 'image/png':
        case 'image/x-png':
          $o_im = imageCreateFromPNG($source);
          break;
      }
      $o_wd = @imagesx($o_im);
      $o_ht = @imagesy($o_im);
      $t_im = @ImageCreateTrueColor($w, $h);
      @ImageCopyResampled($t_im, $o_im, 0, 0, 0, 0, $w + 1, $h + 1, $o_wd, $o_ht);
      if ($watermark != '') {
        $t_im = gcms::watermarkText($t_im, $watermark);
      }
      $newname = substr($name, 0, strrpos($name, '.')).'.jpg';
      if (!@ImageJPEG($t_im, $target.$newname)) {
        $ret = false;
      } else {
        $ret['name'] = $newname;
        $ret['width'] = $w;
        $ret['height'] = $h;
        $ret['mime'] = 'image/jpeg';
      }
      @imageDestroy($o_im);
      @imageDestroy($t_im);
      return $ret;
    } elseif (@copy($source, $target.$name)) {
      $ret['name'] = $name;
      $ret['width'] = $info['width'];
      $ret['height'] = $info['height'];
      $ret['mime'] = $info['mime'];
      return $ret;
    }
    return false;
  }

  // โหลดภาพ jpg และหมุนภาพอัตโนมัติ
  public static function orientImage($source)
  {
    $imgsrc = imageCreateFromJPEG($source);
    if (function_exists('exif_read_data')) {
      // read image exif and rotate
      $exif = exif_read_data($source);
      if ($exif['Orientation'] == 2) {
        // horizontal flip
        $imgsrc = gcms::flipImage($imgsrc);
      } elseif ($exif['Orientation'] == 3) {
        // 180 rotate left
        $imgsrc = imagerotate($imgsrc, 180, 0);
      } elseif ($exif['Orientation'] == 4) {
        // vertical flip
        $imgsrc = gcms::flipImage($imgsrc);
      } elseif ($exif['Orientation'] == 5) {
        // vertical flip + 90 rotate right
        $imgsrc = imagerotate($imgsrc, 270, 0);
        $imgsrc = gcms::flipImage($imgsrc);
      } elseif ($exif['Orientation'] == 6) {
        // 90 rotate right
        $imgsrc = imagerotate($imgsrc, 270, 0);
      } elseif ($exif['Orientation'] == 7) {
        // horizontal flip + 90 rotate right
        $imgsrc = imagerotate($imgsrc, 90, 0);
        $imgsrc = gcms::flipImage($imgsrc);
      } elseif ($exif['Orientation'] == 8) {
        // 90 rotate left
        $imgsrc = imagerotate($imgsrc, 90, 0);
      }
    }
    return $imgsrc;
  }

  // กลับรูปภาพ
  public static function flipImage($imgsrc)
  {
    $width = imagesx($imgsrc);
    $height = imagesy($imgsrc);
    $src_x = $width - 1;
    $src_y = 0;
    $src_width = -$width;
    $src_height = $height;
    $imgdest = imagecreatetruecolor($width, $height);
    if (imagecopyresampled($imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height)) {
      return $imgdest;
    }
    return $imgsrc;
  }

  // ลายน้ำ
  public static function watermarkText($imgsrc, $text, $pos = '', $color = 'CCCCCC', $font_size = 20, $opacity = 50)
  {
    $font = ROOT_PATH.'skin/fonts/leelawad.ttf';
    $offset = 5;
    $alpha_color = imagecolorallocatealpha($imgsrc, hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)), 127 * (100 - $opacity) / 100);
    $box = imagettfbbox($font_size, 0, $font, $text);
    if (preg_match('/center/i', $pos)) {
      $y = $box[1] + (imagesy($imgsrc) / 2) - ($box[5] / 2);
    } elseif (preg_match('/bottom/i', $pos)) {
      $y = imagesy($imgsrc) - $offset;
    } else {
      $y = $box[1] - $box[5] + $offset;
    }
    if (preg_match('/center/i', $pos)) {
      $x = $box[0] + (imagesx($imgsrc) / 2) - ($box[4] / 2);
    } elseif (preg_match('/right/i', $pos)) {
      $x = $box[0] - $box[4] + imagesx($imgsrc) - $offset;
    } else {
      $x = $offset;
    }
    imagettftext($imgsrc, $font_size, 0, $x, $y, $alpha_color, $font, $text);
    return $imgsrc;
  }

  // ฟังก์ชั่น แปลงข้อความภาษาไทยเป็น ข้อความ HTML เช่น ก แปลงเป้น &#3585;
  public static function text2HTML($utf8, $encodeTags)
  {
    $result = '';
    for ($i = 0; $i < strlen($utf8); $i++) {
      $char = $utf8[$i];
      $ascii = ord($char);
      if ($ascii < 128) {
        // one-byte character
        $result .= ($encodeTags) ? htmlentities($char) : $char;
      } else if ($ascii < 192) {
        // non-utf8 character or not a start byte
      } else if ($ascii < 224) {
        // two-byte character
        $result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
        $i++;
      } else if ($ascii < 240) {
        // three-byte character
        $ascii1 = ord($utf8[$i + 1]);
        $ascii2 = ord($utf8[$i + 2]);
        $unicode = (15 & $ascii) * 4096 + (63 & $ascii1) * 64 + (63 & $ascii2);
        $result .= "&#$unicode;";
        $i += 2;
      } else if ($ascii < 248) {
        // four-byte character
        $ascii1 = ord($utf8[$i + 1]);
        $ascii2 = ord($utf8[$i + 2]);
        $ascii3 = ord($utf8[$i + 3]);
        $unicode = (15 & $ascii) * 262144 + (63 & $ascii1) * 4096 + (63 & $ascii2) * 64 + (63 & $ascii3);
        $result .= "&#$unicode;";
        $i += 3;
      }
    }
    return $result;
  }

  // ฟังก์ชั่นส่งเมล์จาก template
  public static function sendMail($id, $module, $datas, $to)
  {
    global $db, $config, $mmktime;
    $sql = "SELECT * FROM `".DB_EMAIL_TEMPLATE."`";
    $sql .= " WHERE `module`='$module' AND `email_id`='$id' AND `language` IN ('".LANGUAGE."','th')";
    $sql .= " LIMIT 1";
    $email = $db->customQuery($sql);
    if (sizeof($email) == 0) {
      return 'Error : email template not found.';
    } else {
      $email = $email[0];
      // ข้อความในอีเมล
      $replace = array();
      $replace['/%WEBTITLE%/'] = strip_tags($config['web_title']);
      $replace['/%WEBURL%/'] = WEB_URL;
      $replace['/%EMAIL%/'] = $to;
      $replace['/%ADMINEMAIL%/'] = $email['from_email'] == '' ? $config['noreply_email'] : $email['from_email'];
      $replace['/%TIME%/'] = gcms::mktime2date($mmktime);
      $replace = array_merge($replace, $datas);
      $patt = array_keys($replace);
      $replace = array_values($replace);
      $msg = preg_replace($patt, $replace, $email['detail']);
      $subject = preg_replace($patt, $replace, $email['subject']);
      // ส่งอีเมล
      return gcms::customMail($to.($email['copy_to'] != '' ? ",$email[copy_to]" : ''), $email['from_email'], $subject, $msg);
    }
  }

  // ฟังก์ชั่นส่งเมล์ (custom)
  public static function customMail($mailto, $replyto, $subject, $msg)
  {
    global $config;
    $charset = $config['email_charset'] == '' ? 'utf-8' : $config['email_charset'];
    $web_title = strip_tags($config['web_title']);
    if ($replyto == '') {
      $replyto = array($config['noreply_email'], $web_title);
    } elseif (preg_match('/^(.*)<(.*)>$/', $replyto, $match)) {
      $replyto = array($match[1], $match[2]);
    } else {
      $replyto = array($replyto, $replyto);
    }
    if (strtolower($charset) !== 'utf-8') {
      $subject = iconv('utf-8', $config['email_charset'], $subject);
      $msg = iconv('utf-8', $config['email_charset'], $msg);
      $replyto[1] = iconv('utf-8', $config['email_charset'], $replyto[1]);
      $web_title = iconv('utf-8', $config['email_charset'], $web_title);
    }
    if ($config['email_use_phpMailer'] !== 1) {
      $headers = "MIME-Version: 1.0\r\n";
      $headers .= "Content-type: text/html; charset=$charset\r\n";
      $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
      $headers .= "To: $mailto\r\n";
      $headers .= "From: $config[noreply_email]\r\n";
      $headers .= "Reply-to: $replyto[0]\r\n";
      $headers .= "X-Mailer: PHP mailer\r\n";
      if (function_exists('imap_8bit')) {
        $subject = "=?$charset?Q?".imap_8bit($subject)."?=";
        $msg = imap_8bit($msg);
      }
      if (@mail($mailto, $subject, $msg, $headers)) {
        return '';
      } else {
        return 'Send Mail Error.';
      }
    } else {
      include_once (str_replace('class.gcms.php', 'class.phpmailer.php', __FILE__));
      $mail = new PHPMailer(true);
      // use SMTP
      $mail->IsSMTP();
      $mail->Encoding = "quoted-printable";
      // charset
      $mail->CharSet = $charset;
      // use html
      $mail->IsHTML();
      if ($config['email_SMTPAuth'] == 1) {
        $mail->SMTPAuth = true;
        $mail->Username = $config['email_Username'];
        $mail->Password = $config['email_Password'];
        $mail->SMTPSecure = $config['email_SMTPSecure'];
      } else {
        $mail->SMTPAuth = false;
      }
      if ($config['email_Host'] != '') {
        $mail->Host = $config['email_Host'];
      }
      if ($config['email_Port'] != '') {
        $mail->Port = $config['email_Port'];
      }
      try {
        $mail->AddReplyTo($replyto[0], $replyto[1]);
        foreach (explode(',', $mailto) AS $email) {
          if (preg_match('/^(.*)<(.*)>$/', $email, $match)) {
            if ($mail->ValidateAddress($match[1])) {
              $mail->AddAddress($match[1], $match[2]);
            }
          } else {
            if ($mail->ValidateAddress($email)) {
              $mail->AddAddress($email, $email);
            }
          }
        }
        $mail->SetFrom($config['noreply_email'], $web_title);
        $mail->Subject = $subject;
        $mail->MsgHTML(preg_replace('/(<br([\s\/]{0,})>)/', "$1\r\n", stripslashes($msg)));
        $mail->Send();
        return '';
      } catch (phpmailerException $e) {
        // Pretty error messages from PHPMailer
        return strip_tags($e->errorMessage());
      } catch (exception $e) {
        // Boring error messages from anything else!
        return strip_tags($e->getMessage());
      }
    }
  }

  // เข้ารหัส
  public static function encode($string)
  {
    $en_key = (string)EN_KEY;
    $j = 0;
    for ($i = 0; $i < mb_strlen($string); $i++) {
      $string[$i] = $string[$i] ^ $en_key[$j];
      if ($j < (mb_strlen($en_key) - 1)) {
        $j++;
      } else {
        $j = 0;
      }
    }
    return base64_encode($string);
  }

  // ถอดรหัส
  public static function decode($string)
  {
    $en_key = (string)EN_KEY;
    $encode = base64_decode($string);
    $j = 0;
    for ($i = 0; $i < mb_strlen($encode); $i++) {
      $encode[$i] = $en_key[$j] ^ $encode[$i];
      if ($j < (mb_strlen($en_key) - 1)) {
        $j++;
      } else {
        $j = 0;
      }
    }
    return $encode;
  }

  // อ่าน ip ของเครื่องที่เรียก
  public static function getip()
  {
    if (isset($_SERVER)) {
      if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
      } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $realip = $_SERVER["HTTP_CLIENT_IP"];
      } else {
        $realip = $_SERVER["REMOTE_ADDR"];
      }
    } else {
      if (getenv('HTTP_X_FORWARDED_FOR')) {
        $realip = getenv('HTTP_X_FORWARDED_FOR');
      } elseif (getenv('HTTP_CLIENT_IP')) {
        $realip = getenv('HTTP_CLIENT_IP');
      } else {
        $realip = getenv('REMOTE_ADDR');
      }
    }
    return $realip;
  }

  // แสดง ip แบบซ่อนหลักหลัง
  public static function showip($ip)
  {
    preg_match('/([0-9]+\.[0-9]+\.)([0-9\.]+)/', $ip, $ips);
    return $ips[1].preg_replace('/[0-9]/', 'x', $ips[2]);
  }

  // ฟังก์ชั่น preg_replace ของ gcms
  public static function pregReplace($patt, $replace, $skin)
  {
    if (!is_array($patt)) {
      $patt = array($patt);
    }
    if (!is_array($replace)) {
      $replace = array($replace);
    }
    foreach ($patt AS $i => $item) {
      if (strpos($item, '/e') === FALSE) {
        $skin = preg_replace($item, $replace[$i], $skin);
      } else {
        $skin = preg_replace_callback(str_replace('/e', '/', $item), $replace[$i], $skin);
      }
    }
    return $skin;
  }

  // HTML highlighter
  public static function htmlhighlighter($text, $canview)
  {
    $patt[] = '/\[(\/)?(i|dfn|b|strong|u|em|ins|del|sub|sup|small|big|ul|ol|li)\]/isu';
    $replace[] = '<\\1\\2>';
    $patt[] = '/\[color=([#a-z0-9]+)\]/isu';
    $replace[] = '<span style="color:\\1">';
    $patt[] = '/\[size=([0-9]+)(px|pt|em|\%)\]/isu';
    $replace[] = '<span style="font-size:\\1\\2">';
    $patt[] = '/\[\/(color|size)\]/isu';
    $replace[] = '</span>';
    $patt[] = '/\[url\](.*)\[\/url\]/U';
    $replace[] = '<a href="\\1" target="_blank" rel="nofollow">\\1</a>';
    $patt[] = '/\[url=(ftp|http)(s)?:\/\/(.*)\](.*)\[\/url\]/U';
    $replace[] = '<a href="\\1\\2://\\3" target="_blank" rel="nofollow">\\4</a>';
    $patt[] = '/\[url=(\/)?(.*)\](.*)\[\/url\]/U';
    $replace[] = '<a href="'.WEB_URL.'/\\2" target="_blank" rel="nofollow">\\3</a>';
    $patt[] = '/(\[code=([a-z]{1,})\](.*?)\[\/code\])/uis';
    $replace[] = $canview ? '<code class="content-code \\2">\\3[/code]' : '<code class="content-code">{LNG_NOT_LOGIN}[/code]';
    $patt[] = '/(\[code\](.*?)\[\/code\])/uis';
    $replace[] = $canview ? '<code class="content-code">\\2[/code]' : '<code class="content-code">{LNG_NOT_LOGIN}[/code]';
    $patt[] = '/\[\/code\]/usi';
    $replace[] = '</code>';
    $patt[] = '/\[\/quote\]/usi';
    $replace[] = '</blockquote>';
    $patt[] = '/\[quote( q=[0-9]+)?\]/usi';
    $replace[] = '<blockquote><b>{LNG_Q_QUOTE}</b>';
    $patt[] = '/\[quote r=([0-9]+)\]/usi';
    $replace[] = '<blockquote><b>{LNG_R_QUOTE} <em>#\\1</em></b>';
    $patt[] = '/\[google\](.*?)\[\/google\]/usi';
    $replace[] = '<a class="googlesearch" href="http://www.google.co.th/search?q=\\1&amp;&meta=lr%3Dlang_th" target="_blank" rel="nofollow">\\1</a>';
    $patt[] = '/((^http|\shttp):\/\/([^\s<>\"\']+))/';
    $replace[] = '<a href="\\1" target="_blank" rel="nofollow">\\1</a>';
    $patt[] = '/\[WEBURL\]/isu';
    $replace[] = WEB_URL;
    $patt[] = '/\[youtube\]([a-z0-9-_]+)\[\/youtube\]/i';
    $replace[] = '<div class="youtube"><iframe src="//www.youtube.com/embed/\\1?wmode=transparent"></iframe></div>';
    return preg_replace($patt, $replace, $text);
  }

  // สำหรับตัด tag ออกจากเนื้อหา
  public static function html2txt($text)
  {
    $patt = array();
    $replace = array();
    // ตัด style
    $patt[] = '@<style[^>]*?>.*?</style>@siu';
    $replace[] = '';
    // ตัด comment
    $patt[] = '@<![\s\S]*?--[ \t\n\r]*>@u';
    $replace[] = '';
    // ตัด tag
    $patt[] = '@<[\/\!]*?[^<>]*?>@iu';
    $replace[] = '';
    // ตัด keywords
    $patt[] = '/{(WIDGET|LNG)_[a-zA-Z0-9_]+}/su';
    $replace[] = '';
    // ลบ BBCode
    $patt[] = '/(\[code(.+)?\]|\[\/code\]|\[ex(.+)?\])/ui';
    $replace[] = '';
    // ลบ BBCode ทั่วไป [b],[i]
    $patt[] = '/\[([a-z]+)([\s=].*)?\](.*?)\[\/\\1\]/ui';
    $replace[] = '\\3';
    $replace[] = ' ';
    // ตัดตัวอักษรที่ไม่ต้องการออก
    $patt[] = '/(&amp;|&quot;|&nbsp;|[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]){1,}/isu';
    $replace[] = ' ';
    return trim(preg_replace($patt, $replace, $text));
  }

  // ฟังก์ชั่นแทนที่คำหยาบ
  public static function CheckRude($temp)
  {
    global $config;
    if (is_array($config['wordrude'])) {
      return preg_replace("/(".implode('|', $config['wordrude']).")/usi", '<em>'.$config['wordrude_replace'].'</em>', $temp);
    } else {
      return $temp;
    }
  }

  // ฟังก์ชั่นแสดงเนื้อหา
  /**
   * showDetail($detail, $canview, $rude = true)
   * ฟังก์ชั่นแสดงเนื้อหา
   * $detail (string) email address
   * $canview (boolean) true=ซ่อนข้อความภายใต้ tag code หากไม่ใช่สมาชิก
   * $rude (boolean) true(default)=ตัดคำหยาบ
   *
   * @return (string)
   */
  public static function showDetail($detail, $canview, $rude = true, $txt = false)
  {
    if ($txt) {
      $detail = preg_replace('/[\t]/', '&nbsp;&nbsp;&nbsp;&nbsp;', $detail);
    }
    if ($rude) {
      return gcms::htmlhighlighter(gcms::CheckRude($detail), $canview);
    } else {
      return gcms::htmlhighlighter($detail, $canview);
    }
  }

  /**
   * CheckLogin($email, $password)
   * ตรวจสอบการ login
   * $email (string) email address
   * $password (string)
   *
   * @return (int) 0 ไม่พบอีเมล
   * @return (int) 1 ยังไม่ได้ activate
   * @return (int) 2 ติดแบน
   * @return (int) 3 รหัสผ่านผิด
   * @return (int) 4 login ต่าง ip กัน
   */
  public static function CheckLogin($email, $password)
  {
    global $config, $db, $mmktime, $mtoday;
    if ($email == '') {
      // ไม่กรอก email มา
      return 0;
    } elseif ($config['demo_mode'] === 1 && $email == 'demo' && $password == 'demo') {
      $login_result = array();
      $login_result['email'] = 'demo';
      $login_result['password'] = 'demo';
      $login_result['displayname'] = 'demo';
      $login_result['status'] = 1;
      $login_result['admin_access'] = 1;
      $login_result['account'] = 'demo';
      return $login_result;
    } else {
      $userupdate = false;
      $login_result = false;
      if ($config['member_login_phone'] == 1) {
        $sql = "SELECT * FROM `".DB_USER."` WHERE `email`='$email' OR `phone1`='$email'";
      } else {
        $sql = "SELECT * FROM `".DB_USER."` WHERE `email`='$email'";
      }
      foreach ($db->customQuery($sql) AS $item) {
        if ($item['password'] == md5($password.$item['email'])) {
          $login_result = $item;
          break;
        }
      }
      if (!$login_result) {
        // ไม่พบ email คืนค่า 0
        // รหัสผ่านผิด คืนค่า 3
        return is_array($item) ? 3 : 0;
      } elseif (trim($login_result['activatecode']) != '') {
        // ยังไม่ได้ activate
        return 1;
      } else {
        // ตรวจสอบการแบน
        if ($login_result['ban_date'] > 0 && $mmktime > $login_result['ban_date'] + ($login_result['ban_count'] * 86400)) {
          // ครบกำหนดการแบนแล้ว เคลียร์การแบน
          $login_result['ban_date'] = 0;
          $login_result['ban_count'] = 0;
          $userupdate = true;
        }
        if ($login_result['ban_date'] > 0) {
          // ติดแบน
          return 2;
        } else {
          $session_id = session_id();
          // ตรวจสอบการ login มากกว่า 1 ip
          $ip = gcms::getip();
          if ($config['member_only_ip'] == 1 && $ip != '') {
            $sql = "SELECT * FROM `".DB_USERONLINE."`";
            $sql .= " WHERE `member_id`='$login_result[id]' AND `ip`!='$ip' AND `ip`!=''";
            $sql .= " ORDER BY `time` DESC LIMIT 1";
            $online = $db->customQuery($sql);
            if (sizeof($online) == 1 && $mmktime - $online[0]['time'] < COUNTER_GAP) {
              // login ต่าง ip กัน
              return 4;
            }
          }
          // อัปเดตการเยี่ยมชม
          if ($session_id != $login_result['session_id']) {
            $login_result['visited'] ++;
            $userupdate = true;
          }
          if ($userupdate) {
            $db->edit(DB_USER, $login_result['id'], array('session_id' => $session_id, 'visited' => $login_result['visited'], 'lastvisited' => $mmktime, 'ip' => $ip));
          }
          return $login_result;
        }
      }
    }
  }

  // ตรวจสอบการ login
  public static function isMember()
  {
    return isset($_SESSION['login']);
  }

  // ตรวจสอบสถานะแอดมิน (สูงสุด)
  public static function isAdmin()
  {
    return (int)$_SESSION['login']['status'] == 1;
  }

  // ตรวจสอบแอดมินและสถานะที่กำหนด
  public static function canConfig($cfg)
  {
    $status = $_SESSION['login']['status'];
    return $status == 1 || (is_array($cfg) && in_array($status, $cfg));
  }

  // ฟังชั่นคืนค่ารูปแบบ url ที่ใช้ได้บนเว็บไซต์
  public static function getURL($module, $document = '', $catid = 0, $id = 0, $query = '', $encode = true)
  {
    $urls = array();
    $patt = array();
    $replace = array();
    global $config;
    $urls['0'] = 'index.php?module={module}-{document}&amp;cat={catid}&amp;id={id}';
    $urls['1'] = '{module}/{catid}/{id}/{document}.html';
    if ($document == '') {
      $patt[] = '/[\/-]{document}/u';
      $replace[] = '';
    } else {
      $patt[] = '/{document}/u';
      $replace[] = $encode ? rawurlencode($document) : $document;
    }
    $patt[] = '/{module}/u';
    $replace[] = $encode ? rawurlencode($module) : $module;
    if ($catid == 0) {
      $patt[] = '/((cat={catid}&amp;)|(\/{catid}))/u';
      $replace[] = '';
    } else {
      $patt[] = '/{catid}/u';
      $replace[] = (int)$catid;
    }
    if ((int)$id == 0) {
      $patt[] = '/(((&amp;|\?)id={id})|(\/{id}))/u';
      $replace[] = '';
    } else {
      $patt[] = '/{id}/u';
      $replace[] = (int)$id;
    }
    $link = preg_replace($patt, $replace, $urls[$config['module_url']]);
    if ($query != '') {
      $link = preg_match('/[\?]/u', $link) ? $link.'&amp;'.$query : $link.'?'.$query;
    }
    return WEB_URL.'/'.$link;
  }

  // โหลด widget
  public static function getWidgets($matches)
  {
    global $config, $lng, $db, $cache, $mmktime, $install_modules, $install_owners, $module_list;
    $owner = strtolower($matches[1]);
    $module = $matches[4];
    if ($matches[3] == ' ') {
      foreach (explode(';', $module) AS $item) {
        list($key, $value) = explode('=', $item);
        $$key = $value;
      }
    }
    if (is_file(ROOT_PATH."widgets/$owner/index.php")) {
      $widget = array();
      // load widget
      include ROOT_PATH."widgets/$owner/index.php";
      return $widget;
    }
    return '';
  }

  // อ่านภาษา
  public static function getLng($matches)
  {
    global $lng;
    return $lng[$matches[1]];
  }

  // array เป็น json
  public static function array2json($array)
  {
    if (is_array($array) && count($array) > 0) {
      $ret = array();
      foreach ($array AS $key => $value) {
        $ret[] = $key."':'".$value;
      }
      return "[{'".implode("','", $ret)."'}]";
    } else {
      return $array;
    }
  }

  /**
   * sql_trim_str_decode($text)
   * แปลงข้อความสำหรับการ quote
   * $text (string) ข้อความที่ต้องการ
   * คืนค่าข้อความ
   *
   * @return (string)
   */
  public static function sql_trim_str_decode($text)
  {
    return str_replace('&#92;', '\\', htmlspecialchars_decode($text));
  }

  /**
   * txtQuote($text)
   * แปลงข้อความสำหรับการ quote
   * $text (string) ข้อความที่ต้องการ
   * $u (boolean) default false, true ถอดรหัสอักขระพิเศษด้วย
   * คืนค่าข้อความ
   *
   * @return (string)
   */
  public static function txtQuote($text, $u = false)
  {
    $text = preg_replace('/<br(\s\/)?>/isu', '', $text);
    if ($u) {
      $text = str_replace(array('&lt;', '&gt;', '&#92;', '&nbsp;'), array('<', '>', '\\', ' '), $text);
    }
    return $text;
  }

  /**
   * txtClean($text)
   * ตัดข้อความที่ไม่พึงประสงค์ก่อนบันทึกลง db ที่มาจาก textarea
   * $text (string) ข้อความที่ submit
   * คืนค่าข้อความ
   *
   * @return (string)
   */
  public static function txtClean($text)
  {
    $patt = array();
    $replace = array();
    $patt[] = '/</u';
    $replace[] = '&lt;';
    $patt[] = '/>/u';
    $replace[] = '&gt;';
    $patt[] = '/\\\\\\\\/u';
    $replace[] = '&#92;';
    $text = nl2br(preg_replace($patt, $replace, $text));
    return defined('DATABASE_DRIVER') ? stripslashes($text) : $text;
  }

  /**
   * ckClean($text)
   * ตัดข้อความที่ไม่พึงประสงค์ก่อนที่มาจาก ckeditor
   * $text (string) ข้อความที่ submit
   * คืนค่าข้อความ
   *
   * @return (string)
   */
  public static function ckClean($text)
  {
    $patt = array();
    $replace = array();
    $patt[] = "/<\?(.*?)\?>/su";
    $replace[] = '';
    $patt[] = '@<script[^>]*?>.*?</script>@siu';
    $replace[] = '';
    $patt[] = '@<style[^>]*?>.*?</style>@siu';
    $replace[] = '';
    $patt[] = '/\\\\\\\\/u';
    $replace[] = '&#92;';
    $patt[] = '/^[\r\n\s]{0,}<br \/>[\r\n\s]{0,}$/';
    $replace[] = '';
    $text = preg_replace($patt, $replace, $text);
    return defined('DATABASE_DRIVER') ? stripslashes($text) : $text;
  }

  /**
   * ckDetail($text)
   * ตัดข้อความที่ไม่พึงประสงค์ก่อนบันทึกลง db ที่มาจาก ckeditor
   * $text (string) ข้อความที่ submit
   * คืนค่าข้อความ
   *
   * @return (string)
   */
  public static function ckDetail($text)
  {
    global $db;
    $patt = array();
    $replace = array();
    $patt[] = '/^(&nbsp;|\s){0,}<br[\s\/]+?>(&nbsp;|\s){0,}$/iu';
    $replace[] = '';
    $patt[] = '/<\?(.*?)\?>/su';
    $replace[] = '';
    $patt[] = '/\\\\\\\\/u';
    $replace[] = '&#92;';
    return $db->sql_clean(preg_replace($patt, $replace, $text));
  }

  /**
   * detail2TXT($text)
   * เข้ารหัส อักขระพิเศษ และ {} ก่อนจะส่งให้กับ CKEditor
   * $text (string) ข้อความที่ต้องการแสดงใน CKEDitor
   * คืนค่าข้อความ
   *
   * @return (string)
   */
  public static function detail2TXT($text)
  {
    return str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), htmlspecialchars($text));
  }

  // ตรวจสอบ referer
  public static function isReferer()
  {
    $server = $_SERVER["HTTP_HOST"] == '' ? $_SERVER["SERVER_NAME"] : $_SERVER["HTTP_HOST"];
    $referer = getenv('HTTP_REFERER') == '' ? $_SERVER['HTTP_REFERER'] : getenv('HTTP_REFERER');
    if (preg_match("/$server/ui", $referer)) {
      return true;
    } elseif (preg_match('/^(http(s)?:\/\/)(.*)(\/.*){0,}$/U', WEB_URL, $match)) {
      return preg_match("/$match[3]/ui", $referer);
    } else {
      return false;
    }
  }

  // อ่าน config จากข้อมูล
  public static function r2config($data, &$config, $replace = true)
  {
    foreach (explode("\n", $data) As $item) {
      if ($item != '') {
        if (preg_match('/^(.*)=(.*)$/U', $item, $match)) {
          if ($replace || !isset($config[$match[1]])) {
            $config[$match[1]] = trim($match[2]);
          }
        }
      }
    }
  }

  // เรียงลำดับ array ตามชื่อฟิลด์
  public static function sortby(&$array, $subkey = 'id', $sort_ascending = false)
  {
    if (count($array)) {
      $temp_array[key($array)] = array_shift($array);
    }
    foreach ($array AS $key => $val) {
      $offset = 0;
      $found = false;
      foreach ($temp_array AS $tmp_key => $tmp_val) {
        if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
          $temp_array = array_merge((array)array_slice($temp_array, 0, $offset), array($key => $val), array_slice($temp_array, $offset));
          $found = true;
        }
        $offset++;
      }
      if (!$found) {
        $temp_array = array_merge($temp_array, array($key => $val));
      }
    }
    if ($sort_ascending) {
      $array = array_reverse($temp_array);
    } else {
      $array = $temp_array;
    }
  }

  /**
   * testDir($dir)
   * ตรวจสอบไดเร็คทอรี่ว่าเขียนได้หรือไม่
   * $dir (string) โฟลเดอร์+path ที่ต้องการทดสอบ
   * คืนค่า true ถ้าเขียนได้
   *
   * @return (boolean) true ถ้าเขียนได้
   */
  public static function testDir($dir)
  {
    global $ftp;
    return $ftp->mkdir($dir);
  }

  // ลบ ไฟล์ และ directory
  public static function rm_dir($dir)
  {
    global $ftp;
    return $ftp->rmdir($dir);
  }

  // แปลงอักขระ HTML กลับเป็นตัวอักษร สำหรับใส่ใน textarea
  public static function unhtmlentities($value)
  {
    $patt = array('/&amp;/', '/&#39;/', '/&quot;/', '/&nbsp;/');
    $replace = array('&', "'", '"', ' ');
    return preg_replace($patt, $replace, $value);
  }

  // โหลด template ของโมดูลที่เลือก
  public static function loadtemplate($module, $owner, $file)
  {
    $template = is_file(ROOT_PATH.SKIN."$module/$file.html") ? $module : $owner;
    return gcms::loadfile(ROOT_PATH.SKIN.str_replace('//', '/', "$template/$file.html"));
  }

  // โหลดไฟล์ ตัด \t และ \r ออก
  public static function loadfile($file)
  {
    return is_file($file) ? preg_replace('/[\t\r]/', '', file_get_contents($file)) : '';
  }

  // อ่าน info ของ theme
  public static function parse_theme($theme)
  {
    $result = array();
    if (is_file($theme) && preg_match('/^[\s]{0,}\/\*(.*)\*\//s', file_get_contents($theme), $match)) {
      if (preg_match_all('/([a-zA-Z]+)[\s:]{0,}(.*)?[\r\n]+/i', $match[1], $datas)) {
        foreach ($datas[1] AS $i => $v) {
          $result[strtolower($v)] = $datas[2][$i];
        }
      }
    }
    return $result;
  }

  // highlight ข้อความค้นหา
  public static function HighlightSearch($text, $search)
  {
    $s = array();
    foreach (explode(' ', $search) AS $i => $q) {
      if ($q != '') {
        $s[$q] ++;
        if ($s[$q] == 1) {
          $text = gcms::doHighlight($text, $q, $i);
        }
      }
    }
    return $text;
  }

  // ทำ highlight ข้อความ
  public static function doHighlight($text, $needle, $index)
  {
    $newtext = '';
    $i = -1;
    $len_needle = mb_strlen($needle);
    while (mb_strlen($text) > 0) {
      $i = mb_stripos($text, $needle, $i + 1);
      if ($i == false) {
        $newtext .= $text;
        $text = '';
      } else {
        $a = gcms::lastIndexOf($text, '>', $i) >= gcms::lastIndexOf($text, '<', $i);
        $a = $a && (gcms::lastIndexOf($text, '}', $i) >= gcms::lastIndexOf($text, '{LNG_', $i));
        $a = $a && (gcms::lastIndexOf($text, '/script>', $i) >= gcms::lastIndexOf($text, '<script', $i));
        $a = $a && (gcms::lastIndexOf($text, '/style>', $i) >= gcms::lastIndexOf($text, '<style', $i));
        if ($a) {
          $newtext .= mb_substr($text, 0, $i).'<mark>'.mb_substr($text, $i, $len_needle).'</mark>';
          $text = mb_substr($text, $i + $len_needle);
          $i = -1;
        }
      }
    }
    return $newtext;
  }

  // ค้นหาข้อความย้อนหลัง
  public static function lastIndexOf($text, $needle, $offset)
  {
    $pos = mb_strripos(mb_substr($text, 0, $offset), $needle);
    return $pos == false ? -1 : $pos;
  }

  // อ่านสถานะของสมาชิกเป็นข้อความ
  public static function id2status($status)
  {
    global $lng, $config;
    $status = is_array($status) ? $status : explode(',', $status);
    $ds = array();
    foreach ($status AS $item) {
      if ($item == -1) {
        $ds[] = '<span class="status">'.$lng['LNG_GUEST'].'</span>';
      } else {
        $ds[] = '<span class="status'.$item.'">'.$config['member_status'][$item].'</span>';
      }
    }
    return implode(',', $ds);
  }

  /**
   * saveConfig($file, $config)
   * บันทึกไฟล์ config
   * $file (string) path ของไฟล์ตั้งแต่ root
   * $config (array) แอเรย์ของ config
   *
   * @return (boolean) true หากสำเร็จ
   */
  public static function saveConfig($file, $config)
  {
    if (!is_array($config) || sizeof($config) == 0) {
      return false;
    } else {
      $datas = array();
      $datas[] = '<'.'?php';
      $datas[] = '// '.str_replace(ROOT_PATH, '', $file);
      foreach ($config AS $key => $value) {
        if (is_array($value)) {
          foreach ($value AS $k => $v) {
            if (is_array($v)) {
              foreach ($v AS $k2 => $v2) {
                $datas[] = '$config[\''.$key.'\'][\''.$k.'\'][\''.$k2.'\'] = \''.$v2.'\';';
              }
            } else {
              $datas[] = '$config[\''.$key.'\'][\''.$k.'\'] = \''.$v.'\';';
            }
          }
        } elseif (is_int($value)) {
          $datas[] = '$config[\''.$key.'\'] = '.$value.';';
        } else {
          $datas[] = '$config[\''.$key.'\'] = \''.$value.'\';';
        }
      }
      $f = @fopen($file, 'wb');
      if (!$f) {
        return false;
      } else {
        fwrite($f, implode("\n\t", $datas));
        fclose($f);
        return true;
      }
    }
  }

  // แปลงขนาดของไฟล์เป็น kb mb
  public static function formatFileSize($bytes, $precision = 2)
  {
    $units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
    if ($bytes <= 0) {
      return '0 Byte';
    } else {
      $bytes = max($bytes, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);
      $bytes /= pow(1024, $pow);
      return round($bytes, $precision).' '.$units[$pow];
    }
  }

  // increment
  public static function inc(&$number, $value = 1)
  {
    $number += $value;
    return $number;
  }

  // decrement
  public static function dec(&$number, $value = 1)
  {
    $number -= $value;
    return $number;
  }

  // install database
  public static function install($sql, $owner = '')
  {
    global $db, $content, $defines, $config;
    // โหลดฐานข้อมูลของโมดูล
    $fr = file($sql);
    foreach ($fr AS $value) {
      $sql = str_replace(array('{prefix}', '{owner}', '/{WEBMASTER}/', '\r', '\n'), array(PREFIX, $owner, $_SESSION['login']['email'], "\r", "\n"), trim($value));
      if ($sql != '') {
        if (preg_match('/^<\?.*\?>$/', $sql)) {
          // php code
        } elseif (preg_match('/^define\([\'"]([A-Z0-9_]+)[\'"](.*)\);$/', $sql, $match)) {
          if (!defined($match[1])) {
            $defines[$match[1]] = $match[0];
          }
        } elseif (preg_match('/DROP[\s]+TABLE[\s]+(IF[\s]+EXISTS[\s]+)?`?([a-z0-9_]+)`?/iu', $sql, $match)) {
          $ret = $db->query($sql);
          $content[] = '<li class='.($ret ? '' : 'in').'valid>DROP TABLE <strong>'.$match[2].'</strong> ...</li>';
        } elseif (preg_match('/CREATE[\s]+TABLE[\s]+(IF[\s]+NOT[\s]+EXISTS[\s]+)?`?([a-z0-9_]+)`?/iu', $sql, $match)) {
          $ret = $db->query($sql);
          $content[] = '<li class='.($ret ? '' : 'in').'valid>CREATE TABLE <strong>'.$match[2].'</strong> ...</li>';
        } elseif (preg_match('/ALTER[\s]+TABLE[\s]+`?([a-z0-9_]+)`?[\s]+ADD[\s]+`?([a-z0-9_]+)`?(.*)/iu', $sql, $match)) {
          // add column
          $search = $db->customQuery("SELECT * FROM `information_schema`.`columns` WHERE `table_schema`='$config[db_name]' AND `table_name`='$match[1]' AND `column_name`='$match[2]'");
          if (sizeof($search) == 1) {
            $db->query("ALTER TABLE `$match[1]` DROP COLUMN `$match[2]`");
          }
          $ret = $db->query($sql);
          if (sizeof($search) == 1) {
            $content[] = '<li class='.($ret ? '' : 'in').'valid>REPLACE COLUMN <strong>'.$match[2].'</strong> to TABLE <strong>'.$match[1].'</strong></li>';
          } else {
            $content[] = '<li class='.($ret ? '' : 'in').'valid>ADD COLUMN <strong>'.$match[2].'</strong> to TABLE <strong>'.$match[1].'</strong></li>';
          }
        } elseif (preg_match('/INSERT[\s]+INTO[\s]+`?([a-z0-9_]+)`?(.*)/iu', $sql, $match)) {
          $ret = $db->query($sql);
          if ($q != $match[1]) {
            $q = $match[1];
            $content[] = '<li class='.($ret ? '' : 'in').'valid>INSERT INTO <strong>'.$match[1].'</strong> ...</li>';
          }
        } else {
          $db->query($sql);
        }
      }
    }
  }

  /**
   * installModule($owner, $module, $title, $menupos, $menu)
   * คิดตั้ง โมดูลและ เมนู
   *
   * @param string $owner ชื่อโฟลเดอร์ของเมนู
   * @param string $module ชื่อโมดูล
   * @param string $title [optinal] title
   * @param string $menupos [optinal] ตำแหน่งของเมนู (MAINMENU,SIDEMENU,BOTTOMMENU)
   * @param string $menu [optinal] ชื่อเมนู
   *
   * @return int ID ของโมดูลที่ติดตั้ง
   */
  public static function installModule($owner, $module, $title = '', $menupos = '', $menu = '')
  {
    global $db;
    $search = $db->basicSearch(DB_MODULES, 'module', $module);
    if (!$search) {
      $id = $db->add(DB_MODULES, array('owner' => $owner, 'module' => $module));
      if ($title != '') {
        $index = $db->add(DB_INDEX, array('module_id' => $id, 'index' => '1', 'published' => '1'));
        $db->add(DB_INDEX_DETAIL, array('module_id' => $id, 'id' => $index, 'topic' => $title));
      }
      if ($menupos != '' && $menu != '') {
        $db->add(DB_MENUS, array('index_id' => $index, 'parent' => $menupos, 'level' => 0, 'menu_text' => $menu, 'menu_tooltip' => $title));
      }
      return $id;
    } else {
      return $search['id'];
    }
  }

  // แปลงเป็นรายการเมนู
  public static function getMenu($item, $arrow = false)
  {
    if ($item['published'] == 0) {
      return '';
    }
    $c = array();
    if ($item['alias'] != '') {
      $c[] = $item['alias'];
    } elseif ($item['module'] != '') {
      $c[] = $item['module'];
    }
    if ($item['published'] != 1) {
      if (gcms::isMember()) {
        if ($item['published'] == '3') {
          $c[] = 'hidden';
        }
      } else {
        if ($item['published'] == '2') {
          $c[] = 'hidden';
        }
      }
    }
    $c = sizeof($c) == 0 ? '' : ' class="'.implode(' ', $c).'"';
    if ($item['index_id'] > 0 || $item['menu_url'] != '') {
      $a = $item['menu_target'] == '' ? '' : ' target='.$item['menu_target'];
      $a .= $item['accesskey'] == '' ? '' : ' accesskey='.$item['accesskey'];
      $a .= ' title="'.$item['menu_tooltip'].'"';
      if ($item['index_id'] > 0) {
        $a .= ' href="'.gcms::getURL($item['module']).'"';
      } elseif ($item['menu_url'] != '') {
        $a .= ' href="'.$item['menu_url'].'"';
      } else {
        $a .= ' tabindex=0';
      }
    } else {
      $a = ' tabindex=0';
    }
    if ($arrow) {
      return '<li'.$c.'><a class=menu-arrow'.$a.'><span>'.($item['menu_text'] == '' ? '&nbsp;' : htmlspecialchars_decode($item['menu_text'])).'</span></a>';
    } else {
      return '<li'.$c.'><a'.$a.'><span>'.($item['menu_text'] == '' ? '&nbsp;' : htmlspecialchars_decode($item['menu_text'])).'</span></a>';
    }
  }

  // URL สำหรับ admin
  public static function adminURL($query, $f)
  {
    $qs = array();
    foreach ($query AS $key => $value) {
      $qs[$key] = "$key=$value";
    }
    if ($f != '') {
      foreach (explode('&', str_replace('&amp;', '&', $f)) AS $item) {
        list($key, $value) = explode('=', $item);
        $qs[$key] = $value == '' ? $key : "$key=$value";
      }
    }
    return str_replace('&amp;id=0', '', 'index.php'.(sizeof($qs) > 0 ? '?'.implode('&amp;', $qs) : ''));
  }

  // แปลงตัวเลขเป็นจำนวนเงิน
  public static function int2Curr($amount, $thousands_sep = ',')
  {
    return number_format((double)$amount, 2, '.', $thousands_sep);
  }

  // คำนวนความแตกต่างของวัน (อายุ)
  public static function dateDiff($start_date, $end_date)
  {
    $Year1 = (int)date("Y", $start_date);
    $Month1 = (int)date("m", $start_date);
    $Day1 = (int)date("d", $start_date);
    $Year2 = (int)date("Y", $end_date);
    $Month2 = (int)date("m", $end_date);
    $Day2 = (int)date("d", $end_date);
    // วันแต่ละเดือน
    $months = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    // ปีอธิกสุรทิน
    if (($Year2 % 4) == 0) {
      $months[2] = 29;
    }
    // ปีอธิกสุรทิน
    if ((($Year2 % 100) == 0) & (($Year2 % 400) != 0)) {
      $months[2] = 28;
    }
    // คำนวนจำนวนวันแตกต่าง
    $YearDiff = $Year2 - $Year1;
    if ($Month2 >= $Month1) {
      $MonthDiff = $Month2 - $Month1;
    } else {
      $YearDiff--;
      $MonthDiff = 12 + $Month2 - $Month1;
    }
    if ($Day1 > $months[$Month2]) {
      $Day1 = 0;
    } elseif ($Day1 > $Day2) {
      $Month2 = $Month2 == 1 ? 13 : $Month2;
      $Day2 += $months[$Month2 - 1];
      $MonthDiff--;
    }
    $ret['year'] = $YearDiff;
    $ret['month'] = $MonthDiff;
    $ret['day'] = $Day2 - $Day1;
    return $ret;
  }

  // ตรวจสอบความถูกต้องของอีเมล
  // คืนค่่า true ถ้าถูกต้อง
  public static function validMail($email)
  {
    return preg_match('/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i', $email);
  }

  // ตรวจสอบว่าเป็นค่าว่างหรือไม่ ถ้าไม่ว่างเอาใส่ array
  public static function checkempty($detail, &$result)
  {
    if (trim($detail) != '') {
      $result[] = $detail;
    }
  }

  /**
   * saveLanguage($database)
   * บันทึกไฟล์ภาษา
   * $database (optional string) ชื่อ database ค่า default คือ DB_LANGUAGE
   *
   * @return (array) รายการภาษาทั้งหมดที่ติดตั้ง
   */
  public static function saveLanguage($database = DB_LANGUAGE)
  {
    global $db;
    // ภาษาที่ติดตั้งหมด
    $languages = array();
    $save = array();
    $save2 = array();
    $l = array('id', 'key', 'type', 'owner', 'js');
    foreach ($db->customQuery("SHOW FIELDS FROM $database") AS $item) {
      if (!in_array($item['Field'], $l)) {
        $languages[] = $item['Field'];
        $save[$item['Field']][] = '<'.'?php';
      }
    }
    // อ่านภาษาและบันทึกเป็นไฟล์
    $sql = "SELECT * FROM `$database` ORDER BY `key`";
    $p2 = array("'", "\\\\'");
    foreach ($db->customQuery($sql) AS $item) {
      foreach ($languages AS $language) {
        if (!(isset($lng[$language][$item['key']]['js']) && $lng[$language][$item['key']]['js'] == $item['js'])) {
          $value[$language] = preg_replace('/[\r\n]{1,}/isu', '\n', $item[$language]);
          if ($item['js'] == 1) {
            $save2[$language][] = "var $item[key] = '".str_replace($p2, "\'", $value[$language])."';";
          } elseif ($item['type'] == 'array' && $value[$language] != '') {
            $lng[$language][$item['key']] = unserialize($value[$language]);
            if (is_array($lng[$language][$item['key']])) {
              $save3 = array();
              foreach ($lng[$language][$item['key']] AS $k => $v) {
                if (preg_match('/^[0-9]+$/', $k)) {
                  $save3[] = "$k => '".str_replace($p2, "\'", $v).'\'';
                } else {
                  $save3[] = "'$k' => '".str_replace($p2, "\'", $v).'\'';
                }
              }
              $save[$language][] = '$lng[\''.$item['key'].'\'] = Array('.implode(', ', $save3).');';
            }
          } elseif ($item['type'] == 'int') {
            $lng[$language][$item['key']] = $value[$language];
            $save[$language][] = '$lng[\''.$item['key'].'\'] = '.str_replace($p2, "\'", $value[$language]).';';
          } else {
            $lng[$language][$item['key']] = $value[$language];
            $save[$language][] = '$lng[\''.$item['key'].'\'] = \''.str_replace($p2, "\'", $value[$language]).'\';';
          }
        }
      }
    }
    // เขียนไฟล์ $language.php,$language.js
    foreach ($languages AS $language) {
      if (sizeof($save[$language]) > 1) {
        $f = fopen(DATA_PATH."language/$language.php", 'wb');
        fwrite($f, implode("\n\t", $save[$language]));
        fclose($f);
      }
      if (is_array($lng[$language]['MONTH_SHORT'])) {
        $save2[$language][] = 'Date.monthNames = ["'.implode('","', $lng[$language]['MONTH_SHORT']).'"];';
      }
      if (is_array($lng[$language]['DATE_SHORT'])) {
        $save2[$language][] = 'Date.dayNames = ["'.implode('","', $lng[$language]['DATE_SHORT']).'"];';
      }
      if (isset($lng[$language]['YEAR_OFFSET'])) {
        $save2[$language][] = 'Date.yearOffset = '.$lng[$language]['YEAR_OFFSET'].';';
      }
      if (sizeof($save2[$language]) > 0) {
        $f = fopen(DATA_PATH."language/$language.js", 'wb');
        fwrite($f, implode("\n", $save2[$language]));
        fclose($f);
      }
    }
    return $languages;
  }

  /**
   * getTags($text)
   * ตรวจสอบข้อความ tags หรือ keywords ช่องว่างไม่เกิน 1 ช่อง,ไม่ขึ้นบรรทัดใหม่,คั่นแต่ละรายการด้วย ,
   * $text ข้อความที่ถูกส่งมาจาก textarea
   *
   * @return (string)
   */
  public static function getTags($text)
  {
    $text = trim(strip_tags($text));
    if ($text == '') {
      return '';
    } else {
      $ds = array();
      foreach (explode(',', $text) AS $item) {
        $item = trim($item);
        if ($item != '') {
          $ds[] = $item;
        }
      }
      return trim(preg_replace('/[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]{1,}/isu', ' ', implode(',', $ds)));
    }
  }

  /**
   * aliasName($text)
   * ตรวจสอบข้อความ ใช้เป็น alias name ตัวพิมพืเล็ก แทนช่องว่างด้วย _
   * $text (string)
   *
   * @return (string)
   */
  public static function aliasName($text)
  {
    return preg_replace(array('/[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]{1,}/isu', '/^(_)?(.*?)(_)?$/'), array('_', '\\2'), strtolower(trim(strip_tags($text))));
  }

  /**
   * checkIDCard($id)
   * ตรวจสอบความถูกต้องของรหัสบัตรประชาชน
   * $id (string) ตัวเลข 13 หลัก
   *
   * @return (boolean) true ถูกต้อง, false ไม่ถูกต้อง
   */
  public static function checkIDCard($id)
  {
    if (preg_match('/^[0-9]{13,13}$/', $id)) {
      for ($i = 0, $sum = 0; $i < 12; $i++) {
        $sum += (int)($id{$i}) * (13 - $i);
      }
      if ((11 - ($sum % 11)) % 10 == (int)($id{12})) {
        return true;
      }
    }
    return false;
  }

  /**
   * ser2Array($text)
   * unserialize
   * $text (string) ข้อความ serialize
   *
   * @return (array)
   */
  public static function ser2Array($text)
  {
    $text = trim($text);
    if ($text != '') {
      $text = unserialize($text);
    }
    return is_array($text) ? $text : array();
  }

  /**
   * array2Ser($array)
   * ตรวจสอบและทำ serialize สำหรับภาษา โดยรายการที่มีเพียงภาษาเดียว จะกำหนดให้ไม่มีภาษา
   * $array (array)
   *
   * @return (string) ที่ทำ serialize แล้ว
   */
  public static function array2Ser($array)
  {
    $new_array = array();
    $l = sizeof($array);
    if ($l > 0) {
      foreach ($array AS $i => $v) {
        if ($l == 1 && $i == 0) {
          $new_array[''] = $v;
        } else {
          $new_array[$i] = $v;
        }
      }
    }
    return serialize($new_array);
  }

  /**
   * ser2Str($datas)
   * อ่านหมวดหมู่ในรูป serialize ตามภาษาที่เลือก
   * $datas (string) ข้อความ serialize
   *
   * @return (string)
   */
  public static function ser2Str($datas)
  {
    if ($datas == '') {
      return '';
    } else {
      $datas = unserialize($datas);
      return $datas[LANGUAGE] == '' ? $datas[''] : $datas[LANGUAGE];
    }
  }

  /**
   * oneLine($text)
   * กำจัดตัวอักษรขึ้นบรรทัดใหม่และช่องว่าง
   * $text (string)
   *
   * @return (string)
   */
  public static function oneLine($text)
  {
    return trim(preg_replace('/[\r\n\t\s]+/', ' ', $text));
  }

  /**
   * breadcrumb($c, $url, $tooltip, $menu, $skin)
   * สร้าง breadcumb
   * $c (string) class สำหรับลิงค์นี้
   * $url (string) ลิงค์
   * $tooltip (string) ทูลทิป
   * $menu (string) ข้อความแสดงใน breadcumb
   * $skin (string) template ของ breadcumb
   *
   * @return (string)
   */
  function breadcrumb($c, $url, $tooltip, $menu, $skin)
  {
    $patt = array('/{CLASS}/', '/{URL}/', '/{TOOLTIP}/', '/{MENU}/');
    return preg_replace($patt, array($c, $url, $tooltip, htmlspecialchars_decode($menu)), $skin);
  }
}
