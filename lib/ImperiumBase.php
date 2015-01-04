<?php
/**
    ImperiumBase - Our Base object, all majors implement this

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013

*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class ImperiumBase implements \ArrayAccess
{
	protected $_data; // Object Data
    protected $_diff = true;  // Do Diff on Save
    protected $_diff_list = array(); // Array of Changed Properties
    protected $_table;
    protected $_sequence;

    /**
        ImperiumBase Model Constructor
    */
    function __construct($x=null)
    {
        // Detect Sequence Name
        if (strlen($this->_sequence) == 0) {
            $this->_sequence = $this->_table . '_id_seq';
        }

        // Detect Object Properties from Table if not Specified
//        if (!isset($this->_properties)) {
//            $this->_properties = array();
//            $d = $this->_db->describeTable($this->_table);
//            foreach ($d as $k=>$v) {
//                $this->_properties[] = $k;
//                if (!isset($this->$k)) {
//                    $this->$k = null;
//                }
//            }
//        }

        // Do Nothing
        if (is_null($x)) {
            return;
        }

        // Load Database Record
        if ((is_numeric($x)) && (intval($x)>0)) {
            $sql = sprintf("select * from \"%s\" where id='%d'",$this->_table,intval($x));
            $x = SQL::fetch_row($sql);
            if (is_object($x)) {
                $p = get_object_vars($x);
                foreach ($p as $k=>$v) {
                    // $this->$k = stripslashes($x->$k);
                    $this->_data[$k] = stripslashes($x->$k);
                }
            }
            if (is_array($x)) {
            	$this->_data = $x;
            }
        }

        // Copy properties from Given object to me!
        if (is_object($x)) {
            $p = get_object_vars($x);
            foreach ($p as $k=>$v) {
                $this->_data[$k] = $x->$k;
            }
            return;
        }

        // Copy Properties from Array Keys
        if (is_array($x)) {
			foreach ($x as $k=>$v) {
				$this->_data[$k] = $v;
			}
        }
    }

    /**
        AppModel delete
        Destroy this object and it's index
    */
    function delete()
    {
        SQL::query("delete from {$this->_table} where id = {$this->_data['id']}");
    }

    /**
        AppModel Save
        @todo use the _data interface, check for dirty
    */
    function save()
    {
        // Set Sane Defaults
        // if (empty($this->_data['auth_user_id'])) {
        //     $this->_data['auth_user_id'] = $_SESSION['uid']; // $cu->id;
        // }
        // if (empty($this->_data['hash'])) $this->_data['hash'] = $this->hash();

        // Set some Fields to Null
        // if (empty($this->_data['link_to'])) $this->_data['link_to'] = null;
        // if (empty($this->_data['link_id'])) $this->_data['link_id'] = null;
        if (isset($this->_data['link_to'])) {
        	if (empty($this->_data['link_to'])) {
        		$this->_data['link_to'] = null;
			}
		}
        if (isset($this->_data['link_id'])) {
        	if (empty($this->_data['link_id'])) {
        		$this->_data['link_id'] = null;
			}
		}

        // Convert to Array
        // $rec = array();
        // foreach ($this->_properties as $k) {
        //     if (empty($this->$k)) {
        //         $rec[$k] = new Zend_Db_Expr('null');
        //     } else {
        //         $rec[$k] = $this->$k;
        //     }
        // }
        // unset($rec['id']);
        $rec = array();
        foreach ($this->_data as $k=>$v) {
        	$rec[$k] = $v;
        }

        // @todo implement ts_vector
        // update vendors set fulltext = to_tsvector('english',name || ' ' || coalesce(description,''))
        if (isset($this->_properties['fulltext'])) {
            // Build Full Text
            // foreach ($this->_properties as $k=>$v) {
            //
            // }
        }

        if ($this->_data['id']) {
            // if ($this->_diff) Base_Diff::diff($this);
            SQL::update($this->_table,$rec,"id={$this->_data['id']}");
        } else {
            $this->_data['id'] = SQL::insert($this->_table, $rec);
            if (intval($this->_data['id'])==0) {
                Radix::dump($this);
                Radix::dump(SQL::lastError());
                Radix::trace('Unexpected error saving: ' . get_class($this));
            }
            // if ($this->_diff) Base_Diff::diff($this);
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

        $c = get_class($o);
        $c = str_replace('\\', '/', $c);
        $c = basename($c);
        $c = strtolower($c);

        return sprintf('%s:%d', $c, intval($o['id']));
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

        // $d = Zend_Registry::get('db');
        // $s = $d->select();
        // $s->from('base_file');
        // $s->where('link = ?', $this->link() );
        // $s->order('name');
        // $r = $d->fetch_all($s);
        // return $r;
        $sql = 'SELECT * FROM base_file WHERE link = ? ORDER BY name';
        $arg = array($this->link());
        $ret = SQL::fetch_all($sql, $arg);
        return $ret;
    }

    /**
        findHistory() - returns history of current Invoice
    */
    function getHistory()
    {
        if (intval($this->id)==0) {
            return null;
        }

        // $db = Zend_Registry::get('db');
        // $sql = sprintf('select * from object_history where link_to=%d and link_id=%d order by cts desc',$ot,$id);
        // $rs = $db->fetch_all($sql);
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
        $r = SQL::fetch_all($s);
        return $r;
    }

    /**
        ImperiumBase getNotes()
    */
    function getNotes()
    {
        if (intval($this['id'])==0) {
            return null;
        }

        $sql = 'SELECT * FROM base_note WHERE link = ? ORDER BY name';
        $arg = array($this->link());
        $ret = SQL::fetch_all($sql, $arg);
		return $ret;
	}

	/**
		ImperiumBase getObject
        @todo Kill This
	*/
	// static function getObject($type,$id)
	// {
	// $x = new $type($id);
	// return $x;
	// }
	/**
		getObjectType
		@param $o is the Object, ObjectName or ObjectInteger
	*/
	static function getObjectType($o,$r=null)
	{
		$arg = array();
		$sql = 'SELECT * FROM base_object ';
		// Convert Object to String to use String Comp below
        if (is_object($o)) {
			$o = strtolower(get_class($o));
		}

		if (intval($o) > 0) {
			$sql.= ' WHERE id = ?';
			$arg[] = intval($o);
			if (empty($r)) {
				$r = 'name';
			}
		} elseif (is_string($o)) {
			$o = strtolower($o);
			$sql.= ' WHERE stub = ? OR path = ? OR link = ? ';
			$arg[] = $o;
			$arg[] = $o;
			$arg[] = $o;
			if (empty($r)) {
				$r = 'id';
			}
		}

		// Find and Return Value
		$ot = SQL::fetch_row($sql);
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

    /*
		Array Accessors
    */
	/**
		@return Boolean
	*/
	public function offsetExists($k) { return isset($this->_data[$k]); }

	/**
		@return Data
	*/
	public function offsetGet($k) { return $this->_data[$k]; }

	/**
		@return void
	*/
	public function offsetSet($k, $v) {
		if ($v != $this->_data[$k]) {
			$this->_diff_list[$k] = true;
		}
		$this->_data[$k] = $v;
	}

	/**
		@return void
	*/
	public function offsetUnset($k) { unset($this->_data[$k]); }
}

