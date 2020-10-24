<?php
// antispamimage.php
session_start();
$_antispamchar = $_SESSION[$_GET['id']];
$im = imagecreate(80, 20);
// transparent
$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans_colour);
// random points
for ($i = 0; $i <= 128; $i++) {
    $point_color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
    imagesetpixel($im, rand(2, 128), rand(2, 38), $point_color);
}
// output characters
for ($i = 0; $i < strlen($_antispamchar); $i++) {
    $text_color = imagecolorallocate($im, rand(0, 255), rand(0, 128), rand(0, 255));
    $x = 5 + $i * 20;
    $y = rand(1, 4);
    imagechar($im, 5, $x, $y, $_antispamchar{$i}, $text_color);
}
// jpeg image
header("Content-type: image/png");
imagepng($im);
// clear
imagedestroy($im);
