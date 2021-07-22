<?php
/**
    @brief Imperium Main Menu Element

    Shows the main menu of the application

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

    @todo need to make this a better heirichal data set
    @todo Make this much smarter, link to something with
*/

// echo '<li><a href="' . Radix::link('/task/edit">' . '<i class="far fa-plus-square"></i> Add Task') . ' Task</a></li>';
// echo '<li><a href="' . Radix::link('/alert/edit">' . '<i class="far fa-plus-square"></i> New Alert') . ' New Alert</a></li>';
// echo '<li><a class="fancybox fancybox.ajax" href="' . Radix::link('/timer/edit') . '"><i class="far fa-clock"></i> New Timer</a></li>';
// echo '<li><a href="' . Radix::link('/calendar') . '"> Calendar</a></li>';
// echo '<li><a href="' . Radix::link('/manual/'') . ' . $this->controller . '/' . $this->action . '"><img alt="Annotated Users Manual" src="' . Radix::link('/img/silk/help.png" /> Manual</a></li>';
// echo '<li><a href="' . Radix::link('/contact/export') . '">Export</a></li>';

namespace Edoceo\Imperium;

use Edoceo\Radix;

if (!ACL::may('/block/menu')) {
	return(0);
}

?>

<style>
nav li {
	position: relative;
}

li.dropdown-hook:active > ul,
li.dropdown-hook:focus > ul,
li.dropdown-hook:hover > ul {
	display: block;
}

