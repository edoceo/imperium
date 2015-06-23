<?php
/**
	@file
	@brief Master Controller for Account 
*/

namespace Edoceo\Imperium;

require_once('Account/Reconcile.php');
require_once('Account/TaxFormLine.php');

// Set Default Data
if (empty($_SESSION['account-view']['period'])) $_SESSION['account-view']['period'] = 'm';
if (empty($_SESSION['account-view']['month'])) $_SESSION['account-view']['month'] = date('m');
if (empty($_SESSION['account-view']['year'])) $_SESSION['account-view']['year'] = date('Y');
if (empty($_SESSION['account-view']['date_alpha'])) $_SESSION['account-view']['date_alpha'] = date('Y-m-01');
if (empty($_SESSION['account-view']['date_omega'])) $_SESSION['account-view']['date_omega'] = date('Y-m-t');

// Attach to Context
$this->Period = $_SESSION['account-view']['period'];
$this->Month = $_SESSION['account-view']['month'];
$this->Year = $_SESSION['account-view']['year'];
$this->date_alpha = $_SESSION['account-view']['date_alpha'];
$this->date_omega = $_SESSION['account-view']['date_omega'];

if (!empty($_GET['p'])) {
	$this->Period = $_GET['p'];
	$this->Month = isset($_GET['m']) ? $_GET['m'] : $_SESSION['account-view']['month'];
	$this->Year = isset($_GET['y']) ? $_GET['y'] : $_SESSION['account-view']['year'];
}

// @todo this is duplicated in the AccountStatement Controller - how to reslove?
// Initialise Inputs
if ( (isset($_GET['d0'])) || (isset($_GET['d1'])) ) {
	$this->Period = 'r';
// //} elseif (isset($_SESSION['AccountPeriod']['date_alpha'])) {
// //	$this->date_alpha = $_SESSION['AccountPeriod']['date_alpha'];
// //	$this->date_omega = $_SESSION['AccountPeriod']['date_omega'];
}

switch ($this->Period) {
case 'm':
	$this->date_alpha = date('Y-m-d',mktime(0,0,0,$this->Month,1,$this->Year));
	$this->date_omega = date('Y-m-t',mktime(0,0,0,$this->Month));
	break;
case 'q':
	// @note this may or may not be an accurate way to find the Quarter
	$this->date_alpha = date('Y-m-d',mktime(0,0,0,$this->Month,1,$this->Year));
	$this->date_omega = date('Y-m-t',mktime(0,0,0,$this->Month+2,1,$this->Year));
	break;
case 'y':
	$this->date_alpha = date('Y-m-d',mktime(0,0,0,$this->Month,1,$this->Year));
	$this->date_omega = date('Y-m-t',mktime(0,0,0,$this->Month+11,1,$this->Year));
	break;
case 'r': // Range
	if (!empty($_GET['d0'])) {
		$this->date_alpha = date('Y-m-d',strtotime($_GET['d0']));
	}
	if (!empty($_GET['d1'])) {
		$this->date_omega = date('Y-m-d',strtotime($_GET['d1']));
	}
	// @todo decide to pick from Alpha or Omega date
	// $this->Month = date('m', strtotime($_GET['d0']));
	// $this->Year = date('Y', strtotime($_GET['d0']));
	break;
}

// Format Date
$this->date_alpha_f = strftime('%B %Y',strtotime($this->date_alpha));
$this->date_omega_f = strftime('%B %Y',strtotime($this->date_omega));

// @deprecated
// $_SESSION['AccountPeriod']['date_alpha'] = $this->date_alpha;
// $_SESSION['AccountPeriod']['date_omega'] = $this->date_omega;

$_SESSION['account-view']['period'] = $this->Period;
$_SESSION['account-view']['month'] = $this->Month;
$_SESSION['account-view']['year'] = $this->Year;
$_SESSION['account-view']['date_alpha'] = $this->date_alpha;
$_SESSION['account-view']['date_omega'] = $this->date_omega;

$_GET['p'] = $this->Period;
$_GET['m'] = $this->Month;
$_GET['y'] = $this->Year;

// Build other View Data (Month, Year, Period)
$this->MonthList = array();
for ($i=1;$i<=12;$i++) {
	$this->MonthList[$i] = sprintf('%02d',$i) . ' ' . strftime('%B',mktime(0,0,0,$i));
}

$this->YearList = array();
$year = date('Y');
for ($i=$year-10;$i<=$year+10;$i++) {
	$this->YearList[$i] = $i;
}

$this->PeriodList = array(
	'm'=>'Monthly',
	'q'=>'Quarterly',
	'y'=>'Yearly'
);

// Account List
// $this->AccountPeriod = $this->_s->AccountPeriod;
$this->AccountList = Account::listAccounts();
$this->AccountKindList = Account::$kind_list;
$this->AccountPairList = Account::listAccountPairs();
