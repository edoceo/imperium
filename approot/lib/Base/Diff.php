<?php
/**
    Logs a Difference of Fields between two Objects

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
*/

class Base_Diff extends Zend_Db_Table
{
    protected $_table = 'base_diff';
    protected static $_skip_list = array(
        'atime',
        'ats',
        'ctime',
        'cts',
    );

    /**
        Difference between two Objects
    */
    static function diff($o0,$o1=null)
    {
        $d = Zend_Registry::get('db');
        $t = new Zend_Db_Table(array('name'=>'base_diff'));
        // if $o1 is empty then all we have is the new one
        //    so shuffle and load old
        if (empty($o1)) {
            $c = get_class($o0);
            $x = new $c($o0->id);
            if (empty($x->id)) {
                // throw new Exception('Cannot Find Previous Object on Single Call Diff',__LINE__);
                self::note($o0,'Created');
                return false;
            }
            $o1 = $o0;
            $o0 = $x;
        }
        //Zend_Debug::dump($o0);
        //Zend_Debug::dump($o1);
        // Discover Properties?
        // Get our Base Object Data
        try {
            $x = ImperiumBase::getObjectType($o0,'link');
            $cols = $d->describeTable($x);
        } catch (Exception $e) {
            // Likely Could not Describe Table
            return;
        }

        if (count($cols) == 0) {
            return(0);
            throw new Exception(sprintf('Cannot Describe Table: %s',$x),__LINE__);
        }
        foreach ($cols as $c) {
            
            $name = $c['COLUMN_NAME'];
            
            if (in_array($name,self::$_skip_list)) {
                continue;
            }
            
            switch ($c['DATA_TYPE']) {
            case 'bool':
            case 'bytea':
            case 'int2':
            case 'tsvector':
                // Ignored
                break;
            case 'bpchar': // What the Hell is that?
            case 'date':
            case 'float4':
            case 'int4':
            case 'numeric':
            case 'text':
            case 'timestamp':
            case 'varchar':
                if ( strval($o0->$name) != strval($o1->$name) ) {
                    $r = array();
                    $r['auth_user_id'] = 1;
                    $r['link'] = $o0->link();
                    $r['f'] = ucwords(str_replace('_',' ',$name));
                    $r['v0'] = strval($o0->$name);
                    $r['v1'] = strval($o1->$name);
                    if (!empty($r['v0'])) {
                        // Zend_Debug::dump($r);
                        $t->insert($r);
                    }
                }
                break;
            default:
                throw new Exception(sprintf('Column "%s" type [%s] not handled',$name,$c['DATA_TYPE']),__LINE__);
            }
        }
    }
    /**
        Create a Note for this Object
    */
    static function note($o,$note)
    {
        if (empty($o)) {
            return;
        }
        if (is_array($note)) {
            $note = implode(";\n",$note);
        }
        
        $t = new Zend_Db_Table(array('name'=>'base_diff'));
        $r = array();
        $r['auth_user_id'] = 1;
        $r['link'] = $o->link();
        $r['f'] = '-Note-';
        $r['v0'] = $note;
        $t->insert($r);
    }
}
