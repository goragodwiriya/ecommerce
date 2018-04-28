<?php
	// modules/document/admin_inint.php
	$config['document']['description'] = $lng['DOCUMENT_DESCRIPTION'];
	// เมนูเขียนเรื่อง
	foreach ($install_owners['document'] AS $items) {
		$admin_menus['modules'][$items['module']]['write'] = '<a href="index.php?module=document-write&amp;id='.$items['id'].'" title="{LNG_DOCUMENT_WRITE}"><span>{LNG_DOCUMENT_WRITE}</span></a>';
	}
