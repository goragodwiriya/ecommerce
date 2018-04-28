<?php
// admin/getnews.php
header("content-type: text/html; charset=UTF-8");
// url ของข่าว
$url = 'http://gcms.in.th/news.php';
if ($feedRef = @fopen($url, 'rb')) {
  $contents = '';
  while (!feof($feedRef)) {
    $contents .= fread($feedRef, 1024);
  }
  fclose($feedRef);
} elseif ($ch = @curl_init()) {
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  // method ที่เราจะส่ง เป็น get หรือ post
  curl_setopt($ch, CURLOPT_POST, 0);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  // ผลการ execute กลับมาเป็น ข้อมูลใน url ที่เรา ส่งคำร้องขอไป
  $contents = curl_exec($ch);
  curl_close($ch);
}
echo $contents;
