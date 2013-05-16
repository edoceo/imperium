<?php
/**

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 2003.01
*/

class IndexController extends ImperiumController
{
    /**
        IndexController init
    */
    function init()
    {
        $acl = Zend_Registry::get('acl');
        $acl->allow('null','index',array('hash','login','logout'));
        //$acl->allow('user','index',array('index','search','search-rebuild'));
        $acl->allow('user','index'); // ,array('index','search','search-rebuild'));
        parent::init();
    }
    /**
        IndexController indexAction
    */
    public function indexAction()
    {
        $this->view->title = 'Dashboard: ' . date('Y-m-d');

        $sql_w = 'SELECT workorder.*, b.name AS contact_name ';
        $sql_w.= ' FROM workorder ';
        $sql_w.= ' JOIN contact b ON workorder.contact_id=b.id ';
        $sql_w.= ' JOIN base_enum ON workorder.kind = base_enum.name ';
        $sql_w.= " WHERE workorder.status in ('Active','Pending') ";
        $sql_w.= ' ORDER BY base_enum.sort, workorder.status, workorder.date desc, workorder.id DESC';

        // Pending Work Order Items
        $sql_woi = 'SELECT workorder.*, contact.name AS contact_name ';
        $sql_woi.= ' FROM workorder ';
        $sql_woi.= ' JOIN contact on workorder.contact_id = contact.id ';
        $sql_woi.= ' JOIN workorder_item ON workorder.id = workorder_item.workorder_id ';
        $sql_woi.= ' JOIN base_enum ON workorder.kind = base_enum.name ';
        $sql_woi.= " WHERE workorder.status = 'Active' AND workorder_item.status = 'Pending' ";
        $sql_woi.= ' ORDER BY base_enum.sort, workorder.status, workorder.date desc, workorder.id DESC';

        $this->view->data = array(
            'Active Timers' => array(
                'css' => 'index_pack',
                'list' => Timer::activeList(),
                'view' => '../elements/timer-list.phtml'),
            'Active Tasks' => array(
                'css' => 'index_pack',
                'list' => Base_Task::activeList(),
                'view' => '../elements/task-list.phtml'),
            'Pending Work Order Items' => array(
                'css' => 'index_pack',
                'list' => $this->_d->fetchAll($sql_woi),
                'view' => '../elements/workorder-list.phtml'),
            'Active Work Orders' => array(
                'css' => 'index_list',
                'list' => $this->_d->fetchAll($sql_w),
                'view' => '../elements/workorder-list.phtml'),
            'Active Invoices' => array(
                'css' => 'index_list',
                'list' => $this->_d->fetchAll("select invoice.*,b.name as contact_name from invoice join contact b on invoice.contact_id=b.id where ((invoice.paid_amount is null or invoice.paid_amount < invoice.bill_amount) and invoice.status in ('Active','Sent','Hawk')) order by invoice.date desc, invoice.id desc"),
                'view' => '../elements/invoice-list.phtml'),
        );
        /*
        $this->paginate = array(
        'WorkOrder' => array(
            'conditions' => 'WorkOrder.status_id in (100,200)',
            'limit'=>50,
            'order' => array('WorkOrder.id'=>'desc','WorkOrder.date'=>'desc'),
            'page'=>1,
            'recursive'=>1,
            ),
        'Invoice' => array(
            'conditions' => '((Invoice.paid_amount is null or Invoice.paid_amount<Invoice.bill_amount) and Invoice.status_id in (100,200))',
            'limit'=>50,
            'order' => array('Invoice.date'=>'desc'),
            'page'=>1,
            'recursive'=>0,
            ),
        );
        */
        unset($this->_s->SearchTerm);
        $this->_s->ReturnTo = '/';
    }
    /**
        Handle an inbound hash link
    */
    function hashAction()
    {

        // From Key Controller Init
        $acl = Zend_Registry::get('acl');
        // $acl->add( new Zend_Acl_Resource('key') );

        //$layout = Zend_Layout::getMvcInstance();
        //$layout->setLayout('public');
        //$p = $this->_getAllParams();
        $ah = Auth_Hash::find($this->_request->getParam('hash')); // ['action']);
        if (empty($ah)) {
            throw new Exception('Access Denied',__LINE__);
        }
        //Zend_Debug::dump($ah);
        // Access to this Key Hash
        //$acl->allow('null','key',$this->__key->hash);
        // Access to the Resource
        //$r = strtolower($this->__key->link_to);
        $acl->add( new Zend_Acl_Resource(strtok($ah->link,':')) );
        $acl->allow('null',strtok($ah->link,':'),'pdf');
        //} else {
        //// @todo check if the length is not right and if so then make specific error?
        //$ss = Zend_Registry::get('session');
        //$ss->err[] = 'Invalid Resource Requested';
        //$this->redirect('/error');
        //}
        //parent::init();

        // From KeyController __call()
        // Add History
        $ip = $_SERVER['REMOTE_ADDR'];
        $hn = gethostbyaddr($ip);
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $msg = "Fetched from $ip";
        if (strlen($hn)) {
          $msg.= " ($hn)";
        }
        if (strlen($ua)) {
          $msg.= " with: " . $ua;
        }

        $ao = Base_Link::load( $ah->link ); // ImperiumBase::getObject($this->__key->link_to,$this->__key->link_id);
        Base_Diff::note($ao,$msg);

        // Forward this to Object Controller
        list($type,$pkid) = explode(':',$ah->link);
        switch (strtolower($type)) {
        case 'invoice':
            $_GET['i'] = $pkid;
            break;
        case 'workorder':
            $_GET['w'] = $pkid;
            break;
        }
        $this->_forward('pdf',$type,null,array('id'=>$pkid));
    }
    /**
        Login Action
    */
    function loginAction()
    {
        $db = Zend_Registry::get('db');
        $ss = Zend_Registry::get('session');

        $this->view->title = 'Login';

        $req = $this->getRequest();
        if ($req->isPost()) {

            $auth = Zend_Auth::getInstance();
            $res = $auth->authenticate( new App_Auth($req->getPost('username'),$req->getPost('password')) );
            if ($res->isValid()) {
                $this->redirect('/');
            } else {
                $ss->fail = $res->getMessages();
                $this->redirect('/login');
            }
        }
    }
    /**
        Imperium Logout Function
    */
    function logoutAction()
    {
        // Destroy Session
        Zend_Registry::set('session',null);
        Zend_Session::destroy(true);
        $this->redirect('/login');
    }
}
