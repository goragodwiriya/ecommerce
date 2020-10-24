<?php
	if (is_array($modules)) {
		// ค่า ที่ส่งมา
		$id = (int)$_GET['id'];
		// query ข้อมูล
		$sql = "SELECT I.`id`,I.`topic`,I.`detail`,I.`create_date`,I.`picture`,U.`email`";
		$sql .= ",(CASE WHEN ISNULL(U.`id`) THEN I.`email` WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) AS `displayname`";
		$sql .= " FROM `".DB_BOARD_Q."` AS I";
		$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=I.`member_id`";
		$sql .= " WHERE I.`id`='$id' AND I.`module_id`='$modules[id]' LIMIT 1";
		$index = $cache->get($sql);
		if (!$index) {
			$index = $db->customQuery($sql);
			$cache->save($sql, $index);
		}
		if (sizeof($index) == 1) {
			$index = $index[0];
			// config
			gcms::r2config($modules['config'], $modules);
			// guest มีสถานะเป็น -1
			$status = gcms::isMember() ? $login['status'] : -1;
			// dir ของรูปภาพอัปโหลด
			$imagedir = DATA_PATH.'board/';
			$imageurl = DATA_URL.'board/';
			// สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
			if (in_array($status, explode(',', $modules['can_view']))) {
				// ความคิดเห็น
				$comments = array();
				$sql = "SELECT C.`detail`,C.`last_update`,C.`ip`,C.`picture`,U.`email`";
				$sql .= ",(CASE WHEN ISNULL(U.`id`) THEN C.`email` WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) AS `displayname`";
				$sql .= " FROM `".DB_BOARD_R."` AS C";
				$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=C.`member_id`";
				$sql .= " WHERE C.`index_id`='$index[id]' AND C.`module_id`='$modules[id]'";
				$sql .= " ORDER BY C.`id` ASC";
				$datas = $cache->get($sql);
				if (!$datas) {
					$datas = $db->customQuery($sql);
					$cache->save($sql, $datas);
				}
				foreach ($datas AS $i => $item) {
					$picture = $item['picture'] != '' && is_file($imagedir.$item['picture']) ? '<p class=img><img src="'.$imageurl.$item['picture'].'" alt=""></p>' : '';
					$i++;
					$row = '<article class=r>';
					$row .= '<div class=detail>'.$picture.gcms::showDetail($item['detail'], true).'</div>';
					$row .= '<footer>';
					$row .= '<p><strong>{LNG_COMMENT_NO}#'.$i.'</strong></p>';
					$row .= '<p><strong>{LNG_BY}</strong>: '.($index['displayname'] == '' ? $index['email'] : $index['displayname']).'</p>';
					$row .= '<p><strong>{LNG_POSTED}</strong>: '.gcms::mktime2date($index['last_update']).'</p>';
					$row .= '<p><strong>{LNG_IP}</strong>: '.gcms::showip($item['ip']).'</p>';
					$row .= '</footer>';
					$row .= '</article>';
					$comments[] = $row;
				}
				$title = $index['topic'];
				if ($index['picture'] != '' && is_file($imagedir.$index['picture'])) {
					$picture = '<p class=img><img src="'.$imageurl.$index['picture'].'" alt=""></p>';
				} else {
					$picture = '';
				}
				$content = '<article>';
				$content .= '<header><h1>'.$index['topic'].'</h1></header>';
				$content .= '<div class=detail>'.$picture.gcms::showDetail($index['detail'], true).'</div>';
				$content .= '<footer>';
				$content .= '<p><strong>{LNG_BY}</strong> : '.($index['displayname'] == '' ? $index['email'] : $index['displayname']).'</p>';
				$content .= '<p><strong>{LNG_POSTED}</strong>: '.gcms::mktime2date($index['create_date']).'</p>';
				$content .= '<p><strong>{LNG_URL}</strong> : '.gcms::getURL($modules['module'], '', $index['category_id'], 0, "wbid=$index[id]").'</p>';
				$content .= '</footer>';
				$content .= '</article>';
				$content .= implode("\n", $comments);
			}
		}
	}
