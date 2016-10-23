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

// echo '<li><a href="' . Radix::link('/task/edit">' . img('/silk/1.3/note_add.png','Add Task') . ' Task</a></li>';
// echo '<li><a href="' . Radix::link('/alert/edit">' . img('/silk/1.3/bell_add.png','New Alert') . ' New Alert</a></li>';
// echo '<li><a class="fancybox fancybox.ajax" href="' . Radix::link('/timer/edit') . '">' . img('/tango/24x24/actions/appointment-new.png','New Timer') . ' New Timer</a></li>';
// echo '<li><a href="' . Radix::link('/calendar') . '">' . img('/tango/24x24/apps/system-file-manager.png','Calendar') . ' Calendar</a></li>';
// echo '<li><a href="' . Radix::link('/manual/'') . ' . $this->controller . '/' . $this->action . '"><img alt="Annotated Users Manual" src="' . Radix::link('/img/silk/help.png" /> Manual</a></li>';
// echo '<li><a href="' . Radix::link('/contact/export') . '">' . img('/silk/1.3/lorry.png','Export').' Export</a></li>';

namespace Edoceo\Imperium;

use Edoceo\Radix;

if (!ACL::may('/block/menu')) {
	return(0);
}

?>

<nav class="one">
<ul class="menu">
<li><a href="<?= Radix::link('/') ?>" title="Dashboard"><i class="fa fa-home"></i></a>
	<ul>
	<?php
	// Show MRU
	if (!empty($_SESSION['mru']) && (count($_SESSION['mru']) > 0)) {
		$mru = array_reverse($_SESSION['mru']);
		foreach ($mru as $key => $val) {
			echo '<li><a href="' . Radix::link($val['link']) . '">' . $val['html'] . '</a></li>';
		}
		echo '<li><hr /></li>';
	}
	?>
	<li><a class="fancybox fancybox.ajax" href="<?= Radix::link('/note/edit?l=r') ?>"><i class="fa fa-file-text-o"></i> New Note</a></li>
	<li><a class="fancybox fancybox.ajax" href="<?= Radix::link('/file/edit?l=r') ?>"><i class="fa fa-file"></i> New File</a></li>
	<li><a href="<?= Radix::link('/timesheet') ?>"><i class="fa fa-tasks"></i> Time Sheet</a></li>
	<li><hr></li>
	<?php
	// Somehow get the list of possible Status and use that to build a list here, colours as well.
	echo '<li><a href="' . Radix::link('/note') . '"><i class="fa fa-file-text-o"></i> Notes</a></li>';
	echo '<li><a href="' . Radix::link('/file') . '"><i class="fa fa-file"></i> Files</a></li>';
	echo '<li><hr /></li>';
	echo '<li><a href="' . Radix::link('/settings') . '"><i class="fa fa-cogs"></i> Settings</a></li>';
	echo '<li><hr /></li>';
	echo '<li><a href="' . Radix::link('/auth/sign-out') . '"><i class="fa fa-sign-out"></i> Sign Out</a></li>';
	?>
	</ul>
</li>

