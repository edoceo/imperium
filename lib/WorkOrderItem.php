<?php
/**
    @file
    @breif Work Order Item Model

    @copyright	2003 Edoceo, Inc
    @package    edoceo-imperium
	@link       http://imperium.edoceo.com
    @since      2003
*/

class WorkOrderItem extends ImperiumBase
{
  protected $_table = 'workorder_item';

	//const OBJECT_TYPE = 401;

	// const STATUS_PENDING = 1000;
	// const STATUS_COMPLETE = 3000;

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
		$this->date = date('Y-m-d');
        $this->quantity = 1;
		parent::__construct($x);
        if (empty($this->time_alpha)) {
            $this->time_alpha = strftime('%H:%M',mktime(date('H')-1,0,0));
            $this->time_omega = strftime('%H:%M',mktime(date('H')+1,0,0));
        }
	}

	/**
		Work Order Item Save
	*/
	function save() {
		if (strtotime($this->date) == false) {
			$this->date = new Zend_Db_Expr('null');
		}

        $this->a_tax_rate = tax_rate_fix($this->a_tax_rate);
        $this->e_tax_rate = tax_rate_fix($this->e_tax_rate);

		parent::save();
	}

	function delete()
	{
		$id = intval($this->id);
		$db = Zend_Registry::get('db');
		$db->query("delete from workorder_item where id = $id");
		return true;
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
