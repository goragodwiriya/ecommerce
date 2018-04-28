<?php
	// widgets/calendar/tooltip.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include ('../../bin/inint.php');
	// referer
	if (gcms::isReferer()) {
		if (preg_match('/^calendar\-([0-9]+){0,2}\-([0-9]+){0,2}\-([0-9]+){0,4}\-([0-9_]+)$/', $_POST['id'], $match)) {
			if ($config['calendar_owner'] == '') {
				$config['calendar_owner'] = 'document';
				$config['calendar_db'] = DB_INDEX;
			}
			echo '<div id=calendar-tooltip>';
			echo '<h5>'.(int)$match[1].' '.$lng['MONTH_SHORT'][(int)$match[2] - 1].' '.$match[3].'</h5>';
			// อ่านข้อมูลที่ id
			foreach (explode('_', $match[4]) AS $id) {
				$ids[] = (int)$id;
			}
			$sql = "SELECT I.`id`,D.`topic`,D.`description`,M.`module`";
			$sql .= " FROM `$config[calendar_db]` AS I";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=I.`module_id` AND M.`owner`='$config[calendar_owner]'";
			$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
			$sql .= " WHERE I.`id` IN(".implode(',', $ids).")";
			foreach ($db->customQuery($sql) AS $item) {
				echo '<a href="'.WEB_URL.'/index.php?module='.$item['module'].'&amp;id='.$item['id'].'" title="'.$item['description'].'">'.$item['topic'].'</a>';
			}
			echo '</div>';
		}
	}
