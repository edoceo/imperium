<?php
/**
    Contact Model

    Contacts: Person, Company and Vendor DataObjects

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 2001
*/

class Contact extends ImperiumBase
{
    const FLAG_BILL = 0x00000020;
    const FLAG_SHIP = 0x00000040;
    const FLAG_HIDE = 0x08000000;

    protected $_table = 'contact';

    public $company;
    public $contact;
    public $sound_code;
    public $name;
    public $phone;
    public $email;
    public $url;
    public $cts;
    public $ats;
    public $kind = 'Person';
    public $status = 'Active';

    /**
        Contact Model save()

        Saves this contact object with some embedded logic
    */
    function save()
    {

        if (intval($this->parent_id)==0) {
            $this->parent_id = null;
        }

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
        switch ($this->kind) {
        case 'Company':
        case 'Vendor':
            // if ( (!empty($this->company)) && (!empty($this->contact)) ) {
            //     $this->name = sprintf('%s [%s]',$this->company,$this->contact);
            // } elseif (!empty($this->company)) {
            //     $this->name = $this->company;
            // } else {
            //     $this->name = $this->contact;
            // }
            $this->name = trim($this->company);
            if (empty($this->name)) {
                $this->name = $this->contact;
            }
            break;
        case 'Person':
        default:
            if ( (!empty($this->company)) && (!empty($this->contact)) ) {
                $this->name = sprintf('%s [%s]',$this->contact,$this->company);
            } elseif (!empty($this->company)) {
                $this->name = $this->company;
            } else {
                $this->name = $this->contact;
            }
            break;
        }
        $this->name = trim($this->name);
        $this->sound_code = metaphone($this->name);
        $this->url = strtolower($this->url);
        $this->email = strtolower($this->email);
        $this->ats = date('Y-m-d H:i:s');
        $this->cts = null;

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
        $db = Zend_Registry::get('db');
        $db->beginTransaction();

        $id = intval($this->id);
        $db->query("delete from contact_address where contact_id = $id");
        //$this->ContactAddress->deleteAll("ContactAddress.contact_id=$id",false,false);
        $db->query("delete from contact_channel where contact_id = $id");
        //$this->ContactChannel->deleteAll("ContactChannel.contact_id=$id",false,false);
        //$db->query("delete from workorder where contact_id = $id");
        //$this->WorkOrder->deleteAll("WorkOrder.contact_id=$id",false,false);
        //$db->query("delete from invoice where contact_id = $id");
        //$this->Invoice->deleteAll("Invoice.contact_id=$id",false,false);

        $x = parent::delete($id,true);

        $db->commit();

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
        // $db = Zend_Registry::get('db');
        // $this->_d->fetchRow('SELECT * FROM account WHERE contact_id = ?',array($c->id));
        $a = new Account($this->account_id);
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
        $db = Zend_Registry::get('db');
        $sql = 'SELECT * ';
        $sql.= 'FROM contact ';
        $sql.= sprintf('WHERE ( id=%d OR parent_id=%d )',$this->id,$this->id);
        if ($flag) {
            $sql.= sprintf(' AND flag & %d = %d ',$flag,$flag);
        }
        $sql.= ' ORDER BY id ';
        $rs = $db->fetchAll($sql);
        if ( (is_array($rs)) && (count($rs)>0) ) {
            return $rs;
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
        $db = Zend_Registry::get('db');

    if ($name == null) {
      $rs = $db->fetchAll("select * from contact_address where contact_id=$this->id");
      $list = array();
      foreach ($rs as $x) {
        $list[] = new ContactAddress($x);
      }
      return $list;
    } else {
      if (is_string($name)) {
        $name = array($name);
      }

      foreach ($name as $x)
      {
        $ca = $this->ContactAddress->find("client_id=$this->id and ContactAddress.name=$x");
        //pr($ca);
      }
    }
  }

    /**
        Contact getChannelList()
    */
    function getChannelList()
    {
        $db = Zend_Registry::get('db');

        // Now Get Child Emails if Company?
        $sql = 'SELECT * FROM contact_channel '; // WHERE kind = 200 ';
        $sql.= ' WHERE 1 = 1 ';
        $sql.= " AND contact_id IN (SELECT id FROM contact WHERE ( id={$this->id} OR parent_id={$this->id} ) ) ";
        $sql.= ' ORDER BY contact_id ';
        // $res = $db->fetchAll($sql);
        $res = $db->fetchAll($sql);
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
