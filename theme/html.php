<?php
/**
    Main HTML Theme
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\Session;

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
?>
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.2/css/all.min.css" integrity="sha256-zmfNZmXoNWBMemUOo1XUGFfc0ihGGLYdgtJS3KCr/l0=" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha256-YLGeXaapI0/5IgZopewRJcFXomhRMlYYjugPLSyNjTY=" crossorigin="anonymous" />
<?php
echo '<link href="' . Radix::link('/css/app.css') . '" rel="stylesheet">';
//echo '<link href="' . Radix::link('/img/imperium-icon.ico') . '" rel="shortcut icon">';
//echo '<link href="' . Radix::link('/img/imperium-icon.png') . '" rel="apple-touch-icon">';
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
echo '<div class="container-fluid">';
echo $this->body;
echo '</div>';

?>

<footer>
	<a href="https://edoceo.com/imperium">Imperium</a> &#169; 2001-2018 <a href="https://edoceo.com/">Edoceo, Inc</a>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha256-fzFFyH01cBVPYzl16KT40wqjhgPtq6FFUB6ckN2+GGw=" crossorigin="anonymous"></script>
<?= Layout::getScript() ?>
</body>
</html>
