<?php
/**
    @file
    @brief Invoice Controller

    @copyright 2008 Edoceo, Inc
    @package   edoceo-imperium
    @link      http://imperium.edoceo.com
    @since     File available since Release 1013
*/


class InvoiceController extends ImperiumController
{
    /**
        InvoiceController init
        Sets the ACL for this Controller
    */
    function init()
    {
        $acl = Zend_Registry::get('acl');
        if ($acl->has('invoice') == false) {
            $acl->add( new Zend_Acl_Resource('invoice') );
        }
        $acl->allow('user','invoice');

        parent::init();

        $sql = 'SELECT name AS id,name FROM base_enum WHERE link = ? ORDER BY sort';
        $this->view->KindList = $this->_d->fetchPairs($sql,array('invoice-kind'));
        $this->view->StatusList = $this->_d->fetchPairs($sql,array('invoice-status'));

    }
    /**
        Invoice Index Action
    */
    function indexAction()
    {
        $sql = $this->_d->select();
        $sql->from('invoice');
        $sql->join('contact','invoice.contact_id=contact.id',array('contact.name as contact_name'));
        $sql->order(array('invoice.date desc','invoice.id desc'));

        $page = Zend_Paginator::factory($sql);
        if (count($page)==0) {
            $this->view->title = 'No Invoices';
            return(0);
        }

        $page->setCurrentPageNumber(intval($_GET['page']));
        $page->setItemCountPerPage(50);
        $page->setPageRange(10);

        $a_id = $page->getItem(1)->id;
        $z_id = $page->getItem($page->getCurrentItemCount())->id;

        $title = array();
        $title[] = sprintf('Invoices %d through %d',$a_id,$z_id);
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
        $this->view->title = array('Invoice','Create');
        $this->view->Invoice = new Invoice(null);

        // Regular GET parameters (preferred) /djb 20110807
        if (!empty($_GET['c'])) {
            $c = new Contact(intval($_GET['c']));
            if ($c->id) {
                $this->view->Invoice->contact_id = $c->id;
                $this->view->Contact = $c;
                // @todo Should be getSubContacts()
                $this->view->ContactList = $this->view->Contact->getContactList();
                $this->view->ContactAddressList = $this->_d->fetchPairs("select id,address from contact_address where contact_id={$c->id}");
            }
        }
        $this->render('view');
    }

    /**
        InvoiceController itemAction

        View/Edit an Item
    */
    function itemAction()
    {
        $ii = new InvoiceItem(intval($_GET['id']));
        switch (strtolower($_POST['cmd'])) {
        case 'cancel':
            $this->redirect('/invoice/view?i=' . $ii->invoice_id);
            break;
        case 'delete':
            $ii->delete();
            $this->_s->info = sprintf('Invoice Item #%d was deleted',$ii->id);
            $this->redirect('/invoice/view?i=' . $ii->invoice_id);
            exit;
            break;
        case 'save':
            $ii->invoice_id = intval($_POST['invoice_id']);
            foreach (array('kind','date','quantity','rate','unit','name','note','tax_rate') as $x) {
                $ii->$x = trim($_POST[$x]);
            }
            // Save to DB
            $ii->save();
            $this->_s->info = sprintf('Invoice Item #%d saved',$ii->id);
            // Update the Balance (Sloppy, should be in IV->saveItem()
            $iv = new Invoice($_POST['invoice_id']);
            $iv->save();
            $this->redirect('/invoice/view?i=' . $ii->invoice_id);
            exit;
            break;
        // case 'create':
        default: // Create
            // Create
            if ( (empty($_GET['id'])) && (!empty($_GET['i'])) && (intval($_GET['i'])>0) ) {
                $this->view->title = array('Invoice','Item','Create');
                $this->view->Invoice = new Invoice(intval($_GET['i']));
                $this->view->InvoiceItem = new InvoiceItem(null);
                $this->view->InvoiceItem->invoice_id = $this->view->Invoice->id;
                $this->view->UnitList = Base_Unit::getList();
                $this->render('item');
                return(0);
            }
            // View
            $this->view->InvoiceItem = new InvoiceItem(intval($_GET['id'])); // $db->fetchRow("select * from invoice_item where id = $id");
            $this->view->Invoice = new Invoice($this->view->InvoiceItem->invoice_id); // $db->fetchRow("select * from invoice where id = {$this->view->InvoiceItem->invoice_id}");
            $this->view->UnitList = Base_Unit::getList();
            $this->view->title = array('Invoice','#'.$this->view->Invoice->id,'Item','#' . $this->view->InvoiceItem->id);
        }
    }

