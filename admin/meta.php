<?php
// admin/meta.php
if (MAIN_INIT == 'admin' && $isAdmin) {
  // title
  $title = $lng['LNG_SEO_SOCIAL_TITLE'];
  $a = array();
  $a[] = '<span class=icon-settings>{LNG_SITE_SETTINGS}</span>';
  $a[] = '{LNG_SEO_SOCIAL}';
  // แสดงผล
  $content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
  $content[] = '<section>';
  $content[] = '<header><h1 class=icon-index>'.$title.'</h1></header>';
  $content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
  $content[] = '<fieldset class=paper>';
  $content[] = '<legend><span class=icon-google>{LNG_GOOGLE}</span></legend>';
  // google_site_verification
  $content[] = '<div class=item>';
  $content[] = '<label for=google_site_verification>{LNG_SITE_VERIFICATION_CODE}</label>';
  $content[] = '<div class="table collapse"><span class="td tablet">&lt;meta name=&quot;google-site-verification&quot; content=&quot;</span><span class=td>';
  $content[] = '<span class=g-input><input type=text id=google_site_verification name=google_site_verification value="'.$config['google_site_verification'].'" title="{LNG_SITE_VERIFICATION_CODE_COMMENT}"></span>';
  $content[] = '</span><span class="td tablet">&quot;&nbsp;/&gt;</span></div>';
  $content[] = '<div class=comment id=result_google_site_verification>{LNG_SITE_VERIFICATION_CODE_COMMENT}</div>';
  $content[] = '</div>';
  // google_profile
  $content[] = '<div class=item>';
  $content[] = '<label for=google_profile>{LNG_GOOGLE_PROFILE}</label>';
  $content[] = '<div class="table collapse"><span class="td tablet">https://plus.google.com/</span><span class=td>';
  $content[] = '<span class=g-input><input type=text id=google_profile name=google_profile value="'.$config['google_profile'].'" title="{LNG_GOOGLE_PROFILE_COMMENT}"></span>';
  $content[] = '</span><span class="td tablet">/</span></div>';
  $content[] = '<div class=comment id=result_google_profile>{LNG_GOOGLE_PROFILE_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  $content[] = '<fieldset class=paper>';
  $content[] = '<legend><span class=icon-bing>{LNG_BING}</span></legend>';
  // msvalidate
  $content[] = '<div class=item>';
  $content[] = '<label for=msvalidate>{LNG_SITE_VERIFICATION_CODE}</label>';
  $content[] = '<div><span class=tablet>&lt;meta name=&quot;msvalidate.01&quot; content=&quot;</span><input type=text class=wide id=msvalidate name=msvalidate value="'.$config['msvalidate'].'" title="{LNG_SITE_VERIFICATION_CODE_COMMENT}"><span class=tablet>&quot;&nbsp;/&gt;</span></div>';
  $content[] = '<div class=comment id=result_msvalidate>{LNG_SITE_VERIFICATION_CODE_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '</fieldset>';
  $content[] = '<fieldset>';
  $content[] = '<legend><span class=icon-facebook>{LNG_FACEBOOK}</span></legend>';
  // facebook_appId
  $content[] = '<div class=item>';
  $content[] = '<label for=facebook_appId>{LNG_FACEBOOK_APPID}</label>';
  $content[] = '<span class="g-input icon-password"><input id=facebook_appId name=facebook_appId type=text value="'.$config['facebook']['appId'].'" title="{LNG_FACEBOOK_COMMENT}"></span>';
  $content[] = '</div>';
  // facebook_picture
  $content[] = '<div class=item>';
  $image = is_file(DATA_PATH.'image/facebook_photo.jpg') ? DATA_URL.'image/facebook_photo.jpg' : WEB_URL.'/skin/img/blank.gif';
  $content[] = '<div class=usericon><span><img src="'.$image.'" alt="Facebook Picture" id=fbPicture></span></div>';
  $content[] = '<label for=facebook_picture>{LNG_BROWSE_FILE}</label>';
  $content[] = '<span class="g-input icon-upload"><input class=g-file id=facebook_picture name=facebook_picture type=file title="{LNG_FACEBOOK_PICTURE_COMMENT}" accept="'.gcms::getEccept(array('jpg')).'" data-preview=fbPicture></span>';
  $content[] = '<div class=comment id=result_facebook_picture>{LNG_FACEBOOK_PICTURE_COMMENT}</div>';
  $content[] = '</div>';
  $content[] = '<aside class=message>{LNG_FACEBOOK_REDIRECT_URL} <em>{WEBURL}/index.php</em></aside>';
  $content[] = '</fieldset>';
  // submit
  $content[] = '<fieldset class=submit>';
  $content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
  $content[] = '</fieldset>';
  $content[] = '</form>';
  $content[] = '</section>';
  $content[] = '<script>';
  $content[] = '$G(window).Ready(function(){';
  $content[] = 'new GForm("setup_frm","saveconfig.php").onsubmit(doFormSubmit);';
  $content[] = '});';
  $content[] = '</script>';
  // หน้านี้
  $url_query['module'] = 'meta';
} else {
  $title = $lng['LNG_DATA_NOT_FOUND'];
  $content[] = '<aside class=error>'.$title.'</aside>';
}
