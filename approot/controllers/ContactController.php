<?php
/**
    Contact Controller

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

    @todo implement AJAX lookups for Company? Then do 'Name / Company'?
    @todo AJAX used to be for Channel Edit, Address Edit, Lookup Name, Lookup Email
    @todo used to be able to create an account attached to a Company or Vendor
*/


class ContactController extends ImperiumController
{
    /**
        ContactController init
        Sets the ACL for this Controller
    */
    function init()
    {
        $acl = Zend_Registry::get('acl');
        $acl->add( new Zend_Acl_Resource('contact') );
        $acl->allow('user','contact');

        parent::init();

        $sql = 'SELECT name AS id,name FROM base_enum WHERE link = ? ORDER BY sort';
        $this->view->KindList = $this->_d->fetchPairs($sql,array('contact-kind'));
        $this->view->StatusList = $this->_d->fetchPairs($sql,array('contact-status'));

    }

    /**
        List Contacts
        @todo Implement some sort of Reporting Feature Here?
    */
    function indexAction()
    {
        $this->view->Char = $_GET['char'];
        $this->view->Sort = $_GET['sort'];
        $this->view->Page = intval($_GET['page']);

        $sql = $this->_d->select();
        $sql->from('contact');
        // Sort Filter / Order
        switch (strtolower($this->view->Sort)) {

        }

        if (strlen($this->view->Char)) {
            $pat = null;
            if ($this->view->Char == '#') {
                $pat = '^[0-9]';
            } elseif (preg_match('/^\w$/',$this->view->Char)) {
                $pat = '^' . $this->view->Char;
            }
            $sql->where('contact ~* ?',$pat);
            $sql->orWhere('company ~* ?','^'.$pat);
            $sql->orWhere('name ~* ?','^'.$pat);
        }
        // Kind Filter
        $title[] = 'Contacts';
        switch (strtolower($_GET['kind'])) {
        case 'companies':
          $title[] = 'Companies';
          $sql->where('kind = ?','Company');
          break;
        case 'vendors':
          $title[] = 'Vendors';
          $sql->where('kind = ?','Vendor');
          break;
        case 'contacts':
          $title[] = 'Contacts';
          $sql->where('kind = ?','Person');
          break;
        }
        $sql->where('status != ?','Inactive');
        
        switch ($_GET['sort']) {
        case 'name':
            break;
        default:
            $sort = explode(',',$_ENV['contact']['sort']);
        }
        $sql->order($sort);

        //Zend_Debug::dump($sql->assemble());
        //exit;

        $p = Zend_Paginator::factory($sql);
        if (count($p) == 0) {
            $this->view->title = 'No Contacts';
            return(0);
        }
        $p->setCurrentPageNumber($this->view->Page);
        $p->setItemCountPerPage(30);
        $p->setPageRange(10);

        if ($p->count() == 0) {
          $title[] = 'Contacts';
        } else {
          $a_id = $p->getItem(1)->name;
          $z_id = $p->getItem($p->getCurrentItemCount())->name;

          $title[] = sprintf('%s through %s',$a_id,$z_id);
        }
        //$title[] = sprintf('Page %d of %d',$page->getCurrentPageNumber(),$page->count());
        $this->view->title = $title;

        $this->view->Page = $p;

    /*
    $rq = $this->getRequest();
    // Automatic Query?
    if ($a = $rq->getQuery('a')) {
    }

        //$ss->where('kind_id in (100,300)');
        $sql->order(array('contact','company'));
    $sql->limitPage($this->view->Paginator->page,$this->view->Paginator->limit);

    // View!
        $this->view->ContactList = $db->fetchAll($sql);
        $this->view->title = array(
      'Contacts',
      'Page ' . $this->view->Paginator->page . ' of ' . $this->view->Paginator->pmax,
      );
    */
    }

    /**
        ContactController createAction
    */
    function createAction()
    {
        $this->view->title = array('Contact','Create');
        $this->view->Contact = new Contact(null);
        $P = new Contact(intval($_GET['parent']));
        if ($P->id) {
            $this->view->Contact->parent_id = $P->id;
            $this->view->Contact->company = $P->company;
            $this->view->Contact->phone = $P->phone;
            $this->view->Contact->url = $P->url;
        }
        $this->render('view');
    }

