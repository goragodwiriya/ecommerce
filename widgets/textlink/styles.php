<?php
// widgets/textlink/styles.php
$textlink_typies = array();
$textlink_typies['custom'] = '';
$textlink_typies['list'] = '<p><a href="{URL}" title="{TITLE}" target=_blank><img alt="{TITLE}" src="{LOGO}"></a></p>';
$textlink_typies['row'] = '<a href="{URL}" title="{TITLE}" target=_blank>{TITLE}</a>';
$textlink_typies['image'] = '<a href="{URL}" target=_blank class=animate><img alt="{TITLE}" src="{LOGO}"></a>';
$textlink_typies['menu'] = '<li><a href="{URL}" title="{TITLE}"><span>{TITLE}</span></a></li>';
$textlink_typies['banner'] = '<a href="{URL}" target=_blank><img alt="{TITLE}" src="{LOGO}"></a>';
$textlink_typies['slideshow'] = '';
