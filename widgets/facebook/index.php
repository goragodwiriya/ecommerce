<?php
	// widgets/facebook/index.php
	if (defined('MAIN_INIT')) {
		$module = $module == '' ? $config['facebook_user'] : $module;
		if ($module == 'hidden') {
			$widget = '';
		} else {
			$facebook = array();
			$facebook[] = '<div id=fb-root></div>';
			$facebook[] = '<div>';
			$facebook[] = '<div style="height:'.$config['facebook_height'].'px;" class=fb-like-box';
			$facebook[] = ' data-href="https://www.facebook.com/'.$module.'"';
			$facebook[] = ' data-width="'.$config['facebook_width'].'"';
			$facebook[] = ' data-height="'.$config['facebook_height'].'"';
			$facebook[] = ' data-show-faces="'.($config['facebook_faces'] == 1 ? 'true' : 'false').'"';
			$facebook[] = ' data-show-border="'.($config['facebook_border'] == 1 ? 'true' : 'false').'"';
			$facebook[] = ' data-stream="'.($config['facebook_stream'] == 1 ? 'true' : 'false').'"';
			$facebook[] = ' data-header="'.($config['facebook_header'] == 1 ? 'true' : 'false').'"></div></div>';
			$facebook[] = '<script>';
			$facebook[] = '(function(d, id) {';
			$facebook[] = 'var js = d.createElement("script");';
			$facebook[] = 'js.id = id;';
			$facebook[] = 'js.src = "//connect.facebook.net/th_TH/all.js#xfbml=1&appId='.$config['facebook']['appId'].'";';
			$facebook[] = 'd.getElementsByTagName("head")[0].appendChild(js);';
			$facebook[] = '}(document, "facebook-jssdk"));';
			$facebook[] = '</script>';
			$widget = implode("\n", $facebook);
		}
	}