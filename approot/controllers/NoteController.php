<?php
/**
    @file 
    @brief NoteController

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

class NoteController extends ImperiumController
{
    /**
        NoteController init
    */
    function init()
    {
        $acl = Zend_Registry::get('acl');
        $acl->add( new Zend_Acl_Resource('note') );
        $acl->allow('user','note');
        parent::init();
    }

    /**
        Display a list of notes
    */
    function indexAction()
    {
        $sql = $this->_d->select();
        $sql->from('base_note',array('id','name','cts'));
        $sql->where('link is null');
        $sql->order(array('name'));

        $page = Zend_Paginator::factory($sql);
        $page->setCurrentPageNumber($this->_request->getParam('page'));
        $page->setItemCountPerPage(30);
        $page->setPageRange(10);

        if ($page->getTotalItemCount()) {
            $a_id = $page->getItem(1)->id;
            $z_id = $page->getItem($page->getCurrentItemCount())->id;

            $title = array();
            $title[] = sprintf('Notes %d through %d',$a_id,$z_id);
            $title[] = sprintf('Page %d of %d',$page->getCurrentPageNumber(),$page->count());
            $this->view->title = $title;
        } else {
            $this->view->title = 'Notes';
            $this->_s->info[] = 'There are no notes created';
        }

        $this->view->Page = $page;

    }

    /**
        NoteController createAction
    */
    function createAction()
    {
        $this->view->title = array('Note','Create');
        $n = new Base_Note();

        // Linked to Object?
        if ($_GET['l'] == 'r') {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            parse_str($url['query'],$arg);
            $_GET = $arg;
        }

        if (!empty($_GET['c'])) {
            $n->link = sprintf('contact:%d',$_GET['c']);
        }
        if (!empty($_GET['i'])) {
            $n->link = sprintf('invoice:%d',$_GET['i']);
        }
        if (!empty($_GET['w'])) {
            $n->link = sprintf('workorder:%d',$_GET['w']);
        }

        if (!empty($n->link)) {
            $this->view->title = array('Note','Create',' #' . $n->link);
        }

        $this->view->Note = $n;
        $this->render('edit');
    }

    /**
        NoteController viewAction
    */
    function editAction()
    {
        // $this->Session->write('Note.id',$id);
        $id = intval($_GET['id']);
        $n = new Base_Note($id);
        $this->view->title = array('Note','Edit',$n->name);
        $this->view->Note = $n;
    }

    /**
        List the Notes
    */
    function listAction()
    {
        $t = new Zend_Db_Table(array('name'=>'base_note'));
        $s = $t->select();

        $w = $this->_request->getParam('link');
        if (!empty($w)) {
            $s->where('link = ?',$w);
        }
        $s->order(array('name'));

        $this->view->NoteList = $t->fetchAll($s);

        $this->render('index');

    }

    /**
        save a note
    */
    function saveAction()
    {
        $req = $this->getRequest();

        $id = intval($_POST['id']);
        $n = new Base_Note($id);

        switch (strtolower($_POST['c'])) {
        case 'delete':
            // @todo Check if IsAllowed
            $back_page = '/';
            $n->delete();
            $this->_s->info[] = 'Note #' . $id . ' was deleted';
            break;
        case 'edit':
            $this->_redirect('/note/edit?id=' . $n->id);
            break;
        case 'save':
            $n->kind = $req->getPost('kind',$n->kind);
            // Append on Conversation
            if ($n->kind == 'Conversation') {
                $data = trim($n->data);
                $data.= "\n---\n# " . date('D \t\h\e jS \o\f F Y \a\t H:i') . "\n";
                $data.= $req->getPost('data');
                $n->data = trim($data);
            } else {
                $n->data = trim($_POST['data']);
            }
            $x = str_replace(array('<br>','<br/>','<br />'),"\n",$n->data);
            $x = trim(strip_tags($x));
            $n->name = strtok($x,"\n");
            $n->link = $_POST['link'];
            $n->save();

            if ($id) {
                $this->_s->info[] = "Note #$id saved";
            } else {
                $id = $n->id;
                $this->_s->info[] = "Note #$id created";
            }
            $back_page = '/note/view?id=' . $n->id;
        }

        // Redirect Out
        if (!empty($n->link)) {
            if (preg_match('/(contact|invoice|workorder):(\d+)/',$n->link,$m)) {
                $back_page = '/' . $m[1] . '/view?' . substr($m[1],0,1) . '=' . $m[2];
            }
        }
        $this->_redirect($back_page);
    }

    /**
        NoteController viewAction
    */
    function viewAction()
    {
        $id = intval($_GET['id']);
        if (empty($id)) {
            throw new Zend_Controller_Action_Exception('Bad Request', 400);
        }

        $n = new Base_Note($id);
        if (empty($n->id)) {
            throw new Zend_Controller_Action_Exception('Note Not Found', 404);
        }

        $this->view->title = array('Note','View',$n->name);
        $this->view->Note = $n;
    }
}
