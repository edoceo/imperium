<?php
/**
	Account Statement Balance Sheet View
	Displays a balance sheet for a given time period
	@package Edoceo Imperium
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;

// Input Form
echo '<div class="print-hide">';
echo '<form>';
echo Radix::block('account-period-input');
echo '</form>';
echo Radix::block('account-period-arrow', $this->date_alpha);
echo '</div>';

switch ($this->Period) {
case 'm':
	$_ENV['h1'] = $_ENV['title'] = 'Monthly Balance Sheet for ' . $this->date_alpha_f;
	break;
case 'q':
	$_ENV['h1'] = $_ENV['title'] = 'Quarterly Balance Sheet: ' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
	break;
case 'y':
	$_ENV['h1'] = $_ENV['title'] = 'Yearly Balance Sheet: ' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
	break;
default:
	$_ENV['h1'] = $_ENV['title'] = 'Balance Sheet from ' . $this->date_alpha_f . ' to ' . $this->date_omega_f;
	break;
}

$sql = 'SELECT distinct kind, kind_sort';
$sql.= ' from account ';
// $sql.= " where type in ('Asset','Liability') or kind = 'Equity: Owners Capital' ";
$sql.= ' order by kind_sort, kind';
$AccountKindList = SQL::fetch_all($sql);
$AccountBalanceList = array();

echo '<table class="table">';
foreach ($AccountKindList as $kind) {

	// Assets
	$sql = 'select a.id,a.type,a.full_code,a.full_name, sum(b.amount) as balance';
	$sql.= ' from account a join account_ledger b on a.id=b.account_id';
	$sql.= ' join account_journal c on b.account_journal_id=c.id ';
	//$sql.= "where substring(kind from 1 for 5) = 'Asset' and date_trunc('$trunc',c.date)='{$this->data['date']}' ";
	$sql.= "where a.kind like '{$kind['kind']}' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
	$sql.= "group by a.id,a.full_code,a.full_name,a.type ";
	$sql.= "order by a.full_code,a.full_name ";
	$AccountList = SQL::fetch_all($sql);

	if (0 == count($AccountList)) {
		continue;
	}

	// Assets
	echo '<tr><th>' . html($kind['kind']) . '</th>';
	echo '<th class="r">Opening</th>';
	echo '<th class="r">Closing</th>';
	echo '<th class="r">Delta</th>';
	echo '</tr>';

	// $this->AssetAccountList = $AssetAccountList;
	$bal = 0; // $sum;
	// $sum = 0;
	foreach ($AccountList as $item) {

		$A = new Account($item);

		$b0 = $A->balanceBefore($this->date_alpha);
		$b1 = $A->balanceAt($this->date_omega);

		switch ($kind['kind']) {
		case 'Asset':
		case 'Asset: Bank':
			$b0 = $b0 * -1;
			$b1 = $b1 * -1;
			break;
		}

		$bal += $item['balance'];

		$arg = array(
			'id' => $item['id'],
			'd0' => $this->date_alpha,
			'd1' => $this->date_omega
		);

		$uri = Radix::link('/account/ledger?' . http_build_query($arg));

		echo '<tr>';
		echo '<td style="text-indent:2em;"><a href="'. $uri . '">' . $item['full_name'] . '</a></td>';
		// echo '<td class="r">' . number_format($item['balance'], 2) . '</td>';



		echo '<td class="r">' . number_format($b0, 2) . '</td>';
		echo '<td class="r">' . number_format($b1, 2) . '</td>';

		echo '<td class="r">' . number_format($b1 - $b0, 2) . '</td>';

		echo '</tr>';

	}

	echo '<tr class="ro">';
	echo '<td class="b" colspan="2">Total ' . $kind['kind'] . ':</td>';
	echo '<td class="r"><span class="du" style="font-weight:bold;">' . number_format($bal, 2) . '</span></td>';
	echo '</tr>';
	echo '<tr><td>&nbsp;</td></tr>';

	$AccountBalanceList[ $kind['kind'] ] = $bal;
}
echo '</table>';

//Radix::dump($AccountBalanceList);

return(0);


// Assets
$sql = 'select a.id,a.full_name,a.full_code,sum(b.amount) as balance';
$sql.= ' from account a join account_ledger b on a.id=b.account_id';
$sql.= ' join account_journal c on b.account_journal_id=c.id ';
//$sql.= "where substring(kind from 1 for 5) = 'Asset' and date_trunc('$trunc',c.date)='{$this->data['date']}' ";
$sql.= "where a.kind like 'Asset%' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
$sql.= "group by a.id,a.full_code,a.full_name ";
$sql.= "order by a.full_code,a.full_name ";
$AssetAccountList = $db->fetchAll($sql);
//Zend_Debug::dump($rs);

// Liabilities
$sql = "select a.id,a.full_name,a.full_code,sum(b.amount) as balance ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
//$sql.= "where substring(kind from 1 for 9) = 'Liability' and date_trunc('$trunc',c.date)='{$this->data['date']}' ";
$sql.= "where a.kind like 'Liability%' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
if (intval(@$_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
$sql.= "group by a.id,a.full_name,a.full_code ";
$sql.= "order by a.full_code,a.full_name ";
$LiabilityAccountList = $db->fetchAll($sql);

$sum = 0;
$list = array();
foreach ($set as $item) {
	$item->balance = ($item->balance * -1);
	$sum += $item->balance;
	$list[$item->full_name] = $item->balance;
}
$this->LiabilityAccountList = $list;
$this->LiabilityAccountBalance = $sum;

// Equity = net_income + capital
$sql = "select a.id,a.full_name,a.code,sum(b.amount) as balance ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
//$sql.= "where substring(kind from 1 for 6) = 'Equity' and date_trunc('$trunc',c.date)='{$this->data['date']}' ";
$sql.= "where a.kind = 'Equity: Owners Capital' and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}'";
if (intval(@$_GET['ex_close']) == 1) {
	$sql.= " and c.kind != 'C' ";
}
$sql.= "group by a.id,a.full_name,a.code ";
$sql.= "order by a.code,a.full_name ";
$set = $db->fetchAll($sql);

$sum = 0;
$list = array();

foreach ($set as $item) {
	$sum += $item->balance;
	$list[$item->full_name] = $item->balance;
}
//$this->set('equity_list',$list);
//$this->set('equity_sum',$sum);
$this->EquityAccountList = $list;
$this->EquityAccountBalance = $sum;



// Input Form
echo $this->form('AccountStatement',array('action'=>$this->appurl.'/account.statement/balance-sheet','class'=>'np'));
echo $this->render('../elements/account-period-input.phtml');
echo '</form>';

// Statement Table
echo '<table class="table">';
// Assets
echo '<tr><th class="l" colspan="3">Assets</th></tr>';

// $this->AssetAccountList = $AssetAccountList;
$AssetAccountBalance = 0; // $sum;
// $sum = 0;
foreach ($AssetAccountList as $item) {
	$item->balance = ($item->balance * -1);
	$uri = $this->link('/account/ledger?' . http_build_query(array('id'=>$item->id,'d0'=>$this->date_alpha,'d1'=>$this->date_omega)));
	echo '<tr><td><a href="'. $uri . '">' . $item->full_name . '</a></td><td class="r">' . number_format($item->balance,2) . '</td></tr>';
	$AssetAccountBalance += $item->balance;
}
echo '<tr class="ro"><td class="b">Total Assets:</td><td class="r"><span class="du">' . number_format($AssetAccountBalance,2) . '</span></td></tr>';
echo '<tr><td>&nbsp;</td></tr>';

// Liabilities
// echo "<tr><td colspan='2'>" . $liability_sql . "</td></tr>";
echo '<tr><th class="l" colspan="3">Liabilities</th></tr>';
foreach ($this->LiabilityAccountList as $k=>$v) {
	echo '<tr><td colspan="2">' . $k . '</td><td class="r">' . number_format($v,2) . '</td></tr>';
}
echo '<tr class="ro"><td class="b" colspan="2">Total Liabilities:</td><td class="r"><span class="du">' . number_format($this->LiabilityAccountBalance,2) . '</span></td></tr>';
echo '<tr><td>&nbsp;</td></tr>';

// Equity = net_income + capital
echo '<tr><th class="l" colspan="3">Equity</th></tr>';
foreach ($this->EquityAccountList as $k=>$v) {
	echo '<tr><td colspan="2">' . $k . '</td><td class="r">' . number_format($v,2) . '</td></tr>';
}
echo '<tr class="ro"><td class="b" colspan="2">Total Equity:</td><td class="r"><span class="du">' . number_format($this->EquityAccountBalance,2) . '</span></td></tr>';

//echo "<tr><td colspan="2">Total Liabilities and Owners Equity:</strong></td><td class='r'>".number_format($this->LiabilityAccountBalance + $this->EquityAccountBalance,2)."</td></tr>";

echo '<tr class="ro"><td class="b">Grand Total:</td>';
echo '<td class="r"><span class="du">' . number_format($this->AssetAccountBalance,2) . '</span>';
echo '<td class="r"><span class="du">' . number_format($this->LiabilityAccountBalance - $this->EquityAccountBalance,2) . '</span>';
echo '</td></tr>';


echo '</table>';

/*
$date = isset($_GET['y']) ? $_GET['y'].'-'.$_GET['m'].'-01' : date('Y-01-01');
if (isset($_GET['date'])) $date = $_GET['date'];

// Determine Period
$x = isset($_GET['p']) ? $_GET['p'] : 'y'; // Default Year

if ($x=='y')
{
  $trunc='year';
  $period_name = 'Yearly';
  $date = date('Y-01-01',strtotime($date));
  $f_date = date('Y',strtotime($date));
}
else
{
  $trunc = 'month';
  $period_name = 'Monthly';
  $date = date('Y-m-01',strtotime($date));
  $f_date = date('m/Y',strtotime($date));
}
*/
