<?php
	// modules/board/search.php
	if (defined('MAIN_INIT')) {
		$sql1 = "SELECT D.`id`,D.`topic` AS `alias`,D.`topic`,'' AS `description`,D.`detail`,0 AS `index`,M.`module`,M.`owner`,3 AS `level`";
		$sql1 .= " FROM `".DB_BOARD_Q."` AS D";
		$sql1 .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id`";
		$sql1 .= " WHERE (".implode(' OR ', $searchs1).")";
		$sql2 = "SELECT D.`id`,D.`topic` AS `alias`,D.`topic`,'' AS `description`,C.`detail`,0 AS `index`,M.`module`,M.`owner`,1 AS `level`";
		$sql2 .= "FROM `".DB_BOARD_R."` AS C";
		$sql2 .= " INNER JOIN `".DB_BOARD_Q."` AS D ON D.`id`=C.`index_id` AND D.`module_id`=C.`module_id`";
		$sql2 .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id`";
		$sql2 .= " WHERE (".implode(' OR ', $searchs2).")";
		$sqls[] = "(SELECT * FROM (($sql1) UNION ($sql2)) AS Q2 GROUP BY Q2.`id`)";
	}
