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

$appurl = rtrim(radix::link(null),'/'); // Zend_Controller_Front::getInstance()->getBaseUrl();;

echo '<ul class="cssMenu menu">';
echo '<li><a href="' . $appurl . '/" title="Dashboard"><span>' . img('/tango/24x24/places/user-desktop.png') . ' Dashboard' . '</span></a>';
    echo '<ul>';
    echo '<li><a class="fancybox fancybox.ajax" href="' . $appurl . '/note/create?l=r">' . img('/tango/24x24/apps/accessories-text-editor.png','New Note') . '&nbsp;New Note</a></li>';
    echo '<li><a class="fancybox fancybox.ajax" href="' . $appurl . '/file/create?l=r">' . img('/tango/24x24/actions/document-new.png','New File') . '&nbsp;New File</a></li>';

    // echo '<li><a href="' . $appurl . '/task/create">' . img('/silk/1.3/note_add.png','Add Task') . '&nbsp;Task</a></li>';
    // echo '<li><a href="' . $appurl . '/alert/create">' . img('/silk/1.3/bell_add.png','New Alert') . '&nbsp;New Alert</a></li>';
    echo '<li><a href="' . $appurl . '/timesheet">' . img('/tango/24x24/actions/appointment-new.png','New Timer') . ' Time Sheet</a></li>';
    echo '<li><a class="fancybox fancybox.ajax" href="' . $appurl . '/timer/create">' . img('/tango/24x24/actions/appointment-new.png','New Timer') . '&nbsp;New Timer</a></li>';
    echo '<li><hr /></li>';

    // Somehow get the list of possible Status and use that to build a list here, colours as well.
    echo '<li><a href="' . $appurl . '/note">' . img('/tango/24x24/apps/accessories-text-editor.png','Note List') .' Notes</a></li>';
    echo '<li><a href="' . $appurl . '/file">' . img('/tango/24x24/apps/system-file-manager.png','Files and Attachments') . ' Files</a></li>';
    echo '<li><a href="' . $appurl . '/calendar">' . img('/tango/24x24/apps/system-file-manager.png','Calendar') . ' Calendar</a></li>';
    echo '<li><hr /></li>';
    // echo '<li><a href="' . $appurl . '/manual/' . $this->controller . '/' . $this->action . '"><img alt="Annotated Users Manual" src="' . $appurl . '/img/silk/help.png" />&nbsp;Manual</a></li>';
    echo '<li><a href="' . $appurl . '/settings">' . img('/tango/24x24/categories/preferences-desktop.png','Settings') . ' Settings</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . $appurl . '/logout">' . img('/tango/24x24/actions/system-log-out.png','Logout') . ' Logout</a></li>';
    echo '</ul>';
echo '</li>';

// Contacts
echo '<li><a href="' . $appurl . '/contact"><span>' . img('/tango/24x24/apps/system-users.png') . ' Contacts</span></a>';
    echo '<ul>';
    if (!empty($_ENV['Contact']['id'])) {
        echo '<li><a href="' . $appurl . '/contact/view?c=' . $_ENV['Contact']['id'] . '">' . img('/silk/1.3/user.png','Back to Contact') . '&nbsp;&laquo;' . $_ENV['Contact']['name'] . '</a></li>';
        echo '<li><hr /></li>';
        echo '<li><a href="' . $appurl . '/contact.channel/create?c=' . $_ENV['Contact']['id'] . '">' . img('/silk/1.3/add.png','Add Contact Channel') . '&nbsp;Add Channel</a></li>';
        // echo '<li><a href="' . $appurl . '/task/create/link/' . $this->Contact->link() . '">' . img('/silk/1.3/note_add.png','Add Task') . '&nbsp;Add Task</a></li>';
        // echo '<li><a href="' . $appurl . '/history/create/link/' . $this->Contact->link() . '">' . img('/silk/1.3/note_add.png','Add Note') . '&nbsp;Add History</a></li>';
        //$menu[] = array('/timer/create',$html->image('clock_add.png').'&nbsp;Clean');
        //$menu[] = array('/timer/create',$html->image('clock_add.png').'&nbsp;Search');
        //$menu[] = array('/timer/create',$html->image('clock_add.png').'&nbsp;Lists');
        //$menu[] = array('/timer/create',$html->image('clock_add.png').'&nbsp;Help');
        echo '<li><hr /></li>';
    }
    echo '<li><a href="' . $appurl . '/contact/create">' . img('/tango/24x24/actions/contact-new.png','New Contact') . ' New Contact</a></li>';
    echo '<li><a href="' . $appurl . '/contact/index/kind/contacts">' . img('/silk/1.3/user_green.png','View Contacts').'&nbsp;Contacts</a></li>';
    echo '<li><a href="' . $appurl . '/contact/index/kind/companies">' . img('/silk/1.3/building.png','View Companies').'&nbsp;Companies</a></li>';
    echo '<li><a href="' . $appurl . '/contact/index/kind/vendors">' . img('/silk/1.3/lorry.png','View Vendors').'&nbsp;Vendors</a></li>';
    echo '<li><a href="' . $appurl . '/contact/labels">' . img('/silk/1.3/lorry.png','Mailing Labels').'&nbsp;Mail Labels</a></li>';
    //echo '<li><a href="' . $appurl . '/contact/export">' . img('/silk/1.3/lorry.png','Export').'&nbsp;Export</a></li>';
    echo '<li><a href="' . $appurl . '/contact/sync">' . img('/silk/1.3/lorry.png','Import').'&nbsp;Sync</a></li>';
    echo '</ul>';
