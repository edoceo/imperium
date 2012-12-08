<?php
/**
	Imperium Login Layout
*/

// Convert Controller Specified Array to String
if (is_array($_ENV['title'])) {
	$_ENV['title'] = implode('&nbsp;&raquo;&nbsp;',$_ENV['title']);
}

echo "<!doctype html>\n";
echo "<html lang=\"en-us\">\n";
echo '<head>';
echo '<title>' . $_ENV['title'] . '</title>';

echo '<link href="//gcdn.org/radix/radix.css" rel="stylesheet" type="text/css">';
echo '<link href="' . radix::link('/imperium.css') . '" rel="stylesheet" type="text/css">';
?>
<link href="<?php echo $this->link('/img/imperium-icon.ico') ?>" rel="shortcut icon">
<link href="<?php echo $this->link('/img/imperium-icon.png') ?>" rel="apple-touch-icon">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
</head>
<body>

<!-- Content Core -->
<div>

<div class="m8 rc"><?php echo radix::link('/','<img alt="logo" src="' . $_ENV['application']['logo'] . '" />'); ?></div>

<div class="m8 rc">
<?php
echo radix_session::flash(); // ImperiumView::drawSessionMessages();
echo $this->body;
?>
</div>
<?php
// echo radix::dump($_ENV);
?>
</div>

<footer>
<a href="http://imperium.edoceo.com">Imperium</a> &copy; 2001-2012 <a href="http://edoceo.com/">Edoceo, Inc</a> | Valid <a href="http://validator.w3.org/check/referer" rel="nofollow">html</a> &amp; <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="nofollow">css</a>
</footer>

</body>
</html>