    /**
        ContactController labelsAction

        Print Labels
    */
    function labelsAction()
    {
        $db = Zend_Registry::get('db');
        $ss = Zend_Registry::get('session');
        $ss->ReturnTo = '/contact/labels';

        $this->view->title = array('Contact','Labels');

    if (empty($ss->ContactLabelList)) {
      $ss->ContactLabelList = array();
    }

    // Add to List
    if ($this->_request->isPost()) {
      $c = strtolower($this->_request->getPost('c'));
      switch ($c) {
      case 'add selected':
        $buf = $this->_request->getPost('address_id');
        if (is_array($buf)) {
          foreach ($buf as $x=>$v) {
            $ss->ContactLabelList[ intval($v) ] = true;
          }
        }
        break;
      case 'print':
        $l = Zend_Layout::getMvcInstance();
        $l->setLayout('pdf');

        $sql = $db->select();
        $sql->from('contact',array('id as contact_id','contact'));
        $sql->join('contact_address','contact.id=contact_address.contact_id',array('id as address_id','kind','address','city','state','post_code','country'));
        $sql->where('contact_address.id in (?)',array_keys($ss->ContactLabelList));
        //echo '<p>' . $sql->assemble() . '</p>';
        //exit;
        $list = $db->fetchAll($sql);

        $pdf = new PDFLabel($this->_request->getPost('page'),$list);

        $this->view->file = new stdClass();
        $this->view->file->name = 'Labels-' . $this->_request->getPost('page') . '.pdf';
        $this->view->file->data = $pdf->render();
        $this->view->file->size = strlen($this->view->file->data);

        $this->_helper->viewRenderer->setNoRender();//supress auto renderning
        //exit;
        return;
        break;
      }
    }

    // Reset Requested?
    $c = strtolower($this->_request->getQuery('c'));
    if ($c == 'reset') {
      unset($ss->ContactLabelList);
    }
    // Existing Search?
    $q = $_GET['q'];
    if (!empty($q)) {
      $sql = $db->select();
      $sql->from('contact',array('id as contact_id','name'));
      $sql->join('contact_address','contact.id=contact_address.contact_id',array('id as address_id','kind','address'));
      $sql->where('contact.name ilike ?','%' . $q . '%');
      //echo '<p>' . $sql->assemble() . '</p>';
      $this->view->ContactList = $db->fetchAll($sql);
    }

    if (count($ss->ContactLabelList)) {
      $sql = $db->select();
      $sql->from('contact',array('id as contact_id','contact','company','name'));
      $sql->join('contact_address','contact.id=contact_address.contact_id',array('id as address_id','kind','address'));
      $sql->where('contact_address.id in (?)',array_keys($ss->ContactLabelList));
      //echo '<p>' . $sql->assemble() . '</p>';
      $this->view->ContactLabelList = $db->fetchAll($sql);
    }

    }

    /**
        ContactController saveAction

        Save Contact
    */
    function saveAction()
    {
        $id = intval($_GET['c']);
        // Delete Requested?
        if (strtolower($_POST['c']) == 'delete') {

            /*
            $c_so = $this->WorkOrder->findCount('WorkOrder.contact_id=' . $id);
            $c_iv = $this->Invoice->findCount('Invoice.contact_id=' . $id);

            if ( (($c_so == 0) && ($c_iv == 0)) || ($this->Session->read('Contact.delete_confirm')==true) ) {

                $this->Contact->delete($id);

                $this->Session->setFlash('Client deleted');
                $this->Session->delete('Contact');

                $this->redirect(2);
            }

            $this->Session->setFlash("This Contact has $c_so " . Configure::read('WorkOrder.names') . " and $c_iv Invoices, are you sure you want to delete?",'default',null,'error');
            $this->Session->write('Contact.delete_confirm',true);
            $this->redirect('/contacts/view?c=' . $id);
            */

            $c = new Contact($id);
            $c->delete();
            $this->_s->info = 'Contact #' . $id . ' was deleted';
            $this->redirect('/contact');
        }

        $co = new Contact($id);
        // @todo Get Current User, this is BAD!
        $co->auth_user_id = 1;
        $co->account_id  = intval($_POST['account_id']);
        $co->parent_id  = null;
        $co->kind    = $_POST['kind'];
        $co->status  = $_POST['status'];
        $co->contact = $_POST['contact'];
        $co->company = $_POST['company'];
        $co->title = $_POST['title'];
        $co->email = $_POST['email'];
        $co->phone = $_POST['phone'];
        $co->url = $_POST['url'];
        $co->tags = $_POST['tags'];

        $co->save();

        if ($id) {
            $this->_s->info = "Contact #$id saved";
        } else {
            $id = $co->id;
            $this->_s->info = "Contact #$id created";
        }

        $this->redirect('/contact/view?c=' . $id);
    }