echo '</li>';

// Workorders
echo '<li><a href="' . $appurl . '/workorder"><span>' . img('/tango/24x24/actions/edit-paste.png') . ' Work Orders</span></a>';
    echo '<ul>';
    if ($_ENV['WorkOrder']['id']) {
        echo '<li><a href="' . $appurl . '/workorder/view?w=' . $_ENV['WorkOrder']['id'] . '">&laquo; Work Order #' . $_ENV['WorkOrder']['id'] . '</a></li>';

        // Add Item
        echo '<li><a class="fancybox fancybox.ajax" href="' . $appurl . '/workorder/item?w=' . $_ENV['WorkOrder']['id'] . '">' . img('/tango/24x24/actions/list-add.png','Add File') . ' Add Item</a></li>';

        //$menu1[] = array('/service_orders/post_payment',img('/silk/1.3/money_add.png').'&nbsp;Post Payment');
        //$menu1[] = array('/workorder/invoice',img('/silk/1.3/layout_link.png','Build Invoice').'&nbsp;Build Invoice');

        echo '<li><hr /></li>';
        // $menu1[] = array("javascript:\$('#EmailSend').submit();",img('/silk/1.3/email_go.png','Send Email').'&nbsp;Send');
        echo '<li><a href="' . $appurl . '/workorder/pdf?w=' . $_ENV['WorkOrder']['id'] . '">' , img('/silk/1.3/page_white_acrobat.png','Download as PDF') , '&nbsp;Printable</a></li>';

        //$menu1[] = array('/service_orders/history',img('/silk/1.3/folder_page.png').'&nbsp;History');
        echo '<li><hr /></li>';
    }

    if ($_ENV['Contact']['id']) {
        echo '<li><a href="' . $appurl . '/workorder/create?c=' . $_ENV['Contact']['id'] . '">' . img('/tango/24x24/actions/list-add.png','New Service Order') . ' Create</a></li>';
    } else {
        echo '<li><a href="' . $appurl . '/workorder/create">' . img('/tango/24x24/actions/list-add.png','New Service Order') . ' Create</a></li>';
    }

    // echo '<li><a href="' . $appurl . '/workorder/index/active' . img('/silk/1.3/table_lightning.png','List Active') . '&nbsp;Active</a></li>';
    // echo '<li><a href="' . $appurl . '/workorder/index/project' . img('/silk/1.3/table_lightning.png','List Projects') . '&nbsp;Projects</a></li>';
    //$menu[] = array('/service_orders/index/project',img('/silk/1.3/table_lightning.png','List Projects').'&nbsp;Projects');
    echo '</ul>';
echo '</li>';

// Invoices
echo '<li><a href="' . $appurl . '/invoice"><span>' . img('/tango/24x24/apps/accessories-calculator.png') . ' Invoices</span></a>';
    echo '<ul>';
    // Create Invoice
    if ($_ENV['Contact']['id']) {
        echo '<li><a href="' . $appurl . '/invoice/create?c=' . $_ENV['Contact']['id'] . '">' . img('/tango/24x24/actions/list-add.png','New Invoice') . ' Create</a></li>';
        // echo '<li><hr /></li>';
    } else {
        echo '<li><a href="' . $appurl . '/invoice/create">' . img('/tango/24x24/actions/list-add.png','New Invoice') . ' Create</a></li>';
        // echo '<li><hr /></li>';
    }
    if (!empty($_ENV['Invoice']['id'])) {
        echo '<li><a href="' . $appurl . '/invoice/view?i=' . $_ENV['Invoice']['id'] . '">' . img('/silk/1.3/layout.png','Invoice') . '&nbsp;&laquo;Invoice #' . $_ENV['Invoice']['id'] . '</a></li>';
        echo '<li><a href="' . $appurl . '/invoice/pdf?i=' . $_ENV['Invoice']['id'] . '">' . img('/silk/1.3/page_white_acrobat.png','Get PDF') . '&nbsp;Printable</a></li>';
        echo '<li><hr /></li>';
    }
    // echo '<li><a href="' . $appurl . '/invoice/filter/active">' , img('/silk/1.3/money_add.png','Active').'&nbsp;Active</a></li>';
    // echo '<li><a href="' . $appurl . '/invoice/filter/past_due">' , img('/silk/1.3/money_add.png','Past Due').'&nbsp;Past Due</a></li>';
    // echo '<li><a href="' . $appurl . '/invoice/filter/paid">' , img('/silk/1.3/money_add.png','Paid').'&nbsp;Paid</a></li>';
    echo '</ul>';
