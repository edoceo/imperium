<?php
/**
    Imperium Login View

    Draws the Login Form

    @copyright      2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\HTML\Form;

$_ENV['title'] = $_ENV['h1'] = 'Sign In';

echo '<form method="post">';

//echo "<div>";
//echo "<input id='flash_enabled' name='flash_enabled' type='hidden' value='' />";
//echo "<input id='js_enabled' name='js_enabled' type='hidden' value='' />";
//echo "<input id='java_enabled' name='java_enabled' type='hidden' value='' />";
//echo "<input id='pdf_enabled' name='pdf_enabled' type='hidden' value='' />";
//echo "</div>";

//if (isset($_GET['r'])) echo "<input name='r' type='hidden' value='".$_GET['r']."' />\n";
echo '<table id="signin">';
echo '<tr><td class="l">Username:</td><td>' . Form::text('username', null) . '</td></tr>';
echo '<tr><td class="l">Password:</td><td>' . Form::password('password', null) .'</td></tr>';
echo '<tr><td class="l">Save 24h:</td><td><input name="s24" type="checkbox" value="1"></td></tr>'; 
echo '</table>';

echo '<div><input name="a" type="submit" value="Sign In"></div>';

echo '</form>';