    /**
        Pulls Data from the Google Apps for Current User
    */
    function syncAction()
    {
        // All In View #fail
    }

    /**
        ContactController viewAction
    */
    function viewAction()
    {
        $c = new Contact(intval($_GET['c']));
        if (!$c->id) {
            $this->_s->err = 'Contact not found';
            $this->redirect('/contact');
        }

        $this->view->Contact = $c;
        $this->view->ContactList = $this->_d->fetchAll("select * from contact where id != ? AND (parent_id = ? OR company = ?)",array($c->id,$c->id,$c->company));
        $this->view->ContactAddressList = $c->getAddressList();
        $this->view->ContactChannelList = $c->getChannelList();
        $this->view->ContactNoteList = $c->getNotes();
        $this->view->ContactFileList = $c->getFiles();
        // @note what does order by star, status do? Join base_enum?
        $this->view->WorkOrderList = $this->_d->fetchAll("select * from workorder where contact_id={$c->id} order by date desc, id desc");
        $this->view->InvoiceList = $this->_d->fetchAll("select * from invoice where contact_id={$c->id} order by date desc, id desc");

        // Why Pointing this way?
        $this->view->Account = $c->getAccount();

        $this->_s->Contact = $c;

        $this->view->title = array(
            $this->view->Contact->kind,
            $this->view->Contact->name
        );
    }
    
