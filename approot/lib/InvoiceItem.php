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
        $this->quantity = floatval($this->quantity);
        $this->rate = floatval($this->rate);
        $this->tax_rate = tax_rate_fix($this->tax_rate);
        parent::save();
    }
}
