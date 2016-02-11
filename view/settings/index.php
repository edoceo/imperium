<?php
/**
	Imperium Config Index View
	Start of the Configuration Pages

	$cu = $this->_a->getIdentity();
	$this->view->PreferenceList = $cu->preference();
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

$_ENV['title'] = 'Settings';

acl::permit('/auth/google');


if (!is_writable(APP_ROOT . '/var/')) {
	echo '<li class="fail">Not Writable: ' . APP_ROOT . '/var/</li>';
	echo '<li>chown -R ' . posix_getuid() . ':' . APP_ROOT . '/var</li>';
}


echo '<ul>';
echo '<li><a href="' . Radix::link('/settings/business') . '">Business Profile</li>';
echo '<li><a href="' . Radix::link('/settings/profile') . '">User Profile</li>';
echo '<li><a href="' . Radix::link('/settings/password') . '">Change Password</li>';
echo '<li><a href="' . Radix::link('/settings/session') . '">View Session</li>';
echo '</ul>';

echo '<hr>';
echo '<ul>';
echo '<li><a href="' . Radix::link('/settings/dashboard') . '">Dashboard</a> - Configure layout and selection criteria</li>';
echo '<li><a href="' . Radix::link('/settings/workorder') . '">Work Orders</a> - Orders from Contacts to perform Work</li>';
echo '<li><a href="' . Radix::link('/settings/invoice') . '">Invoices</a> - Invoicing Contacts</li>';
echo '<li><a href="' . Radix::link('/settings/accounting') . '">Accounting</a> - Configure the accounting system.</li>';
//echo "<li>" . $html->link('Purchse Orders','/settings/purchase_orders') . " - Purchase Equipment from Vendors</li>";
//echo "<hr />";
//echo "<ul>";
//echo "<li>" . Radix::link('/settings/tags','Tags') . " - Object Tags and Categories</li>\n";
//echo "<li>" . Radix::link('/settings/labels','Colour Labels') . " - Colour labels for each Object</li>\n";
//echo "<li><a href='./plugins.php'>Plugins</a> - Frame Based Plugins</li>\n";
//echo "<li><a href='./remember.php'>Remember Me</a> - Configure Imperium to remember you on this computer.</li>";

//echo "</ul>\n";
//echo "<hr />";
//echo "<ul>";
echo '<li><a href="' . Radix::link('/settings/users') . '">Users</a></li>';
// echo '<li><a href="' . Radix::link('/selenium') . '">Run Selenum Tests</a></li>';
echo '</ul>';

?>

<h3>Service Integrations</h3>

<dl>
<dt><a href="<?= Radix::link('/auth/google') ?>">Google Apps</a></dt>
<dd>Connect Calendar, Contacts and other Services: Authorize Google.</dd>
</dl>


<h3>Payment Notifications</h3>
<p>Imperium can connect with a bunch of different payment systems.</p>

<dl>
<dt>Gumroad</dt>
<dd><code>https://<?=Radix::$host?><?=Radix::$base?>/hook/gumroad?auth=SomeKey</code></dd>
<dd>See <a href="https://gumroad.com/settings/developer" target="_blank">gumroad.com/settings/developer</a> for more information.</dd>

<dt>Paypal</dt>
<dd><code>https://<?=Radix::$host?><?=Radix::$base?>/hook/paypal</code></dd>

<dt>Square</dt>
<dd>
	<ol>
	<li>Create a New Application in Square <a href="https://connect.squareup.com/apps/new" target="_blank">connect.squareup.com/apps/new</a>.</li>
	<li>Enter <em>Imperium</em> as the App Name</li>
	<li>Enter <em>https://<?php echo $_SERVER['SERVER_NAME'] ?>/imperium/auth/square</em> as the Redirect URL</li>
	<li>Enter the Application ID, Application Secret and Personal Access Token into your Imperium configuration</li>
	</ol>
</dd>

<dt>Stripe</dt>
<dd>
	<ol>
	<li>Create a New Webhook in Stripe: <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">dashboard.stripe.com/account/webhooks</a></li>
	<li>Add URL: <code>https://<?=radix::$host?><?=radix::$base?>/hook/stripe</code></li>
	<li>Add once for <em>Live</em> and once for <em>Test</em> mode</li>
	<li>Click <em>Test Webhooks</em></li>
	</ol>
</dl>

<?php

// echo '<h2>Imperium Session</h2>';
// Radix::dump($_SESSION);
// 
// echo '<h2>Imperium Config</h2>';
// Radix::dump($_ENV);
// 
// echo '<h2>Imperium Server</h2>';
// Radix::dump($_SERVER);
// 