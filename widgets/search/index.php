<?php
// widgets/search/index.php
if (defined('MAIN_INIT')) {
  $patt = array('/[\t\r]/', '/{(LNG_[A-Z0-9_]+)}/e', '/{WEBURL}/', '/{SEARCH}/');
  $replace = array();
  $replace[] = '';
  $replace[] = 'gcms::getLng';
  $replace[] = WEB_URL;
  $replace[] = preg_replace('/[\+\s]+/u', ' ', $_GET['q']);
  $widget = gcms::pregReplace($patt, $replace, file_get_contents(ROOT_PATH.'widgets/search/search.html'));
}
