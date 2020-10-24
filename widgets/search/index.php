<?php
// widgets/search/index.php
if (defined('MAIN_INIT')) {
    $q = isset($_GET['q']) ? $_GET['q'] : '';
    $patt = array('/[\t\r]/', '/{(LNG_[A-Z0-9_]+)}/e', '/{WEBURL}/', '/{SEARCH}/');
    $replace = array();
    $replace[] = '';
    $replace[] = 'gcms::getLng';
    $replace[] = WEB_URL;
    $replace[] = preg_replace('/[\+\s]+/u', ' ', $q);
    $widget = gcms::pregReplace($patt, $replace, file_get_contents(ROOT_PATH.'widgets/search/search.html'));
}
