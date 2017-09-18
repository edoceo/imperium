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

echo "<!DOCTYPE html>\n<html lang=\"en\">\n";
echo '<head>';
echo '<meta charset="utf-8">';
echo '<meta name="viewport" content="initial-scale=1, user-scalable=yes">';
echo '<meta name="HandheldFriendly" content="True">';
echo '<meta name="MobileOptimized" content="320">';
echo '<meta name="apple-mobile-web-app-capable" content="yes">'; // Install to Home Screen on iOS
echo '<meta name="mobile-web-app-capable" content="yes">'; // Android to Home Screen
echo '<meta name="theme-color" content="#212121">'; // Android Browser Titlebar Background
echo '<meta name="viewport" content="initial-scale=1, user-scalable=yes">';

echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha256-m/h/cUDAhf6/iBRixTbuc8+Rg2cIETQtPcH9D3p2Kg0=" crossorigin="anonymous" />';
echo '<link href="' . Radix::link('/css/app.css') . '" rel="stylesheet">';
//echo '<link href="' . Radix::link('/img/imperium-icon.ico') . '" rel="shortcut icon">';
//echo '<link href="' . Radix::link('/img/imperium-icon.png') . '" rel="apple-touch-icon">';
//echo '<script src="' . Radix::link('/lib/jquery/dist/jquery.min.js') . '"></script>';
//echo '<script src="' . Radix::link('/lib/jquery-ui/jquery-ui.min.js') . '"></script>';
// echo '<script src="//gcdn.org/jquery-ui/1.10.2/jquery-ui.js"></script>';
// echo '<script src="' . Radix::link('/js/imperium.js') . '"></script>';
// echo '<script>Imperium.base = "' . Radix::base(true) . '";</script>';
echo '<title>Imperium: ' . $_ENV['title'] . '</title>';
echo "</head>\n<body>\n";

// Content Header
echo Radix::block('menu.php');

if (!empty($_ENV['h1'])) {
	echo '<h1>' . $_ENV['h1'] . '</h1>';
}

// Flash Messages
$x = Session::flash();
if (!empty($x)) {
	echo '<div class="radix-session-flash">';
	echo $x;
	echo '</div>';
}


// Core of Page
echo '<div id="core">';

echo $this->body;

echo '</div>'; // #core

?>

<footer>
	<a href="http://imperium.edoceo.com">Imperium</a> &#169; 2001-2016 <a href="http://edoceo.com/">Edoceo, Inc</a>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha256-DiWJXXyq81WlPRnDfGmgYZj2aOVCKyEdJ1l+2TmDuAs=" crossorigin="anonymous"></script>

</body>
</html>