    /**
        InvoiceController pdfAction
    */
    function pdfAction()
    {
        $l = Zend_Layout::getMvcInstance();
        $l->setLayout('pdf');

        $iv = null;
        if (!empty($_GET['i'])) {
            $iv = new Invoice($_GET['i']);
        } else {
            $ss = Zend_Registry::get('session');
            if (isset($ss->Invoice)) {
                $iv = $ss->Invoice;
            } else {
                $iv = new Invoice($_GET['i']);
            }
        }
        if (empty($iv)) {
            throw new Exception('Invalid Invoice Id',__LINE__);
        }

        $pdf = new InvoicePDF($iv);

        $this->view->file = new stdClass();
        $this->view->file->name = 'Invoice-' . $iv->id . '.pdf';
        $this->view->file->data = $pdf->render();
        $this->view->file->size = strlen($this->view->file->data);

        $this->_helper->viewRenderer->setNoRender();//supress auto renderning

    }

    /**
        Invoice Save Action
    */
    function saveAction()
    {
        $Invoice = new Invoice(intval($_GET['i']));

        switch (strtolower($_POST['c'])) {
        case 'delete':
            $Invoice->delete();
            $this->_s->msg = 'Invoice #' . $Invoice->id . ' was deleted';
            $this->redirect('/');
            break;
        case 'hawk':
            $Invoice->setFlag(Invoice::FLAG_HAWK);
            $Invoice->save();
            $this->_s->msg = 'Hawk monitoring has been added to this invoice, reminders will be according to cron schedule';
            $this->redirect('/invoice/view?i=' . $Invoice->id);
            break;
        case 'no hawk':
            $Invoice->delFlag(Invoice::FLAG_HAWK);
            $Invoice->save();
            $this->_s->msg = 'Hawk monitoring has been removed from this invoice';
            $this->redirect('/invoice/view?i=' . $Invoice->id);
            break;
        case 'copy':
            // Copy Invoice
            $iB = new Invoice();
            foreach (array('contact_id','requester','kind','status','base_rate','base_unit','bill_address_id','ship_address_id','note') as $x) {
                $iB->$x = $Invoice->$x;
            }
            $iB->setFlag(Invoice::FLAG_OPEN);
            $iB->save();
            // Copy Invoice Items
            $list = $Invoice->getInvoiceItems();
            foreach ($list as $iiA) {
                $iiB = new InvoiceItem(null);
                $iiB->invoice_id = $iB->id;
                foreach (array('quantity','rate','unit','name','note','tax_rate') as $x) {
                    $iiB->$x = $iiA->$x;
                }
                $iiB->save();
            }
            $this->_redirect('/invoice/view?i=' . $iB->id);
            break;
        case 'paid':

            // New Transaction Holder
            $at = new stdClass();
            $at->AccountJournalEntry = new AccountJournalEntry();
            $at->AccountJournalEntry->date = date('Y-m-d');
            $at->AccountJournalEntry->note = 'Payment for Invoice #' . $Invoice->id;
            $at->AccountLedgerEntryList = array();
            // @todo Detect if should be Inbound Cash or Account Rx
            // @todo: Remove Account Hard Coding

            // This is the Cash Based Method :(
//            // Inbound Cash
//            $a = new Account( $_ENV['account']['inbound_account_id'] );
//            $ale = new AccountLedgerEntry();
//            $ale->account_id = $a->id;
//            $ale->account_name = $a->full_name;
//            $ale->amount = abs($Invoice->bill_amount) * -1;
//            $at->AccountLedgerEntryList[] = $ale;
//            // Invoice Revenue
//            $a = new Account( $_ENV['account']['revenue_account_id'] );
//            $ale = new AccountLedgerEntry();
//            $ale->account_id = $a->id;;
//            $ale->account_name = $a->full_name;
//            $ale->amount = abs($Invoice->bill_amount);
//            $ale->link_to = ImperiumBase::getObjectType($Invoice);
//            $ale->link_id = $Invoice->id;

            // Debit Contact Sub Ledger Cash & Attach to Invoice
            $C = new Contact($Invoice->contact_id);
            if (empty($C->account_id)) {
                 $a = new Account();
                 $a->id = 0;
                 $a->full_name = '- Uknown -';
            //     // $a->parent_id = 
            //     $a->name = $C->name;
            //     $a->code = sprintf('%04d',$C->id);
            //     $a->save();
            } else {
                $a = new Account($C->account_id);
            }
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;
            $ale->account_name = $a->full_name;
            $ale->amount = abs($Invoice->bill_amount) * -1;
            $ale->link_to = ImperiumBase::getObjectType($Invoice);
            $ale->link_id = $Invoice->id;
            $at->AccountLedgerEntryList[] = $ale;

            // Credit AR
            $a = new Account( $_ENV['account']['receive_account_id'] );
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;;
            $ale->account_name = $a->full_name;
            $ale->amount = abs($Invoice->bill_amount);
            $ale->link_to = ImperiumBase::getObjectType('contact');
            $ale->link_id = $Invoice->contact_id;
            $at->AccountLedgerEntryList[] = $ale;

            // Debit Inbound Cash Asset (maybe Paypal if Payment Processor)
            $a = new Account( $_ENV['account']['inbound_account_id'] );
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;
            $ale->account_name = $a->full_name;
            $ale->amount = abs($Invoice->bill_amount) * -1;
            $at->AccountLedgerEntryList[] = $ale;

            // Credit to Revenue Account
            $a = new Account( $_ENV['account']['revenue_account_id'] );
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;
            $ale->account_name = $a->full_name;
            $ale->amount = abs($Invoice->bill_amount);
            $at->AccountLedgerEntryList[] = $ale;

            // Debit Sales Tax Account
            if (!empty($_ENV['account']['taxhold_account_id'])) {
                $a = new Account( $_ENV['account']['taxhold_account_id'] );
                $ale = new AccountLedgerEntry();
                $ale->account_id = $a->id;;
                $ale->account_name = $a->full_name;
                $ale->amount = abs($Invoice->tax_total);
                $ale->link_to = ImperiumBase::getObjectType($Invoice);
                $ale->link_id = $Invoice->id;
            }
            // Credit Accounts Receivable

            $this->_s->AccountTransaction = $at;
            $this->_s->ReturnTo = sprintf('/invoice/view?i=%d',$Invoice->id);
            // $ss->ReturnTo = '/invoice/payment/' . $iv->id . '?paid=true';
            /*
            $this->view->AccountJournalEntry = new AccountJournalEntry(null);
            $this->view->AccountJournalEntry->date = date('m/d/Y');
            $this->view->AccountLedgerEntryList = array();
            $this->view->AccountLedgerEntryList[] = new AccountLedgerEntry(null);
            $this->view->AccountLedgerEntryList[] = new AccountLedgerEntry(null);
            */
            $this->redirect('/account/transaction');

        // Post Charges to Customer Account
        case 'post':

            $C = new Contact($Invoice->contact_id);

            // Generate a Transaction to Post to This Clients Account Receivable

            $at = new stdClass();
            $at->AccountJournalEntry = new AccountJournalEntry();
            $at->AccountJournalEntry->date = $Invoice->date;
            $at->AccountJournalEntry->note = 'Charge for Invoice #' . $Invoice->id;
            $at->AccountLedgerEntryList = array();

            // Debit Accounts Receivable for this Client
            $a = new Account( $_ENV['account']['receive_account_id'] );
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;
            $ale->account_name = $a->full_name;
            $ale->amount = abs($Invoice->bill_amount) * -1;
            $ale->link_to = ImperiumBase::getObjectType('contact');
            $ale->link_id = $Invoice->contact_id;
            $at->AccountLedgerEntryList[] = $ale;

            // Credit Customer Account - or Revenue for Instant Revenue?
            // Old Query, Why from account by contact?
            // $x = $this->_d->fetchRow('SELECT * FROM account WHERE contact_id = ?',array($c->id));
            // if ($x->id) {
            //     $a = new Account($x->id);
            if ($C->account_id) {
                $a = new Account($C->account_id);
            } else {
                $a = new Account( $_ENV['account']['revenue_account_id'] );
            }
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;;
            $ale->account_name = $a->full_name;
            $ale->amount = abs($Invoice->bill_amount);
            $ale->link_to = ImperiumBase::getObjectType($Invoice);
            $ale->link_id = $Invoice->id;
            $at->AccountLedgerEntryList[] = $ale;

            $this->_s->AccountTransaction = $at;
            $this->_s->ReturnTo = sprintf('/invoice/view?i=%d',$Invoice->id);
            $this->redirect('/account/transaction');
            break;

        // Save the Updated Invoice
        case 'save':

            // Save Request
            foreach (array('contact_id','date','kind','status','bill_address_id','ship_address_id','note') as $x) {
                $Invoice->$x = trim($_POST[$x]);
            }
            if (empty($Invoice->flag)) {
                $Invoice->setFlag(Invoice::FLAG_OPEN);
            }
            $Invoice->save();

            if ($id) {
                $this->_s->msg = 'Invoice #' . $Invoice->id . ' saved';
            } else {
                $this->_s->msg = 'Invoice #' . $Invoice->id . ' created';
            }
            $this->redirect('/invoice/view?i=' . $Invoice->id);
            break;

        // Email the Invoice
        case 'send':

            $co = new Contact($Invoice->contact_id);

            // Sent Good
            if ($_GET['sent']=='true') {
                $msg = 'Invoice #' . $Invoice->id . ' sent to ' . $this->_s->EmailSentMessage->to;
                unset($this->_s->EmailSentMessage->to);
                Base_Diff::note($iv,$msg);
                $this->_s->msg = $msg;
                $this->redirect('/invoice/view?i=' . $Invoice->id);
            }

            $ah = Auth_Hash::make($Invoice);

            $this->_s->EmailComposeMessage = new stdClass();
            $this->_s->EmailComposeMessage->to = $co->email;
            //$ss->EmailComposeMessage->to = $co->email;
            //if ($co->kind != 'Person') {
            //    if ($list = $co->getContactList(Contact::FLAG_BILL)) {
            //        $to = array();
            //        foreach ($list as $x) {
            //            $to[] = $x->email;
            //        }
            //        $this->_s->EmailComposeMessage->to = implode(',',$to);
            //        $this->_s->EmailComposeMessage->body = 'Hello, ' . $list[0]->first_name . ",\n";
            //    }
            //}
            $this->_s->EmailComposeMessage->subject = 'Invoice #' . $Invoice->id . ' from ' . $_ENV['company']['name'];

            // Load Template File
            $file = APP_ROOT . '/approot/etc/invoice-mail.txt';
            if (is_file($file)) {

                $body = file_get_contents($file);

                // Substitutions
                $body = str_replace('$app_company',$_ENV['company']['name'],$body);
                $body = str_replace('$contact_name',$co->contact,$body);
                $body = str_replace('$invoice_id',$Invoice->id,$body);
                $body = str_replace('$invoice_date',strftime($_ENV['format']['nice_date'],strtotime($Invoice->date)),$body);
                if (strpos($body,'$invoice_link')) {
                    $ah = Auth_Hash::make($Invoice);
                    $body = str_replace('$invoice_link',"{$_ENV['application']['base']}/hash/{$ah['hash']}",$body);
                }

                // @todo collect associated work-orders?
                $sql = 'SELECT DISTINCT workorder.id FROM workorder ';
                $sql.= ' JOIN workorder_item ON workorder.id = workorder_item.workorder_id ';
                $sql.= ' WHERE workorder_item.id IN ( ';
                    $sql.= 'SELECT workorder_item_id FROM invoice_item WHERE invoice_item.invoice_id = %d ';
                $sql.= ') ';
                $sql.= ' ORDER BY 1 ';
                $res = $this->_d->fetchAll(sprintf($sql,$Invoice->id));
                $id_list = array(); // IDs
                $al_list = array(); // Authorization Hash Links
                foreach ($res as $x) {
                    $ah = Auth_Hash::make(new WorkOrder($x->id));
                    $id_list[] = sprintf('#%d',$x->id);
                    $al_list[] = sprintf('%s/hash/%s',$_ENV['application']['base'],$ah['hash']);
                }
                $body = str_replace('$workorder_id',implode(', ',$id_list),$body);
                $sep = "\n  "; // Default, use matched if one is found
                if (preg_match('/^(.+)\$workorder_link/m',$body,$m)) {
                    $sep = "\n{$m[1]}";
                }
                $body = str_replace('$workorder_link',implode($sep,$al_list),$body);

                $body = str_replace('$payment_link',"{$_ENV['application']['base']}/checkout/invoice/hash/{$Invoice->hash}",$body);

                $this->_s->EmailComposeMessage->body = $body;

            }

            $this->_s->ReturnGood = sprintf('/invoice/view?i=%d&sent=good',$Invoice->id);
            $this->_s->ReturnFail = sprintf('/invoice/view?i=%d&sent=fail',$Invoice->id);
            $this->_redirect('/email/compose');
            break;

        case 'void':
            // Voiding out an Invoice
            $Invoice->note = trim($_POST['note']);
            $Invoice->status = 'Void';
            $Invoice->setFlag(Invoice::FLAG_VOID);
            $Invoice->bill_amount = 0;
            $Invoice->paid_amount = 0;
            $Invoice->save();
            $this->redirect('/');
            break;
        }
    }

