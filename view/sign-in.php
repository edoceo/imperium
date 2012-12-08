<?php
/**
    Imperium Login View, Draws the Login Form

    @copyright  2008 Edoceo, Inc
    @package    imperium

*/

echo '<form method="post">';

echo '<table>';
echo '<tr><td class="l">Username:</td><td><input name="username" type="text" value=""></td></tr>';
echo '<tr><td class="l">Password:</td><td><input name="password" type="text" value=""></td></tr>';
echo '<tr><td class="l">Save 24h:</td><td><input name="s24" type="checkbox" value="1"></td></tr>';
echo '</table>';

echo '<div><input name="c" type="submit" value="Login"></div>';

echo '</form>';

//if (isset($_GET['r'])) echo "<input name='r' type='hidden' value='".$_GET['r']."' />\n";
//echo "<div>";
//echo "<input id='flash_enabled' name='flash_enabled' type='hidden' value='' />";
//echo "<input id='js_enabled' name='js_enabled' type='hidden' value='' />";
//echo "<input id='java_enabled' name='java_enabled' type='hidden' value='' />";
//echo "<input id='pdf_enabled' name='pdf_enabled' type='hidden' value='' />";
//echo "</div>";
