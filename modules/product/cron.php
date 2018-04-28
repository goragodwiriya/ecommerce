<?php
// modules/product/cron.php
if (defined('MAIN_INIT')) {
  /*
    function _getXMLHeader($xml) {
    $headers = explode('<'.'?xml', $xml);
    $ret = '';
    for ($i = 0; $i < sizeof($headers); $i++) {
    $ret .= _parseXMLHeader(trim($headers[$i]));
    }
    return $ret;
    }
    function _parseXMLHeader($data) {
    if ($data != '') {
    $EndPos = mb_strpos($data, '?>');
    $datas = explode(' ', mb_substr($data, 0, $EndPos));
    for ($i = 0; $i < sizeof($datas); $i++) {
    $temps = explode('=', $datas[$i]);
    if (trim($temps[0]) == 'encoding') {
    $value = trim($temps[1]);
    $value = str_replace('"', '', $value);
    $value = str_replace("'", '', $value);
    return $value;
    }
    }
    }
    return;
    }
    function _RSStoArray($xml) {
    $items = preg_split('/<item[\s|>]/', $xml, -1, PREG_SPLIT_NO_EMPTY);
    array_shift($items);
    $datas = array();
    foreach ($items AS $item) {
    $targetCurrency = _getTextBetweenTags($item, 'cb:targetCurrency');
    $value = _getTextBetweenTags($item, 'cb:value');
    $datas[$targetCurrency] = $value;
    }
    return $datas;
    }
    function _getTextBetweenTags($text, $tag) {
    $StartTag = "<$tag";
    $EndTag = "</$tag";
    $StartPosTemp = mb_strpos($text, $StartTag);
    $StartPos = mb_strpos($text, '>', $StartPosTemp);
    $StartPos = $StartPos + 1;
    $EndPos = mb_strpos($text, $EndTag);
    $StartAttr = $StartPosTemp + mb_strlen($StartTag) + 1;
    $text = mb_substr($text, $StartPos, ($EndPos - $StartPos));
    return trim($text);
    }
    // exchage rate rss
    $url = 'http://www2.bot.or.th/RSS/fxrates/fxrate-all.xml';
    // get rate
    if (function_exists('curl_init') && $ch = @curl_init()) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($ch);
    curl_close($ch);
    } else {
    $contents = @file_get_contents($url);
    }
    if ($contents != '') {
    $charset = _getXMLHeader($contents);
    $charset = ($charset == '') ? 'utf-8' : strtolower($charset);
    if ($charset != 'utf-8') {
    $contents = iconv($charset, 'UTF-8', $contents);
    }
    if (preg_match('/<dc:date>(.*)<\/dc:date>/', $contents, $match)) {
    // extract datas
    $rates = _RSStoArray($contents);
    unset($rates['THB']);
    // date from xml
    $rates['date'] = $match[1];
    // วันเวลาที่อ่านข้อมูลนี้
    $rates['last_update'] = $mmktime;
    // save to db
    $db->add(DB_EXCHANGRATE, $rates);
    }
    }
   */
}
