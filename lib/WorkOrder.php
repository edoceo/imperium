<?php
/**
    @file
    @brief Work Order Model - Track Jobs associated with Contacts

    @copyright  2003 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      2003
*/

namespace Edoceo\Imperium;

class WorkOrder extends ImperiumBase
{
    protected $_table = 'workorder';

    /**
        ImperiumBase findByHash
    */
    static function findByHash($h)
    {
        $db = Zend_Registry::get('db');
        $h = pg_escape_string($h);
        $sql = "select id from workorder where hash='$h'";
        $id = $db->fetchOne($sql);
        if ($id) {
            $x = new WorkOrder($id);
            return $x;
        }
        return null;
    }

    /**
        Construct a new Work Order
    */
    function __construct($id)
    {
        $this['kind'] = $_ENV['workorder']['kind'];
        $this['status'] = $_ENV['workorder']['status'];
        $this['base_rate'] = $_ENV['workorder']['base_rate'];
        $this['base_unit'] = $_ENV['workorder']['base_unit'];
        $this['date'] = date('Y-m-d');

        parent::__construct($id);
    }

    /**
        Delete WorkOrder and Items and Notes and Files
    */
    function delete()
    {
        $id = intval($this['id']);
        // $db = Zend_Registry::get('db');
        radix_db_sql::query('DELETE FROM base_file WHERE link = ?', array($this->link()) );
        radix_db_sql::query('DELETE FROM base_note WHERE link = ?', array($this->link()) );
        radix_db_sql::query('DELETE FROM workorder_item WHERE workorder_id = ?', array($id) );
        radix_db_sql::query('DELETE FROM workorder WHERE id = ?', array($id) );
        return true;
    }

    /**
        Save
    */
    function save()
    {
        $this->note = utf8_decode($this->note);
        if (empty($this->_data['auth_user_id'])) {
        	$this->_data['auth_user_id'] = $_SESSION['uid'];
        }
        $x = parent::save();
        $this->_updateBalance();
        return $x;
    }

    /**
        Return List of Interested Contacts
    */
    function getContacts()
    {
        $db = Zend_Registry::get('db');
        $sql = 'SELECT * ';
        $sql.= 'FROM contact ';
        $sql.= 'JOIN workorder_contact ON contact.id = workorder_contact.contact_id ';
        $sql.= 'WHERE workorder_contact.workorder_id = %d';
        $res = $db->fetchAll(sprintf($sql,$this->id));
        return (is_array($res) && count($res)) ? $res : null;
    }

    /**
        addWorkOrderItem($data)
    */
    function addWorkOrderItem($woi)
    {
        $woi['workorder_id'] = $this->_data['id'];
        $woi->save();
        $this->_updateBalance();
        return $woi;
    }

    /**
        delWorkOrderItem($data)
    */
    function delWorkOrderItem($id)
    {
		radix_db_sql::query('begin');
        radix_db_sql::query('DELETE from workorder_item WHERE id = ? AND workorder_id = ?', array($id, $this->_data['id']));
        // Base_Diff::note($this,'Work Order Item #' . $id . ' removed');
        $this->_updateBalance();
        radix_db_sql::query('commit');
        return true;
    }

    /**
        getWorkOrderItems()
    */
    function getWorkOrderItems($where=null)
    {
        $sql = 'SELECT * FROM workorder_item WHERE workorder_id = ? ';
        $sql.= ' ORDER BY status, date, kind, a_rate, a_quantity ';
        $res = radix_db_sql::fetch_all($sql, array($this->_data['id']));

        $ret = array();
        foreach ($res as $x) {
            $ret[] = new WorkOrderItem($x);
        }

        return $ret;
    }