ul.dropdown-menu {
	/* border: 1px outset #000; */
	display: none;
	left: 0;
	margin: 0;
	padding: 0;
	min-width: 15em;
	position: absolute;
	top: 98%;
	z-index: 20;
}
ul.dropdown-menu a {
	display: block;
	padding: 0.125em 0.5em;
	white-space: nowrap;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
<div class="container-fluid">

<a class="navbar-brand" href="<?= Radix::link('/') ?>" title="Home"><i class="fas fa-home"></i></a>

<button class="navbar-toggler" type="button"
	aria-controls="menu0" aria-expanded="false" aria-label="Toggle navigation"
	data-bs-toggle="collapse"
	data-bs-target="#menu0"
	>
	<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="menu0">

<ul class="navbar-nav me-auto">
<li class="nav-item">
<!--
	<ul>
	<?php
	// Show MRU
	// if (!empty($_SESSION['mru']) && (count($_SESSION['mru']) > 0)) {
	// 	$mru = array_reverse($_SESSION['mru']);
	// 	foreach ($mru as $key => $val) {
	// 		echo '<li><a href="' . Radix::link($val['link']) . '">' . $val['html'] . '</a></li>';
	// 	}
	// 	echo '<li><hr /></li>';
	// }
	?>
	<li><a class="fancybox fancybox.ajax" href="<?= Radix::link('/note/edit?l=r') ?>"><i class="far fa-plus-square"></i> New Note</a></li>
	<li><a class="fancybox fancybox.ajax" href="<?= Radix::link('/file/edit?l=r') ?>"><i class="far fa-plus-square"></i> New File</a></li>
	<li><a href="<?= Radix::link('/timesheet') ?>"><i class="fas fa-tasks"></i> Time Sheet</a></li>
	<li><hr></li>
	<?php
	// Somehow get the list of possible Status and use that to build a list here, colours as well.
	echo '<li><a href="' . Radix::link('/note') . '"><i class="far fa-sticky-note"></i> Notes</a></li>';
	echo '<li><a href="' . Radix::link('/file') . '"><i class="far fa-file-alt"></i> Files</a></li>';
	echo '<li><hr /></li>';
	echo '<li><a href="' . Radix::link('/settings') . '"><i class="far fa-cogs"></i> Settings</a></li>';
	echo '<li><hr /></li>';
	?>
	</ul>
-->
</li>

<!-- Contacts -->
<li class="nav-item dropdown-hook">
	<a class="nav-link" href="<?= Radix::link('/contact') ?>"><i class="fas fa-users"></i> Contacts</a>
	<ul class="dropdown-menu">
	<?php
	if (!empty($_ENV['contact']['id'])) {
		echo '<li><a href="' . Radix::link('/contact/view?c=' . $_ENV['contact']['id']) . '"><i class="far fa-user"></i> ' . $_ENV['contact']['name'] . '</a></li>';
		echo '<li><hr /></li>';
		echo '<li><a href="' . Radix::link('/contact/channel?a=create&amp;c=' . $_ENV['contact']['id']) . '"><i class="far fa-plus-square"></i> Add Channel</a></li>';
		// echo '<li><a href="' . Radix::link('/task/edit/link/' . $this->Contact->link() . '"><i class="far fa-plus-square"></i> Add Task</a></li>';
		// echo '<li><a href="' . Radix::link('/history/edit/link/' . $this->Contact->link() . '"><i class="far fa-plus-square"></i> Add History</a></li>';
		//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Clean');
		//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Search');
		//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Lists');
		//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Help');
		echo '<li><hr /></li>';
	}
	?>
	<li><a href="<?= Radix::link('/contact/view') ?>"><i class="far fa-plus-square"></i> Create</a></li>
	<li><a href="<?= Radix::link('/contact?kind=contacts')  ?>"><i class="fas fa-users"></i> Contacts</a></li>
	<li><a href="<?= Radix::link('/contact?kind=companies') ?>"><i class="fas fa-building"></i> Companies</a></li>
	<li><a href="<?= Radix::link('/contact?kind=vendors') ?>"><i class="fas fa-truck"></i> Vendors</a></li>
	<li><a href="<?= Radix::link('/contact/labels') ?>"><i class="fas fa-mail-bulk"></i> Mail Labels</a></li>
	<li><hr></li>
	<li><a href="<?= Radix::link('/contact/import') ?>"><i class="fas fa-upload"></i> Import</a></li>
	<li><a href="<?= Radix::link('/contact/sync') ?>"><i class="fas fa-sync"></i> Sync</a></li>
	</ul>
</li>

<!-- Work Orders -->
<li class="nav-item dropdown-hook">
	<a class="nav-link" href="<?= Radix::link('/workorder') ?>"><i class="fas fa-clipboard-list"></i> Work Orders</a>
	<ul class="dropdown-menu">
	<?php
	if (!empty($_ENV['workorder']['id'])) {
		echo '<li><a href="' . Radix::link('/workorder/view?w=' . $_ENV['workorder']['id']) . '">&laquo; Work Order #' . $this->WorkOrder->id . '</a></li>';

        // Add Item
        echo '<li><a class="ajax-edit" data-name="woi-edit" href="' . Radix::link('/workorder/item?w=' . $this->WorkOrder->id) . '"><i class="far fa-plus-square"></i> Add Item</a></li>';

        //$menu1[] = array('/service_orders/post_payment','<i class="fas fa-money-check-alt"></i> Post Payment');
        //$menu1[] = array('/workorder/invoice', '<i class="fas fa-file-invoice-dollar"></i> Build Invoice');

        echo '<li><hr /></li>';
        // $menu1[] = array("javascript:\$('#EmailSend').submit();", '<i class="fas fa-share-square"></i> Send');
        echo '<li><a href="' . Radix::link('/workorder/pdf?w=' . $_ENV['workorder']['id']) . '"><i class="fas fa-print"></i> Printable</a></li>';

        //$menu1[] = array('/service_orders/history', '<i class="fas fa-history"></i> History');
        echo '<li><hr /></li>';
    }

    if ($_ENV['contact']['id']) {
        echo '<li><a href="' . Radix::link('/workorder/new?c=' . $_ENV['contact']['id']) . '"><i class="far fa-plus-square"></i> Create</a></li>';
    } else {
        echo '<li><a href="' . Radix::link('/workorder/new') . '"><i class="far fa-plus-square"></i> Create</a></li>';
    }

    echo '<li><a href="' . Radix::link('/workorder/report') . '"><i class="fas fa-chart-bar"></i> Reports</a></li>';
    // echo '<li><a href="' . Radix::link('/workorder/index/project' . '<i class="far fa-list-alt"></i> Projects</a></li>';
    //$menu[] = array('/service_orders/index/project', '<i class="far fa-list-alt"></i> Projects');
	?>
	</ul>
</li>

<!-- Invoices -->
<li class="nav-item dropdown-hook">
	<a class="nav-link" href="<?= Radix::link('/invoice') ?>"><i class="fas fa-file-invoice-dollar"></i> Invoices</a>
	<ul class="dropdown-menu">
	<?php
    if (!empty($_ENV['invoice']['id'])) {
        echo '<li><a href="' . Radix::link('/invoice/view?i=' . $_ENV['invoice']['id']) . '"><i class="fas fa-file-invoice-dollar"></i> &laquo;Invoice #' . $_ENV['invoice']['id'] . '</a></li>';
        echo '<li><a href="' . Radix::link('/invoice/pdf?i=' . $_ENV['invoice']['id']) . '"><i class="fas fa-print"></i> Printable</a></li>';
        echo '<li><hr /></li>';
    }

    // Create Invoice
    if ($_ENV['contact']['id']) {
        echo '<li><a href="' . Radix::link('/invoice/new?c=' . $_ENV['contact']['id']) . '"><i class="far fa-plus-square"></i> Create</a></li>';
    } else {
        echo '<li><a href="' . Radix::link('/invoice/new') . '"><i class="far fa-plus-square"></i> Create</a></li>';
    }
    // echo '<li><a href="' . Radix::link('/invoice/filter/active') . '">' , '<i class="fas fa-money-check-alt"></i> Active').' Active</a></li>';
    // echo '<li><a href="' . Radix::link('/invoice/filter/past_due') . '">' , '<i class="fas fa-money-check-alt"></i> Past Due').' Past Due</a></li>';
    // echo '<li><a href="' . Radix::link('/invoice/filter/paid') . '">' , '<i class="fas fa-money-check-alt"></i> Paid').' Paid</a></li>';
    echo '<li><a href="' . Radix::link('/invoice/report') . '"><i class="fas fa-chart-bar"></i> Reports</a></li>';
	?>
	</ul>
</li>

<!-- Accounts -->
<li class="nav-item dropdown-hook">
	<a class="nav-link" href="<?= Radix::link('/account') ?>"><i class="fas fa-chart-bar"></i> Accounts</a>
	<ul class="dropdown-menu">
	<?php
    echo '<li><a href="' . Radix::link('/account/transaction?id=-1') . '"><i class="far fa-plus-square"></i> Transaction</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . Radix::link('/account/edit') . '"><i class="far fa-plus-square"></i> New Account</a></li>';
    // echo '<li><a href="' . Radix::link('/account/cheque') . '"><i class="fas fa-chart-bar"></i> Cheque</a></li>';
    echo '<li><a href="' . Radix::link('/account.wizard') . '"><i class="fas fa-magic"></i> Wizard</a></li>';
    echo '<li><a href="' . Radix::link('/account/reconcile') . '"><i class="fas fa-sync-alt"></i> Reconcile</a></li>';
    echo '<li><a href="' . Radix::link('/account/close') . '"><i class="fas fa-flag-checkered"></i> Close Period</a></li>';

    echo '<li><hr /></li>';
    echo '<li><a href="' . Radix::link('/account/report/income') . '"><i class="fas fa-funnel-dollar"></i>  Income (P&amp;L)</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/balance-sheet') . '"><i class="fas fa-balance-scale"></i> Balance Sheet</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/cash-flow') . '"><i class="fas fa-search-dollar"></i> Cash Flow</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/retained-earnings') . '"><i class="fas fa-piggy-bank"></i> Retained Earnings</a></li>';

    echo '<li><hr /></li>';
    echo '<li><a href="' . Radix::link('/account/report/trial-balance') . '">Trial Balance</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/equity') . '">Owner Equity</a></li>';

    // echo '<li><a href="' . Radix::link('/account/tax-form') . '">Tax Schedules</a></li>';
