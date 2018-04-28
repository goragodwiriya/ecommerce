<?php
	// modules/board/feed.php
	if (is_array($modules)) {
		$sql = "SELECT I.`id`,(CASE WHEN I.`comment_date`='0' THEN I.`last_update` ELSE I.`comment_date` END) AS `last_update`";
		$sql .= ",I.`topic`,I.`detail`,I.`picture` FROM `".DB_BOARD_Q."` AS I";
		$cat = isset($_GET['cat']) ? ' AND I.`category_id`='.(int)$_GET['cat'] : '';
		$cat .= isset($_GET['user']) ? ' AND I.`member_id`='.(int)$_GET['user'] : '';
		$sql .= " WHERE I.`module_id`='$modules[id]' $cat";
		if (isset($_GET['album'])) {
			$sql .= " AND I.`picture`!=''";
		}
		$sql .= " ORDER BY ".(isset($_GET['rnd']) ? 'RAND()' : 'I.`last_update` DESC')." LIMIT $count";
		foreach ($db->customQuery($sql) AS $item) {
			$link = gcms::getURL($modules['module'])."?wbid=$item[id]";
			echo '<item>';
			echo '<title>'.$item['topic'].'</title>';
			echo '<link>'.$link.'</link>';
			echo '<description><![CDATA['.gcms::cutstring(gcms::html2txt($item['detail']), 50).']]></description>';
			if ($item['picture'] != '' && is_file(DATA_PATH."board/thumb-$item[picture]")) {
				echo '<enclosure url="'.DATA_URL."board/thumb-$item[picture]\" type=\"image/jpeg\"></enclosure>";
			}
			echo '<guid isPermaLink="true">'.$link.'</guid>';
			echo '<pubDate>'.date("D, d M Y H:i:s +0700", $item['last_update']).'</pubDate>';
			echo '</item>';
		}
	}
