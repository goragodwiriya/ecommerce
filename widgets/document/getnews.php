<?php
// widgets/document/getnews.php
header("content-type: text/html; charset=UTF-8");
// inint
include '../../bin/inint.php';
// ตรวจสอบ referer
if (gcms::isReferer() && preg_match('/^widget_([a-z0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)$/', $_POST['id'], $match)) {
    if ($match[4] > 0) {
        // เรียงลำดับ
        $sorts = array('I.`last_update` DESC,I.`id` DESC', 'I.`create_date` DESC,I.`id` DESC', 'I.`published_date` DESC,I.`last_update` DESC', 'I.`id` DESC');
        // query
        $sql = "SELECT I.`id`,D.`topic`,I.`alias`,I.`comment_date`,I.`last_update`,I.`create_date`,I.`picture`,D.`description`,I.`comments`,I.`visited`,U.`status`,U.`id` AS `member_id`,U.`displayname`,U.`email`";
        $sql .= " FROM `".DB_INDEX."` AS I";
        $sql .= " LEFT JOIN `".DB_USER."` AS U ON U.`id`=I.`member_id`";
        $sql .= " INNER JOIN `".DB_INDEX_DETAIL."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN ('".LANGUAGE."','')";
        $sql .= " WHERE I.`module_id`=$match[2]";
        if ($match[3] > 0) {
            $sql .= " AND I.category_id=$match[3]";
        }
        $sql .= " AND I.`published`='1' AND I.`index`='0' ORDER BY ".$sorts[$match[6]]." LIMIT $match[4]";
        $datas = $cache->get($sql);
        if (!$datas) {
            $datas = $db->customQuery($sql);
            $cache->save($sql, $datas);
        }
        // เครื่องหมาย new
        $valid_date = $mmktime - $match[5];
        // template
        $skin = gcms::loadtemplate($match[1], 'document', 'widgetitem');
        $patt = array('/{BG}/', '/{URL}/', '/{TOPIC}/', '/{DETAIL}/', '/{DATE}/', '/{UID}/',
            '/{SENDER}/', '/{STATUS}/', '/{COMMENTS}/', '/{VISITED}/', '/{THUMB}/', '/{ICON}/');
        $widget = array();
        foreach ($datas as $i => $item) {
            if ($i > 0 && $match[7] > 1 && $i % $match[7] == 0) {
                $widget[] = '</div><div class=ggrid>';
            }
            $bg = $bg == 'bg1' ? 'bg2' : 'bg1';
            $replace = array();
            $replace[] = "$bg background".rand(0, 5);
            if ($config['module_url'] == '1') {
                $replace[] = gcms::getURL($match[1], $item['alias']);
            } else {
                $replace[] = gcms::getURL($match[1], '', 0, $item['id']);
            }
            $replace[] = $item['topic'];
            $replace[] = $item['description'];
            $replace[] = gcms::mktime2date($item['create_date']);
            $replace[] = $item['member_id'];
            $replace[] = $item['displayname'] == '' ? $item['email'] : $item['displayname'];
            $replace[] = $item['status'];
            $replace[] = $item['comments'];
            $replace[] = $item['visited'];
            if ($item['picture'] != '' && is_file(DATA_PATH."document/$item[picture]")) {
                $replace[] = DATA_URL."document/$item[picture]";
            } else {
                $replace[] = WEB_URL.'/'.SKIN.'document/img/default-icon.png';
            }
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
            $blocks = array(1 => 12, 2 => 6, 3 => 4, 4 => 3, 6 => 2);
            $patt = array('/{BLOCK}/', '/{ITEMW}/', '/{(LNG_[A-Z0-9_]+)}/e');
            $replace = array();
            $replace[] = $blocks[$match[7]];
            $replace[] = 100 / $match[7];
            $replace[] = 'gcms::getLng';
            if ($match[7] > 1) {
                echo gcms::pregReplace($patt, $replace, '<div class=ggrid>'.implode('', $widget).'</div>');
            } else {
                echo gcms::pregReplace($patt, $replace, implode('', $widget));
            }
        }
    }
}
