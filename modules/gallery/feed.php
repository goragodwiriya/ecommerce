<?php
	// modules/gallery/feed.php
	if (is_array($modules)) {
		// ค่าที่ีส่งมา
		$aid = (int)$_GET['album'];
		// query
		$sql = "SELECT C.`id`,C.`topic`,C.`detail`,C.`last_update`,G.`image` FROM `".DB_GALLERY."` AS G";
		$sql .= " INNER JOIN `".DB_GALLERY_ALBUM."` AS C ON C.`module_id`='$modules[id]' AND C.`id`=G.`album_id`";
		$sql .= " WHERE G.`module_id`='$modules[id]'";
		if ($aid == -1) {
			$sql .= " AND G.`count`='0'";
		} elseif ($aid > 0) {
			$sql .= " AND G.`album_id`=$aid";
		}
		$sql .= ' ORDER BY '.(isset($_GET['rnd']) ? 'RAND()' : 'G.`id` DESC');
		$sql .= " LIMIT $count";
		foreach ($db->customQuery($sql) AS $item) {
			$link = gcms::getURL($modules['module'], '', 0, 0, "id=$item[id]");
			echo '<item>';
			echo '<title>'.$item['topic'].'</title>';
			echo '<link>'.$link.'</link>';
			echo '<description><![CDATA['.gcms::cutstring(gcms::html2txt($item['detail']), 50).']]></description>';
			echo '<enclosure url="'.urldecode(DATA_URL."gallery/$item[id]/thumb_$item[image]").'" type="image/jpeg"></enclosure>';
			echo '<guid isPermaLink="true">'.$link.'</guid>';
			echo '<pubDate>'.date("D, d M Y H:M", $item['last_update']).':00 +0700</pubDate>';
			echo '</item>';
		}
	}
