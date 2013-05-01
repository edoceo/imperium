<?php
/**
    ImperiumBase - Our Base object, all majors implement this

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

*/

class ImperiumBase
{
    protected $_db; // Database Reference
    protected $_diff = true;  // Do Diff on Save
    protected $_table;
    protected $_sequence;

    public $id;
    public $auth_user_id;

    /**
        ImperiumBase Model Constructor
    */
    function __construct($x=null)
    {
        $this->_db = Zend_Registry::get('db');
        // Detect Sequence Name
        if (strlen($this->_sequence) == 0) {
            $this->_sequence = $this->_table . '_id_seq';
        }

        // Detect Object Properties from Table if not Specified
        if (!isset($this->_properties)) {
            $this->_properties = array();
            $d = $this->_db->describeTable($this->_table);
            foreach ($d as $k=>$v) {
                $this->_properties[] = $k;
                if (!isset($this->$k)) {
                    $this->$k = null;
                }
            }
        }

        // Do Nothing
        if (is_null($x)) {
            return;
        }

        // Load Database Record
        if ((is_numeric($x)) && (intval($x)>0)) {
            $sql = sprintf("select * from \"%s\" where id='%d'",$this->_table,intval($x));
            $x = $this->_db->fetchRow($sql);
            if (is_object($x)) {
                $p = get_object_vars($x);
                foreach ($p as $k=>$v) {
                    $this->$k = stripslashes($x->$k);
                }
            }
        }

        // Copy properties from Given object to me!
        if (is_object($x)) {
            $p = get_object_vars($x);
            foreach ($p as $k=>$v) {
                $this->$k = $x->$k;
            }
            return;
        }

        // Copy Properties from Array Keys
        if (is_array($x)) {
            foreach ($x as $k=>$v) {
                $this->$k = $x[$k];
            }
            return;
        }
    }

    /**
        AppModel delete
        Destroy this object and it's index
    */
    function delete()
    {
        $this->_db->query("delete from $this->_table where id=$this->id");
    }
    /**
        AppModel Save
    */
    function save()
    {
        // Set Sane Defaults
        if (empty($this->auth_user_id)) {
            $cu = Zend_Auth::getInstance()->getIdentity();
            $this->auth_user_id = $cu->id;
        }
        if (empty($this->hash)) $this->hash = $this->hash();
        // Set some Fields to Null
        if (empty($this->link_to)) $this->link_to = new Zend_Db_Expr('null');
        if (empty($this->link_id)) $this->link_id = new Zend_Db_Expr('null');

        // Convert to Array
        $rec = array();
        foreach ($this->_properties as $k) {
            if (empty($this->$k)) {
                $rec[$k] = new Zend_Db_Expr('null');
            } else {
                $rec[$k] = $this->$k;
            }
        }
        unset($rec['id']);

        // @todo implement ts_vector
        // update vendors set fulltext = to_tsvector('english',name || ' ' || coalesce(description,''))
        if (isset($this->_properties['fulltext'])) {
            // Build Full Text
            // foreach ($this->_properties as $k=>$v) {
            //
            // }
        }

        if ($this->id) {
            if ($this->_diff) Base_Diff::diff($this);
            $this->_db->update($this->_table,$rec,"id={$this->id}");
        } else {
            $this->_db->insert($this->_table,$rec);
            $this->id = $this->_db->lastSequenceId($this->_sequence);
            if (intval($this->id)==0) {
                Zend_Debug::dump($this);
                die('Unexpected error saving: ' . get_class($this));
            }
            if ($this->_diff) Base_Diff::diff($this);

        }
    }
    /**
        Generates a Hash for this object
    */
    function hash()
    {
        return sha1(time().serialize($this));
    }
    /**
        Return a Text pointer to this Object
    */
    function link($o=null)
    {
        if ($o == null) {
            $o = $this;
        }
        return sprintf('%s:%d',strtolower(get_class($o)),intval($o->id));
    }
    /**
    Index the fields for Lucene
    */
    function indexFields()
    {
        if (empty($this->name)) {
            return null;
        }

        $set = array();
        // Field is not tokenized nor indexed, but is stored in the index.

        // Field is not tokenized, but is indexed and stored within the index
        $set[] = Zend_Search_Lucene_Field::Keyword('link','/' . strtolower(get_class($this)) . '/view/' . $this->id);
        $set[] = Zend_Search_Lucene_Field::Keyword('name',$this->name);
        // $set[] = Zend_Search_Lucene_Field::Keyword('title',$this->name);

        // Field is tokenized and indexed, but is not stored in the index.
        $content = null;
        foreach ($this->_properties as $p) {
            $x = substr($p,-2);
            if ( ($x=='id') || ($x=='ts') ) {
                continue;
            }
            $content.= $this->$p . ' ';
        }
        $set[] = Zend_Search_Lucene_Field::UnStored('text',trim($content));
        return $set;
    }

