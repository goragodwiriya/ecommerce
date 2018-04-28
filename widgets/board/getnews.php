<?php
	// widgets/board/getnews.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include ('../../bin/inint.php');
	// ตรวจสอบ referer
	if (gcms::isReferer() && preg_match('/^widget_([a-z0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)$/', $_POST['id'], $match)) {
		if ($match[4] > 0) {
			// query
			$sql = "SELECT Q.`id`,Q.`topic`,Q.`last_update`,Q.`comment_date`,Q.`create_date`,Q.`detail`,U.`status`,U.`id` AS `member_id`";
			$sql .= ",(CASE WHEN ISNULL(U.`id`) THEN (CASE WHEN Q.`comment_date`>0 THEN Q.`commentator` ELSE Q.`email` END) ELSE (CASE WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) END) AS `displayname`";
			$sql .= " FROM `".DB_BOARD_Q."` AS Q";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=(CASE WHEN Q.`comment_date`>0 THEN Q.`commentator_id` ELSE Q.`member_id` END)";
			$sql .= " WHERE Q.`module_id`=$match[2]";
			if ($match[3] > 0) {
				$sql .= " AND Q.`category_id`=$match[3]";
			}
			$sql .= " ORDER BY Q.`last_update` DESC LIMIT $match[4]";
			$datas = $cache->get($sql);
			if (!$datas) {
				$datas = $db->customQuery($sql);
				$cache->save($sql, $datas);
			}
			// เครื่องหมาย new
			$valid_date = $mmktime - $match[5];
			// template
			$skin = gcms::loadtemplate($match[1], 'board', 'widgetitem');
			$patt = array('/{BG}/', '/{URL}/', '/{TOPIC(-([0-9]+))?}/e', '/{DETAIL(-([0-9]+))?}/e',
				'/{DATE}/', '/{UID}/', '/{SENDER}/', '/{STATUS}/', '/{ICON}/');
			$widget = array();
			foreach ($datas AS $item) {
				$bg = $bg == 'bg1' ? 'bg2' : 'bg1';
				$replace = array();
				$replace[] = "$bg background".rand(0, 5);
				$replace[] = gcms::getURL($match[1], '', 0, 0, "wbid=$item[id]");
				$replace[] = create_function('$matches', 'return gcms::cutstring("'.$item['topic'].'",(int)$matches[2]);');
				$replace[] = create_function('$matches', 'return gcms::cutstring(gcms::html2txt("'.$item['detail'].'"),(int)$matches[2]);');
				$replace[] = gcms::mktime2date($item['comment_date'] > 0 ? $item['comment_date'] : $item['last_update']);
				$replace[] = $item['member_id'];
				$replace[] = $item['displayname'];
				$replace[] = $item['status'];
				if ($item['create_date'] > $valid_date && $item['comment_date'] == 0) {
					$replace[] = 'new';
				} elseif ($item['last_update'] > $valid_date || $item['comment_date'] > $valid_date) {
					$replace[] = 'update';
				} else {
					$replace[] = '';
				}
				$widget[] = gcms::pregReplace($patt, $replace, $skin);
			}
			if (sizeof($widget) > 0) {
				echo gcms::pregReplace('/{(LNG_[A-Z0-9_]+)}/e', 'gcms::getLng', implode('', $widget));
			}
		}
	}
