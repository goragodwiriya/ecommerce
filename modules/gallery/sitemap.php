<?php
	// modules/gallery/sitemap.php
	if (is_array($owners['gallery'])) {
		// อัลบัมทั้งหมด
		$sql = "SELECT `id`,`module_id`,`last_update` FROM `".DB_GALLERY_ALBUM."`";
		$datas = $cache->get($sql);
		if (!$datas) {
			$datas = $db->customQuery($sql);
			$cache->save($sql, $datas);
		}
		foreach ($datas AS $item) {
			$link = gcms::getURL($modules[$item['module_id']], '', 0, 0, "id=$item[id]");
			echo '<url>';
			echo '<loc>'.$link.'</loc>';
			echo '<lastmod>'.date("Y-m-d", $item['last_update']).'</lastmod>';
			echo '<changefreq>daily</changefreq>';
			echo '<priority>0.5</priority>';
			echo '</url>';
		}
	}
