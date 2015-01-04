<?php
/**
	@file
	@breif Work Order Item Model

	@copyright	2003 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  2003
*/

namespace Edoceo\Imperium;

class WorkOrderItem extends ImperiumBase
{
	protected $_table = 'workorder_item';

	public static $kind_list = array(
		'Labour'=>'Labour',
		'Parts'=>'Parts',
		'Registration'=>'Registration',
		'Subscription'=>'Subscription',
		'Travel'=>'Travel'
	);

	/**
		Imperium Functions
	*/
	function __construct($x=null)
	{
		$this->_data['date'] = date('Y-m-d');
		$this->_data['a_quantity'] = 2;
		$this->_data['a_unit'] = 'hr';
		$this->_data['e_unit'] = 'hr';

		parent::__construct($x);

		if (empty($this->_data['time_alpha'])) {
			$this->_data['time_alpha'] = strftime('%H:%M',mktime(date('H')-1,0,0));
			$this->_data['time_omega'] = strftime('%H:%M',mktime(date('H')+1,0,0));
		}
	}

	/**
		Work Order Item Save
	*/
	function save()
	{

		if (empty($this->_data['auth_user_id'])) {
			$this->_data['auth_user_id'] = $_SESSION['uid'];
		}

		if (strtotime($this->_data['date']) == false) {
			$this->_data['date'] = null;
		}
		foreach (array('e_quantity','e_rate','e_tax_rate', 'a_quantity', 'a_rate', 'a_tax_rate') as $x) {
			if (empty($this->_data[$x])) $this->_data[$x] = 0;
		}

		$this->a_tax_rate = tax_rate_fix($this->a_tax_rate);
		$this->e_tax_rate = tax_rate_fix($this->e_tax_rate);

		parent::save();
	}

	function delete()
	{
		throw new Exception("Delete Items from their Parent WorkOrder Object");
	}

	/**
		Imperium Functions
	*/
	function bindToInvoiceItem($id) {

		$woi_id = intval($this->id);
		$ivi_id = intval($id);

		$db = Zend_Registry::get('db');
		$c = $db->fetchOne("select count(*) from workorder_item_invoice_item where workorder_item_id=$woi_id and invoice_item_id=$ivi_id");
		if ($c == 0) {
			$db->query("insert into workorder_item_invoice_item values ($woi_id,$ivi_id)");
		}
		return true;
	}
}