    /**
        Address Action
    */
    function addressAction()
    {
        $this->kind_list = ContactAddress::$kind_list;

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
            // Create
            $this->view->Contact = new Contact($this->_s->Contact->id);
            $this->view->ContactAddress = new ContactAddress(null);
            $this->view->ContactAddress->contact_id = $this->view->Contact->id;
            $this->view->title = array('Contact',$this->view->Contact->name,'Address','New');
            break;
            // $this->render('view');
        case 'save':

            $ca = new ContactAddress($_POST['id']);

            // Copy from Post to Address
            foreach (array('id','contact_id','kind','rcpt','address','city','state','post_code','country') as $x) {
                $ca->$x = $_POST[$x];
            }

            switch (strtolower($_POST['a'])) {
            // Delete Requested?
            case 'delete':
                $ca->delete();
                $this->_s->msg = 'Contact Address ' . $ca->kind . ' was deleted';
                $this->redirect('/contact/view?c=' . $this->_s->Contact->id);
                break;
            case 'save':
                $ca->save();
                $this->redirect('/contact/view?c=' . $this->_s->Contact->id);
            // Use Google to Validate
            case 'validate':
                $ca = $this->addressValidate($ca);
                $this->view->ContactAddress = new ContactAddress($_GET['id']);
                $this->view->Contact = new Contact($this->view->ContactAddress->contact_id);
                $this->view->title = array('Contact',$this->view->Contact->name,'Address',$this->view->ContactAddress->kind);
                return(0);
                break;
            }
            break;
        case 'view':
            $this->view->ContactAddress = new ContactAddress($_GET['id']);
            $this->view->Contact = new Contact($this->view->ContactAddress->contact_id);
            $this->view->title = array('Contact',$this->view->Contact->name,'Address',$this->view->ContactAddress->kind);
            break;
        default:
            throw new Exception("Unhandled Mode: '$mode'",__LINE__);
        }
    }
    
    /**
        @param $a ContactAddress Object
    */
    function addressValidate($ca)
    {
        // Build Address
        //$address = null;
        //foreach (array('address','city','state','post_code','country') as $x) {
        //  $address.= ' ' . $this->_request->getPost($x);
        //}
        // Lookup Address
        $map_url = 'http://maps.google.com/maps/geo?output=xml&key=' . $_ENV['google']['map_key'];
        $http = new Zend_Http_Client($map_url . '&q=' . urlencode($ca));
        $http->setCookieJar(true);
        $http->setHeaders('Accept','application/xml');
        $http->setHeaders('User-Agent','Edoceo Imperium Google GeoCoder 0.2');
        $page = $http->request();
        //Zend_Debug::dump($page);
        // Parse Response
        $xml = simplexml_load_string($page->getBody());
        // Zend_Debug::dump($xml->asXML());
        switch (intval($xml->Response->Status->code)) {
        case 200:
            // Success - Parse Address
            $ad = $xml->Response->Placemark->AddressDetails->Country;
            $ca->address = (string)$ad->AdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
            $ca->city = (string)$ad->AdministrativeArea->Locality->LocalityName;
            $ca->state = (string)$ad->AdministrativeArea->AdministrativeAreaName;
            $ca->post_code = (string)$ad->AdministrativeArea->Locality->PostalCode->PostalCodeNumber;
            $ca->country = (string)$ad->CountryNameCode;
            // Coordinates
            $buf = explode(',',(string)$xml->Response->Placemark->Point->coordinates);
            $ca->lat = $buf[1];
            $ca->lon = $buf[0];
            $this->_s->msg = 'Contact Address #' . $ca->kind . ' was validated';
            break;
        case 610:
            $this->_s->msg = 'Failed to Geocode Error: #' . intval($xml->Response->Status->code);
            break;
        case 620:
            $this->_s->msg = 'Failed to Geocode Error: #' . intval($xml->Response->Status->code) . ': Requests too frequent';
            break;
        }
        return $ca;
    }

    /**
        Handles AJAX requests
    */
    function ajaxAction()
    {
        switch (strtolower($_GET['field'])) {
        case 'google': // Load the Google Data

            $GA = new Google_Account($_ENV['google']['username'],$_ENV['google']['password']);
            $gdf = $GA->getContact($this->_s->Contact->google_contact_id);
            echo '<pre> ' . html(print_r($gdf,true)) . '</pre>';
            //$xml = simplexml_load_string( $gdf->getXML() );
            // echo '<pre> ' . html(str_replace('><',">\n<",$xml->asXML())) . '</pre>';
            // exit(0);
            exit(0); // return(true);
        }

        // Query Handling
        $q = trim($_GET['term']);
        if (strlen($q) == 1) {
            $q = '^' . $q;
        }

        $s = $this->_d->select();
        $s->from('contact',array('id','name as result','name as label','email','phone','contact','company'));
        switch (strtolower($_GET['field'])) {
        case 'company':
            $s->where('company ~* ?',$q);
            break;
        case 'email':
            $s = $this->_d->select();
            $s->from('contact',array('email as result','name as label'));
            $s->where('contact ~* ?',$q);
            $s->orWhere('company ~* ?',$q);
            $s->orWhere('name ~* ?',$q);
            $s->distinct();
            break;
        case 'kind':
            $s = $this->_d->select();
            $s->from('contact',array('kind as result','kind as label'));
            $s->distinct();
            break;
        case 'status':
            $s = $this->_d->select();
            $s->distinct();
            $s->from('contact',array('status as result','status as label'));
            break;
        default:
            $s->where('contact ~* ?',$q);
            $s->orWhere('company ~* ?',$q);
            $s->orWhere('name ~* ?',$q);
            $s->orWhere('email ~* ?',$q);
            break;
        }
        $r = $this->_d->fetchAll($s);
        echo json_encode($r);
        return(true);
    }
}