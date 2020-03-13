<?php
	// modules/document/action.php
	header("content-type: text/html; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบ referer
	if (gcms::isReferer() && preg_match('/(quote|edit|delete|deleting|pin|lock|print|pdf)-([0-9]+)-([0-9]+)-([0-9]+)-(.*)$/', $_POST['id'], $match)) {
		$action = $match[1];
		$qid = (int)$match[2];
		$rid = (int)$match[3];
		$no = (int)$match[4];
		$module = $match[5];
		if ($rid > 0) {
			// คำตอบ
			$sql = "SELECT C.`detail`,Q.`category_id`,C.`member_id`,U.`status`,M.`id` AS `module_id`,M.`module`,M.`config`";
			$sql .= " FROM `".DB_COMMENT."` AS C";
			$sql .= " INNER JOIN `".DB_INDEX."` AS Q ON Q.`id`=C.`index_id` AND Q.`module_id`=C.`module_id` AND Q.`index`='0'";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=C.`module_id`";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=C.`member_id`";
			$sql .= " WHERE C.`id`='$rid'";
			$sql .= " LIMIT 1";
		} else {
			// คำถาม
			$sql = "SELECT D.`topic`,D.`detail`,Q.`category_id`,Q.`member_id`,U.`status`,M.`id` AS `module_id`,M.`module`,M.`config`";
			$sql .= " FROM `".DB_INDEX."` AS Q";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=Q.`module_id`";
			$sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=Q.`id` AND D.`module_id`=Q.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
			$sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=Q.`member_id`";
			$sql .= " WHERE Q.`id`='$qid'";
			$sql .= " LIMIT 1";
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
			// ผู้ดูแล,เจ้าของเรื่อง (ลบ-แก้ไข บทความ,ความคิดเห็นได้)
			$moderator = gcms::canConfig(explode(',', $index['moderator']));
			$moderator = $isMember && ($moderator || $index['member_id'] == $login['id']);
			if ($action == 'quote') {
				// อ้างอิง
				if ($index['detail'] == '') {
					$ret['detail'] = '';
				} else {
					$ret['detail'] = rawurlencode('[quote'.($rid > 0 ? " r=$no]" : ']').gcms::txtQuote($index['detail'], true).'[/quote]');
				}
			} elseif ($action == 'delete' && $isMember) {
				// สามารถลบได้ (mod=ลบ,สมาชิก=แจ้งลบ)
				if ($moderator || $index['member_id'] == $login['id']) {
					// ลบ
					$ret['confirm'] = $rid > 0 ? 'CONFIRM_DELETE_COMMENT' : 'CONFIRM_DELETE_DOCUMENT';
					$action = 'deleting';
				} elseif (defined('DB_PM')) {
					// แจ้งลบ
					if ($rid > 0) {
						// คำตอบ
						$ret['confirm'] = 'CONFIRM_SEND_DELETE_COMMENT';
						$ret['url'] = rawurlencode(WEB_URL."/index.php?module=$module&amp;id=$qid#R_$rid");
						$ret['topic'] = '';
					} else {
						// คำถาม
						$ret['confirm'] = 'CONFIRM_SEND_DELETE';
						$ret['url'] = rawurlencode(WEB_URL."/index.php?module=$module&amp;id=$qid");
						$ret['topic'] = rawurlencode($index['topic']);
					}
					$action = "senddelete-$qid-$rid-$no-$module";
				}
			} elseif (in_array($action, array('deleting', 'mdelete')) && $moderator) {
				// ลบ mod หรือ เจ้าของ
				if ($rid > 0) {
					// ลบคำตอบ
					$db->delete(DB_COMMENT, $rid);
					// อัปเดตจำนวนคำตอบของคำถาม
					$sql = "UPDATE `".DB_INDEX."`";
					$sql .= " SET `comments`=(";
					$sql .= "SELECT COUNT(*) FROM `".DB_COMMENT."` WHERE `index_id`='$qid' AND `module_id`='$index[module_id]'";
					$sql .= ") WHERE `id`='$qid' LIMIT 1";
					$db->query($sql);
					$ret['remove'] = "R_$rid";
				} else {
					// ลบคำถาม
					$db->delete(DB_INDEX, $qid);
					// ลบคำตอบ
					$db->query("DELETE FROM `".DB_COMMENT."` WHERE `index_id`='$qid'");
					if ($action == 'deleting') {
						// กลับไปหน้าหลักของโมดูลที่เลือก
						$ret['location'] = gcms::getURL($module);
					} else {
						// ลบรายการออก
						$ret['remove'] = "L_$qid";
					}
				}
				// อัปเดตหมวดหมู่
				if ($index['category_id'] > 0) {
					// อัปเดตจำนวนเรื่อง และ ความคิดเห็น ในหมวด
					$sql1 = "SELECT COUNT(*) FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]' AND `index`='0'";
					$sql2 = "SELECT `id` FROM `".DB_INDEX."` WHERE `category_id`=C.`category_id` AND `module_id`='$index[module_id]' AND `index`='0'";
					$sql2 = "SELECT COUNT(*) FROM `".DB_COMMENT."` WHERE `index_id` IN ($sql2) AND `module_id`='$index[module_id]'";
					$sql = "UPDATE `".DB_CATEGORY."` AS C SET C.`c1`=($sql1),C.`c2`=($sql2) WHERE C.`module_id`='$index[module_id]'";
					$db->query($sql);
				}
			} elseif ($action == 'edit' && $moderator) {
				// แก้ไข mod หรือ เจ้าของ
				if ($rid > 0) {
					$ret['location'] = WEB_URL."/index.php?module=$module-edit&id=$rid";
				} else {
					$ret['location'] = WEB_URL."/index.php?module=$module-write&id=$qid";
				}
			}
			$ret['action'] = $action;
		}
		// คืนค่าเป็น JSON
		echo gcms::array2json($ret);
	}
