<?php
/**
	Master Controller for Account
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

require_once('Account/Reconcile.php');
require_once('Account/TaxFormLine.php');

// Set Default Data
if (empty($_SESSION['account-view']['period'])) $_SESSION['account-view']['period'] = 'm';
if (empty($_SESSION['account-view']['month'])) $_SESSION['account-view']['month'] = date('m');
if (empty($_SESSION['account-view']['year'])) $_SESSION['account-view']['year'] = date('Y');
if (empty($_SESSION['account-view']['date_alpha'])) $_SESSION['account-view']['date_alpha'] = strtotime(date('Y-m-01'));
if (empty($_SESSION['account-view']['date_omega'])) $_SESSION['account-view']['date_omega'] = strtotime(date('Y-m-t'));

// Attach to Context
$this->Period = $_SESSION['account-view']['period'];
$this->Month = $_SESSION['account-view']['month'];
$this->Year = $_SESSION['account-view']['year'];
$this->date_alpha_ts = $_SESSION['account-view']['date_alpha'];
$this->date_omega_ts = $_SESSION['account-view']['date_omega'];

if (!empty($_GET['p'])) {
	$this->Period = $_GET['p'];
	$this->Month = isset($_GET['m']) ? $_GET['m'] : $_SESSION['account-view']['month'];
	$this->Year = isset($_GET['y']) ? $_GET['y'] : $_SESSION['account-view']['year'];
}

// @todo this is duplicated in the AccountStatement Controller - how to reslove?
// Initialise Inputs
if ( (isset($_GET['d0'])) && (isset($_GET['d1'])) ) {
	$this->Period = 'r';
}

switch ($this->Period) {
case 'm':
	$this->date_alpha_ts = mktime(0,0,0, $this->Month, 1, $this->Year);
	$this->date_omega_ts = mktime(23, 59, 59, $this->Month + 1, 0, $this->Year); // Zero Day of Next Month is really last day of "this" month
	break;
case 'q':
	// @note this may or may not be an accurate way to find the Quarter
	$this->date_alpha_ts = mktime(0,0,0,$this->Month, 1, $this->Year);
	$this->date_omega_ts = mktime(0,0,0,$this->Month+2 , 1,$this->Year);
	break;
case 'y':
	$this->date_alpha_ts = mktime(0,0,0,$this->Month,1,$this->Year);
	$this->date_omega_ts = mktime(0,0,0,$this->Month+11,1,$this->Year);
	break;
case 'r': // Range

	if (!empty($_GET['d0'])) {
		$this->date_alpha_ts = strtotime($_GET['d0']);
	}
	if (!empty($_GET['d1'])) {
		$this->date_omega_ts = strtotime($_GET['d1']);
	}

	break;
}

// Format Date
$this->date_alpha = date('Y-m-d', $this->date_alpha_ts);
$this->date_omega = date('Y-m-d', $this->date_omega_ts);
$this->date_alpha_f = strftime('%B %Y', $this->date_alpha_ts);
$this->date_omega_f = strftime('%B %Y', $this->date_omega_ts);

$this->Month = date('m', $this->date_alpha_ts);
$this->Year = date('Y', $this->date_alpha_ts);

$_SESSION['account-view']['year'] = $this->Year;
$_SESSION['account-view']['month'] = $this->Month;
$_SESSION['account-view']['period'] = $this->Period;
$_SESSION['account-view']['date_alpha'] = $this->date_alpha_ts;
$_SESSION['account-view']['date_omega'] = $this->date_omega_ts;

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
$this->AccountList = Account::listAccounts();
$this->AccountKindList = Account::$kind_list;
$this->AccountPairList = Account::listAccountPairs();

// For use by the HTML Select
$sel = array();
$sel[-1] = 'All - General Ledger';
foreach ($this->AccountList as $item) {
    $sel[$item['id']] = $item['full_name'];
}
$this->AccountList_Select = $sel;
