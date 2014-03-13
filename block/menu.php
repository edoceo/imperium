<?php
/**
    @file
    @brief Imperium Main Menu Element

    Shows the main menu of the application

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

    @todo need to make this a better heirichal data set
    @todo Make this much smarter, link to something with
*/

if (!acl::may('/block/menu')) {
	return(0);
}

// $here = $this->controller . '/' . $this->action;
// $c = $this->controller == 'index' ? '/' : ('/' . $this->controller);

echo '<ul class="menu">';
echo '<li><a href="' . radix::link('/') . '" title="Dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>';
    echo '<ul>';
    echo '<li><a class="fancybox fancybox.ajax" href="' . radix::link('/note/edit?l=r') . '">' . img('/tango/24x24/apps/accessories-text-editor.png','New Note') . '&nbsp;New Note</a></li>';
    echo '<li><a class="fancybox fancybox.ajax" href="' . radix::link('/file/edit?l=r') . '">' . img('/tango/24x24/actions/document-new.png','New File') . '&nbsp;New File</a></li>';

    // echo '<li><a href="' . radix::link('/task/edit">' . img('/silk/1.3/note_add.png','Add Task') . '&nbsp;Task</a></li>';
    // echo '<li><a href="' . radix::link('/alert/edit">' . img('/silk/1.3/bell_add.png','New Alert') . '&nbsp;New Alert</a></li>';
    echo '<li><a href="' . radix::link('/timesheet') . '">' . img('/tango/24x24/actions/appointment-new.png','New Timer') . ' Time Sheet</a></li>';
    echo '<li><a class="fancybox fancybox.ajax" href="' . radix::link('/timer/edit') . '">' . img('/tango/24x24/actions/appointment-new.png','New Timer') . '&nbsp;New Timer</a></li>';
    echo '<li><hr /></li>';

    // Somehow get the list of possible Status and use that to build a list here, colours as well.
    echo '<li><a href="' . radix::link('/note') . '">' . img('/tango/24x24/apps/accessories-text-editor.png','Note List') .' Notes</a></li>';
    echo '<li><a href="' . radix::link('/file') . '">' . img('/tango/24x24/apps/system-file-manager.png','Files and Attachments') . ' Files</a></li>';
    echo '<li><a href="' . radix::link('/calendar') . '">' . img('/tango/24x24/apps/system-file-manager.png','Calendar') . ' Calendar</a></li>';
    echo '<li><hr /></li>';
    // echo '<li><a href="' . radix::link('/manual/'') . ' . $this->controller . '/' . $this->action . '"><img alt="Annotated Users Manual" src="' . radix::link('/img/silk/help.png" />&nbsp;Manual</a></li>';
    echo '<li><a href="' . radix::link('/settings') . '">' . img('/tango/24x24/categories/preferences-desktop.png','Settings') . ' Settings</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . radix::link('/auth/sign-out') . '"><i class="fa fa-sign-out"></i> Sign Out</a></li>';
    echo '</ul>';
echo '</li>';

// Contacts
echo '<li><a href="' . radix::link('/contact') . '"><i class="fa fa-users"></i> Contacts</a>';
    echo '<ul>';
    if (!empty($_ENV['contact']['id'])) {
        echo '<li><a href="' . radix::link('/contact/view?c=' . $_ENV['contact']['id']) . '">' . img('/silk/1.3/user.png','Back to Contact') . '&nbsp;&laquo;' . $_ENV['contact']['name'] . '</a></li>';
        echo '<li><hr /></li>';
        echo '<li><a href="' . radix::link('/contact.channel/edit?c=' . $_ENV['contact']['id']) . '">' . img('/silk/1.3/add.png','Add Contact Channel') . '&nbsp;Add Channel</a></li>';
        // echo '<li><a href="' . radix::link('/task/edit/link/' . $this->Contact->link() . '">' . img('/silk/1.3/note_add.png','Add Task') . '&nbsp;Add Task</a></li>';
        // echo '<li><a href="' . radix::link('/history/edit/link/' . $this->Contact->link() . '">' . img('/silk/1.3/note_add.png','Add Note') . '&nbsp;Add History</a></li>';
        //$menu[] = array('/timer/edit',$html->image('clock_add.png').'&nbsp;Clean');
        //$menu[] = array('/timer/edit',$html->image('clock_add.png').'&nbsp;Search');
        //$menu[] = array('/timer/edit',$html->image('clock_add.png').'&nbsp;Lists');
        //$menu[] = array('/timer/edit',$html->image('clock_add.png').'&nbsp;Help');
        echo '<li><hr /></li>';
    }
    echo '<li><a href="' . radix::link('/contact/edit') . '">' . img('/tango/24x24/actions/contact-new.png','New Contact') . ' New Contact</a></li>';
    echo '<li><a href="' . radix::link('/contact?kind=contacts') . '">' . img('/silk/1.3/user_green.png','View Contacts').'&nbsp;Contacts</a></li>';
    echo '<li><a href="' . radix::link('/contact?kind=companies') . '">' . img('/silk/1.3/building.png','View Companies').'&nbsp;Companies</a></li>';
    echo '<li><a href="' . radix::link('/contact?kind=vendors') . '">' . img('/silk/1.3/lorry.png','View Vendors').'&nbsp;Vendors</a></li>';
    echo '<li><a href="' . radix::link('/contact/labels') . '">' . img('/silk/1.3/lorry.png','Mailing Labels').'&nbsp;Mail Labels</a></li>';
    //echo '<li><a href="' . radix::link('/contact/export') . '">' . img('/silk/1.3/lorry.png','Export').'&nbsp;Export</a></li>';
    echo '<li><a href="' . radix::link('/contact/sync') . '">' . img('/silk/1.3/lorry.png','Import').'&nbsp;Sync</a></li>';
    echo '</ul>';
