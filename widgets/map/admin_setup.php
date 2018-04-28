<?php
	// widgets/map/admin_setup.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// ตรวจสอบค่า default
		$config['map_latigude'] = $config['map_latigude'] == '' ? '14.132081110519639' : $config['map_latigude'];
		$config['map_lantigude'] = $config['map_lantigude'] == '' ? '99.69822406768799' : $config['map_lantigude'];
		$config['map_info_latigude'] = $config['map_info_latigude'] == '' ? '14.132081110519639' : $config['map_info_latigude'];
		$config['map_info_lantigude'] = $config['map_info_lantigude'] == '' ? '99.69822406768799' : $config['map_info_lantigude'];
		$config['map_zoom'] = (int)$config['map_zoom'] == 0 ? 5 : $config['map_zoom'];
		$config['map_height'] = (int)$config['map_height'] == 0 ? 400 : $config['map_height'];
		// title
		$title = $lng['LNG_WIDGETS_MAP_SETTINGS'];
		$a = array();
		$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
		$a[] = '{LNG_WIDGETS_MAP}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-map>'.$title.'</h1></header>';
		$content[] = '<div class=setup_frm>';
		$content[] = '<form id=setup_frm class=paper method=post action=index.php>';
		$content[] = '<fieldset>';
		$content[] = '<legend><span>{LNG_WIDGETS_MAP_SETTINGS_SECTION}</span></legend>';
		// size
		$content[] = '<div class=item>';
		$content[] = '<label for=map_width>{LNG_SIZE_OF} {LNG_WIDGETS_MAP} ({LNG_PX})</label>';
		$content[] = '<div class=input-groups-table>';
		$content[] = '<label class=width for=map_width>{LNG_WIDTH}</label>';
		$content[] = '<span class="width g-input icon-width"><input type=number name=map_width id=map_width value="'.$config['map_width'].'" title="{LNG_WIDTH} {LNG_PX}"></span><span class=width>{LNG_PX}</span>';
		$content[] = '</div>';
		$content[] = '<div class=comment>{LNG_MAP_SIZE_COMMENT}</div>';
		$content[] = '</div>';
		// zoom
		$content[] = '<div class=item>';
		$content[] = '<label for=map_zoom>{LNG_MAP_ZOOM}</label>';
		$content[] = '<span class="table g-input icon-search"><input type=text id=map_zoom name=map_zoom value="'.$config['map_zoom'].'" readonly></span>';
		$content[] = '<div class=comment id=result_map_zoom>{LNG_MAP_ZOOM_COMMENT}</div>';
		$content[] = '</div>';
		// map position
		$content[] = '<div class=item>';
		$content[] = '<label for=map_latigude>{LNG_MAP_POSITION}</label>';
		$content[] = '<div class="table collapse">';
		$content[] = '<label class=td for=map_latigude>{LNG_LATIGUDE}&nbsp;</label>';
		$content[] = '<div class=td><span class="g-input icon-location"><input type=text name=map_latigude id=map_latigude value="'.$config['map_latigude'].'"></span></div>';
		$content[] = '<label class=td for=map_lantigude>&nbsp;{LNG_LANTIGUDE}&nbsp;</label>';
		$content[] = '<div class=td><span class="g-input icon-location"><input type=text name=map_lantigude id=map_lantigude value="'.$config['map_lantigude'].'"></span></div>';
		$content[] = '<label class=td>&nbsp;<input type=button id=find_me value="{LNG_FIND_ME}" class="button go small" disabled></label>';
		$content[] = '<label class=td>&nbsp;<input type=button id=map_search value="{LNG_SEARCH}" class="button go small"></label>';
		$content[] = '</div>';
		$content[] = '<div class=comment>{LNG_MAP_POSITION_COMMENT}</div>';
		$content[] = '</div>';
		$content[] = '</fieldset>';
		$content[] = '<fieldset>';
		$content[] = '<legend><span>{LNG_WIDGETS_MAP_INFO_SECTION}</span></legend>';
		// info
		$content[] = '<div class=item>';
		$content[] = '<label for=map_info>{LNG_GOOGLE_INFO}</label>';
		$content[] = '<span class="g-input icon-file"><textarea name=map_info id=map_info rows=3 cols=60 title="{LNG_GOOGLE_INFO_COMMENT}">'.gcms::detail2TXT(str_replace(array('\r', '\n'), array("\r", "\n"), $config['map_info'])).'</textarea></span>';
		$content[] = '<div class=comment id=result_map_info>{LNG_GOOGLE_INFO_COMMENT}</div>';
		$content[] = '</div>';
		// info position
		$content[] = '<div class=item>';
		$content[] = '<label for=info_latigude>{LNG_INFO_POSITION}</label>';
		$content[] = '<div class="table collapse">';
		$content[] = '<label class=td for=info_latigude>{LNG_LATIGUDE}&nbsp;</label>';
		$content[] = '<div class=td><span class="g-input icon-location"><input type=text name=info_latigude id=info_latigude value="'.$config['map_info_latigude'].'"></span></div>';
		$content[] = '<label class=td for=info_lantigude>&nbsp;{LNG_LANTIGUDE}&nbsp;</label>';
		$content[] = '<div class=td><span class="g-input icon-location"><input type=text name=info_lantigude id=info_lantigude value="'.$config['map_info_lantigude'].'"></span></div>';
		$content[] = '</div>';
		$content[] = '</div>';
		$content[] = '</fieldset>';
		// submit
		$content[] = '<fieldset class=submit>';
		$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		$content[] = "<div class=map-demo><div id=map_canvas style=\"height:$config[map_height]px\">Google Map</div></div>";
		$content[] = '<aside class=message>{LNG_MAP_SETUP_COMMENT}</aside>';
		$content[] = '</div>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = 'new GForm("setup_frm","'.WEB_URL.'/widgets/map/admin_save.php").onsubmit(doFormSubmit);';
		$content[] = 'inintMapDemo();';
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'map-setup';
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
