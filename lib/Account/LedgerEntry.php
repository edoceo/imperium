<?php
/**

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

class AccountLedgerEntry extends ImperiumBase
{
    protected $_table = 'account_ledger';

    public $account_id;
    public $amount = null;
    public $note;
    public $link_to;
    public $object_id;

    /*
    function afterFind($res,$pri=false)
    {
        foreach ($res[0]['LedgerEntry'] as $i=>$item) {
            if ($item['amount'] < 0) {
                $res[0]['LedgerEntry'][$i]['debit_amount'] = abs($item['amount']);
            } else {
                $res[0]['LedgerEntry'][$i]['credit_amount'] = abs($item['amount']);
            }
        }
        return $res;
    }
    */

}