<!-- Contacts -->
<li><a href="<?= Radix::link('/contact') ?>"><i class="fa fa-users"></i> Contacts</a>
<ul>
<?php
if (!empty($_ENV['contact']['id'])) {
	echo '<li><a href="' . Radix::link('/contact/view?c=' . $_ENV['contact']['id']) . '"><i class="fa fa-user"></i> ' . $_ENV['contact']['name'] . '</a></li>';
	echo '<li><hr /></li>';
	echo '<li><a href="' . Radix::link('/contact/channel?a=create&amp;c=' . $_ENV['contact']['id']) . '"><i class="fa fa-plus"></i> Add Channel</a></li>';
	// echo '<li><a href="' . Radix::link('/task/edit/link/' . $this->Contact->link() . '">' . img('/silk/1.3/note_add.png','Add Task') . ' Add Task</a></li>';
	// echo '<li><a href="' . Radix::link('/history/edit/link/' . $this->Contact->link() . '">' . img('/silk/1.3/note_add.png','Add Note') . ' Add History</a></li>';
	//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Clean');
	//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Search');
	//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Lists');
	//$menu[] = array('/timer/edit',$html->image('clock_add.png').' Help');
	echo '<li><hr /></li>';
}
?>
<li><a href="<?= Radix::link('/contact/view') ?>"><i class="fa fa-plus"></i> Create</a></li>
<li><a href="<?= Radix::link('/contact?kind=contacts')  ?>"><i class="fa fa-users"></i> Contacts</a></li>
<li><a href="<?= Radix::link('/contact?kind=companies') ?>"><i class="fa fa-building"></i> Companies</a></li>
<li><a href="<?= Radix::link('/contact?kind=vendors') ?>"><i class="fa fa-truck"></i> Vendors</a></li>
<li><a href="<?= Radix::link('/contact/labels') ?>"><i class="fa fa-file-text-o"></i> Mail Labels</a></li>
<li><hr></li>
<li><a href="<?= Radix::link('/contact/import') ?>"><i class="fa fa-cloud-upload"></i> Import</a></li>
<li><a href="<?= Radix::link('/contact/sync') ?>"><i class="fa fa-refresh"></i> Sync</a></li>
</ul>
</li>
<?php
// Workorders
echo '<li><a href="' . Radix::link('/workorder') . '"><i class="fa fa-clock-o"></i> Work Orders</a>';
    echo '<ul>';
    if (!empty($_ENV['workorder']['id'])) {
        echo '<li><a href="' . Radix::link('/workorder/view?w=' . $_ENV['workorder']['id']) . '">&laquo; Work Order #' . $this->WorkOrder->id . '</a></li>';

        // Add Item
        echo '<li><a class="ajax-edit" data-name="woi-edit" href="' . Radix::link('/workorder/item?w=' . $this->WorkOrder->id) . '"><i class="fa fa-plus-square"></i> Add Item</a></li>';

        //$menu1[] = array('/service_orders/post_payment',img('/silk/1.3/money_add.png').' Post Payment');
        //$menu1[] = array('/workorder/invoice',img('/silk/1.3/layout_link.png','Build Invoice').' Build Invoice');

        echo '<li><hr /></li>';
        // $menu1[] = array("javascript:\$('#EmailSend').submit();",img('/silk/1.3/email_go.png','Send Email').' Send');
        echo '<li><a href="' . Radix::link('/workorder/pdf?w=' . $_ENV['workorder']['id']) . '"><i class="fa fa-file-pdf-o"></i> Printable</a></li>';

        //$menu1[] = array('/service_orders/history',img('/silk/1.3/folder_page.png').' History');
        echo '<li><hr /></li>';
    }

    if ($_ENV['contact']['id']) {
        echo '<li><a href="' . Radix::link('/workorder/new?c=' . $_ENV['contact']['id']) . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    } else {
        echo '<li><a href="' . Radix::link('/workorder/new') . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    }

    echo '<li><a href="' . Radix::link('/workorder/report') . '"><i class="fa fa-bar-chart"></i> Reports</a></li>';
    // echo '<li><a href="' . Radix::link('/workorder/index/project' . img('/silk/1.3/table_lightning.png','List Projects') . ' Projects</a></li>';
    //$menu[] = array('/service_orders/index/project',img('/silk/1.3/table_lightning.png','List Projects').' Projects');
    echo '</ul>';
echo '</li>';

// Invoices
echo '<li><a href="' . Radix::link('/invoice') . '"><i class="fa fa-usd"></i> Invoices</a>';
    echo '<ul>';

    if (!empty($_ENV['invoice']['id'])) {
        echo '<li><a href="' . Radix::link('/invoice/view?i=' . $_ENV['invoice']['id']) . '"><i class="fa fa-usd"></i> &laquo;Invoice #' . $_ENV['invoice']['id'] . '</a></li>';
        echo '<li><a href="' . Radix::link('/invoice/pdf?i=' . $_ENV['invoice']['id']) . '"><i class="fa fa-file-pdf-o"></i> Printable</a></li>';
        echo '<li><hr /></li>';
    }

    // Create Invoice
    if ($_ENV['contact']['id']) {
        echo '<li><a href="' . Radix::link('/invoice/new?c=' . $_ENV['contact']['id']) . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    } else {
        echo '<li><a href="' . Radix::link('/invoice/new') . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    }
    // echo '<li><a href="' . Radix::link('/invoice/filter/active') . '">' , img('/silk/1.3/money_add.png','Active').' Active</a></li>';
    // echo '<li><a href="' . Radix::link('/invoice/filter/past_due') . '">' , img('/silk/1.3/money_add.png','Past Due').' Past Due</a></li>';
    // echo '<li><a href="' . Radix::link('/invoice/filter/paid') . '">' , img('/silk/1.3/money_add.png','Paid').' Paid</a></li>';
    echo '<li><a href="' . Radix::link('/invoice/report') . '"><i class="fa fa-bar-chart"></i> Reports</a></li>';
    echo '</ul>';
