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

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1, user-scalable=yes">
<meta name="theme-color" content="#336699">
<link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="/vendor/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="/vendor/bootstrap/bootstrap.min.css">
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
echo '<main style="min-height: 85vh;">';
echo '<div class="container-fluid">';
echo $this->body;
echo '</div>';
echo '</main>';
?>

<footer>
	<a href="https://edoceo.com/imperium">Imperium</a> &#169; 2001-2023 <a href="https://edoceo.com/">Edoceo, Inc</a>
</footer>

<script src="/vendor/jquery/jquery.min.js"></script>
<script src="/vendor/jquery-ui/jquery-ui.min.js"></script>
<script src="/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/js/imperium.js"></script>
<?= Layout::getScript() ?>
</body>
</html>
