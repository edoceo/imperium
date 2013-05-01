<?php
/**
    ImperiumController

    @copyright	2001 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/


class ImperiumController extends Zend_Controller_Action
{
	private $_logger = null;
	protected $_a; // Auth Instance
	protected $_d; // Database Handle
	protected $_s; // Session
	protected $_u; // User
	/**
		ImperiumController init()
	*/
	function init()
    {
		parent::init();

		$this->_a = Zend_Auth::getInstance();
		$this->_d = Zend_Registry::get('db');
		$this->_s = Zend_Registry::get('session');

        $this->view->controller = strtolower($this->_request->getControllerName());
        $this->view->action = strtolower($this->_request->getActionName());

        $this->view->appurl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->view->base = Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->checkAuth();

        //$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        //$viewRenderer->setViewSuffix('iphone.phtml');
        //Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        //$viewHelper = $this->_helper->getHelper('view');
        //$viewHelper->setViewSuffix('iphone.phtml');
    }

    /**
        For AJAX Requests
    */
    function preDispatch()
    {
        // Ajax?
        $x = strtolower(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null);
        if ($x == 'xmlhttprequest') {
            $l = Zend_Layout::getMvcInstance();
            $l->setLayout('ajax');
            if (preg_match('/application\/json/i',$_SERVER['HTTP_ACCEPT'])) {
                $this->getHelper('viewRenderer')->setNoRender();
                $l->setLayout('json');
            }
        }
    }

    /**
        
    */
    function checkAuth()
    {
		// Now Determine the User Id
        $ss = Zend_Registry::get('session');
        $auth = Zend_Auth::getInstance();
        //Zend_Debug::dump($auth->getStorage()->read());
        $acl = Zend_Registry::get('acl');

        $p = $this->_getAllParams();
        $c = strtolower($p['controller']);
        $a = strtolower($p['action']);

        // Add Resource if Not Defined
        if ($acl->has($c)==false) {
            $acl->add( new Zend_Acl_Resource($c) );
            $acl->allow('user',$c);
        }

        // If Anonymous is Allowed (special name 'null')
        if ($acl->isAllowed('null',$c,$a)) {
            return true;
        }
    // Now Require Identity
    if ($auth->hasIdentity() === false) {
      $ss->fail[] = 'Identity Required';
      // @todo Send them to login and redirect back to where they wanted to go?
      //die('Identity Required');
      $this->redirect('/login');
    }

    // Is User Allowed?
    $user = $auth->getIdentity();

    if ($acl->isAllowed($user->username,$c,$a)) {
      return true;
    }

    // Default Deny Access
    $ss->fail[] = 'User Access Denied to: ' . $c . '/' . $a;
    $this->redirect('/');
  }

    // @deprecated - remove!
	function redirect($to)
	{

		// HOST
		if (!preg_match('!^https?:\/\/!',$to)) {

            $host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :
                                ( isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ($_SERVER['SERVER_ADDR']) ) );

            $url = 'http://' . $host;
            // if ($_SERVER['SERVER_PORT'] != 80) {
            // 	$url = 'http://' . $host . ':' . $_SERVER['SERVER_PORT'];
            // }

            // SSL?
            if (isset($_SERVER['HTTPS'])) {
                $url = 'https://' . $host;
                if ($_SERVER['SERVER_PORT'] != 443) {
                    $url = 'http://' . $host . ':' . $_SERVER['SERVER_PORT'];
                }
            }

            // USER/PASS?
            $url .= Zend_Controller_Front::getInstance()->getBaseUrl();

            $url .= $to;

            $res = $this->getResponse();
            $res->clearBody();
        } else {
            $url = $to;
        }

		header('HTTP/1.1 302 See Other');
		header('Location: ' . $url);
		// Zend_Debug::dump($host);
		// Zend_Debug::dump($url);
		// Zend_Debug::dump($res);
		exit;
		// $res->setRedirect($url);
		// $res->sendResponse();
		// exit;
	}

  /**
    ImperiumController readPaginator

    Reads "Paginator" type inputs and merges them into the view->Paginator
  */
  function readPaginator()
  {
    $rq = $this->getRequest();

    if (empty($this->view->Paginator)) {
      $this->view->Paginator = new stdClass();
    }

    $this->view->Paginator->page = $rq->getQuery('p');
    $this->view->Paginator->sort = $rq->getQuery('s');

    $this->view->Paginator->limit  = intval($rq->getQuery('l'));
    if (intval($this->view->Paginator->limit) == 0) {
      $this->view->Paginator->limit = 50;
    }

    $this->view->Paginator->pmax = ceil($this->view->Paginator->count / $this->view->Paginator->limit);


    $this->view->Paginator->page_prev = 0;
    $this->view->Paginator->page_next = 0;

    if ($this->view->Paginator->page > 1) {
      $this->view->Paginator->page_prev = $this->view->Paginator->page - 1;
    }

    if ($this->view->Paginator->page < $this->view->Paginator->pmax) {
      $this->view->Paginator->page_next = $this->view->Paginator->page + 1;
    }

    for ($i = 1; $i <= $this->view->Paginator->pmax; $i++) {
      $this->view->Paginator->pages[] = $i;
    }

  }
}