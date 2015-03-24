<?php
/**
	Note Model

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
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
		$this->_data['cts'] = date('Y-m-d');
		$this->_data['name'] = 'New Note';
		$this->_data['kind'] = 'Note';
		$this->_data['status'] = 'New';
		parent::__construct($x);
	}

	/**
		Saves the Note
	*/
	function save()
	{
		$this->_data['note'] = str_replace("\r\n","\n",$this->_data['note']);
		$this->_data['note'] = utf8_decode($this->_data['note']);

		$this->_data['name'] = substr(strtok($this->_data['note'], "\n"), 0, 255);

		if ( (empty($this->_data['cts'])) || (strtotime($this->_data['cts']) <= 0) ) {
			$this->_data['cts'] = date('Y-m-d');
		}

		return parent::save();
	}
}