?>
	</ul>
</li>

<?php
// Plugins
// @todo Google Link-Up - Plugin Menu/Config?
//$list = array();
//if (!empty($_ENV['google']['apps_domain'])) {
//    // @note Think These should Link Out to Google Services
//    $x = $_ENV['google']['apps_domain'];
//    $list[] = '<li><a href="https://mail.google.com/a/' . $x . '" target="_blank">Google Mail</a></li>';
//    $list[] = '<li><a href="https://www.google.com/calendar/hosted/' . $x . '" target="_blank">Google Calendar</a></li>';
//    $list[] = '<li><a href="https://docs.google.com/a/' . $x . '" target="_blank">Google Documents</a></li>';
//    // $list[] = sprintf('<a href="https://mail.google.com/tasks/a/' . $x . '/m" target="_blank"> Tasks</a>',$x);
//    $list[] = '<li><a href="https://mail.google.com/tasks/a/' . $x . '/ig" target="_blank">Google Tasks</a></li>';
//}
//if (!empty($_ENV['plugin'])) {
//    foreach ($_ENV['plugin'] as $k=>$v) {
//        $list[] = '<li><a href="' . Radix::link('/plugin?p=' . $k) .'">' . $k . '</a></li>';
//        // echo '<li><a href="/plugin?p=' . $_ENV[$v]['link'] . '">' . $k . '</a></li>';
//    }
//}
//if (count($list)) {
//    echo '<li><a href="#"><i class="fas fa-cogs"></i> Plugins</a>';
//    echo '<ul>' . implode('',$list) . '</ul>';
//    echo '</li>';
//}

?>
</ul>

<div class="mx-2">
<form action="<?= Radix::link('/search') ?>" autocomplete="off" method="get">
	<div class="input-group input-group-sm">
		<input autocomplete="off" autofocus class="form-control" id="q" name="q" placeholder="Search" type="text" value="" aria-label="Search">
		<button class="btn btn-outline-success" type="submit">Search</button>
	</div>
</form>
</div>

<ul class="navbar-nav">
	<li><a class="nav-link" href="<?= Radix::link('/auth/sign-out') ?>"><i class="fas fa-sign-out-alt"></i></a></li>
</ul>

</div> <!-- /.collapse .navbar-collapse -->

</div>
</nav>
