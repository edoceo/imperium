<?php
/**
	@file
	@brief Contact Address Model

	@copyright	2008 Edoceo, Inc
	@package	edoceo-imperium
	@link	   http://imperium.edoceo.com
	@since	  File available since Release 1013
*/

namespace Edoceo\Imperium;

class ContactAddress extends ImperiumBase
{
	protected $_table = 'contact_address';

	public static $kind_list = array(
		'Billing' => 'Billing',
		'Home' => 'Home',
		'Mailing' => 'Mailing',
		'Office' => 'Office',
	);

	// public $address;
	// public $city;
	// public $post_code;
	// public $kind;
	// public $rcpt;
	// public $country;

	/**
		Called when object used in string context - PHP Magic
	*/
	function __toString() {

		$ret = strlen($this->_data['address']) ? trim($this->_data['address']) : null;
		$ret.= strlen($ret) ? "\n" : null;
		if (stripos($ret, $this->_data['city'].', ') === false) {
			$ret.= strlen($this->_data['city']) ? $this->_data['city'].', ' : null;
		}
		//if (stripos($ret,"$this->state ")===false) $ret.= strlen($this->state) ? "$this->state " : null;
		if ((strlen($this->_data['post_code'])>0) && (stripos($ret, $this->_data['post_code'])===false)) {
			$ret.= strlen($this->_data['post_code']) ? $this->_data['post_code'] : null;
		}
		//if (stripos($ret,$this->country)===false) $ret.= strlen(trim($this->country)) ? "$this->country" : null;
		return trim(str_replace(array("\r","\n","\t"),' ',$ret));
	}

	/**
		Attempts to parse US addresses using common keywork patterns
	*/
	private function parseOut()
	{
		$p_hm_num = "[\d\-]+|\d+\-?\w";
		$p_dir = "northeast|northwest|southeast|southwest|north|east|south|west|ne|nw|se|sw|e|n|s|w";
		$p_street = "[\w ]+";
		$p_unit = "[\w\#\- ]+";
		$p_city = "[\w ]+";
		$p_state = "\w{2,4}";
		$p_postal = "(\d{5,9})|([\w ]{6,8})";

		// Street Suffix
		$p_suffix = "None|Alley|Annex|Arcade|Avenue|Bayou|Beach|Bend|Bluff|Bluffs|Bottom|Boulevard|Branch|Bridge|Brook|Brooks|Burg|Burgs|Bypass|";
		$p_suffix.= "Camp|Canyon|Cape|Causeway|Center|Centers|Circle|Circles|Cliff|Cliffs|Club|Common|Corner|Corners|Course|Court|Courts|Cove|Coves|Creek|Crescent|Crest|";
		$p_suffix.= "Crossing|Curve|Crossroad|Dale|Dam|Divide|Drive|Drives|Expressway|Estate|Estates|Extension|Extensions|Fall|Falls|Ferry|Field|Fields|Flat|Flats|Ford|Fords|";
		$p_suffix.= "Forest|Forge|Forges|Fork|Forks|Fort|Freeway|Garden|Gardens|Gateway|Glen|Glens|Green|Greens|Grove|Groves|Harbor|Harbors|Haven|Heights|Highway|Hill|Hills|Hollow|";
		$p_suffix.= "Inlet|Island|Islands|Isle|Junction|Junctions|Key|Keys|Knoll|Knolls|Lake|Lakes|Land|Landing|Lane|Light|Lights|Loaf|Lock|Locks|Lodge|Loop|Mall|Manor|Manors|Meadow|";
		$p_suffix.= "Meadows|Mews|Mill|Mills|Mission|Motorway|Mount|Mountain|Mountains|Neck|Orchard|Oval|Overpass|Park|Parkway|Pass|Passage|Path|Pike|Pine|Pines|Place|Plain|Plains|";
		$p_suffix.= "Plaza|Point|Points|Port|Ports|Prairie|Radial|Ramp|Ranch|Rapid|Rapids|Rest|Ridge|Ridges|River|Road|Roads|Route|Row|Rue|Run|Shoal|Shoals|Shore|Shores|Skyway|Spring|";
		$p_suffix.= "Springs|Spur|Square|Squares|Station|Stravenue|Stream|Street|Streets|Summit|Terrace|Throughway|Trace|Track|Trafficway|Trail|Tunnel|Turnpike|Underpass|Union|Unions|";
		$p_suffix.= "Valley|Valleys|Viaduct|View|Views|Village|Villages|Ville|Vista|Walk|Wall|Way|Well|Wells|";
		$p_suffix.= "ave|blvd|cir|ct|dr|ln|pl|rd|st|terr";

		$address = $this->__toString();
		$address = preg_replace("[\.,\"]",'',trim($address));

		if (preg_match("/($p_city), ($p_state) ($p_postal)/",$address,$m))
		{
			$this->city = $m[1];
			$this->state = $m[2];
			$this->post_code = $m[3];
		}
	/*
	// Big Ass Pattern Testing
	if (preg_test("^(" & P_HM_NUM & ") (" & P_DIR & ") (" & P_STREET & ") (" & P_SUFFIX & ") (" & P_UNIT & ")$",address)) then
	  m = preg_match("^(" & P_HM_NUM & ") (" & P_DIR & ") (" & P_STREET & ") (" & P_SUFFIX & ") (" & P_UNIT & ")$",address)
	  hm_num = m(0)
	  dir_pre = m(1)
	  st_name = m(2)
	  st_suf = m(3)
	  hm_unit = m(4)
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_DIR & ") (" & P_STREET & ") (" & P_SUFFIX & ")$",address)) then
	  m = preg_match("^(" & P_HM_NUM & ") (" & P_DIR & ") (" & P_STREET & ") (" & P_SUFFIX & ")$",address)
	  hm_num = m(0)
	  dir_pre = m(1)
	  st_name = m(2)
	  st_suf = m(3)
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_STREET & ") (" & P_SUFFIX & ") (" & P_DIR & ")$",address)) then
	  m = preg_match("^(" & P_HM_NUM & ") (" & P_STREET & ") (" & P_SUFFIX & ") (" & P_DIR & ")$",address)
	  hm_num = m(0)
	  st_name = m(1)
	  st_suf = m(2)
	  dir_suf = m(3)
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_STREET & ") (" & P_SUFFIX & ") (" & P_UNIT & ")$",address)) then
	  m = preg_match("^(" & P_HM_NUM & ") (" & P_STREET & ") (" & P_SUFFIX & ") (" & P_UNIT & ")$",address)
	  hm_num = m(0)
	  st_name = m(1)
	  st_suf = m(2)
	  hm_unit = m(3)
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_STREET & ") (" & P_SUFFIX & ")$",address)) then
	  m = preg_match("^(" & P_HM_NUM & ") (" & P_STREET & ") (" & P_SUFFIX & ")$",address)
	  hm_num = m(0)
	  st_name = m(1)
	  st_suf = m(2)
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_DIR & ") (" & P_STREET & ")$",address)) then
	  trigger_error("ContactAddress::parse() - Unhandled Pattern");
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_STREET & ")$",address)) then
	elseif (preg_test("^(" & P_HM_NUM & ") (" & P_STREET & ")$",address)) then
	  m = preg_match("^(" & P_HM_NUM & ") (" & P_STREET & ")$",address)
	  hm_num = m(0)
	  st_name = m(1)
	else
	  trigger_error "Canont parse this address: " & address
	end if
	*/
	}
}
