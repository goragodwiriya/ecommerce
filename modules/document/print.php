<?php
	if (is_array($modules)) {
		// ค่า ที่ส่งมา
		$id = (int)$_GET['id'];
		// query ข้อมูล
		$sql = "SELECT I.`id`,I.`alias`,D.`topic`,D.`detail`,I.`last_update`,U.`displayname`,U.`email`";
		$sql .= " FROM `".DB_INDEX."` AS I";
		$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
		$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=I.`member_id`";
		$sql .= " WHERE I.`id`='$id' AND I.`index`='0' AND I.`published`='1' AND I.`module_id`='$modules[id]' LIMIT 1";
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
			// สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
			if (in_array($status, explode(',', $modules['can_view']))) {
				// ความคิดเห็น
				$comments = array();
				// แสดงความคิดเห็นได้
				if ($modules['can_reply'] != '') {
					$sql = "SELECT C.`detail`,C.`last_update`,C.`ip`";
					$sql .= ",(CASE WHEN ISNULL(U.`id`) THEN C.`email` ELSE (CASE WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) END) AS `displayname`";
					$sql .= " FROM `".DB_COMMENT."` AS C";
					$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=C.`member_id`";
					$sql .= " WHERE C.`index_id`='$index[id]' AND C.`module_id`='$modules[id]'";
					$sql .= " ORDER BY C.`id` ASC";
					$datas = $cache->get($sql);
					if (!$datas) {
						$datas = $db->customQuery($sql);
						if (sizeof($datas) > 0) {
							$cache->save($sql, $datas);
						}
					}
					foreach ($datas AS $i => $item) {
						$i++;
						$row = '<article class=r>';
						$row .= '<div class=detail>'.gcms::showDetail($item['detail'], true).'</div>';
						$row .= '<footer>';
						$row .= '<p><strong>{LNG_COMMENT_NO}#'.$i.'</strong></p>';
						$row .= '<p><strong>{LNG_BY}</strong>: '.$index['displayname'].'</p>';
						$row .= '<p><strong>{LNG_POSTED}</strong>: '.gcms::mktime2date($index['last_update']).'</p>';
						$row .= '<p><strong>{LNG_IP}</strong>: '.gcms::showip($item['ip']).'</p>';
						$row .= '</footer>';
						$row .= '</article>';
						$comments[] = $row;
					}
				}
				$title = $index['topic'];
				if ($config['module_url'] == '1') {
					$url = gcms::getURL($modules['module'], $index['alias'], 0, 0, '', false);
				} else {
					$url = gcms::getURL($modules['module'], '', 0, $index['id'], '', false);
				}
				$content = '<article>';
				$content .= '<header><h1>'.$index['topic'].'</h1></header>';
				$content .= '<div class=detail>'.gcms::showDetail($index['detail'], true, false).'</div>';
				$content .= '<footer>';
				$content .= '<p><strong>{LNG_WRITER}</strong> : '.($index['displayname'] == '' ? $index['email'] : $index['displayname']).'</p>';
				$content .= '<p><strong>{LNG_LAST_UPDATE}</strong>: '.gcms::mktime2date($index['last_update']).'</p>';
				$content .= '<p><strong>{LNG_URL}</strong> : '.$url.'</p>';
				$content .= '</footer>';
				$content .= '</article>';
				$content .= implode("\n", $comments);
			}
		}
	}
