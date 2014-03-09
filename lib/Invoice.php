<?php
/**
    @file
    @brief Invoice Model

    A Bill to Send to a Contact

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      2003
*/

class Invoice extends ImperiumBase
{
    const FLAG_OPEN = 0x00000001;
    // const FLAG_POST = 0x00001000;
    const FLAG_SENT = 0x00040000;
    const FLAG_HAWK = 0x00080000;
    const FLAG_VOID = 0x00400000;
    const FLAG_PAID = 0x00800000;

    protected $_table = 'invoice';

    /**
        ImperiumBase findByHash
    */
    static function findByHash($h)
    {
        $sql = "select id from invoice where hash = ?";
        $id = radix_db_sql::fetch_one($sql,array($h));
        if ($id) {
          $x = new Invoice($id);
          return $x;
        }
        return null;
    }

    /**
        Create an Invoice
    */
    function __construct($x=null)
    {

        $this->_data['status'] = $_ENV['invoice']['status'];
        $this->_data['net'] = $_ENV['invoice']['term_days'];
        $this->_data['date'] = date('Y-m-d');
        $this->_data['bill_amount'] = 0;
        $this->_data['paid_amount'] = 0;
        $this->_data['sub_total'] = 0;
        $this->_data['tax_total'] = 0;
        $this->_data['bill_address_id'] = null;
        $this->_data['ship_address_id'] = null;

        parent::__construct($x);

        // Now to Stuff with this new Data!
        // @todo Update Properties
        // @todo Update due_diff to the number of days until or after payment is due
        $due_date = strtotime($this->_data['date']);
        if (!empty($this->_data['net'])) {
            $due_date += ($this->_data['net'] * 86400); // add($this->net,Zend_Date::DAY);
        }
        $now_date = time();
        $this->_date['due_diff'] = floor(($now_date - $due_date) / 86400);
    }

    /**
        Invoice Model save()
    */
    function save()
    {
        // @todo Due to Zend bug we cannot re-make these fields NULL the only can be 0 (which would violate FK)
        // if (empty($this->bill_address_id)) $this->bill_address_id = new Zend_Db_Expr('null');
        // if (empty($this->ship_address_id)) $this->ship_address_id = new Zend_Db_Expr('null');
        parent::save();
        $this->_updateBalance();
    }

    /**
    */
    function delete()
    {
        $id = intval($this->_data['id']);
        $db = Zend_Registry::get('db');
        $db->query(sprintf("delete from base_note where link = '%s'",$this->link()));
        $db->query("delete from invoice_item where invoice_id = $id");
        $db->query("delete from invoice where id = $id");
        return true;
    }

    /**
        Determines if this Invoice is Hawk-able
        @return true|false
    */
    function canHawk()
    {
        if ($this->_data['status'] == 'Paid') {
            return false;
        }

        if ($this->hasFlag(self::FLAG_PAID))  {
            return false;
        }

        return !$this->hasFlag(self::FLAG_HAWK);
    }

    /**
        Imperium Specific Functions
    */
    function addInvoiceItem($r)
    {
        $t = new Zend_Db_Table(array('name'=>'invoice_item'));
        $r['auth_user_id'] = $this->_data['auth_user_id'];
        $r['invoice_id'] = $this->_data['id'];
        if ($t->insert($r)) {
            Base_Diff::note($this,'Invoice Item: ' . $r['name'] . ' created');
            $this->_updateBalance();
        }
    }

    /**
    */
    function delInvoiceItem($id)
    {
        Base_Diff::note($this,'Invoice Item #' . $id . ' removed');
        radix_db_sql::query('DELETE FROM invoice_item WHERE id = ?', array($id));
        $this->updateBalance();
        return true;
    }

    /**
        @return ResultSet of InvoiceItems
    */
    function getInvoiceItems()
    {
        $sql = 'SELECT * FROM invoice_item WHERE invoice_id = ? ORDER BY line, rate DESC, quantity DESC';
        $arg = array($this->id);
        $res = radix_db_sql::fetchAll($sql, $arg);

        $ret = array();
        foreach ($res as $x) {
            $ret[] = new InvoiceItem($x);
        }
        return $ret;
    }

