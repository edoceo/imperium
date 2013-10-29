<?php
/**
	Contact Channel Model

	@copyright	2008 Edoceo, Inc
	@package    edoceo-imperium
	@link       http://imperium.edoceo.com
	@since      File available since Release 1013
*/
class ContactChannel extends ImperiumBase
{
  protected $_table = 'contact_channel';

  public static $kind_list = array(
    self::PHONE=>'Phone',
    self::EMAIL=>'Email',
    self::IM=>'I.M.',
    self::FAX=>'Fax',
    self::SIP=>'SIP',
    self::PAGER=>'Pager',
  );

	//const OBJECT_TYPE = 202;

  const PHONE = 100;
  const EMAIL = 200;
  const IM = 300;
  const SIP = 400;
  const FAX = 500;
  const PAGER = 600;

	public $kind;
	public $name;
	public $data;

}