    /**
        Invoice View Action
    */
    function viewAction()
    {
        $id = intval($_GET['i']);
        $this->view->Invoice = new Invoice($id);
        if ( (!empty($_GET['sent'])) && ($_GET['sent'] == 'good') ) {
            $this->_s->info[] = 'Invoice Status updated';
            $this->view->Invoice->status = 'Sent';
            $this->view->Invoice->setFlag(Invoice::FLAG_SENT);
            $this->view->Invoice->save();
            Base_Diff::note($this->view->Invoice,$this->_s->info);
        }
        $this->view->Contact = new Contact($this->view->Invoice->contact_id);
        $this->view->ContactAddressList = $this->_d->fetchPairs("select id,address from contact_address where contact_id={$this->view->Invoice->contact_id}");
        $this->view->InvoiceItemList = $this->view->Invoice->getInvoiceItems();
        $this->view->InvoiceNoteList = $this->view->Invoice->getNotes();
        $this->view->InvoiceFileList = $this->view->Invoice->getFiles();
        $this->view->InvoiceHistoryList = $this->view->Invoice->getHistory();
        $this->view->InvoiceTransactionList = $this->view->Invoice->getTransactions();

        // Add Prev / Next Links
        $this->view->jump_list = array();
        if (!empty($this->view->Invoice->id)) {
            $s = sprintf('SELECT id FROM invoice where id < %d order by id desc limit 5',$this->view->Invoice->id);
            $r = $this->_d->fetchAll($s);
            $r = array_reverse($r);
            foreach ($r as $x) {
                $this->view->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$x->id);
            }
            $s = sprintf('SELECT id FROM invoice where id > %d order by id asc limit 5',$this->view->Invoice->id);
            $r = $this->_d->fetchAll($s);
            foreach ($r as $x) {
                $this->view->jump_list[] = array('controller'=>'invoice','action'=>'view','id'=>$x->id);
            }
        }
        $this->_s->Invoice = $this->view->Invoice;
    }

}
