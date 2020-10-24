<?php
	// widgets/rss/index.php
	if (defined('MAIN_INIT')) {
		$widget = array();
		if (preg_match('/([0-9]+)(_([0-9]+))?/', $module, $match)) {
			$id = $match[1] == 0 ? '' : $match[1];
			$interval = $match[3] == '' ? 30 : $match[3];
		} else {
			$id = '';
			$interval = 30;
		}
		if (is_array($config['rss_tabs'])) {
			$widget[] = '<div class=rss_widget>';
			$widget[] = '<div id=rss_tab_'.$id.' class=rss_tab></div>';
			$widget[] = '<div id=rss_div_'.$id.' class=rss_div></div>';
			$widget[] = '</div>';
			$widget[] = '<script>';
			$widget[] = "var rss = new GRSSTab('rss_tab_$id','rss_div_$id', $interval);";
			foreach ($config['rss_tabs'] AS $item) {
				if ($id == $item[2]) {
					$widget[] = "rss.add('$item[0]', '$item[1]', {rows:$item[3],cols:$item[4]});";
				}
			}
			$widget[] = 'rss.show(0);';
			$widget[] = '</script>';
		}
		$widget = implode('', $widget);
	}
