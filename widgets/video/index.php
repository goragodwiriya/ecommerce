<?php
	// widgets/video/index.php
	if (defined('MAIN_INIT')) {
		if (preg_match('/^([0-9]+)_([0-9]+)$/', $module, $match)) {
			$cols = max(1, $match[1]);
			$where = "ORDER BY C.`id` DESC LIMIT ".(max(1, $match[2]) * $cols);
		} elseif (preg_match('/[a-zA-Z0-9\-_]{11,11}/', $module, $match)) {
			$youtube = $module;
		} elseif (preg_match('/[0-9]+/', $module, $match)) {
			$where = "WHERE C.`id`=".(int)$module." LIMIT 1";
		} else {
			$cols = 2;
			$where = "ORDER BY C.`id` DESC LIMIT 2";
		}
		if ($where != '') {
			$sql = "SELECT C.`id`,C.`topic`,C.`youtube` FROM `".DB_VIDEO."` AS C";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`owner`='video' AND M.`id`=C.`module_id` $where";
			$list = $cache->get($sql);
			if (!$list) {
				$list = $db->customQuery($sql);
				$cache->save($sql, $list);
			}
			if ($cols == '' && sizeof($list) == 1) {
				$youtube = $list[0]['youtube'];
			}
		}
		$widget = array();
		if ($youtube == '') {
			$patt = array('/{ID}/', '/{THUMB}/', '/{YOUTUBE}/', '/{TOPIC}/', '/{DESCRIPTION}/', '/{VIEWS}/', '/{BLOCK}/');
			$skin = gcms::loadtemplate('video', 'video', 'listitem');
			$a = gcms::rndname(5);
			$widget[] = '<div class=video_list id=video_list_'.$a.'><div class="ggrid rows clear">';
			foreach ($list AS $i => $item) {
				$widget[] = $i > 0 && $i % $cols == 0 ? '</div><div class="ggrid rows clear">' : '';
				$replace = array();
				$replace[] = $item['id'];
				$replace[] = is_file(DATA_PATH."video/$item[youtube].jpg") ? DATA_URL."video/$item[youtube].jpg" : WEB_URL.'/modules/video/img/nopicture.jpg';
				$replace[] = $item['youtube'];
				$replace[] = $item['topic'];
				$replace[] = $item['description'];
				$replace[] = $item['views'];
				$replace[] = 12 / $cols;
				$widget[] = preg_replace($patt, $replace, $skin);
			};
			$widget[] = '</div></div>';
			$widget[] = '<script>';
			$widget[] = "inintVideoList('video_list_$a');";
			$widget[] = '</script>';
		} else {
			$widget[] = '<div class="youtube"><iframe src="//www.youtube.com/embed/'.$youtube.'?wmode=transparent"></iframe></div>';
		}
		$widget = implode('', $widget);
	}