echo '</li>';

// Accounting
echo '<li><a href="' . Radix::link('/account') . '"><i class="fa fa-bar-chart"></i> Accounts</a>';
    echo '<ul>';
    echo '<li><a href="' . Radix::link('/account/transaction?id=-1') . '"><i class="fa fa-plus-square"></i> Transaction</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . Radix::link('/account/edit') . '"><i class="fa fa-plus-square"></i> New Account</a></li>';
    // echo '<li><a href="' . Radix::link('/account/cheque') . '"><i class="fa fa-bar-chart"></i> Cheque</a></li>';
//    echo '<li><a href="' . Radix::link('/account.wizard') . '"><i class="fa fa-magic"></i> Wizard</a></li>';
    echo '<li><a href="' . Radix::link('/account/reconcile') . '"><i class="fa fa-refresh"></i> Reconcile</a></li>';
    echo '<li><a href="' . Radix::link('/account/close') . '"><i class="fa fa-flag-checkered"></i> Close Period</a></li>';

    echo '<li><hr /></li>';
    echo '<li><a href="' . Radix::link('/account/report/income') . '"><i class="fa fa-list-ol"></i>  Income (P&amp;L)</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/balance-sheet') . '"><i class="fa fa-list-ol"></i> Balance Sheet</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/cash-flow') . '"><i class="fa fa-list-ol"></i> Cash Flow</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/retained-earnings') . '">Retained Earnings</a></li>';

    echo '<li><hr /></li>';
    echo '<li><a href="' . Radix::link('/account/report/trial-balance') . '"><i class="fa fa-list-ol"></i> Trial Balance</a></li>';
    echo '<li><a href="' . Radix::link('/account/report/equity') . '"><i class="fa fa-list-ol"></i> Owner Equity</a></li>';

    // echo '<li><a href="' . Radix::link('/account/tax-form') . '">Tax Schedules</a></li>';
    echo '</ul>';
echo '</li>';

// Plugins
// @todo Google Link-Up - Plugin Menu/Config?
$list = array();
if (!empty($_ENV['google']['apps_domain'])) {
    // @note Think These should Link Out to Google Services
    $x = $_ENV['google']['apps_domain'];
    $list[] = '<li><a href="https://mail.google.com/a/' . $x . '" target="_blank">Google Mail</a></li>';
    $list[] = '<li><a href="https://www.google.com/calendar/hosted/' . $x . '" target="_blank">Google Calendar</a></li>';
    $list[] = '<li><a href="https://docs.google.com/a/' . $x . '" target="_blank">Google Documents</a></li>';
    // $list[] = sprintf('<a href="https://mail.google.com/tasks/a/' . $x . '/m" target="_blank"> Tasks</a>',$x);
    $list[] = '<li><a href="https://mail.google.com/tasks/a/' . $x . '/ig" target="_blank">Google Tasks</a></li>';
}
if (!empty($_ENV['plugin'])) {
    foreach ($_ENV['plugin'] as $k=>$v) {
        $list[] = '<li><a href="' . Radix::link('/plugin?p=' . $k) .'">' . $k . '</a></li>';
        // echo '<li><a href="/plugin?p=' . $_ENV[$v]['link'] . '">' . $k . '</a></li>';
    }
}
if (count($list)) {
    echo '<li><a href="#"><span>' . img('/tango/24x24/categories/preferences-system.png') . ' Plugins</span></a>';
    echo '<ul>' . implode('',$list) . '</ul>';
    echo '</li>';
}

// Search Form
echo '<li>';
echo '<form action="' . Radix::link('/search') . '" method="get">';
echo '<input id="q" name="q" placeholder="Search" value="" />';
echo '</form></li>';
echo '</ul>';
?>
</nav>

<nav class="two">
	<i class="fa fa-gears"></i>
</nav>