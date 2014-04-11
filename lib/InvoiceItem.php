<?php
/**
    @file
    @brief Invoice Model

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      2003
*/

class InvoiceItem extends ImperiumBase
{
    protected $_table = 'invoice_item';

    public static $kind_list = array(
		'Labour'=>'Labour',
		'Parts'=>'Parts',
		'Registration'=>'Registration',
		'Subscription'=>'Subscription',
		'Travel'=>'Travel'
    );

    public function save()
    {
		if (strtotime($this->_data['date']) == false) {
			$this->_data['date'] = null;
		}
        $this->_data['quantity'] = floatval($this['quantity']);
        $this->_data['rate'] = floatval($this['rate']);
        $this->_data['tax_rate'] = tax_rate_fix($this['tax_rate']);

        return parent::save();
    }
}
