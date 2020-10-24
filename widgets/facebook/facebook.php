<?php
	// widgets/facebook/facebook.php
	// inint
	include '../../bin/inint.php';
	// ตรวจสอบค่า default
	$config['facebook_width'] = $config['facebook_width'] == '' ? 295 : $config['facebook_width'];
	$config['facebook_height'] = $config['facebook_height'] == '' ? 250 : $config['facebook_height'];
	// หน้าเว็บ Facebook
	$facebook = array();
	$facebook[] = '<!DOCTYPE html>';
	$facebook[] = '<html lang='.LANGUAGE.' dir=ltr>';
	$facebook[] = '<head>';
	$facebook[] = '<title>Facebook</title>';
	$facebook[] = '<meta charset=utf-8>';
	$facebook[] = '<style>';
	$facebook[] = '#fb-root{display: none}';
	$facebook[] = '.fb_iframe_widget, .fb_iframe_widget span, .fb_iframe_widget span iframe[style] {width: 100% !important;}';
	$facebook[] = '</style>';
	$facebook[] = '</head>';
	$facebook[] = '<body>';
	$facebook[] = '<div id=fb-root></div>';
	$facebook[] = '<div class="fb-like-box" data-href="https://www.facebook.com/'.$config['facebook_user'].'"';
	$facebook[] = ' data-width="'.$config['facebook_width'] .'"';
	$facebook[] = ' data-height="'.$config['facebook_height'].'"';
	$facebook[] = ' data-show-faces="'.($config['facebook_faces'] == 1 ? 'true' : 'false').'"';
	$facebook[] = ' data-show-border="'.($config['facebook_border'] == 1 ? 'true' : 'false').'"';
	$facebook[] = ' data-stream="'.($config['facebook_stream'] == 1 ? 'true' : 'false').'"';
	$facebook[] = ' data-header="'.($config['facebook_header'] == 1 ? 'true' : 'false').'"></div>';
	$facebook[] = '<script>';
	$facebook[] = '(function(d, id) {';
	$facebook[] = 'var js = d.createElement("script");';
	$facebook[] = 'js.id = id;';
	$facebook[] = 'js.src = "//connect.facebook.net/th_TH/all.js#xfbml=1&appId='.$config['facebook']['appId'].'";';
	$facebook[] = 'd.getElementsByTagName("head")[0].appendChild(js);';
	$facebook[] = '}(document, "facebook-jssdk"));';
	$facebook[] = '</script>';
	$facebook[] = '</body>';
	$facebook[] = '</html>';
	echo implode("\n", $facebook);