    // func: lock()
    function lock()
    {
        print_r($this);
        echo "Doing Lock\n";
    }

    // func: unlock();
    function unlock()
    {
        print_r($this);
        echo "Unlock\n";
    }

    /**
        ImperiumBase getNotes()
    */
    function getFiles()
    {
        if (intval($this->id)==0) {
            return null;
        }

        $d = Zend_Registry::get('db');
        $s = $d->select();
        $s->from('base_file');
        $s->where('link = ?', $this->link() );
        $s->order('name');
        $r = $d->fetchAll($s);
        return $r;
    }
    /**
        findHistory() - returns history of current Invoice
    */
    function getHistory()
    {
        if (intval($this->id)==0) {
            return null;
        }

        $d = Zend_Registry::get('db');

        // $db = Zend_Registry::get('db');
        // $sql = sprintf('select * from object_history where link_to=%d and link_id=%d order by cts desc',$ot,$id);
        // $rs = $db->fetchAll($sql);
        // return $rs;

        $s = 'select id,auth_user_id,ctime,link,f,v0,v1 from base_diff ';
        $s.= sprintf(' where link = \'%s\' ',$this->link() );
        // $s.= ' order by ctime ';
        $s.= ' union all ';
        $s.= 'select id,auth_user_id,cts,link,\'-None-\',message,null from object_history ';
        $s.= sprintf(' where link = \'%s\' ',$this->link() );
        $s.= ' order by ctime desc ';
       
        // $s = $d->select();
        // $s->from('object_history');
        // $s->where('link = ?', $this->link() );
        // $s->order('cts');
        $r = $d->fetchAll($s);
        return $r;
    }
    /**
        ImperiumBase getNotes()
    */
    function getNotes()
    {
        if (intval($this->id)==0) {
            return null;
        }
        $d = Zend_Registry::get('db');
        $s = $d->select();
        $s->from('base_note');
        $s->where('link = ?', $this->link() );
        $s->order('name');
        $r = $d->fetchAll($s);
        return $r;

    }
  /**
    ImperiumBase getObject

        @todo Kill This
  */
  static function getObject($type,$id)
  {
    $x = new $type($id);
    return $x;
  }
  /**
    getObjectType
    @param $o is the Object, ObjectName or ObjectInteger
  */
  static function getObjectType($o,$r=null)
  {
    $db = Zend_Registry::get('db');
    $sql = $db->select();
    $sql->from('base_object');
    // Convert Object to String to use String Comp below
        if (is_object($o)) {
      $o = strtolower(get_class($o));
        }

    if (intval($o) > 0) {
      $sql->where('id = ?',intval($o));
      if (empty($r)) {
        $r = 'name';
      }
    } elseif (is_string($o)) {
      $o = strtolower($o);
      $sql->where(' stub = ?',$o);
      $sql->orWhere(' path = ?',$o);
      $sql->orWhere(' link = ?',$o);
      if (empty($r)) {
        $r = 'id';
      }
    }
    // Find and Return Value
    $ot = $db->fetchRow($sql);
    if ($ot) {
      switch ($r) {
      case 'id':
        return $ot->id;
      case 'link':
        return $ot->link;
      case 'name':
        return $ot->name;
      case 'path':
        return $ot->path;
      case 'stub':
        return $ot->stub;
      default:
        return $ot;
      }
    }
    //throw new Exception('Cannot Handle Object Type ' . get_class($o) . '/' . $r . '[' . $sql->assemble() . ']');

    return null;
    }
    
    /**
        Flag Handling
    */
    function delFlag($f) { $this->flag = (intval($this->flag) & ~$f); }
    function hasFlag($f) { return (intval($this->flag) & $f); }
    function getFlag($fmt='d')
    {
        switch($fmt) {
        case 'b': // Binary
            return sprintf('0b%032s',decbin($this->flag));
        case 'd': // Decimal
            return sprintf('%u',$this->flag);
        case 's': // String
            $rc = new ReflectionClass($this);
            $set = $rc->getConstants();
            $ret = array();
            foreach ($set as $k=>$v) {
              if ((preg_match('/^FLAG_/',$k)) && ($this->hasFlag($v))) {
                $ret[] = $k;
              }
            }
            return implode(',',$ret);
        case 'x': // Hex
            return sprintf('0x%08x',$this->flag);
        }
    }
    function setFlag($f) { $this->flag = (intval($this->flag) | $f); }
}

