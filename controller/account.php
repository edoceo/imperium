<?php
/**
	@file
	@brief Master Controller for Account 
*/

require_once('Account/Reconcile.php');

$_ENV['title'] = 'Accounts';

$this->Period = isset($_GET['p']) ? $_GET['p'] : 'm';
$this->Month = isset($_GET['m']) ? $_GET['m'] : date('m');
$this->Year = isset($_GET['y']) ? $_GET['y'] : date('Y');

// @todo this is duplicated in the AccountStatement Controller - how to reslove?
// Initialise Inputs
if ( (isset($_GET['d0'])) && (isset($_GET['d1'])) ) {
	$this->Period = 'x';
	$this->date_alpha = date('Y-m-d',strtotime($_GET['d0']));
	$this->date_omega = date('Y-m-d',strtotime($_GET['d1']));
} elseif (isset($_SESSION['AccountPeriod']['date_alpha'])) {
	$this->date_alpha = $_SESSION['AccountPeriod']['date_alpha'];
	$this->date_omega = $_SESSION['AccountPeriod']['date_omega'];
}

// Period Processing?
if ($this->Period == 'm') {
	$this->date_alpha = date('Y-m-d',mktime(0,0,0,$this->Month,1,$this->Year));
	$this->date_omega = date('Y-m-t',mktime(0,0,0,$this->Month));
} elseif ($this->Period == 'q') {
	// @note this may or may not be an accurate way to find the Quarter
	$this->date_alpha = date('Y-m-d',mktime(0,0,0,$this->Month,1,$this->Year));
	$this->date_omega = date('Y-m-t',mktime(0,0,0,$this->Month+2,1,$this->Year));
} elseif ($this->Period == 'y') {
	// @note this may or may not be an accurate way to find the full 12 months
	$this->date_alpha = date('Y-m-d',mktime(0,0,0,$this->Month,1,$this->Year));
	$this->date_omega = date('Y-m-t',mktime(0,0,0,$this->Month+11,1,$this->Year));
}

// Handle Empties
if (empty($this->date_alpha)) {
	$this->date_alpha = date('Y-m-01');
}
if (empty($this->date_omega)) {
	$this->date_omega = date('Y-m-t');
}

// Format Date
$this->date_alpha_f = strftime('%B %Y',strtotime($this->date_alpha));
$this->date_omega_f = strftime('%B %Y',strtotime($this->date_omega));

// Save to Session
// @todo This should be done differently
// @todo Would also like to make AccountTransaction Controller that does Transaction and Wizard in one

$_SESSION['AccountPeriod']['date_alpha'] = $this->date_alpha;
$_SESSION['AccountPeriod']['date_omega'] = $this->date_omega;

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

// 	$sql = 'SELECT DISTINCT id, full_name AS label, full_name AS result';
// 	$sql.= ' FROM account';
// 	$sql.= ' WHERE name ~* ? OR full_name ~* ?';
// 	$sql.= ' ORDER BY full_name';
// 	$res = radix_db_sql::fetchAll($sql, array($q, "^$q"));
// 	die(json_encode($res));

$this->AccountPairList = Account::listAccountPairs();
// Account Kind List
$this->AccountKindList = Account::$kind_list;
