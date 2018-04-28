<?php
	// widgets/share/index.php
	if (defined('MAIN_INIT')) {
		// share on tweeter & facebook
		$widget[] = '<div class=widget_share>';
		$widget[] = '<a rel=nofollow class="fb_share icon-facebook" title="Facebook Share"></a>';
		$widget[] = '<a rel=nofollow class="twitter_share icon-twitter" title="Twitter"></a>';
		$widget[] = '<a rel=nofollow class="gplus_share icon-googleplus" title="Google Plus"></a>';
		if ($config['google_profile'] != '') {
			$widget[] = '<a rel=nofollow href="http://plus.google.com/'.$config['google_profile'].'" class="google_profile icon-google" target=_blank title="Google Profile"></a>';
		}
		$widget[] = '<a rel=nofollow class="email_share icon-email" title="{LNG_SHARE_TITLE}"></a>';
		$widget[] = '<script>';
		$widget[] = '$G(window).Ready(function(){';
		$widget[] = 'inintShareButton(document);';
		$widget[] = '});';
		$widget[] = '</script>';
		$widget[] = '</div>';
		$widget = implode("\n", $widget);
	}
