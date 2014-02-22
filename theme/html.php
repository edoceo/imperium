<?php
/**
    @file
    @brief A Single Column Layout, CSS Dropdown Menus
*/

// $layout = $this->layout();

// Convert Controller Specified Array to String
if (is_array($_ENV['title'])) {
	$_ENV['title'] = implode(' &raquo; ',$_ENV['title']);
}

echo "<!doctype html>\n<html>\n";
echo '<head>';
echo '<title>Imperium: ' . $_ENV['title'] . '</title>';
// $this->headLink()->appendAlternate('/feed/', 'application/rss+xml', 'RSS Feed');
echo '<script src="//gcdn.org/jquery/1.10.2/jquery.js"></script>';
echo '<script src="//gcdn.org/jquery-ui/1.10.2/jquery-ui.js"></script>';
echo '<script src="' . radix::link('/js/imperium.js') . '"></script>';

echo '<link href="//gcdn.org/radix/radix.css" rel="stylesheet" type="text/css">';
// echo '<link href="//gcdn.org/jquery-ui/1.10.2/smoothness.css" rel="stylesheet" type="text/css">';
echo '<link href="' . radix::link('/css/base.css') . '" rel="stylesheet" type="text/css">';
echo '<link href="' . radix::link('/img/imperium-icon.ico') . '" rel="shortcut icon">';
echo '<link href="' . radix::link('/img/imperium-icon.png') . '" rel="apple-touch-icon">';

echo '<meta name="viewport" content="initial-scale=1, user-scalable=yes">';

echo "</head>\n<body>\n";

// Content Header
echo '<header>';
$menu = radix::block('menu.php');
if (!empty($menu)) {
    echo '<div id="menu">';
    echo $menu;
    echo '</div>';
	// echo ImperiumView::mruDraw();
}

if (!empty($_ENV['h1'])) {
	echo '<h1>' . $_ENV['h1'] . '</h1>';
}
// if (!empty($this->title_one)) {
//     echo '<h2>' . $this->title_one . '</h2>';
// }
// if (!empty($this->title_two)) {
//     echo $this->title_two;
// }
echo '</header>';

// Menu for Authenticated Users Only
// $auth = Zend_Auth::getInstance();

// Core of Page
echo '<div id="core">';

// Title for Printing Only (po)
// echo '<div class="po">';
// echo '<h1>' . $_ENV['title'] . '</h1>';
// if (!empty($this->title_one)) {
//   echo '<h2>' . $this->title_one . '</h2>';
// }
// echo '</div>';

echo radix_session::flash();

echo $this->body;

echo '</div>'; // #core

radix::dump(str_replace('<br>', "\n", radix::info()));
radix::dump($_SESSION);

?>

<footer>
<a href="http://imperium.edoceo.com">Imperium</a> &#169; 2001-2013 <a href="http://edoceo.com/">Edoceo, Inc</a> | Valid <a href="http://validator.w3.org/check/referer" rel="nofollow">html</a> &amp; <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="nofollow">css</a>
</footer>

<script type="text/javascript">
$(document).ready(function() {
    // $('input[type=text], textarea').focus(function(e) { this.select(); }).mouseup(function(e){ e.preventDefault(); });
    // $('.fancybox').fancybox();
    $('.ajax-edit').on('click',function(e) {
        // var o = $(this).parent().offset();
        var t = $(this).data('name');
        $('#' + t).load(this.href,function(res,txt,xhr) {
            $('#' + t).addClass('edit-show');
            // $(document).animate({ scrollTop: o.top},'slow');
            // $(document).scrollTop(o.top - 16);
        });
        e.preventDefault();
    });
    $(".star").on("click", star_step );
});
</script>
</body>
</html>
