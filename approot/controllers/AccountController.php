<?php
/**
    @copyright    2008 Edoceo, Inc
  @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/


class AccountController extends ImperiumController
{
    /**
        AccountController init
    */
    function init()
    {
        $acl = Zend_Registry::get('acl');
        $acl->add( new Zend_Acl_Resource('account') );
        $acl->allow('user','account');
        parent::init();
    }

    /**
        Account Controller internal preDispatch
    */
    function preDispatch()
    {

        // Add this if not present
        if (empty($this->_s->Account)) {
            $this->_s->Account = new Account();
        }

        // @todo this is duplicated in the AccountStatement Controller - how to reslove?
        // Initialise Inputs
        if ( (isset($_GET['d0'])) && (isset($_GET['d1'])) ) {
            $this->view->Period = 'x';
            $this->view->date_alpha = date('Y-m-d',strtotime($_GET['d0']));
            $this->view->date_omega = date('Y-m-d',strtotime($_GET['d1']));
        } elseif (isset($this->_s->AccountPeriod->date_alpha)) {
            $this->view->date_alpha = $this->_s->AccountPeriod->date_alpha;
            $this->view->date_omega = $this->_s->AccountPeriod->date_omega;
        } else {
            $this->view->Period = isset($_GET['p']) ? $_GET['p'] : 'm';
            $this->view->Month = isset($_GET['m']) ? $_GET['m'] : date('m');
            $this->view->Year = isset($_GET['y']) ? $_GET['y'] : date('Y');
        }

        // Period Processing?
        if ($this->view->Period == 'm') {
            $this->view->date_alpha = date('Y-m-d',mktime(0,0,0,$this->view->Month,1,$this->view->Year));
            $this->view->date_omega = date('Y-m-t',mktime(0,0,0,$this->view->Month));
        } elseif ($this->view->Period == 'q') {
            // @note this may or may not be an accurate way to find the Quarter
            $this->view->date_alpha = date('Y-m-d',mktime(0,0,0,$this->view->Month,1,$this->view->Year));
            $this->view->date_omega = date('Y-m-t',mktime(0,0,0,$this->view->Month+2,1,$this->view->Year));
        } elseif ($this->view->Period == 'y') {
            // @note this may or may not be an accurate way to find the full 12 months
            $this->view->date_alpha = date('Y-m-d',mktime(0,0,0,$this->view->Month,1,$this->view->Year));
            $this->view->date_omega = date('Y-m-t',mktime(0,0,0,$this->view->Month+11,1,$this->view->Year));
        }
        
        // Handle Empties
        if (empty($this->view->date_alpha)) {
            $this->view->date_alpha = date('Y-m-01');
        }
        if (empty($this->view->date_omega)) {
            $this->view->date_omega = date('Y-m-t');
        }
        
        // Format Date
        $this->view->date_alpha_f = strftime('%B %Y',strtotime($this->view->date_alpha));
        $this->view->date_omega_f = strftime('%B %Y',strtotime($this->view->date_omega));
    // Save to Session
    // @todo This should be done differently
    // @todo Would also like to make AccountTransaction Controller that does Transaction and Wizard in one
    if (empty($this->_s->AccountPeriod)) {
      $this->_s->AccountPeriod = new stdClass();
    }
        $this->_s->AccountPeriod->date_alpha = $this->view->date_alpha;
        $this->_s->AccountPeriod->date_omega = $this->view->date_omega;

        // Build other View Data (Month, Year, Period)
        $this->view->MonthList = array();
        for ($i=1;$i<=12;$i++) {
            $this->view->MonthList[$i] = sprintf('%02d',$i) . ' ' . strftime('%B',mktime(0,0,0,$i));
        }

        $this->view->YearList = array();
        $year = date('Y');
        for ($i=$year-10;$i<=$year+10;$i++) {
            $this->view->YearList[$i] = $i;
        }

        $this->view->PeriodList = array(
            'm'=>'Monthly',
            'q'=>'Quarterly',
            'y'=>'Yearly'
        );

        // Account List
        $this->view->AccountPeriod = $this->_s->AccountPeriod;
        $this->view->AccountList = Account::listAccounts();
        $this->view->AccountPairList = Account::listAccountPairs();
        // Account Kind List
        $this->view->AccountKindList = Account::$kind_list;
    }

    /**
        Index Action
    */
    function indexAction()
    {
        $this->view->title = array('Accounting','Chart of Accounts');

        // Find period containting this date
        // $sql = 'SELECT * FROM account_period WHERE date_alpha <= ? AND date_omega >= ?';
        // $arg = array(
        //     $this->view->date_alpha,
        //     $this->view->date_omega,
        // );
        // $x = $this->_d->fetchRow($sql,$arg);
        // if (empty($x)) {
        //     $this->_s->fail[] = 'Invalid Account Period';
        // }
        // $this->view->AccountPeriod = $x;
    }
    
    /**
        Handles AJAX requests
    */
    function ajaxAction()
    {
        $q = strtolower(trim($_GET['term']));
        if (strlen($q) == 1) {
            $q = '^' . $q;
        }

        switch ($_GET['a']) {
        case 'account':

            $s = $this->_d->select();
            $s->from('account',array('id','full_name as label','full_name as result'));
            $s->where('name ~* ?',$q);
            $s->orWhere('full_name ~* ?','^'.$q);
            // $s->orWhere('name ~* ?','^'.$q);
            $s->order(array('full_name'));
            $r = $this->_d->fetchAll($s);

            echo json_encode($r);
            break;

        case 'contact':

            $s = $this->_d->select();
            $s->from('contact',array('id','name as label','name as result'));
            $s->where('contact ~* ?',$q);
            $s->orWhere('company ~* ?','^'.$q);
            $s->orWhere('name ~* ?','^'.$q);
            $s->order(array('contact'));
            $r = $this->_d->fetchAll($s);

            echo json_encode($r);
        }

        exit;
    }
    
    /**
        Cheque Action
    */
    function chequeAction()
    {

        $req = $this->getRequest();

        if ($req->isPost()) {

            $pdf = new Zend_Pdf();
            $pdf->properties['Title'] = 'Cheque #1234';
            $pdf->properties['Author'] = 'Edoceo Imperium';
            //$pdf->properties['Subject'] = 'Edoceo Imperium';
            //$pdf->properties['Keywords'] = 'Edoceo Imperium';
            $pdf->properties['Creator'] = 'Edoceo Imperium';
            $pdf->properties['Producer'] = 'Edoceo Imperium';
            //$pdf->properties['CreationDate']
            //$pdf->properties['ModDate'];
            //$pdf->properties['Trapped']

            //$font_m = Zend_Pdf_Font::fontWithPath(APP_ROOT . '/var/fonts/Edoceo-MICR.ttf');
            $font_m = Zend_Pdf_Font::fontWithPath(APP_ROOT . '/var/fonts/micr.ttf');
            //$font_c = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
            $font_h = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            //$font_hb = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);

            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

            // MICR Line
            $page->setFont($font_m,12);
            $t = 'C' . sprintf('%06d',$req->getPost('number')) . 'C  A' . $req->getPost('routing') . 'A  ' . $req->getPost('account'). 'C';
            $page->drawText($t,108,558);

            // Small Text
            // @todo needs to come from Account
            $page->setFont($font_h,10);
            $page->drawText('Key Bank National Association',324,760);
            $page->drawText('Seattle, WA 98104',324,750);
            $page->drawText('19-57',324,730);
            $page->drawText('1250-250',324,720);
            // Rest of Cheque
            $page->setFont($font_h,12);
            $page->drawText($req->getPost('number'),504,756);
            $page->drawText($req->getPost('date'),468,720);
            // Pay To
            $page->drawText('Pay to the Order of:',32,702);
            $page->drawText($req->getPost('payto'),144,702);
            // Amount Text and Number
            $page->drawText($req->getPost('amount_t'),32,675);
            $page->drawText('$ ' . number_format($req->getPost('amount_n'),2),504,675);
            // Memo
            $page->drawText($req->getPost('memo'),32,594);

            // Second Panel
            $page->setFont($font_h,12);
            $page->drawText($_ENV['company']['name'],32,522);
            $page->drawText(sprintf('%06d',$req->getPost('number')),504,522);
            $page->drawText($req->getPost('payto'),72,504);
            $page->drawText($req->getPost('date'),432,504);
            // Source Account
            // @todo Needs to come from the Account
            $page->drawText('Source Account for the Expense',72,476);
            $page->drawText(number_format($req->getPost('amount_n'),2),432,476);
            // Drawn Account, Memo and Amount
            $page->drawText('Checking Account',72,324);
            $page->drawText(number_format($req->getPost('amount_n'),2),432,324);
            $page->drawText($req->getPost('memo'),72,310);

            // Third Panel
            $page->drawText($_ENV['company']['name'],32,270);
            $page->drawText(sprintf('%06d',$req->getPost('number')),504,270);
            $page->drawText($req->getPost('payto'),72,238);
            $page->drawText($req->getPost('date'),432,238);
            // Source Account
            $page->drawText('Source Account for the Expense',72,210);
            $page->drawText(number_format($req->getPost('amount_n'),2),432,210);
            // Drawn Account, Memo and Amount
            $page->drawText('Checking Account',72,86);
            $page->drawText(number_format($req->getPost('amount_n'),2),432,86);
            $page->drawText($req->getPost('memo'),72,72);

            $pdf->pages[] = $page;

            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="Cheque-' . $req->getPost('number') . '.pdf"');

            echo $pdf->render();

            exit(0);
        }

        $this->view->title = array('Accounting','Cheque');

        $sql = "select id,full_name ";
        $sql.= " from account";
        $sql.= " where kind like 'Expense%' ";
        $sql.= " order by full_code";
        $this->view->ExpenseAccountList = $this->_d->fetchPairs($sql);

        $sql = "select id,full_name ";
        $sql.= " from account";
        $sql.= " where kind like 'Asset%' ";
        $sql.= " order by full_code";
        $this->view->AssetAccountList = $this->_d->fetchPairs($sql);

    }

    /**
        Accounting Controller Close Period
    */
    function closeAction()
    {
        // All Handled in View
    }
    /**
        Account Controller Create Action
    */
    function createAction()
    {
        $this->view->title = array('Account','Create');
        $a = new Account();
        if (!empty($this->_s->Account)) {
            $a = $this->_s->Account;
            unset($this->_s->Account);
        }
        $this->view->Account = $a;
        $this->view->AccountTaxLineList = AccountTaxFormLine::listTaxLines();
        $this->render('view');
    }

    /**
        Account Journal Action
    */
    function journalAction()
    {

        $req = $this->getRequest();

        $id = intval($_GET['id']);
        $this->view->Account = new Account($id);

        if ( (strtolower($_GET['c'])=='post') && (!empty($this->view->Account->id)) ) {
            // Post to this Account
            // New Transaction Holder
            $at = new stdClass();
            $at->AccountJournalEntry = new AccountJournalEntry();
            $at->AccountJournalEntry->note = null;
            $at->AccountLedgerEntryList = array();

            // First Item is this Account
            $a = new Account( $this->view->Account->id );
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;
            $ale->account_name = $a->full_name;
            // $ale->amount = abs($Invoice->bill_amount) * -1;
            // $ale->link_to = ImperiumBase::getObjectType($Invoice);
            // $ale->link_id = $Invoice->id;
            $at->AccountLedgerEntryList[] = $ale;
            // Next Line Accounts Receivable
            $ale = new AccountLedgerEntry();
            $at->AccountLedgerEntryList[] = $ale;

            $this->_s->AccountTransaction = $at;
            $this->_s->ReturnTo = sprintf('/account/journal?id=%d',$this->view->Account->id);
            $this->redirect('/account/transaction');
        }

        $this->view->title = array('Account','Journal',$this->view->Account->name,$this->view->date_alpha.' to '.$this->view->date_omega);
        // ImperiumView::mruAdd($this->view->link(),'Journal ' . $this->view->Account->name);

        //$this->set('debit_total',$this->Account->debitTotal($account_id,$date_alpha,$date_omega));
        //$this->set('credit_total',$this->Account->creditTotal($account_id,$date_alpha,$date_omega));

        $this->view->openBalance = $this->view->Account->balanceAt($this->view->date_alpha);

        // Try One
        $sql = "select * from general_ledger ";
        $sql.= " where account_id=$id and (date>='{$this->view->date_alpha}' and date<='{$this->view->date_omega}') ";
        $sql.= " order by date,amount ";

        // Try Two
        $sql = "select a.*,b.*,c.* ";
        $sql.= ' from account a ';
            $sql.= ' join account_ledger b on a.id=b.account_id ';
            $sql.= ' join account_journal c on b.account_journal_id=c.id ';
        $sql.= " where account_id=$id and (c.date>='{$this->view->date_alpha}' and c.date<='{$this->view->date_omega}') ";
        $sql.= " order by date,amount ";

        // Try Three
        $sql = 'select * from general_ledger ';
        $sql.= " where (date>='{$this->view->date_alpha}' and date<='{$this->view->date_omega}') ";
            $sql.= " and account_journal_id in (select account_journal_id from account_ledger where account_id = $id) ";
        $sql.= ' order by date,account_journal_id,amount ';

        $this->view->JournalEntryList = $this->_d->fetchAll($sql);

        $this->_s->Account->id = $id;

        //Zend_Debug::dump($this->view);
        //exit;

    }

    /**
        Account Ledger Action
    */
    function ledgerAction()
    {

        $rq = $this->getRequest();

        // Load Specified Account or Session Account
        if ( ($id = intval($_GET['id'])) > 0) {
            $this->view->Account = new Account($id);
        } elseif ( ($id = intval($_GET['id'])) > 0) {
            $this->view->Account = new Account($id);
        } elseif ( ($id = intval($_GET['id'])) == -1) {
            $this->view->Account = new Account(-1);
        } elseif ($this->_s->Account) {
            $this->view->Account = new Account($this->_s->Account->id);
        } else {
            $this->view->Account = new Account();
        }

        if ( (strtolower($_GET['c'])=='post') && (!empty($this->view->Account->id)) ) {

            // Post to this Account
            // New Transaction Holder
            $at = new stdClass();
            $at->AccountJournalEntry = new AccountJournalEntry();
            $at->AccountJournalEntry->note = null;
            $at->AccountLedgerEntryList = array();

            // First Item is this Account
            $a = new Account( $this->view->Account->id );
            $ale = new AccountLedgerEntry();
            $ale->account_id = $a->id;
            $ale->account_name = $a->full_name;
            // $ale->amount = abs($Invoice->bill_amount) * -1;
            // $ale->link_to = ImperiumBase::getObjectType($Invoice);
            // $ale->link_id = $Invoice->id;
            $at->AccountLedgerEntryList[] = $ale;
            // Next Line Accounts Receivable
            $ale = new AccountLedgerEntry();
            $at->AccountLedgerEntryList[] = $ale;

            $this->_s->AccountTransaction = $at;
            $this->_s->ReturnTo = sprintf('/account/ledger?id=%d',$this->view->Account->id);
            $this->redirect('/account/transaction');
        }

        if (empty($this->view->Account->id)) {

            // Show General Ledger (All Accounts!)
            unset($this->_s->Account);
            $this->view->openBalance = 0;

            $where = " (date>='{$this->view->date_alpha}' and date<='{$this->view->date_omega}') ";
            $order = " date,kind, account_journal_id, amount asc ";

            $this->view->dr_total = $this->_d->fetchOne("select sum(amount) from general_ledger where amount < 0 and $where");
            $this->view->cr_total = $this->_d->fetchOne("select sum(amount) from general_ledger where amount > 0 and $where");

            $this->view->Account = new Account(array('name'=>'General Ledger'));
        } else {
            // Show this specific Account
            $this->_s->Account = $this->view->Account;
            $this->view->openBalance = $this->view->Account->balanceBefore($this->view->date_alpha);

            $where = " (account_id={$this->view->Account->id} OR parent_id = {$this->view->Account->id}) and (date>='{$this->view->date_alpha}' and date<='{$this->view->date_omega}') ";
            // $where.= " and amount < 0 ";
            $order = " date,kind desc,amount asc ";

            $this->view->title = array('Ledger',"{$this->view->Account->full_name} from {$this->view->date_alpha_f} to {$this->view->date_omega_f}");
            //$this->view->AccountLedger = $data;
            $this->view->dr_total = abs($this->view->Account->debitTotal($this->view->date_alpha,$this->view->date_omega));
            $this->view->cr_total = abs($this->view->Account->creditTotal($this->view->date_alpha,$this->view->date_omega));
        }
        if (strlen($_GET['link'])) {
            // $l = ImperiumBase::getObjectType($o)
            $l = Base_Link::load($_GET['link']);
            $link_to = Base_Link::getObjectType($l,'id');  // Get Object Type ID
            $link_id = $l->id;
            if ( (!empty($link_to)) && (!empty($link_id)) ) {
                $where .= sprintf(' and link_to = %d and link_id = %d ',$link_to,$link_id);
            }
        }
        $sql = "select * from general_ledger where $where order by $order";
        $this->view->LedgerEntryList = $this->_d->fetchAll($sql);

        $this->view->title = array('General Ledger',"{$this->view->date_alpha_f} to {$this->view->date_omega_f}");
        // ImperiumView::mruAdd($this->view->link(),'Ledger ' . $this->view->Account->name);
        $this->_s->ReturnTo = '/account/ledger';
    }

    /**
        Account Conroller Reconcile Action
    */
    function reconcileAction()
    {
        // See View
        if ( ($id = intval($_GET['id'])) > 0) {
            $this->view->Account = new Account($id);
        } elseif (!empty($this->_s->reconcile_upload_id)) {
            $this->view->Account = new Account($this->_s->reconcile_upload_id);
        } elseif (!empty($_ENV['account']['banking_account_id'])) {
            $this->view->Account = new Account($_ENV['account']['banking_account_id']);
        }
        // Zend_Debug::dump($_ENV);

        // Preveiw
        switch (strtolower($_POST['cmd'])) {
        case 'upload': // Read the Uploaded Data
            $_ENV['mode'] = 'view';
            // Zend_Debug::dump($_POST);
            // Zend_Debug::dump($_FILES);
            if ($_FILES['file']['error']==0) {
                $this->view->Account = new Account($this->_request->getPost('account_id'));
                $this->view->title = array('Account','Reconcile',$this->view->Account->full_name,'Preview');
                // Read File
                $arg = array(
                    'kind' => $_POST['format'],
                    'file' => $_FILES['file']['tmp_name'],
                    'account_id' => $_POST['upload_id'],
                );
                $this->view->JournalEntryList = Account_Reconcile::parse($arg);
            } else {
                $this->_s->fail[] = 'Failed to Upload';
            }

            // @todo If the Target Account is Asset then Other Side Only (and vice-versa)
            $sql = 'SELECT id,full_name ';
            $sql.= 'FROM account ';
            // $sql.= "WHERE kind like 'Expense%' ";
            $sql.= 'ORDER BY full_code ASC, code ASC';
            $this->view->AccountPairList = $this->_d->fetchPairs($sql);

            $this->_s->reconcile_upload_id = $_POST['upload_id'];
            $this->_s->reconcile_offset_id = $_POST['offset_id'];

            $_ENV['upload_account_id'] = $this->_s->reconcile_upload_id;
            $_ENV['offset_account_id'] = $_POST['offset_id'];

            break;
        case 'save': // Save the Uploaded Transactions

            $_ENV['upload_account_id'] = $this->_s->reconcile_upload_id;

            $c = ceil(count($_POST) / 4);
            for ($i=1;$i<=$c;$i++) {

                if (empty($_POST[sprintf('je%ddate',$i)])) {
                    continue;
                }
                if (!empty($_POST[sprintf('je%did',$i)])) {
                    continue; // Have this one already;
                }

                // Journal Entry
                $je = new AccountJournalEntry();
                $je->auth_user_id = $cu->id;
                $je->date = $_POST[sprintf('je%ddate',$i)]; // $req->getPost('date');
                $je->note = $_POST[sprintf('je%dnote',$i)]; // $req->getPost('note');
                $je->kind = 'N'; // $req->getPost('kind');
                $je->save();

                // Debit Side
                $dr = new AccountLedgerEntry();
                $dr->auth_user_id = $cu->id;
                $dr->account_journal_id = $je->id;
                // $dr->account_id = $_POST[sprintf('je%daccount_id')]; // $req->getPost($i . '_account_id');
                // $le->amount = ($dr > $cr) ? abs($dr) * -1 : abs($cr);
                // Bind to an object
                // $le->link_id = $req->getPost($i . '_link_id');
                // $le->link_to = $req->getPost($i . '_link_to');
                // Save Ledger Entry

                // Credit Side
                $cr = new AccountLedgerEntry();
                $cr->auth_user_id = $cu->id;
                $cr->account_journal_id = $je->id;
                // $cr->account_id = $req->getPost($i . '_account_id');
                // $ale->note = $req->getPost($i . '_note');
                // $cr->amount = ($dr > $cr) ? abs($dr) * -1 : abs($cr);
                // Bind to an object
                // $cr->link_id = $req->getPost($i . '_link_id');
                // $cr->link_to = $req->getPost($i . '_link_to');


                if (!empty($_POST[sprintf('je%dcr',$i)])) {
                    // Credit to the Upload Target Account
                    $dr->account_id = $_POST[sprintf('je%daccount_id',$i)];
                    $cr->account_id = $_ENV['upload_account_id'];
                    $dr->amount = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%dcr',$i)])) * -1;
                    $cr->amount = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%dcr',$i)]));
                } else {
                    // Debit to the Upload Target Account
                    $dr->account_id = $_ENV['upload_account_id'];
                    $cr->account_id = $_POST[sprintf('je%daccount_id',$i)];
                    $dr->amount = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%ddr',$i)])) * -1;
                    $cr->amount = abs(preg_replace('/[^\d\.]+/',null,$_POST[sprintf('je%ddr',$i)]));
                }
                $dr->save();
                $cr->save();

            }
            // Zend_Debug::dump($_POST);
            break;
        }
    }

    /**
        AccountController transactionAction
    */
    function transactionAction()
    {
        $cu = Zend_Auth::getInstance()->getIdentity();
        $req = $this->getRequest();

        if ($req->isPost()) {
            $this->transactionActionPost();
        }

        // View!
        $id = intval($_GET['id']);
        $this->view->title = array('Accounts','Transaction', $id ? "#$id" : 'Create' );

        if ($id) {
            $this->view->AccountJournalEntry = new AccountJournalEntry($id); //
            //$this->_d->fetchRow("select * from account_journal where id = $id");
            //$sql = $this->_d->select();
            //$sql->from('general_ledger');
            //$sql->where('account_journal_id = ?', $id);
            //$sql->order(array('account_full_code','amount'));
            $sql = $this->_d->select();
            $sql->from(array('al'=>'account_ledger'));
            $sql->join(array('a'=>'account'),'al.account_id = a.id',array('a.full_name as account_name'));
            $sql->where('al.account_journal_id = ?', $id);
            //$sql->order(array('al.amount asc','a.full_code'));
            $sql->order(array('al.id asc'));
            // $sql = "select * from account_ledger where account_journal_id=$id order by amount"
            $this->view->AccountLedgerEntryList = $this->_d->fetchAll($sql);
            $this->view->FileList = $this->view->AccountJournalEntry->getFiles();
        } elseif (isset($this->_s->AccountTransaction)) {
            $this->view->AccountJournalEntry = $this->_s->AccountTransaction->AccountJournalEntry;
            $this->view->AccountLedgerEntryList = $this->_s->AccountTransaction->AccountLedgerEntryList;
            // @todo Here on on Save (above)?
            unset($this->_s->AccountTransaction);
        } else {
            $this->view->AccountJournalEntry = new AccountJournalEntry(null);
            $this->view->AccountLedgerEntryList = array();
            $this->view->AccountLedgerEntryList[] = new AccountLedgerEntry(null);
            $this->view->AccountLedgerEntryList[] = new AccountLedgerEntry(null);
        }

        // Correct Missing Date
        if (empty($this->view->AccountJournalEntry->date)) {
            $this->view->AccountJournalEntry->date = isset($this->_s->AccountJournalEntry->date) ? $this->_s->AccountJournalEntry->date : date('Y-m-d');
        }
        
        // Add Prev / Next Links
        $this->view->jump_list = array();
        if (!empty($this->view->AccountJournalEntry->id)) {
            // $s = sprintf('SELECT id FROM account_journal where id < %d order by id desc limit 1',$this->view->AccountJournalEntry->id);
            // $this->view->prev = $this->_d->fetchOne($s);
            // $s = sprintf('SELECT id FROM account_journal where id > %d order by id asc limit 1',$this->view->AccountJournalEntry->id);
            // $this->view->next = $this->_d->fetchOne($s);
            
            // Prev Five
            $s = sprintf('SELECT id FROM account_journal where id < %d order by id desc limit 5',$this->view->AccountJournalEntry->id);
            $r = $this->_d->fetchAll($s);
            $r = array_reverse($r);
            foreach ($r as $x) {
                $this->view->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x->id);
            }
            // This
            $this->view->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$this->view->AccountJournalEntry->id);
            // Next Five
            $s = sprintf('SELECT id FROM account_journal where id > %d order by id asc limit 5',$this->view->AccountJournalEntry->id);
            $r = $this->_d->fetchAll($s);
            foreach ($r as $x) {
                $this->view->jump_list[] = array('controller'=>'account','action'=>'transaction','id'=>$x->id);
            }
        }

        $this->view->LinkToList = array(
            null=>null,
            ImperiumBase::getObjectType('contact')   =>'Contact',
            ImperiumBase::getObjectType('invoice')   =>'Invoice',
            ImperiumBase::getObjectType('workorder') =>'Work Order',
        );
    }

    /**
        AccountController transactionActionPost()
    */
    function transactionActionPost()
    {
        $cu = Zend_Auth::getInstance()->getIdentity();
        $req = $this->getRequest();

        $id = intval($req->getPost('id'));

        // Delete
        if ($req->getPost('c') == 'Delete') {
            $aje = new AccountJournalEntry($id);
            $aje->delete();
            $this->_s->info = 'Journal Entry #' . $id . ' deleted';
            $this->redirect('/account/ledger');
        }
        
        $this->_d->beginTransaction();

        $aje = new AccountJournalEntry($id);
        $aje->auth_user_id = $cu->id;
        $aje->date = $req->getPost('date');
        $aje->note = $req->getPost('note');
        $aje->kind = $req->getPost('kind');
        $aje->save();

        $this->_s->AccountJournalEntry->date = $this->_request->getPost('date');
        
        // And Make the Wizard
        $awj = AccountWizardJournal::makeFromAccountJournal($aje);

        if ($id) {
            $this->_s->info[] = 'Account Journal Entry #' . $id . ' updated';
        } else {
            $this->_s->info[] = 'Account Journal Entry #' . $aje->id . ' created';
        }
        

        // Save Ledger Entries
        $list = $req->getPost();
        foreach ($list as $k=>$v) {
            // Trigger process only when matchin this
            if (!preg_match('/^(\d+)_id$/',$k,$m)) {
                // ignore others
                continue;
            }

            $i = $m[1];

            // Debit or Credit
            $dr = floatval( preg_replace('/[^\d\.]+/',null,$req->getPost($i . '_dr')));
            $cr = floatval( preg_replace('/[^\d\.]+/',null,$req->getPost($i . '_cr')));
            // Skip Empty
            if ( ($cr == 0) && ($dr == 0) ) {
                continue;
            }

            $id = intval($req->getPost($i . '_id'));
            $ale = new AccountLedgerEntry($id);
            $ale->auth_user_id = $cu->id;
            $ale->account_id = $req->getPost($i . '_account_id');
            $ale->account_journal_id = $aje->id;
            // $ale->note = $req->getPost($i . '_note');
            $ale->amount = ($dr > $cr) ? abs($dr) * -1 : abs($cr);
            // Bind to an object
            $ale->link_id = $req->getPost($i . '_link_id');
            $ale->link_to = $req->getPost($i . '_link_to');
            // Save Ledger Entry
            $ale->save();
            // Save Ledger Entry to Wizard
            $awj->addLedgerEntry($ale);

            if ($id) {
                $this->_s->info[] = 'Account Ledger Entry #' . $id . ' updated';
            } else {
                $this->_s->info[] = 'Account Ledger Entry #' . $ale->id . ' created';
            }
        }

        // Memorise the Transaction
        if ($req->getPost('memorise') == 1) {
            $awj->save();
            $this->_s->info[] = 'Account Wizard Memorised';
        }

        // File!
        if ( (!empty($_FILES['file'])) && (Base_File::goodPost($_FILES['file'])) ) {
             $bf = Base_File::copyPost($_FILES['file']);
             $bf->link = $bf->link($aje);
             $bf->save();
             $this->_s->info[] = 'Attachment Created';
        }

        // Commit and Redirect
        // $this->_d->commit();

        if ('Apply' == $_POST['c']) {
            $this->_redirect('/account/transaction?id=' . $aje->id);
        }
        // @todo Determine some redirect logic?  If Session Account go there, else go to the Debit account Journal?
        // Need a Work FLow Processor - that knows an event name where work_flow should happen
        if (!empty($this->_s->ReturnTo)) {
            $this->_redirect($this->_s->ReturnTo);
        }
        $this->_redirect('/account/ledger'); // /' . $this->Session->read('Account.id'));
    }
    
    function batchUpdateAction()
    {
        $this->view->title = array('Accounts','Batch Update');

        switch ($_POST['exec']) {
        case 'Preview':

            $date_alpha = date('Y-m-d',strtotime($_POST['d0']));
            $date_omega = date('Y-m-d',strtotime($_POST['d1']));
    
            // Zend_Debug::dump($_POST);
            // update account_ledger set account_id = 37 where account_journal_id in (select id from account_journal where note ilike '%hattie%') and amount < 0;
            $where = $this->_d->quoteInto(' account_id = ? ',$_POST['account_id']);
    
            // Date Dates
            $where.= " AND (date>='{$date_alpha}' AND date<='{$date_omega}') ";
    
            // Debit or Credit Side?
            if (!empty($_POST['crdr'])) {
                switch ($_POST['crdr']) {
                case 'cr':
                    $where .= ' AND amount > 0 ';
                    break;
                case 'dr':
                    $where .= ' AND amount < 0 ';
                    break;
                }
            }
            if (!empty($_POST['note'])) {
                $where .= $this->_d->quoteInto(' AND note ilike ?',$_POST['note']);
            }
            $order = " date,kind, account_journal_id, amount asc ";
    
    
            $sql = "select * from general_ledger where $where order by $order";
            Zend_Debug::dump($sql);
    
            $this->view->account_id = $_POST['account_id'];
            $this->view->date_alpha = $date_alpha;
            $this->view->date_omega = $date_omega;
            $this->view->LedgerEntryList = $this->_d->fetchAll($sql);
            break;
        }

    }
    

    /**
        Save an Account
    */
    function saveAction()
    {
        $a = new Account($_POST['id']);

        if ('delete' == strtolower($_POST['c'])) {
            $a->delete();
            $this->_s->msg = 'Account #' . $a->id . ' deleted';
            $this->redirect('/account');
        }

        $a->parent_id = $_POST['parent_id'];
        $a->account_tax_line_id = $_POST['account_tax_line_id'];
        $a->code = $_POST['code'];
        $a->kind = $_POST['kind'];
        $a->name = $_POST['name'];

        $a->bank_account = $_POST['bank_account'];
        $a->bank_routing = $_POST['bank_routing'];

        $a->save();

        $this->_s->msg = 'Account #' . $a->id . ' saved';

        $this->redirect('/account');
    }

    /**
        Account Controller View Account

        Displays an Account and allows edit to saveAction()
    */
    function viewAction()
    {
        $this->view->title = array('Accounting','Account','View');
        $this->view->Account = new Account(intval($_GET['id']));
        $this->view->AccountTaxLineList = AccountTaxFormLine::listTaxLines();
    }
}
