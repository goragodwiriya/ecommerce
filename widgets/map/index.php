<?php
	// widgets/map/index.php
	if (defined('MAIN_INIT')) {
		$widget = '<div class=youtube><iframe src="'.WEB_URL.'/widgets/map/map.php?p='.rawurlencode($module).'"></iframe></div>';
	}
