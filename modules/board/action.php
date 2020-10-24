<?php
	// modules/board/action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer
	if (gcms::isReferer()) {
		// ค่าที่ส่งมา
		if (preg_match('/(quote|edit|delete|pin|lock|deleting)-([0-9]+)-([0-9]+)-([0-9]+)-(.*)$/', $_POST['id'], $match)) {
			$action = $match[1];
			$qid = (int)$match[2];
			$rid = (int)$match[3];
			$no = (int)$match[4];
			$module = $match[5];
		} else {
			$action = $_POST['senddelete_action'];
			$qid = (int)$_POST['senddelete_qid'];
			$rid = (int)$_POST['senddelete_rid'];
			$no = (int)$_POST['senddelete_no'];
			$module = $_POST['senddelete_module'];
		}
		if ($rid > 0) {
			// คำตอบ
			$sql = "SELECT C.`detail`,C.`picture`,C.`member_id`,U.`status`,C.`module_id`,M.`module`,M.`config`,Q.`category_id`";
			$sql .= " FROM `".DB_BOARD_R."` AS C";
			$sql .= " INNER JOIN `".DB_BOARD_Q."` AS Q ON Q.`id`=C.`index_id` AND Q.`module_id`=C.`module_id`";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id`";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=C.`member_id`";
			$sql .= " WHERE C.`id`='$rid' LIMIT 1";
		} else {
			// คำถาม
			$sql = "SELECT Q.`topic`,Q.`detail`,Q.`picture`,Q.`member_id`,Q.`pin`,Q.`locked`,Q.`category_id`,U.`status`,Q.`module_id`,M.`module`,M.`config`";
			$sql .= " FROM `".DB_BOARD_Q."` AS Q";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=Q.`module_id`";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=Q.`member_id`";
			$sql .= " WHERE Q.`id`='$qid' LIMIT 1";
		}
		$index = $db->customQuery($sql);
		$ret = array();
		if (sizeof($index) == 0) {
			$ret['error'] = 'ACTION_ERROR';
		} else {
			$index = $index[0];
			// config
			gcms::r2config($index['config'], $index);
			// login
			$login = $_SESSION['login'];
			// สมาชิก
			$isMember = gcms::isMember();
			// ผู้ดูแล
			$moderator = $isMember && gcms::canConfig(explode(',', $index['moderator']));
			if ($action == 'quote') {
				// อ้างอิง
				if ($index['detail'] == '') {
					$ret['detail'] = '';
				} else {
					$ret['detail'] = rawurlencode('[quote'.($rid > 0 ? " r=$no]" : ']').gcms::txtQuote($index['detail'], true).'[/quote]');
				}
			} elseif ($qid > 0 && in_array($action, array('pin', 'lock')) && $moderator) {
				if ($action == 'pin') {
					$ret['value'] = $index['pin'] == 0 ? 1 : 0;
					$db->edit(DB_BOARD_Q, $qid, array('pin' => $ret['value']));
					$ret['title'] = $lng['LNG_'.($ret['value'] == 0 ? '' : 'UN').'PIN'];
					$ret['error'] = 'BOARD_'.($ret['value'] == 0 ? 'UN' : '').'PIN_SUCCESS';
				} elseif ($action == 'lock') {
					$ret['value'] = $index['locked'] == 0 ? 1 : 0;
					$db->edit(DB_BOARD_Q, $qid, array('locked' => $ret['value']));
					$ret['title'] = $lng['LNG_'.($ret['value'] == 0 ? '' : 'UN').'LOCK'];
					$ret['error'] = 'BOARD_'.($ret['value'] == 0 ? 'UN' : '').'LOCKED_SUCCESS';
				}
			} elseif ($action == 'delete' && $isMember) {
				// สามารถลบได้ (mod=ลบ,สมาชิก=แจ้งลบ)
				if ($moderator) {
					// ลบ
					$ret['confirm'] = $rid > 0 ? 'CONFIRM_DELETE_COMMENT' : 'BOARD_CONFIRM_DELETE_Q';
					$action = 'deleting';
				} elseif (defined('DB_PM') && is_file(ROOT_PATH.'modules/pm/class.pm.php')) {
					// แจ้งลบ
					if ($rid > 0) {
						// คำตอบ
						$ret['confirm'] = 'CONFIRM_SEND_DELETE_COMMENT';
						$ret['url'] = rawurlencode(WEB_URL."/index.php?module=$module&amp;wbid=$qid#R_$rid");
						$ret['topic'] = '';
					} else {
						// คำถาม
						$ret['confirm'] = 'BOARD_CONFIRM_SEND_DELETE_Q';
						$ret['url'] = rawurlencode(WEB_URL."/index.php?module=$module&amp;wbid=$qid");
						$ret['topic'] = rawurlencode($index['topic']);
					}
					$action = "senddelete-$qid-$rid-$no-$module";
				}
			} elseif ($action == 'deleting' && $moderator) {
				if ($rid > 0) {
					// ลบรูปภาพในคำตอบ
					@unlink(DATA_PATH."board/$index[picture]");
					// ลบคำตอบ
					$db->delete(DB_BOARD_R, $rid);
					// อ่านคำตอบล่าสุดของคำถามนี้
					$sql = "SELECT C.`id`,C.`module_id`,C.`last_update`,U.`id` AS `member_id`,U.`status`";
					$sql .= ",(CASE WHEN ISNULL(U.`id`) THEN C.`email` WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) AS `displayname`";
					$sql .= " FROM `".DB_BOARD_R."` AS C";
					$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=C.`member_id`";
					$sql .= " WHERE C.`index_id`='$qid' AND C.`module_id`='$index[module_id]'";
					$sql .= " ORDER BY C.`id` DESC LIMIT 1";
					$r = $db->customQuery($sql);
					// อัปเดตคำถาม
					$sql = "UPDATE `".DB_BOARD_Q."` SET";
					$sql .= " `comment_id`='".(int)$r[0]['id']."'";
					$sql .= ",`commentator`='".addslashes($r[0]['displayname'])."'";
					$sql .= ",`commentator_id`='".(int)$r[0]['member_id']."'";
					$sql .= ",`comment_date`='".(int)$r[0]['last_update']."'";
					$sql .= ",`comments`=(SELECT COUNT(*) FROM `".DB_BOARD_R."` WHERE `index_id`='$qid' AND `module_id`='$index[module_id]')";
					$sql .= ",`hassubpic`=(SELECT COUNT(*) FROM `".DB_BOARD_R."` WHERE `index_id`='$qid' AND `module_id`='$index[module_id]' AND `picture`<>'')";
					$sql .= ",`last_update`=$mmktime";
					$sql .= " WHERE `id`='$qid' LIMIT 1";
					$db->query($sql);
					$ret['remove'] = "R_$rid";
				} else {
					// ลบรูปภาพทั้งหมดภายในคำตอบของคำถามนี้
					$sql = "SELECT `picture`";
					$sql .= " FROM `".DB_BOARD_R."`";
					$sql .= " WHERE `picture` <> '' AND `index_id`='$qid' AND `module_id`='$index[module_id]'";
					foreach ($db->customQuery($sql) AS $item) {
						@unlink(DATA_PATH."board/$item[picture]");
					}
					// ลบคำตอบ
					$sql = "DELETE FROM `".DB_BOARD_R."`";
					$sql .= " WHERE `index_id`='$qid' AND `module_id`='$index[module_id]'";
					$db->query($sql);
					// ลบรูปภาพของคำถาม
					@unlink(DATA_PATH."board/$index[picture]");
					@unlink(DATA_PATH."board/thumb-$index[picture]");
					// ลบคำถาม
					$db->delete(DB_BOARD_Q, $qid);
					// กลับไปหน้าหลักของโมดูลที่เลือก
					$ret['location'] = gcms::getURL($module);
				}
				if ($index['category_id'] > 0) {
					// อัปเดตจำนวนคำตอบและคำถามที่เหลือภายในหมวด
					$sql1 = "SELECT COUNT(*) FROM `".DB_BOARD_Q."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]'";
					$sql2 = "SELECT `id` FROM `".DB_BOARD_Q."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]'";
					$sql2 = "SELECT COUNT(*) FROM `".DB_BOARD_R."` WHERE `index_id` IN ($sql2) AND `module_id`='$index[module_id]'";
					$sql = "UPDATE `".DB_CATEGORY."` AS C SET C.`c1`=($sql1),C.`c2`=($sql2) WHERE C.`module_id`='$index[module_id]'";
					$db->query($sql);
				}
			} elseif ($action == 'edit' && ($moderator || ($isMember && $index['member_id'] == $login['id']))) {
				if ($rid > 0) {
					$ret['location'] = WEB_URL."/index.php?module=$module-edit&rid=$rid";
				} else {
					$ret['location'] = WEB_URL."/index.php?module=$module-edit&qid=$qid";
				}
			}
			$ret['action'] = $action;
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