    /**
        newWorkOrderItem($data)
    */
    function newWorkOrderItem()
    {
        $woi = new WorkOrderItem(null);
        $woi['workorder_id'] = $this->_data['id'];
        $woi['a_rate'] = $this->_data['base_rate'];
        $woi['a_unit'] = $this->_data['base_unit'];
        $woi['e_rate'] = $this->_data['base_rate'];
        $woi['e_unit'] = $this->_data['base_unit'];
        return $woi;
    }

    /**
        Generate an Invoice from this WorkOrder
        @param $iv Invoice to Add to
        @return Invoice
    */
    function toInvoice($iv=null)
    {
        // Add Invoice Items
        $w = null;
        switch ($this['kind']) {
        case 'Monthly':
        case 'Quarterly':
        case 'Yearly':
            $w = array(
                'woi.kind = ? ' => array('Subscription'),
                'woi.status = ?' => array('Active')
            );
            break;
        default:
            $w = array(
                'woi.status in (?)' => array('Active','Complete')
            );
            break;
        }

        $woi_list = $this->getWorkOrderItems($w);
        if ( (empty($woi_list)) || (!is_array($woi_list)) ) {
            throw new Exception('No WorkOrder Items to Generate Invoice');
        }

        // Add to Existing Invoice or Create a New one
        if (!empty($iv)) {
            if ($iv instanceof Invoice) {
                // All Good
            } elseif (is_numeric($iv)) {
                $iv = new Invoice($iv);
            }
        } else {
            $iv = new Invoice();
            $iv['auth_user_id'] = $this['auth_user_id'];
            $iv['contact_id'] = $this['contact_id'];
            $iv['note'] = $this['note'];
            $iv->save();
        }

        // Add Items
        $ivi_c = 0;
        foreach ($woi_list as $woi) {

            $ivi = new InvoiceItem(); // array();
            $ivi['workorder_item_id'] = $woi['id'];
            $ivi['kind'] = $woi['kind'];
            $ivi['quantity'] = $woi['a_quantity'];
            $ivi['rate'] = $woi['a_rate'];
            $ivi['unit'] = $woi['a_unit'];
            $ivi['tax_rate'] = $woi['a_tax_rate'];
            $ivi['name'] = $woi['name'];
            $ivi['note'] = $woi['note'];

            $iv->addInvoiceItem($ivi);
            $ivi_c++;

            // If Complete then Mark as Billed
            // @todo needs to come from a workflow type list
            // @todo Handle WorkOrder Kind/Status too
            if ($woi['status'] == 'Complete') {
                $woi['status'] = 'Billed';
                $woi->save();
            }
        }
        $iv->save();

        // Close Single Job Work Orders
        if ($this['kind'] == 'Single') {
            $this['status'] = 'Closed';
            // Base_Diff::note($this,'Closed & converted to Invoice');
        }

        $this->save();

        // Add History
        $msg = sprintf('Invoice #%d created from Work Order #%d with %d items', $iv['id'], $this['id'], $ivi_c);
        // Base_Diff::note($this,$msg);
        // Base_Diff::note($iv,$msg);
        // $this->_d->commit();

        return $iv;
    }

    /**
        Work Order Update Balance
        @todo handle totals differently for Subscription vs One-Time Work Orders
    */
    private function _updateBalance()
    {
    	$id = $this->_data['id'];

        $sql = 'update workorder set ';
        $sql.= 'bill_amount = (';
            $sql.= "select sum(a_quantity * a_rate) from workorder_item ";
            $sql.= " where workorder_id=$id and status = 'Billed' ) ";
        $sql.= ',';
        $sql.= 'open_amount = (';
            $sql.= 'select sum(a_quantity * a_rate) from workorder_item ';
            $sql.= " where workorder_id = $id and status in ('Active','Complete') ";
        $sql.= ") where id=$id";
        radix_db_sql::query($sql);

        $this->bill_amount = radix_db_sql::fetch_one("SELECT bill_amount FROM workorder WHERE id = $id");
        $this->open_amount = radix_db_sql::fetch_one("SELECT open_amount FROM workorder WHERE id = $id");
    }
}