echo '</li>';

// Workorders
echo '<li><a href="' . radix::link('/workorder') . '"><i class="fa fa-clock-o"></i> Work Orders</a>';
    echo '<ul>';
    if (!empty($_ENV['workorder']['id'])) {
        echo '<li><a href="' . radix::link('/workorder/view?w=' . $_ENV['workorder']['id']) . '">&laquo; Work Order #' . $this->WorkOrder->id . '</a></li>';

        // Add Item
        echo '<li><a class="ajax-edit" data-name="woi-edit" href="' . radix::link('/workorder/item?w=' . $this->WorkOrder->id) . '"><i class="fa fa-plus-square"></i> Add Item</a></li>';

        //$menu1[] = array('/service_orders/post_payment',img('/silk/1.3/money_add.png').'&nbsp;Post Payment');
        //$menu1[] = array('/workorder/invoice',img('/silk/1.3/layout_link.png','Build Invoice').'&nbsp;Build Invoice');

        echo '<li><hr /></li>';
        // $menu1[] = array("javascript:\$('#EmailSend').submit();",img('/silk/1.3/email_go.png','Send Email').'&nbsp;Send');
        echo '<li><a href="' . radix::link('/workorder/pdf?w=' . $_ENV['workorder']['id']) . '">' , img('/silk/1.3/page_white_acrobat.png','Download as PDF') , '&nbsp;Printable</a></li>';

        //$menu1[] = array('/service_orders/history',img('/silk/1.3/folder_page.png').'&nbsp;History');
        echo '<li><hr /></li>';
    }

    if ($_ENV['contact']['id']) {
        echo '<li><a href="' . radix::link('/workorder/new?c=' . $_ENV['contact']['id']) . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    } else {
        echo '<li><a href="' . radix::link('/workorder/new') . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    }

    echo '<li><a href="' . radix::link('/workorder/report') . '">' . img('/silk/1.3/chart_bar_edit.png','Reports') . ' Reports</a></li>';
    // echo '<li><a href="' . radix::link('/workorder/index/project' . img('/silk/1.3/table_lightning.png','List Projects') . '&nbsp;Projects</a></li>';
    //$menu[] = array('/service_orders/index/project',img('/silk/1.3/table_lightning.png','List Projects').'&nbsp;Projects');
    echo '</ul>';
echo '</li>';

