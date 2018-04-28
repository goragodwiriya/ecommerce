<?php
	// modules/board/sitemap.php
	if (is_array($owners['board'])) {
		$sql = "SELECT `id`,`module_id`,`last_update`,`comment_date`";
		$sql .= " FROM `".DB_BOARD_Q."` WHERE `module_id` IN(".implode(',', $owners['board']).")";
		$datas = $cache->get($sql);
		if (!$datas) {
			$datas = $db->customQuery($sql);
			$cache->save($sql, $datas);
		}
		foreach ($datas AS $item) {
			$link = gcms::getURL($modules[$item['module_id']])."?wbid=$item[id]";
			echo '<url>';
			echo "<loc>$link</loc>";
			echo '<lastmod>'.date("Y-m-d", ($item['comment_date'] > 0 ? $item['comment_date'] : $item['last_update'])).'</lastmod>';
			echo '<changefreq>daily</changefreq>';
			echo '<priority>0.5</priority>';
			echo '</url>';
		}
	}
