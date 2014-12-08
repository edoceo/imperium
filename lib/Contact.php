<?php
/**
	@file
	@brief Contact Model; Contacts: Person, Company and Vendor DataObjects

	@copyright  2001 Edoceo, Inc
	@package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 2001
*/

namespace Edoceo\Imperium;

class Contact extends ImperiumBase
{
    const FLAG_BILL = 0x00000020;
    const FLAG_SHIP = 0x00000040;
    const FLAG_HIDE = 0x08000000;

    protected $_table = 'contact';

    /**
        Contact Model save()

        Saves this contact object with some embedded logic
    */
    function save()
    {

        // $this->name = null;
        // if (!empty($this->first_name)) {
        //     $this->name.= $this->first_name;
        // }
        // if (!empty($this->last_name)) {
        //     $this->name.= ' ' . $this->last_name;
        // }
        // if (!empty($this->company)) {
        //     $this->name.= (empty($this->name) ? null : '@') . $this->company;
        // }

        // Copy Data from Parent
        // if (!empty($this->company)) {
        //     $p = new Contact($this->parent_id);
        //     if (empty($this->phone)) {
        //         $this->phone = $p->phone;
        //     }
        //     if (empty($this->url)) {
        //         $this->url = $p->url;
        //     }
        //     if (empty($this->company)) {
        //         $this->company = $p->company;
        //     }
        // }

        // Normalise Name
        switch ($this->_data['kind']) {
        case 'Company':
        case 'Vendor':
            // if ( (!empty($this->company)) && (!empty($this->contact)) ) {
            //     $this->name = sprintf('%s [%s]',$this->company,$this->contact);
            // } elseif (!empty($this->company)) {
            //     $this->name = $this->company;
            // } else {
            //     $this->name = $this->contact;
            // }
            $this->_data['name'] = trim($this->_data['company']);
            if (empty($this->_data['name'])) {
                $this->_data['name'] = $this->_data['contact'];
            }
            break;
        case 'Person':
        default:
            if ( (!empty($this->_data['company'])) && (!empty($this->_data['contact'])) ) {
                $this->_data['name'] = sprintf('%s [%s]',$this->_data['contact'], $this->_data['company']);
            } elseif (!empty($this->company)) {
                $this->_data['name'] = $this->_data['company'];
            } else {
                $this->_data['name'] = $this->_data['contact'];
            }
            break;
        }
        $this->_data['name'] = trim($this->_data['name']);
        $this->_data['email'] = strtolower($this->_data['email']);
        $this->_data['sound_code'] = metaphone($this->_data['name']);
        $this->_data['url'] = strtolower($this->_data['url']);
        $this->_data['ats'] = date('Y-m-d H:i:s');
        $this->_data['cts'] = null;

        if (empty($this->_data['account_id'])) {
        	$this->_data['account_id'] = null;
        }

        if (empty($this->_data['parent_id'])==0) {
            $this->_data['parent_id'] = null;
        }

        $ret = parent::save();

        return $ret;

    }

    /**
        Contact Model delete()

        Removes this Contact and All Addresses, Channels, Work Orders and Invoices!
        @todo Should block if the Contact has a WorkOrder or Invoice
    */
    function delete()
    {
        $id = intval($this->id);

        // Check Workorder
        $res = radix_db_sql::fetch_one('SELECT count(id) FROM workorder WHERE contact_id = ?', array($id));
        if ($res > 0) {
        	throw new Exception("Cannot delete Contact who owns Work Orders");
        }

        // Check Invoice
        $res = radix_db_sql::fetch_one('SELECT count(id) FROM invoice WHERE contact_id = ?', array($id));
        if ($res > 0) {
        	throw new Exception("Cannot delete Contact who owns Invoices");
        }

        radix_db_sql::query('DELETE FROM contact_address WHERE contact_id = ?', array($id));
        radix_db_sql::query('DELETE FROM contact_channel WHERE contact_id = ?', array($id));

        //$db->query("delete from workorder where contact_id = $id");
        //$this->WorkOrder->deleteAll("WorkOrder.contact_id=$id",false,false);
        //$db->query("delete from invoice where contact_id = $id");
        //$this->Invoice->deleteAll("Invoice.contact_id=$id",false,false);

        $x = parent::delete($id,true);

        return $x;
    }

    /**
        Get their Account Object
        @return Account
        @note at one point we were fetching from account by contact_id - but no more.
              I forget why it was that way for a time
    */
    function getAccount()
    {
        $a = new Account($this->_data['account_id']);
        return $a;
    }

