<?php die('File not found !')?>
DELETE FROM `{prefix}_language` WHERE `owner`='facebook';
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('FACEBOOK_INVALID_USERNAME','text','facebook','1','Username ของเฟซบุคไม่ถูกต้อง');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_LIKE_BOX','text','facebook','0','Facebook Like Box');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_SETTINGS','text','facebook','0','ตั้งค่าการทำงานของ Facebook Like Box');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_SHOW_FACES','text','facebook','0','Show Faces');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_SHOW_HEADER','text','facebook','0','Show Header');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_SHOW_STREAM','text','facebook','0','Show Stream');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_SIZE_COMMENT','text','facebook','0','ตั้งค่าขนาดการแสดงผลของกรอบ Facebool Like Box');
INSERT INTO `{prefix}_language` (`key`, `type`, `owner`, `js`, `th`) VALUES ('LNG_FACEBOOK_USER_COMMENT','text','facebook','0','Username ของเฟซบุคของคุณ เช่น http://www.facebook.com/<em>username</em>');