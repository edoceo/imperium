<?php
/**
    Main HTML Theme
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;

// $layout = $this->layout();

// Convert Controller Specified Array to String
if (empty($_ENV['h1'])) $_ENV['h1'] = $_ENV['title'];

if (is_array($_ENV['h1'])) {
	$_ENV['h1'] = implode(' &raquo; ',$_ENV['h1']);
}
if (is_array($_ENV['title'])) {
	$_ENV['title'] = implode(' &raquo; ',$_ENV['title']);
}

echo "<!DOCTYPE html>\n<html>\n";
echo '<head>';
echo '<meta charset="utf-8">';
echo '<meta name="viewport" content="initial-scale=1, user-scalable=yes">';
//echo '<link href="' . Radix::link('/lib/reset-css/reset.css') . '" rel="stylesheet">';
echo '<link href="' . Radix::link('/lib/HTML5-Reset/assets/css/reset.css') . '" rel="stylesheet">';
echo '<link href="' . Radix::link('/lib/font-awesome/css/font-awesome.min.css') . '" rel="stylesheet">';
// echo '<link href="//gcdn.org/jquery-ui/1.10.2/smoothness.css" rel="stylesheet">';
// echo '<link href="//gcdn.org/radix/radix.css" rel="stylesheet">';
echo '<link href="' . Radix::link('/css/app.css') . '" rel="stylesheet">';
echo '<link href="' . Radix::link('/img/imperium-icon.ico') . '" rel="shortcut icon">';
echo '<link href="' . Radix::link('/img/imperium-icon.png') . '" rel="apple-touch-icon">';
echo '<script src="' . Radix::link('/lib/jquery/dist/jquery.min.js') . '"></script>';
// echo '<script src="//gcdn.org/jquery-ui/1.10.2/jquery-ui.js"></script>';
// echo '<script src="' . Radix::link('/js/imperium.js') . '"></script>';
// echo '<script>Imperium.base = "' . Radix::base(true) . '";</script>';
echo '<title>Imperium: ' . $_ENV['title'] . '</title>';
echo "</head>\n<body>\n";

$x = Session::flash();
if (!empty($x)) {
	echo '<div>';
	echo $x;
	echo '</div>';
}

// Content Header
echo '<header>';
$menu = Radix::block('menu.php');
if (!empty($menu)) {
    echo '<div id="menu">';
    echo $menu;
    echo '</div>';
	// echo ImperiumView::mruDraw();
}
echo '</header>';

// Core of Page
echo '<div id="core">';

if (!empty($_ENV['h1'])) {
	echo '<h1>' . $_ENV['h1'] . '</h1>';
}

echo $this->body;

echo '</div>'; // #core

?>

<footer>
	<a href="http://imperium.edoceo.com">Imperium</a> &#169; 2001-2016 <a href="http://edoceo.com/">Edoceo, Inc</a>
</footer>

</body>
</html>