    /**
        Contact getContactList

        @param flag
        @return Array of Contacts
    */
    function getContactList($flag=null)
    {
        if (intval($this->id)==0) {
          return null;
        }
        $sql = 'SELECT * ';
        $sql.= 'FROM contact ';
        $sql.= sprintf('WHERE ( id=%d OR parent_id=%d )',$this->id,$this->id);
        if ($flag) {
            $sql.= sprintf(' AND flag & %d = %d ',$flag,$flag);
        }
        $sql.= ' ORDER BY id ';
        $res = radix_db_sql::fetchAll($sql);
        if ( (is_array($res)) && (count($res)>0) ) {
            return $res;
        }
        return null;
    }
    /**
        getEmailList
        @return List of Email Addresses and Names from Contact and All Sub Contacts
    */
    function getEmailList()
    {
        $db = Zend_Registry::get('db');

        $list = array();

        $sql = "select id,email,contact from contact where (id={$this->id}) or (parent_id={$this->id})";
        $co_list = $db->fetchAll($sql);
        foreach ($co_list as $x) {

          $co = new Contact($x);

          if (strlen($co->email)) {
            $list[$co->email] = $co->contact . '(' . $co->email . ')';
          }

          $ch_list = $co->getChannelList();
          foreach ($ch_list as $ch) {
            if ($ch->kind == ContactChannel::EMAIL) {
              $list[$ch->data] = $co->contact . '(' . $ch->data . ')';
            }
          }
        }
        return $list;
    }

    /**
        Find
        @param $q string|array of strings like email, phone, name...
        @return single Contact record, array of Contact records
    */
    static function find($q)
    {
        $db = Zend_Registry::get('db');
        if (!is_array($q)) {
            $q = array($q);
        }
        if (count($q) == 0) return null;

        $sql = 'SELECT DISTINCT contact.* ';
        $sql.= ' FROM contact ';
        $sql.= ' LEFT JOIN contact_channel ON contact.id = contact_channel.contact_id ';
        $sql.= ' WHERE ';
        $buf = array();
        foreach ($q as $x) {
            $x = strtolower($x);
            $buf[] = $db->quoteInto(' lower(contact.email) = ?',$x);
            $buf[] = $db->quoteInto(' lower(contact.phone) = ?',$x);
            // $buf[] = $db->quoteInto(' contact_channel.data = ?',$x);
            $buf[] = $db->quoteInto(' lower(contact.name) = ?',$x);
        }
        $sql.= implode(' OR ',$buf);
        $sql.= ' ORDER BY contact.name ';

        // echo $sql;

        $rs = $db->fetchAll($sql);
        $c = count($rs);
        if ($c==0) {
            return null;
        } elseif ($c==1) {
            return $rs[0];
        } else {
            return $rs;
        }
    }

  function del_address($ca)
  {
    if ($ca instanceof ContactAddress)
        {
            $idc = ImperiumConnection::singleton();
            $idc->query("delete from contact_address where contact_id=$this->id and id=$ca->id");
        }
  }

  // Finds the first of the addresses specified
  function find_address($spec)
  {
    foreach ($spec as $x)
    {
      $ca = $this->get_address($x);
      if ($ca->id) break;
    }
    return $ca;
  }

	// func: getAddressList($name) - returns the address object for that named address, or fresh object when not found
	function getAddressList($name=null)
	{
		if ($name == null) {
			$rs = radix_db_sql::fetch_all('SELECT * FROM contact_address WHERE contact_id = ?', array($this->_data['id']));
			$list = array();
			foreach ($rs as $x) {
				$list[] = new ContactAddress($x);
			}
			return $list;
		} else {
			die(print_r(debug_backtrace()));
			if (is_string($name)) {
				$name = array($name);
			}

			// foreach ($name as $x) {
			// 	$ca = $this->ContactAddress->find("client_id=$this->id and ContactAddress.name=$x");
			// 	//pr($ca);
			// }
		}
	}

    /**
        Contact getChannelList()
    */
    function getChannelList()
    {
        // Now Get Child Emails if Company?
        $sql = 'SELECT * FROM contact_channel '; // WHERE kind = 200 ';
        $sql.= ' WHERE AND contact_id IN (SELECT id FROM contact WHERE ( id = ? OR parent_id = ? ) ) ';
        $sql.= ' ORDER BY contact_id ';
        // $res = $db->fetchAll($sql);
        $res = radix_db_sql::fetch_all($sql, array($this->_data['id'], $this->_data['id']));
        $list = array();
        foreach ($res as $x) {
            $list[] = new ContactChannel($x);
        }

        return $list;
    }

  // get_credit_cards ************************************************
  function get_credit_cards()
  {
		if (!$this->id) return null;
        $idc = ImperiumConnection::singleton();
		$rs = $idc->query("select * from contact_credit_card where contact_id=$this->id");
		return pg_numrows($rs)?$rs:null;
  }

  function get_past_due_invoices()
  {
    if (!$this->id) return null;
        $idc = ImperiumConnection::singleton();
    $sql = "select a.id from invoice a ";
    $sql.= " left join v_invoice_item c on a.id = c.invoice_id";
    $sql.= " left join v_invoice_payment d on a.id = d.invoice_id";
    $sql.= " where a.contact_id = $this->id and a.date < current_timestamp - interval '15 days' and (c.amount > d.amount or d.amount is null) ";
    $sql.= " order by a.date desc,a.id desc";
    $rs = $idc->query($sql);
    return pg_numrows($rs) ? $rs : null;
  }
}
