<?php
	// widgets/map/map.php
	// inint
	include '../../bin/inint.php';
	$pos = trim($_GET['p']);
	if ($pos != '' && preg_match('/^([0-9]+\.[0-9+]),([0-9]+\.[0-9+])$/', $pos, $match)) {
		$config['map_lantigude'] = $match[1];
		$config['map_latigude'] = $match[0];
		$config['map_info_lantigude'] = $match[1];
		$config['map_info_latigude'] = $match[0];
	}
	$config['map_latigude'] = $config['map_latigude'] == '' ? '14.132081110519639' : $config['map_latigude'];
	$config['map_lantigude'] = $config['map_lantigude'] == '' ? '99.69822406768799' : $config['map_lantigude'];
	$config['map_info_latigude'] = $config['map_info_latigude'] == '' ? '14.132081110519639' : $config['map_info_latigude'];
	$config['map_info_lantigude'] = $config['map_info_lantigude'] == '' ? '99.69822406768799' : $config['map_info_lantigude'];
	$config['map_zoom'] = (int)$config['map_zoom'] == 0 ? 5 : $config['map_zoom'];
	// หน้าเว็บ Google Map
	$map[] = '<!DOCTYPE html>';
	$map[] = '<html lang='.LANGUAGE.' dir=ltr>';
	$map[] = '<head>';
	$map[] = '<title>Google Map</title>';
	$map[] = '<style>';
	$map[] = 'html,body,#map_canvas{height:100%}';
	$map[] = 'body{margin:0 auto;padding:0;font-family:Tahoma;font-size:12px;text-align:center;line-height:1.5em}';
	$map[] = '</style>';
	$map[] = '<script src="//maps.google.com/maps/api/js?sensor=false&language='.LANGUAGE.'"></script>';
	$map[] = '<meta charset=utf-8>';
	$map[] = '<script>';
	$map[] = 'function initialize() {';
	$map[] = "var myLatlng = new google.maps.LatLng('$config[map_latigude]','$config[map_lantigude]');";
	$map[] = 'var myOptions = {';
	$map[] = 'zoom:'.$config['map_zoom'].',';
	$map[] = 'center:myLatlng,';
	$map[] = 'mapTypeId:google.maps.MapTypeId.ROADMAP';
	$map[] = '}';
	$map[] = 'var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);';
	if ($config['map_info'] != '') {
		$map[] = "var infowindow = new google.maps.InfoWindow({content:'".str_replace(array('\r', '\n'), array('<br>', ''), $config['map_info'])."'});";
	}
	$map[] = "var info = new google.maps.LatLng('$config[map_info_latigude]','$config[map_info_lantigude]');";
	$map[] = 'var marker = new google.maps.Marker({position:info,map:map});';
	if ($config['map_info'] != '') {
		$map[] = 'infowindow.open(map,marker);';
		$map[] = 'google.maps.event.addListener(marker,"click",function(){';
		$map[] = 'infowindow.open(map,marker);';
		$map[] = '})';
	}
	$map[] = '}';
	$map[] = '</script>';
	$map[] = '</head>';
	$map[] = '<body onload="initialize()">';
	$map[] = '<div id=map_canvas>Google Map</div>';
	$map[] = '</body>';
	$map[] = '</html>';
	echo implode("\n", $map);
