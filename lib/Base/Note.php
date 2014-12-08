<?php
/**
    Note Model

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

namespace Edoceo\Imperium;

class Base_Note extends ImperiumBase
{
    protected $_table = 'base_note';

    /**
        Note __construct

        Creates note with Default Values
    */
    function __construct($x=null)
    {
        $this->cts = date('Y-m-d');
        $this->name = 'New Note';
        $this->kind = 'Note';
        $this->status = 'New';
        parent::__construct($x);
    }
    /**
        Saves the Note
    */
    function save()
    {
        if ((strlen($this->cts)==0) || (strtotime($this->cts)<0)) $this->cts = date('Y-m-d');
        $this->data = str_replace("\r\n","\n",$this->data);
        $this->data = utf8_decode($this->data);
        return parent::save();
    }
}