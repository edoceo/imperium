<?php
/**
    @file
    @brief A Single Column Layout, CSS Dropdown Menus
*/

// Convert Controller Specified Array to String
if (is_array($_ENV['title'])) {
	$_ENV['title'] = implode(' &raquo; ',$_ENV['title']);
}

echo "<!doctype html>\n";
echo "<html lang=\"en-us\">\n";
echo '<head>';
?>
<title>Imperium: <?php echo $_ENV['title']; ?></title>
<link href="<?php echo radix::link('/img/imperium-icon.ico') ?>" rel="shortcut icon">
<link href="<?php echo radix::link('/img/imperium-icon.png') ?>" rel="apple-touch-icon">
<?php
echo '<link href="' . radix::link('/base.css') . '" rel="stylesheet">';
echo '<link href="//gcdn.org/fancybox/2.1.0/fancybox.css" rel="stylesheet">';
// $this->headLink()->appendAlternate('/feed/', 'application/rss+xml', 'RSS Feed');
// $this->headLink()->appendStylesheet($this->link('/css/jquery-ui-1.8.6.custom.css'),'all');
// $this->headLink()->appendStylesheet($this->link('/css/ui.autocomplete.css'),'all');
// $this->headLink()->appendStylesheet($this->link('/css/jquery.wysiwyg.css'),'all');

// $this->headScript()->appendFile('//gcdn.org/jquery/1.8.0/jquery.js');
// $this->headScript()->appendFile('//gcdn.org/jquery-ui/1.8.23/jquery-ui.js');
// $this->headScript()->appendFile('//gcdn.org/fancybox/2.1.0/fancybox.js');
// $this->headScript()->appendFile($this->link('/js/imperium.js'));
// 
// // Header Output
// echo $this->headScript();

?>
<!--
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="HandheldFriendly" content="true" />
<meta name="viewport" content="width=680, height=device-height, initial-scale=1, maximum-scale=2, user-scalable=yes" />
-->
</head>
<body ontouchstart="">
<div id="wrap">

<!-- Content Header -->
<div id="head">
<div id="logo"><?php
$img = '<img alt="logo" src="' . $_ENV['Application']['logo'] . '" />';
echo radix::link('/',$img);
?></div>
<?php
echo '<h1>' . $_ENV['title'] . '</h1>';
if (!empty($this->title_one)) {
  echo '<h2>' . $this->title_one . '</h2>';
}
if (!empty($this->title_two)) {
  echo $this->title_two;
}
// echo ImperiumView::mruDraw();
echo '</div>'; // #head

// Menu
echo '<div id="menu">';
echo radix::block('menu.php');
echo '<hr /></div>';

// Core of Page
echo '<div id="core">';

// Title for Printing Only (po)
echo '<div class="po">';
echo '<h1>' . $_ENV['title'] . '</h1>';
if (!empty($this->title_one)) {
  echo '<h2>' . $this->title_one . '</h2>';
}
echo '</div>';

//echo ImperiumView::drawSessionMessages();
echo radix_session::flash();

echo $this->body;

echo '<hr style="clear:both;">';
echo '</div>'; // #core

?>
<script type="text/javascript">
// $('input[type=text], textarea').focus(function(e) { this.select(); }).mouseup(function(e){ e.preventDefault(); });
</script>

<address>
<a href="http://imperium.edoceo.com">Imperium</a> &#169; 2001-2012 <a href="http://edoceo.com/">Edoceo, Inc</a> | Valid <a href="http://validator.w3.org/check/referer" rel="nofollow">html</a> &amp; <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="nofollow">css</a>
</address>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $(".fancybox").fancybox();
    $(".star").on("click", star_step );
});
</script>
</body>
</html>
