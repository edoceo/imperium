<?php
/**
    EdoceoUser interfaces with the auth_user table

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

class ImperiumUser extends ImperiumBase
{
    protected $_table = 'auth_user';

    //const OBJECT_TYPE = 6564;

    /**
        ImperiumUser login

        @return null|ImperiumUser
        @static
    */
  
    /**
        Read/Write Preference to Database
    */
    function preference($name=null,$data=null)
    {
        try {
            $db = Zend_Registry::get('db');
            if ($data !== null) {
                // Write Operation
                $data = serialize($data);
                $x = $db->fetchOne('SELECT name FROM auth_user_pref WHERE uid = ? AND name = ?',array($this->id,$name));
                if ($x) {
                    $rec = array('data'=>$data);
                    $db->update('auth_user_pref',$rec,array('id'=>$this->id,'name'=>$name));
                } else {
                    $rec = array('uid'=>$this->id,'name'=>$name,'data'=>$data);
                    $db->insert('auth_user_pref',$rec);
                }
            } else {
                // Read Operation
                //echo "'SELECT data FROM auth_user_pref WHERE uid = $1 AND name = $2',array($this->id,$name)\n";
                $data = $db->fetchOne('SELECT data FROM auth_user_pref WHERE uid = ? AND name = ?',array($this->id,$name));
                return @unserialize($data);
            }
        } catch (Exception $e) {
            return null;
        }
    }
  static function findByUsername($u)
  {
        $db = Zend_Registry::get('db');
        $sql = sprintf("select * from auth_user where username = '%s'",pg_escape_string($u));
        $rs = $db->fetchRow($sql);
    if ($rs) {
      $x = new ImperiumUser($x);
      return $x;
    } 
    return null;
  }
  
  /**
    ImperiumUser save
  */
  function save()
  {
        if (!preg_match('/^[0-9a-f]{40}$/',$this->password)) {
            $this->password = self::makePassword($this->username,$this->password);
        }
    
    if ( ($this->active != 'f') || ($this->active != 't') ) {
      $this->active = 't';
    }
    
    parent::save();

  }
  /**
    ImperiumUser makePassword
  */
    static function makePassword($u,$p)
    {
        $x = sha1(trim($u) . ' . ' . trim($p));
        return $x;
    }
}
