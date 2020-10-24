<?php
	// widgets/gallery/index.php
	if (defined('MAIN_INIT')) {
		$config['widget_gallery_cols'] = empty($config['widget_gallery_cols']) ? 2 : $config['widget_gallery_cols'];
		$config['widget_gallery_rows'] = empty($config['widget_gallery_rows']) ? 4 : $config['widget_gallery_rows'];
		$config['widget_gallery_width'] = empty($config['widget_gallery_width']) ? 75 : $config['widget_gallery_width'];
		$config['widget_gallery_url'] = empty($config['widget_gallery_url']) ? 'http://gallery.gcms.in.th/gallery.rss' : $config['widget_gallery_url'];
		$widget[] = '<div id=rss_gallery></div>';
		$widget[] = '<script>';
		$widget[] = "new RSSGal({'feedurl':'$config[widget_gallery_url]','rows':$config[widget_gallery_rows],'cols':$config[widget_gallery_cols],'imageWidth':$config[widget_gallery_width],'tags':'$config[widget_gallery_tags]','album':'$config[widget_gallery_album_id]','user':'$config[widget_gallery_user_id]'}).show('rss_gallery');";
		$widget[] = '</script>';
		$widget = implode("\n", $widget);
	}
