<?php
// modules/board/inint.php
if (defined('MAIN_INIT')) {
    // ติดตั้ง RSS
    foreach ($install_modules as $items) {
        if ($items['owner'] == 'board') {
            // โหลด rss
            $topic = empty($items['menu_text']) ? ucwords($items['module']) : $items['menu_text'];
            $meta[] = '<link rel=alternate type=application/rss+xml title="'.$topic.'" href="'.WEB_URL.'/'.$items['module'].'.rss">';
        }
    }
}
