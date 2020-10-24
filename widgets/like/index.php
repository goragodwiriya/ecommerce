<?php
	// widgets/like/index.php
	if (defined('MAIN_INIT')) {
		// facebook like button and +1
		$widget[] = '<div id=g-plusone></div>';
		$widget[] = '<div id=fb-likebox></div>';
		$widget[] = '<div id=twitter-share></div>';
		$widget[] = '<script>';
		$widget[] = 'function setLikeURL(src, url){';
		$widget[] = 'var e = $E(src);';
		$widget[] = 'var d = (e.contentWindow || e.contentDocument);';
		$widget[] = 'd.location.replace(url);';
		$widget[] = '};';
		$widget[] = 'function createLikeButton(){';
		$widget[] = 'var url = encodeURIComponent(getCurrentURL());';
		$a = 'http://www.facebook.com/plugins/like.php?layout='.($module == 'tall' ? 'box_count' : 'button_count').'&node_type=link&show_faces=false&href=';
		$widget[] = 'setLikeURL("fb-likebox-iframe", "'.$a.'" + url);';
		$a = 'https://plusone.google.com/_/+1/fastbutton?bsv&size='.($module == 'tall' ? 'tall' : 'medium').'&count=true&hl='.LANGUAGE.'&url=';
		$widget[] = 'setLikeURL("g-plusone-iframe", "'.$a.'" + url);';
		$a = 'http://platform.twitter.com/widgets/tweet_button.1404859412.html#count='.($module == 'tall' ? 'vertical' : 'horizontal').'&lang='.LANGUAGE.'&url=';
		$widget[] = 'setLikeURL("twitter-share-iframe", "'.$a.'" + url);';
		$widget[] = '};';
		$widget[] = '$G(window).Ready(function(){';
		foreach (array('fb-likebox','g-plusone','twitter-share') AS $item) {
			$widget[] = '$E("'.$item.'").style.display = "inline";';
			$widget[] = "var iframe = document.createElement('iframe');";
			$widget[] = "iframe.id = '$item-iframe';";
			$widget[] = "iframe.frameBorder = 0;";
			$widget[] = "iframe.scrolling = 'no';";
			if ($module == 'tall') {
				$widget[] = "iframe.width = '60';";
				$widget[] = "iframe.height = '68';";
			} else {
				$widget[] = "iframe.width = '90';";
				$widget[] = "iframe.height = '28';";
			}
			$widget[] = "iframe.style.overflow = 'hidden';";
			$widget[] = '$E("'.$item.'").appendChild(iframe);';
			if ($item == 'g-plusone') {
				$widget[] = '$G(iframe).setStyle("float","left");';
			}
		}
		$widget[] = 'createLikeButton();';
		$widget[] = '});';
		$widget[] = '</script>';
		$widget = implode("\n", $widget);
	}
