<?php
	// widgets/gallery/reader.php
	error_reporting(E_ALL ^ E_NOTICE);
	// ค่าที่ส่งมา
	$rows = (int)$_POST['rows'];
	$cols = (int)$_POST['cols'];
	$imageWidth = (int)$_POST['imageWidth'];
	$className = $_POST['className'];
	$url = $_POST['url'];
	if (preg_match('/(http:\/\/gallery\.g\-th\.com|'.preg_quote($_SERVER['HTTP_HOST']).')(.*?)/i', $url)) {
		$url .= '?rnd&count='.($rows * $cols).'&tags='.rawurlencode($_POST['tags']).'&album='.(int)$_POST['album'].'&user='.(int)$_POST['user'].'&w='.$imageWidth;
	}
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
		header("content-type: text/html; charset=UTF-8");
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding('utf-8');
		}
		$rss = RSStoArray($contents);
		$listcount = $rows * $cols;
		$w = 100 / $cols;
		$data = '<table class="'.$className.'"><tr class=bg1>';
		for ($i = 0; $i < sizeof($rss) && $listcount > 0; $i++) {
			if ($i > 0 && $i % $cols == 0) {
				$data .= "</tr><tr>";
			}
			$data .= '<td style="width:'.$w.'%">';
			$data .= '<a href="'.$rss[$i]['link']['data'].'" title="'.htmlspecialchars($rss[$i]['title']['data']).'" target=_blank';
			$data .= ' style="background-image:url(';
			$data .= $rss[$i]['media:thumbnail']['url'] == '' ? $rss[$i]['enclosure']['url'] : $rss[$i]['media:thumbnail']['url'];
			$data .= ')">&nbsp;</a></td>';
			$listcount--;
		}
		echo $data.'</tr></table>';
	}
	function RSStoArray($xml) {
		$items = preg_split('/<item[\s|>]/', $xml, -1, PREG_SPLIT_NO_EMPTY);
		array_shift($items);
		$i = 0;
		foreach ($items AS $item) {
			$array[$i]['title'] = getTextBetweenTags($item, 'title');
			$array[$i]['link'] = getTextBetweenTags($item, 'link');
			$array[$i]['description'] = getTextBetweenTags($item, 'description');
			$array[$i]['author'] = getTextBetweenTags($item, 'author');
			$array[$i]['category'] = getTextBetweenTags($item, 'category');
			$array[$i]['comments'] = getTextBetweenTags($item, 'comments');
			$array[$i]['enclosure'] = getTextBetweenTags($item, 'enclosure');
			$array[$i]['guid'] = getTextBetweenTags($item, 'guid');
			$array[$i]['pubDate'] = getTextBetweenTags($item, 'pubDate');
			$array[$i]['source'] = getTextBetweenTags($item, 'source');
			if (preg_match('/<img.*src=\"?(http:\/\/.*\.(jpg|gif|png))\".*>/', $array[$i]['description']['data'], $match)) {
				$array[$i]['enclosure']['url'] = $match[1];
				$typies = array('jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
				$array[$i]['enclosure']['type'] = $typies[$match[2]];
			} else {
				$array[$i]['media:thumbnail'] = getTextBetweenTags($item, 'media:thumbnail');
				$array[$i]['enclosure'] = getTextBetweenTags($item, 'enclosure');
			}
			$array[$i]['description']['data'] = strip_tags($array[$i]['description']['data']);
			$i++;
		}
		return $array;
	}
	function getTextBetweenTags($text, $tag) {
		$StartTag = "<$tag";
		$EndTag = "</$tag";
		$StartPosTemp = mb_strpos($text, $StartTag);
		$StartPos = mb_strpos($text, '>', $StartPosTemp);
		$StartPos = $StartPos + 1;
		$EndPos = mb_strpos($text, $EndTag);
		$StartAttr = $StartPosTemp + mb_strlen($StartTag) + 1;
		$EndAttr = $StartPos;
		if ($EndAttr > $StartAttr) {
			$attribute = mb_substr($text, $StartAttr, $EndAttr - $StartAttr - 1);
			$datas = explode(' ', $attribute);
			for ($i = 0; $i < sizeof($datas); $i++) {
				if (preg_match('/^([a-zA-Z:]+)=["\'](.*)["\']/', $datas[$i], $match)) {
					$items[$match[1]] = $match[2];
				}
			}
		}
		$text = mb_substr($text, $StartPos, ($EndPos - $StartPos));
		if (mb_strpos($text, '[CDATA[') == false) {
			$text = str_replace('&lt;', '<', $text);
			$text = str_replace('&gt;', '>', $text);
			$text = str_replace('&amp;', '&', $text);
			$text = str_replace('&quot;', '"', $text);
		} else {
			$text = str_replace('<![CDATA[', '', $text);
			$text = str_replace(']]>', '', $text);
		}
		$items['data'] = trim($text);
		return $items;
	}