echo '</li>';

// Accounting
echo '<li><a href="' . $appurl . '/account"><span>' . img('/tango/24x24/mimetypes/x-office-spreadsheet-template.png') . ' Accounts</span></a>';
    echo '<ul>';
    echo '<li><a href="' . $appurl . '/account/transaction">' . img('/silk/1.3/money_add.png','Transaction').' Transaction</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . $appurl . '/account/create">' . img('/tango/24x24/actions/list-add.png','New Account') . ' New Account</a></li>';
    // $menu1[] = array('/account/transaction',img('/silk/1.3/money_add.png','Transaction').'&nbsp;Transaction</a></li>';
    echo '<li><a href="' . $appurl . '/account/cheque">',img('/silk/1.3/money_add.png','Cheque').'&nbsp;Cheque</a></li>';
    echo '<li><a href="' . $appurl . '/account.wizard">',img('/silk/1.3/money_add.png','Wizard').'&nbsp;Wizard</a></li>';
    echo '<li><a href="' . $appurl . '/account/reconcile">',img('/silk/1.3/table_lightning.png','Reconcile to Bank Statement').'&nbsp;Reconcile</a></li>';
    //$menu1[] = array('/account/trial-balance',img('/silk/1.3/table_lightning.png','View Trial Balance').'&nbsp;Trial Balance');
    echo '<li><a href="' . $appurl . '/account/close">' , img('/silk/1.3/table_gear.png','Close Period') . '&nbsp;Close Period</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . $appurl . '/account.statement/trial-balance">',img('/silk/1.3/application_view_detail.png','Trial Balance').'&nbsp;Trial Balance</a></li>';
    echo '<li><a href="' . $appurl . '/account.statement/income">',img('/silk/1.3/application_view_detail.png','Income Statement').'&nbsp;Income (P&amp;L)</a></li>';
    echo '<li><a href="' . $appurl . '/account.statement/owner-equity">',img('/silk/1.3/application_view_detail.png','Owners Equity Statement').'&nbsp;Owner Equity</a></li>';
    echo '<li><a href="' . $appurl . '/account.statement/balance-sheet">',img('/silk/1.3/application_view_detail.png','Balance Sheet').'&nbsp;Balance Sheet</a></li>';
    echo '<li><a href="' . $appurl . '/account.statement/cash-flow">',img('/silk/1.3/application_view_detail.png','Cash Flow Statement').'&nbsp;Cash Flow</a></li>';
    echo '<li><hr /></li>';
    echo '<li><a href="' . $appurl . '/account.form',img('/silk/1.3/application_view_detail.png','Tax Forms').'&nbsp;Tax Schedules</a></li>';


    echo '</ul>';
echo '</li>';

// Plugins
echo '<li><a href="#"><span>' . img('/tango/24x24/categories/preferences-system.png') . ' Plugins</span></a>';
    echo '<ul>';
    // @todo Google Link-Up - Plugin Menu/Config?
    if (!empty($_ENV['Google']['apps_domain'])) {
        // @note Think These should Link Out to Google Services
        $x = $_ENV['Google']['apps_domain'];
        echo '<li><a href="https://mail.google.com/a/' . $x . '" target="_blank">Google Mail</a></li>';
        echo '<li><a href="https://www.google.com/calendar/hosted/' . $x . '" target="_blank">Google Calendar</a></li>';
        echo '<li><a href="https://docs.google.com/a/' . $x . '" target="_blank">Google Documents</a></li>';
        // $menu0[] = sprintf('<a href="https://mail.google.com/tasks/a/edoceo.com/m" target="_blank">&nbsp;Tasks</a>',$x);
        echo '<li><a href="https://mail.google.com/tasks/a/' . $x . '/ig" target="_blank">Google Tasks</a></li>';
    }
    // Not used anymore?
    if (!empty($_ENV['Plugin'])) {
        foreach ($_ENV['Plugin'] as $k=>$v) {
            echo '<li><a href="' . radix::link('/plugin?p=' . $k) .'">' . $k . '</a></li>';
            // echo '<li><a href="/plugin?p=' . $_ENV[$v]['link'] . '">' . $k . '</a></li>';
        }
    }
    echo '</ul>';
echo '</li>';

// Search Form
echo '<li>';
echo '<form action="' . $appurl . '/search" method="get">';
echo '<input id="q" name="q" placeholder="Search" value="" />';
echo '</form></li>';
echo '</ul>';