    /**
        Invoice::getWorkOrders
        Returns a list of WorkOrders that have contributed to this Invoice
    */
    function getWorkOrders()
    {
        if (intval($this->id)==0) {
          return null;
        }
        $db = Zend_Registry::get('db');
        $sql = 'select distinct b.* from workorder_item_invoice_item a';
        $sql.= ' join workorder b on a.workorder_id = b.id';
        $sql.= ' where a.invoice_id = ' . $this->id;
        $x = $db->fetchAll($sql);
        return $x;
    }

    // Return the set of LedgerEntry objects that match
    function getTransactions()
    {
        // Transaction
        if (intval($this->id)==0) {
            return null;
        }

        $sql = 'SELECT al.id,al.account_id,al.amount,aj.id as account_journal_id,aj.date,aj.note,a.name as account_name ';
        $sql.= ' from account_ledger al ';
        $sql.= ' join account_journal aj on al.account_journal_id = aj.id ';
        $sql.= ' join account a on al.account_id = a.id ';
        // KIND needs to be A/R + Asset //
        $sql.= ' WHERE ';
        // $sql.= " a.kind = 'Asset: Accounts Receivable' AND ";
        $sql.= sprintf(' al.link_to = %d AND al.link_id = %d',self::getObjectType($this),$this->id);
        $sql.= ' ORDER BY aj.date ASC, al.amount DESC';

        $rs = radix_db_sql::fetchAll($sql);
        return $rs;
    }

    /**
        Sum of Transactions
    */
    function getTransactionSum()
    {
    	$id = intval($this->_data['id']);
        if ($id <= 0) {
            return null;
        }

        $sql = "SELECT abs(sum(al.amount)) from account_ledger al ";
        $sql.= " JOIN account_journal aj on al.account_journal_id = aj.id ";
        $sql.= " JOIN account a on al.account_id = a.id ";
        // KIND needs to be A/R + Asset //
        $sql.= " WHERE al.link_to=" . self::getObjectType($this) . " and al.link_id=$id";
        // If Posting & Paying do this
        $sql.= ' AND amount < 0 ';
        // Elseif CASH basis don't use AND amount...
        $ret = radix_db_sql::fetch_one($sql);
        return $ret;
    }

    /**
        Update Balance
        Updates the Invoice Balance after it's been saved
    */
    private function _updateBalance()
    {
    	$id = intval($this->_data['id']);

        // $r = array();
        $sql = 'UPDATE invoice SET ';
        // $r['sub_total'] = floatval($d->fetchOne("select sum( quantity * rate ) as sub_total from invoice_item where invoice_id={$id}"));
        $sql.= ' sub_total = ?, ';
        $arg[] = floatval(radix_db_sql::fetch_one("select sum( quantity * rate ) as sub_total from invoice_item where invoice_id={$id}"));
        // $r['tax_total'] = floatval($d->fetchOne("select sum( quantity * rate * tax_rate) as tax_total from invoice_item where invoice_id={$id}"));
        $sql.= ' sub_total = ?, ';
        $arg[] = floatval(radix_db_sql::fetch_one("select sum( quantity * rate * tax_rate) as tax_total from invoice_item where invoice_id={$id}"));
        // $r['bill_amount'] = $r['sub_total'] + $r['tax_total'];
        $sql.= ' bill_amount = ?, ';
        $arg[] = $r['sub_total'] + $r['tax_total'];
        // $r['paid_amount'] = $this->getTransactionSum();
        $sql.= ' paid_amount = ? ';
        $arg[] = $this->getTransactionSum();

        // @todo Force Marking as Paid Amount Full?
        // if ($this->status == 'Paid') {
        //     $r['paid_amount'] = $r['bill_amount'];
        // }
        // $w = array('id = ?'=>$this->id);
        // $t = new Zend_Db_Table(array('name'=>'invoice'));
        // $t->update($r,$w);

        $sql.= ' WHERE id = ?';
        $arg[] = $id;

        radix_db_sql::query($sql, $arg);

        // @todo Save to Object Data?
        // $this->bill_amount = $r['bill_amount'];
        // $this->paid_amount = $r['paid_amount'];
        // $this->sub_total = $r['sub_total'];
        // $this->tax_total = $r['tax_total'];

    }
}
