<?php
/**
    @file
    @brief WorkorderController

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/


class WorkorderController extends ImperiumController
{
    /**
        WorkorderController init
        Sets the ACL for this Controller
    */
    function init()
    {
        $acl = Zend_Registry::get('acl');
        if ($acl->has('workorder') == false) {
            $acl->add( new Zend_Acl_Resource('workorder') );
        }
        $acl->allow('user','workorder');
        parent::init();
    }

    /**
        WorkorderController indexAction
    */
    function indexAction()
    {
        $sql = $this->_d->select();
        $sql->from('workorder');
        $sql->join('contact','workorder.contact_id=contact.id',array('contact.name as contact_name'));
        $sql->order(array('workorder.date desc','workorder.id desc'));

        $page = Zend_Paginator::factory($sql);

        if (count($page)==0) {
            $this->view->title = 'No Work Orders';
            return(0);
        }

        $page->setCurrentPageNumber(intval($_GET['page']));
        $page->setItemCountPerPage(30);
        $page->setPageRange(10);

        $a_id = $page->getItem(1)->id;
        $z_id = $page->getItem($page->getCurrentItemCount())->id;

        $title = array();
        $title[] = sprintf('Work Orders %d through %d',$a_id,$z_id);
        $title[] = sprintf('Page %d of %d',$page->getCurrentPageNumber(),$page->count());
        $this->view->title = $title;

        $this->view->Page = $page;

    }

    /**
        Work Order Create Action
        Create a new Work Order
    */
    function createAction()
    {
        $this->view->title = array('WorkOrder','Create');
        $this->view->WorkOrder = new WorkOrder(null);

        // Regular GET parameters (preferred) /djb 20110807
        if (!empty($_GET['c'])) {
            $c = new Contact(intval($_GET['c']));
            if ($c->id) {
                $this->view->WorkOrder->contact_id = $c->id;
                $this->view->Contact = $c;
                if (!empty($c->contact)) {
                    $this->view->WorkOrder->requester = $c->contact;
                }
                // @todo Should be getSubContacts()
                $this->view->ContactList = $this->view->Contact->getContactList();
                // $this->view->ContactAddressList = $db->fetchPairs("select id,address from contact_address where contact_id={$id}");
            }
        }
        $this->render('view');
    }

    /**
        Work Order Email Action
        Emails the Work Order as a PDF
    */
    /**
        Work Order Invoice Action
        Builds an invoice out of this service order
    */
    function invoiceAction()
    {
        switch (strtolower($_POST['cmd'])) {
        case 'invoice':
            $this->_billAction();
            break;
        }

        $wo = new WorkOrder(intval($_GET['w']));

        $this->view->title = 'Build Invoice';
        $this->view->WorkOrder = $wo;
        //$this->view->WorkOrderItemList = $wo->getWorkOrderItems(array(
        //  'woi.kind = ?'=>'Subscription',
        //  'woi.status = ?' => 'Active'));

        $w = array('woi.status in (?)' => array('Active','Complete'));
        $w = null;
        $this->view->WorkOrderItemList = $wo->getWorkOrderItems($w);
        $this->view->Contact = new Contact($wo->contact_id);

        $sql = $this->_d->select();
        $sql->from('invoice');
        $sql->where('contact_id = ?',$wo->contact_id);
        $sql->where('status = ?','Active');
        $sql->order(array('id'));
        $list = $this->_d->fetchAll($sql);
        // Zend_Debug::dump($list);
        $this->view->InvoiceList = array(0=>'- New -');
        foreach ($list as $x) {
            $k = $x->id;
            $v = 'Invoice #' . $x->id;
            $v.= ' from ' . date('m/d/y',strtotime($x->date));
            $this->view->InvoiceList[$k] = $v;
        }

    }

    /**
    */
    function _billAction()
    {
        $wo = new WorkOrder($_POST['id']);

        // if ($wo->status != 'Active') {
        //     $this->_s->fail[] = 'Only Active WorkOrders may build an Invoice';
        //     $this->redirect('/workorder/view?w=' . $id);
        // }

        // $this->_d->beginTransaction();
        $iv = $wo->toInvoice($_POST['invoice_id']);
        $x = $iv->getInvoiceItems();

        $msg = sprintf('Invoice #%d created from Work Order #%d with %d items', $iv->id, $wo->id, count($x));
        Base_Diff::note($wo,$this->_s->info);
        Base_Diff::note($iv,$this->_s->info);
        $this->_s->info = $msg;
        // $this->_d->commit();
        $this->_redirect('/invoice/view?i=' . $iv->id);
    }

    /**
        WorkorderController pdfAction
    */
    function pdfAction()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('pdf');

        $ss = Zend_Registry::get('session');
        if (isset($ss->WorkOrder)) {
            $wo = $ss->WorkOrder;
        } else {
            $wo = new WorkOrder($_GET['w']);
        }

        $pdf = new WorkOrderPDF($wo);

        $this->view->file = new stdClass();
        $this->view->file->name = 'WorkOrder-' . $wo->id . '.pdf';
        $this->view->file->data = $pdf->render();
        $this->view->file->size = strlen($this->view->file->data);

        $this->_helper->viewRenderer->setNoRender(); //supress auto renderning
    }

    /**
        Save Work Order
    */
    function saveAction()
    {
        $id = intval($_GET['w']);
        $wo = new WorkOrder($id);

        switch (strtolower($_POST['c'])) {
        case 'bill':
            $this->_redirect('/workorder/invoice?w=' . $id);
            $this->invoiceAction();
            // $this->_billAction();
            break;
        case 'close':
            $sql = "UPDATE workorder_item SET status = 'COMPLETE' ";
            $sql.= sprintf('WHERE workorder_id = %d',$wo->id);
            $this->_d->query($sql);
            $wo->status = 'Closed';
            $wo->save();
            $this->_s->info[] = sprintf('Work Order #%d Closed',$wo->id);
            $this->_redirect(sprintf('/workorder/view?w=%d',$wo->id));
        case 'delete':
            $wo->delete();
            $this->_s->info[] = 'Work Order #' . $id . ' was deleted';
            $this->_redirect('/workorder');
            break;
        case 'send':

            $co = new Contact($wo->contact_id);

            // Make a Key
            $ah = Auth_Hash::make($wo);

            $this->_s->EmailComposeMessage = new stdClass();
            $this->_s->EmailComposeMessage->to = $co->email;
            $this->_s->EmailComposeMessage->subject = 'Work Order #' . $wo->id . ' from ' . $this->_c->Company->name;

            // Load Template File
            $file = APP_ROOT . '/approot/etc/workorder-mail.txt';
            if (is_file($file)) {
                $this->_s->EmailComposeMessage->body = file_get_contents($file);
            }
            // $this->_s->EmailComposeMessage->RecipientList[''] = '- none -';
            // $this->_s->EmailComposeMessage->RecipientList+= $co->getEmailList();
            // $this->_s->EmailComposeMessage->subject = 'Work Order #' . $wo->id . ' from ' . $this->_c->Company->name;
            // $this->_s->EmailComposeMessage->body = "Hello $contact,\n";
            // $this->_s->EmailComposeMessage->body.= '  A link to your recent Work Order is included below.';
            // $this->_s->EmailComposeMessage->body.= ' This is to inform you of the work performed, please retain a copy for your records.';
            // $this->_s->EmailComposeMessage->body.= "\n\n";
            // $this->_s->EmailComposeMessage->body.= "Work Order #" . $wo->id . "\n  " . AppTool::baseUri() . '/hash/' . $ah['hash'] . "\n";
            // $this->_s->EmailComposeMessage->body.= "\n";
            // $this->_s->EmailComposeMessage->body.= 'Thank you for your continued business.';
            // $this->_s->EmailComposeMessage->body.= "\n\nSincerely,\n";
            // $this->_s->EmailComposeMessage->body.= '  ' . $this->_c->Company->name . "\n\n";
            // $this->_s->EmailComposeMessage->body.= 'PS: The linked files are in Adobe PDF format. You must have Acrobat Reader (or other compatible software) installed to view these documents.';

            // $this->_s->ReturnTo = sprintf('/workorder/view/id/%d?sent=true',$wo->id);
            $this->_s->ReturnGood = sprintf('/workorder/view?w=%d?sent=good',$wo->id);
            $this->_s->ReturnFail = sprintf('/workorder/view?w=%d?sent=fail',$wo->id);
            $this->_redirect('/email/compose');
            break;
        case 'save':
            $list = array('contact_id','date','kind','base_rate','base_unit','requester','note');
            foreach ($list as $x) {
                $wo->$x = trim($_POST[$x]);
            }
            $wo->save();

            if ($id) {
                $this->_s->info[] = "Work Order #$id saved";
            } else {
                $id = $wo->id;
                $this->_s->info[] = "Work Order #$id created";
            }
            $this->_redirect('/workorder/view?w=' . $id);
            break;
        case 'void':
            $wo->status = 'Void';
            $wo->save();
            $this->_s->info[] = 'Work Order #' . $wo->id . ' voided';
            $this->_redirect('/');
            break;

        }
    }

    /**
        WorkOrder View Action
    */
    function viewAction()
    {
        $id = intval($_GET['w']);

        $this->view->WorkOrder = new WorkOrder($id);

        // Record Being Sent
        if ( (!empty($this->_s->ReturnFrom)) && ($this->_s->ReturnFrom == 'mail') ) {
            unset($this->_s->ReturnFrom);
            if ($this->_s->ReturnCode == 200) {
                $msg = 'Work Order #' . $this->view->WorkOrder->id . ' sent to ' . $this->_s->EmailSentMessage->to;
                unset($this->_s->EmailSentMessage->to);
                Base_Diff::note($this->view->WorkOrder,$msg);
                $this->_s->info[] = $msg;
            }
            unset($this->_s->ReturnCode);
        }

        // Adding a Contact?
        if ( ($_POST['c'] == 'Add') && (!empty($_POST['add_contact_id'])) ) {
            $sql = 'INSERT INTO workorder_contact (workorder_id,contact_id) VALUES (%d,%d)';
            $this->_d->query(sprintf($sql,$this->view->WorkOrder->id,$_POST['add_contact_id']));
            $this->_s->info[] = 'The Contact has been Added';
            $this->_redirect('/workorder/view?w=' . $this->view->WorkOrder->id);
            break;
        }

        if ($this->view->WorkOrder->id > 0) {
            $this->view->title = array('WorkOrder','View',"#$id");
            $this->view->Contact = new Contact($this->view->WorkOrder->contact_id);

            // Show Notes
            $this->view->WorkOrderNoteList = $this->view->WorkOrder->getNotes();
            // Show Files
            $this->view->WorkOrderFileList = $this->view->WorkOrder->getFiles();
            // $this->view->ContactList = $this->view->Contact->getContactList();

            // Active Work Order Items
            //$where = array(
            //  'woi.status in (?)' => array(WorkOrderItem::STATUS_PENDING,'Active','Complete'),
            //  );
            $where = null;
            $this->view->WorkOrderItemList = $this->view->WorkOrder->getWorkOrderItems($where);

            // Show History Here
            //$where = array(
            //  'woi.status = ?' => array('Billed'),
            //  );
            //$this->view->WorkOrderItemHistoryList = $this->view->WorkOrder->getWorkOrderItems($where);
            // $this->view->WorkOrderHistoryList = $this->view->WorkOrder->getHistory();
            $this->_s->WorkOrder = $this->view->WorkOrder;
        }

        // Work Order Jump List
        // Add Prev / Next Links
        $this->view->jump_list = array();
        if ($this->view->WorkOrder->id > 0) {
            // Prev Five
            $s = sprintf('SELECT id FROM workorder where id < %d order by id desc limit 5',$this->view->WorkOrder->id);
            $r = $this->_d->fetchAll($s);
            $r = array_reverse($r);
            foreach ($r as $x) {
                $this->view->jump_list[] = $x->id;
            }
            // This
            $this->view->jump_list[] = $this->view->WorkOrder->id;
            // Next Five
            $s = sprintf('SELECT id FROM workorder where id > %d order by id asc limit 5',$this->view->WorkOrder->id);
            $r = $this->_d->fetchAll($s);
            foreach ($r as $x) {
                $this->view->jump_list[] = $x->id;
            }
        }

    }
    
    /**
        Report Actions
    */
    function reportAction()
    {
        $time_alpha = $_SERVER['REQUEST_TIME'];
        if (!empty($_GET['d'])) $time_alpha = strtotime($_GET['d']);

        // Last 30 Days by Day
        $sql = 'SELECT date, workorder_id, kind, sum(a_rate * a_quantity) ';
        $sql.= ' FROM workorder_item ';
        $sql.= ' WHERE date <= ? AND date >= ?';
        $sql.= ' GROUP BY date, workorder_id, kind ';
        $sql.= ' ORDER BY date DESC, workorder_id ';
        $sql.= ' LIMIT 60 ';

        $date_alpha = strftime('%Y-%m-%d',$time_alpha);
        $date_omega = strftime('%Y-%m-%d',$time_alpha - (86400 * 30 * 36));

        $this->view->DataByDay = $this->_d->fetchAll($sql,array($date_alpha,$date_omega));

        // Last 6 Months by Week
        $sql = "SELECT date_trunc('week',date) as date, sum(a_rate * a_quantity) as value ";
        $sql.= ' FROM workorder_item ';
        $sql.= ' WHERE date <= ? AND date >= ?';
        $sql.= ' GROUP BY 1 ';
        $sql.= ' ORDER BY date DESC';
        $sql.= ' LIMIT 60 ';

        $date_alpha = strftime('%Y-%m-%d',$time_alpha);
        $date_omega = strftime('%Y-%m-%d',$time_alpha - (86400 * 30 * 36));

        $this->view->DataByWeek = $this->_d->fetchAll($sql,array($date_alpha,$date_omega));

        // Last 12 Months by Month?
        $sql = "SELECT date_trunc('month',date) as date, workorder_id, kind, sum(a_rate * a_quantity) ";
        $sql.= ' FROM workorder_item ';
        $sql.= ' WHERE date <= ? AND date >= ?';
        $sql.= ' GROUP BY date, workorder_id, kind ';
        $sql.= ' ORDER BY date DESC, workorder_id ';
        $sql.= ' LIMIT 60 ';

        $date_alpha = strftime('%Y-%m-%d',$time_alpha);
        $date_omega = strftime('%Y-%m-%d',$time_alpha - (86400 * 30 * 36));

        $this->view->time_alpha = $time_alpha;
        $this->view->DataByMonth = $this->_d->fetchAll($sql,array($date_alpha,$date_omega));

    }

    /**
        Item Action handles requests to create, view and save an Item
    */
    function itemAction()
    {
        $mode = 'create';
        $x = intval($_GET['w']);
        if (!empty($x)) {
            $mode = 'create';
        }
        $x = intval($_GET['id']);
        if (!empty($x)) {
            $mode = 'view';
        }
        if (count($_POST)) {
            $mode = 'save';
        }

        switch ($mode) {
        case 'create':
            $this->view->title = array('Work Order','Item','Create');
            $this->view->WorkOrder = new WorkOrder(intval($_GET['w']));
            $this->view->WorkOrderItem = $this->view->WorkOrder->newWorkOrderItem();
            // Notify?
            if ($_ENV['aorkorder']['notify_send']) {
                $c = new Contact($this->view->WorkOrder->contact_id);
                $this->view->WorkOrderItem->notify = $c->email;
            }
            break;

        case 'save':
            $id = intval($_GET['id']);

            // Delete Request?
            if ($_POST['c'] == 'Delete') {
                $woi = new WorkOrderItem($id);
                $woi->delete();
                $this->_s->msg = 'Work Order Item #' . $id . ' was deleted';
                $this->redirect('/workorder/view?w=' . $woi->workorder_id);
            }

            // Save Request
            $wo = new WorkOrder($_POST['workorder_id']);
            $woi = new WorkOrderItem($id);
            $set = array(
              'kind','date','time_alpha','time_omega',
              'e_rate','e_quantity','e_unit','e_tax_rate',
              'a_rate','a_quantity','a_unit','a_tax_rate',
              'name','note','status','notify');
            foreach ($set as $x) {
                $woi->$x = trim($_POST[$x]);
            }
            $woi = $wo->addWorkOrderItem($woi);

            // Save to DB
            if ($id) {
                $this->_s->msg[] = "Work Order Item #$id saved";
            } else {
                $id = $woi->id;
                $this->_s->msg[] = "Work Order Item #$id created";
            }
            $wo->save();

            // If Notify!
            if (!empty($_POST['notify'])) {

                $this->_s->EmailComposeMessage = new stdClass();
                $this->_s->EmailComposeMessage->to = $_POST['notify'];
                $this->_s->EmailComposeMessage->subject = 'Work Order #' . $wo->id . ' Update Notification';

                // Template
                $file = APP_ROOT . '/approot/etc/workorder-item-mail.txt';
                if (is_file($file)) {
                    $body = file_get_contents($file);
                }

                $body = str_replace('$wo_id',$wo->id,$body);
                $body = str_replace('$wo_note',$wo->note,$body);
                $body = str_replace('$wo_open_amount',$wo->open_amount,$body);

                $body = str_replace('$wi_date',$woi->date,$body);
                $body = str_replace('$wi_kind',$woi->kind,$body);
                $body = str_replace('$wi_name',$woi->name,$body);
                $body = str_replace('$wi_note',$woi->note,$body);
                $body = str_replace('$wi_quantity',$woi->a_quantity,$body);
                $body = str_replace('$wi_rate',$woi->a_rate,$body);
                $body = str_replace('$wi_unit',$woi->a_unit,$body);
                $body = str_replace('$wi_status',$woi->status,$body);

                $this->_s->EmailComposeMessage->body = $body;

                // Want to Add This History
                $this->_s->ReturnTo = '/workorder/view?w=' . $wo->id;
                $this->redirect('/email/compose');

            }
            $this->redirect('/workorder/view?w=' . $wo->id);

            break;
        case 'view':
            $id = intval($_GET['id']);
            $woi = new WorkOrderItem($id);
            if (empty($woi->id)) {
                $this->_s->fail[] = sprintf('Cannot find Work Order Item #%d',$id);
                return;
            }

            $this->view->WorkOrder = new WorkOrder($woi->workorder_id);
            $this->view->WorkOrderItem = $woi;

            break;
        }

    }
}
