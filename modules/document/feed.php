<?php
	// modules/document/feed.php
	if (is_array($modules)) {
		$sql = "SELECT I.`id`,D.`topic`,I.`alias`,D.`description`,I.`last_update`,I.`picture`,I.`pictureW`,I.`pictureH`";
		$sql .= " FROM `".DB_INDEX."` AS I";
		$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
		$cat = isset($_GET['cat']) ? ' AND I.`category_id`='.(int)$_GET['cat'] : '';
		$cat .= isset($_GET['user']) ? ' AND I.`member_id`='.(int)$_GET['user'] : '';
		$sql .= " WHERE I.`module_id`='$modules[id]' $cat AND I.`index`='0' AND I.`published`='1' AND I.`published_date`<='$today'";
		$sql .= " ORDER BY I.`last_update` DESC LIMIT $count";
		foreach ($db->customQuery($sql) AS $item) {
			if ($config['module_url'] == '1') {
				$link = gcms::getURL($modules['module'], $item['alias']);
			} else {
				$link = gcms::getURL($modules['module'], '', 0, $item['id']);
			}
			echo '<item>';
			echo '<title>'.$item['topic'].'</title>';
			echo '<link>'.$link.'</link>';
			echo '<description><![CDATA['.$item['description'].']]></description>';
			if ($item['picture'] != '' && is_file(DATA_PATH."document/$item[picture]")) {
				echo '<enclosure url="'.DATA_URL."document/$item[picture]\" width=\"$item[pictureW]\" height=\"$item[pictureH]\" type=\"image/jpeg\"></enclosure>";
			}
			echo '<guid isPermaLink="true">'.$link.'</guid>';
			echo '<pubDate>'.date("D, d M Y H:i:s +0700", $item['last_update']).'</pubDate>';
			echo '</item>';
		}
	}
