<?php
	// widgets/calendar/index.php
	if (defined('MAIN_INIT')) {
		$widget[] = '<div id=widget-calendar></div>';
		$widget[] = '<script>';
		$widget[] = 'inintCalendar("widget-calendar", false);';
		$widget[] = '</script>';
		$widget = implode("\n", $widget);
	}