// Invoices
echo '<li><a href="' . radix::link('/invoice') . '"><i class="fa fa-usd"></i> Invoices</a>';
    echo '<ul>';

    if (!empty($_ENV['invoice']['id'])) {
        echo '<li><a href="' . radix::link('/invoice/view?i=' . $_ENV['invoice']['id']) . '">' . img('/silk/1.3/layout.png','Invoice') . '&nbsp;&laquo;Invoice #' . $this->Invoice->id . '</a></li>';
        echo '<li><a href="' . radix::link('/invoice/pdf?i=' . $_ENV['invoice']['id']) . '">' . img('/silk/1.3/page_white_acrobat.png','Get PDF') . '&nbsp;Printable</a></li>';
        echo '<li><hr /></li>';
    }

    // Create Invoice
    if ($_ENV['contact']['id']) {
        echo '<li><a href="' . radix::link('/invoice/edit?c=' . $_ENV['contact']['id']) . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    } else {
        echo '<li><a href="' . radix::link('/invoice/edit') . '"><i class="fa fa-plus-square"></i> Create</a></li>';
    }
    // echo '<li><a href="' . radix::link('/invoice/filter/active') . '">' , img('/silk/1.3/money_add.png','Active').'&nbsp;Active</a></li>';
    // echo '<li><a href="' . radix::link('/invoice/filter/past_due') . '">' , img('/silk/1.3/money_add.png','Past Due').'&nbsp;Past Due</a></li>';
    // echo '<li><a href="' . radix::link('/invoice/filter/paid') . '">' , img('/silk/1.3/money_add.png','Paid').'&nbsp;Paid</a></li>';
    echo '<li><a href="' . radix::link('/invoice/report') . '">' . img('/silk/1.3/chart_bar_edit.png','Reports') . ' Reports</a></li>';
    echo '</ul>';
echo '</li>';

// Accounting
echo '<li><a href="' . radix::link('/account') . '"><i class="fa fa-money"></i> Accounts</a>';
    echo '<ul>';
    echo '<li><a href="' . radix::link('/account/transaction?id=-1') . '"><i class="fa fa-plus-square"></i> Transaction</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . radix::link('/account/edit') . '"><i class="fa fa-plus-square"></i> New Account</a></li>';
    // $menu1[] = array('/account/transaction',img('/silk/1.3/money_add.png','Transaction').'&nbsp;Transaction</a></li>';
    echo '<li><a href="' . radix::link('/account/cheque') . '">',img('/silk/1.3/money_add.png','Cheque').'&nbsp;Cheque</a></li>';
    echo '<li><a href="' . radix::link('/account.wizard') . '">',img('/silk/1.3/money_add.png','Wizard').'&nbsp;Wizard</a></li>';
    echo '<li><a href="' . radix::link('/account/reconcile') . '">',img('/silk/1.3/table_lightning.png','Reconcile to Bank Statement').'&nbsp;Reconcile</a></li>';
    //$menu1[] = array('/account/trial-balance',img('/silk/1.3/table_lightning.png','View Trial Balance').'&nbsp;Trial Balance');
    echo '<li><a href="' . radix::link('/account/close') . '">' , img('/silk/1.3/table_gear.png','Close Period') . '&nbsp;Close Period</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . radix::link('/account/report/trial-balance') . '">',img('/silk/1.3/application_view_detail.png','Trial Balance').'&nbsp;Trial Balance</a></li>';
    echo '<li><a href="' . radix::link('/account/report/income') . '">',img('/silk/1.3/application_view_detail.png','Income Statement').'&nbsp;Income (P&amp;L)</a></li>';
    echo '<li><a href="' . radix::link('/account/report/owner-equity') . '">',img('/silk/1.3/application_view_detail.png','Owners Equity Statement').'&nbsp;Owner Equity</a></li>';
    echo '<li><a href="' . radix::link('/account/report/balance-sheet') . '">',img('/silk/1.3/application_view_detail.png','Balance Sheet').'&nbsp;Balance Sheet</a></li>';
    echo '<li><a href="' . radix::link('/account/report/cash-flow') . '">',img('/silk/1.3/application_view_detail.png','Cash Flow Statement').'&nbsp;Cash Flow</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . radix::link('/account/tax-form') . '">Tax Schedules</a></li>';
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
    // $list[] = sprintf('<a href="https://mail.google.com/tasks/a/' . $x . '/m" target="_blank">&nbsp;Tasks</a>',$x);
    $list[] = '<li><a href="https://mail.google.com/tasks/a/' . $x . '/ig" target="_blank">Google Tasks</a></li>';
}
if (!empty($_ENV['plugin'])) {
    foreach ($_ENV['plugin'] as $k=>$v) {
        $list[] = '<li><a href="' . radix::link('/plugin?p=' . $k) .'">' . $k . '</a></li>';
        // echo '<li><a href="/plugin?p=' . $_ENV[$v]['link'] . '">' . $k . '</a></li>';
    }
}
if (count($list)) {
    echo '<li><a href="#"><span>' . img('/tango/24x24/categories/preferences-system.png') . ' Plugins</span></a>';
    echo '<ul>' . implode('',$list) . '</ul>';
    echo '</li>';
}

// Search Form
echo '<li style="float:right;">';
echo '<form action="' . radix::link('/search') . '" method="get">';
echo '<input id="q" name="q" placeholder="Search" value="" />';
echo '</form></li>';
echo '</ul>';
