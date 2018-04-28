<?php
if (INSTALL_INIT == 'upgrade') {
  $current_version = '5.0.0';
  // 5.0.1 upgrade table user
  $db->query("ALTER TABLE `".DB_USER."` ADD `invite_id` INT(11) UNSIGNED NOT NULL");
  $db->query("ALTER TABLE `".DB_USER."` ADD `subscrib` BOOLEAN NOT NULL DEFAULT '1'");
  // rename index.keyword เป็น index.keywords, add index.index, add create_date
  $db->query("ALTER TABLE `".DB_INDEX."` ADD `index` BOOLEAN NOT NULL DEFAULT '0'");
  $db->query("ALTER TABLE `".DB_INDEX."` CHANGE `keyword` `keywords` VARCHAR( 149) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
  $db->query("ALTER TABLE `".DB_INDEX."` ADD `create_date` INT(11) UNSIGNED NOT NULL");
  echo '<li class=correct>Upgrade <strong>'.DB_INDEX.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  // upgrade menu
  $sql = "SELECT I.`id`";
  $sql .= " FROM `".DB_MODULES."` AS M";
  $sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`menu_text`!='' AND I.`module_id`=M.`id`";
  $sql .= " WHERE M.`parent`!=''";
  $sql .= " ORDER BY I.`menu_order` DESC";
  $menu_order = 0;
  foreach ($db->customQuery($sql) AS $item) {
    $menu_order++;
    $orderd[$item['id']] = $menu_order;
  }
  $sql = 'DROP TABLE IF EXISTS `'.DB_MENUS.'`;';
  $db->query($sql);
  $sql = 'CREATE TABLE IF NOT EXISTS `'.DB_MENUS.'` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`index_id` int(11) unsigned NOT NULL,
				`parent` varchar(20) collate utf8_unicode_ci NOT NULL,
				`level` smallint(2) unsigned NOT NULL,
				`language` varchar(3) collate utf8_unicode_ci NOT NULL,
				`menu_text` varchar(100) collate utf8_unicode_ci NOT NULL,
				`menu_tooltip` varchar(100) collate utf8_unicode_ci NOT NULL,
				`accesskey` varchar(1) collate utf8_unicode_ci NOT NULL,
				`menu_order` int(11) unsigned NOT NULL,
				`menu_url` varchar(255) collate utf8_unicode_ci NOT NULL,
				`menu_target` varchar(6) collate utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`))
				ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0;';
  $db->query($sql);
  echo '<li class=correct>Create table <strong>'.DB_MENUS.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  $sql = "SELECT I.`id`,I.`topic`,I.`menu_text`,I.`menu_tooltip`,I.`language`,";
  $sql .= "M.`id` AS `module_id`,M.`module`,M.`owner`,M.`menu_url`,M.`last_update`,M.`parent`,M.`config`";
  $sql .= " FROM `".DB_MODULES."` AS M";
  $sql .= " LEFT JOIN `".DB_INDEX."` AS I ON I.`menu_text`!='' AND I.`module_id`=M.`id`";
  $sql .= " WHERE M.`parent`!=''";
  foreach ($db->customQuery($sql) AS $item) {
    $save['index_id'] = $item['menu_url'] != '' ? 0 : $item['id'];
    $save['parent'] = $item['parent'];
    $save['level'] = '0';
    $save['language'] = $item['language'];
    $save['menu_text'] = addslashes($item['menu_text']);
    $save['menu_tooltip'] = addslashes($item['menu_tooltip']);
    $save['menu_order'] = $orderd[$item['id']];
    $save['menu_url'] = addslashes($item['menu_url']);
    $save['menu_target'] = $item['menu_target'];
    $db->add(DB_MENUS, $save);
  }
  echo '<li class=correct>Upgrade menus <strong>'.DB_MENUS.'</strong> <i>complete...</i></li>';
  ob_flush();
  flush();
  $sql = "SELECT I.`id`,I.`topic`,M.`owner`";
  $sql .= " FROM `".DB_MODULES."` AS M,`".DB_INDEX."` AS I";
  $sql .= " WHERE (I.`menu_text`!='' OR M.`owner`='index') AND I.`module_id`=M.`id`";
  foreach ($db->customQuery($sql) AS $item) {
    if ($item['owner'] != 'index' && $item['topic'] == '') {
      // ลบโมดูลที่ไม่มีหน้าเว็บ
      $db->delete(DB_INDEX, $item['id']);
    } else {
      // กำหนดให้เป็นหน้าหลักของโมดูล
      $db->edit(DB_INDEX, $item['id'], array('index' => 1));
    }
  }
  // update index
  $db->query("UPDATE `".DB_INDEX."` SET `create_date`=`last_update`");
  $db->query("ALTER TABLE `".DB_INDEX."` DROP `menu_text`");
  $db->query("ALTER TABLE `".DB_INDEX."` DROP `menu_tooltip`");
  $db->query("ALTER TABLE `".DB_INDEX."` DROP `menu_order`");
  // update modules
  $db->query("ALTER TABLE `".DB_MODULES."` DROP `ip`");
  $db->query("ALTER TABLE `".DB_MODULES."` DROP `last_update`");
  $db->query("ALTER TABLE `".DB_MODULES."` DROP `visited`");
  $db->query("ALTER TABLE `".DB_MODULES."` DROP `parent`");
  $db->query("ALTER TABLE `".DB_MODULES."` DROP `menu_target`");
  $db->query("ALTER TABLE `".DB_MODULES."` DROP `menu_url`");
}
