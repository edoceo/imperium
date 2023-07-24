<?php
/**
 * Account Close Period View
 *
 * Wizard for Closing a Period
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\HTML\Form;

$_ENV['h1'] = $_ENV['title'] = array('Accounting', 'Period', 'Close', $this->date_alpha . ' to ' . $this->date_omega);

$this->Account = new Account($_GET['account_id']);

// Check Account Period
$chk_lo = SQL::fetch_row('SELECT * FROM account_period WHERE :d0 >= date_alpha AND :d0 <= date_omega', [ ':d0' => $this->date_alpha ]);
switch ($chk_lo['status_id']) {
	case 100:
		// Period Active
		break;
	case 200:
		// Period Closed
		Session::flash('fail', sprintf('Cannot Operate on "%s"; Period Closed', $this->date_alpha));
	default:
		Session::flash('fail', sprintf('Cannot Operate on "%s"; Period Unknown', $this->date_alpha));
		break;
}

$chk_hi = SQL::fetch_row('SELECT * FROM account_period WHERE :d0 >= date_alpha AND :d0 <= date_omega', [ ':d0' => $this->date_omega ]);
switch ($chk_hi['status_id']) {
	case 100:
		// Period Active
		break;
	case 200:
		// Period Closed
		Session::flash('fail', sprintf('Cannot Operate on "%s"; Period Closed', $this->date_omega));
		break;
	default:
		Session::flash('fail', sprintf('Cannot Operate on "%s"; Period Unknown', $this->date_omega));
		break;
}

// @todo To determine if a period is closed we need
// a closing entry in revenue
// a closing entry in expense
// a closing entry in capital
// a closing entry in drawing

// $sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance,count(b.id) as journal_count ";
// $sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
// $sql.= " where ( a.kind = 'Asset: Income Summary' ) ";
// $sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
// $sql.= " and c.kind = 'C' ";
// $sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
// $sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
// $this->Close_Revenue_List = $db->fetchAll($sql);
// print_r($this->Close_Revenue_List);
// exit;

echo '<form method="get">';
echo '<div class="d-flex">';
echo '<div class="me-2"><h2>Closing Period:</h2></div>';
echo '<div class="me-2">' . Form::date('d0', $this->date_alpha, [ 'class' => 'form-control', 'style' => 'width: 14em;' ]) . '</div>';
echo '<div class="me-2">' . Form::date('d1', $this->date_omega, [ 'class' => 'form-control', 'style' => 'width: 14em;' ]) . '</div>';
echo '<div class="me-2">' . Form::select('account_id', $this->Account['id'], $this->AccountList_Select, [ 'class' => 'form-control'] ) . '</div>';
echo '<div class="me-2"><button class="btn btn-primary" name="c" type="submit" value="view">View</button></div>';
echo '</div>';
echo '</form>';

// Has Revenue Been Closed (zero balance)?
echo '<h2>Revenue Accounts</h2>';
echo '<table class="table">';
echo '<thead>';
echo '<tr><th>Account</th><th>Close</th><th class="r">Debit</th><th class="r">Credit</th></tr>';
echo '</thead>';

// Revenue Query
$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance, ";
$sql.= " CASE c.kind WHEN 'N' THEN 1 WHEN 'A' THEN 2 WHEN 'C' then 3 end as kind_sort ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where (substring(a.kind from 1 for 7) = 'Revenue' ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
$sql.= "order by a.full_code,a.code,a.full_name,kind_sort ";
$this->Close_Revenue_List = SQL::fetch_all($sql);

$rv_total = 0;
$rv_total_n = 0;
$rv_total_c = 0;
if (count($this->Close_Revenue_List)) {

	foreach ($this->Close_Revenue_List as $line) {

		$data = array(
			'line' => $line,
			'date_alpha' => $this->date_alpha,
			'date_omega' => $this->date_omega
		);
		echo Radix::block('account-close-line', $data);

		if ($line['kind'] == 'C') {
			$rv_total_c += $line['balance'];
		} else {
			$rv_total_n += $line['balance'];
		}
	}
	$rv_total = $rv_total_n + $rv_total_c;
}
if ($rv_total == 0) {
	echo '<tr><th colspan="2">Revenues Closed</th>';
	echo '<th class="r">' . number_format($rv_total_n,2) . '</th>';
	echo '<th class="r">' . number_format($rv_total_c,2) . '</th>';
	echo '</tr>';
} else {

	// New Transaction Holder
	$at = [];
	$at['je'] = [];
	$at['je']['kind'] = 'C';
	$at['je']['date'] = $this->date_omega;
	$at['je']['note'] = 'Closing Revenues to Income Summary';
	$at['le'] = [];

	// Find List of Accounts to Close
	$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,sum(b.amount) as balance ";
	$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
	$sql.= " where (substring(a.kind from 1 for 7) = 'Revenue' ) ";
	$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
	$sql.= " and c.kind != 'C' ";
	$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name ";
	$sql.= "order by a.full_code,a.code,a.full_name ";
	$res = SQL::fetch_all($sql);
	foreach ($res as $a) {
		// $ale = new AccountLedgerEntry();
		$le = [];
		$le['account_id'] = $a['id'];
		$le['account_name'] = $a['full_name'];
		$le['amount'] = $a['balance'] * -1;
		$at['le'][] = $le;
	}

	// Close to Income Summary (Credit)
	$le = []; // new AccountLedgerEntry();
	$le['account_id'] = $this->Account['id'];
	$le['account_name'] = $this->Account['full_name'];
	$le['amount'] = $rv_total_n;
	$at['le'][] = $le;

	_close_account_button($at, $rv_total_n, $rv_total_c);

}
echo '</table>';

// Has Expense Been Closed?
echo '<h2>Expense Accounts</h2>';
echo '<table class="table">';
echo '<thead>';
echo '<tr><th>Expense Accounts</th><th>Close</th><th class="r">Debit</th><th class="r">Credit</th></tr>';
echo '</thead>';

// Expense Query
$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance, ";
$sql.= " CASE c.kind WHEN 'N' THEN 1 WHEN 'A' THEN 2 WHEN 'C' then 3 end as kind_sort ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where (substring(a.kind from 1 for 7) = 'Expense' ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
// $sql.= " and c.kind = 'C' ";
$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
$this->Close_Expense_List = SQL::fetch_all($sql);

// $sql.= " and c.kind != 'C' ";

$ex_total = 0;
$ex_total_n = 0;
$ex_total_c = 0;
if (count($this->Close_Expense_List)) {
	foreach ($this->Close_Expense_List as $line) {

		$data = array(
			'line' => $line,
			'date_alpha' => $this->date_alpha,
			'date_omega' => $this->date_omega
		);
		echo Radix::block('account-close-line', $data);

		if ($item['kind'] == 'C') {
			$ex_total_c += $line['balance'];
		} else {
			$ex_total_n += $line['balance'];
		}
	}
	$ex_total = $ex_total_n + $ex_total_c;
}
if ($ex_total == 0) {
	echo '<tr><th colspan="2">Expenses Closed</th>';
	echo '<th class="r">' . number_format($ex_total_n,2) . '</th>';
	echo '<th class="r">' . number_format($ex_total_c,2) . '</th>';
	echo '</tr>';
} else {

	// Has Not Been Properly Close
	// @todo try to find the closing transaction(s)
	// New Transaction Holder
	$at = [];
	$at['je'] = [];
	$at['je']['kind'] = 'C';
	$at['je']['date'] = $this->date_omega;
	$at['je']['note'] = 'Closing Expenses to Income Summary';
	$at['le'] = [];

	// Close to Income Summary (Debit)
	$le = [];
	$le['account_id'] = $this->Account['id'];
	$le['account_name'] = $this->Account['full_name'];
	$le['amount'] = $ex_total;
	$at['le'][] = $le;

	// Find List of Accounts to Close
	// Duplicate to query above, just filtering the type 'C'
	$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance ";
	$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
	$sql.= " where (substring(a.kind from 1 for 7) = 'Expense' ) ";
	$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
	$sql.= " and c.kind != 'C' ";
	$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
	$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
	$res = SQL::fetch_all($sql);
	foreach ($res as $a) {
		$le = [];
		$le['account_id'] = $a['id'];
		$le['account_name'] = $a['full_name'];
		$le['amount'] = $a['balance'] * -1;
		$at['le'][] = $le;
	}

	_close_account_button($at, $ex_total_n, $ex_total_c);

	echo '</table>';

	return(0);
}
echo '</table>';

// Debit Income Summary and Credit Owners Capital
$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance, ";
$sql.= " CASE c.kind WHEN 'N' THEN 1 WHEN 'A' THEN 2 WHEN 'C' then 3 end as kind_sort ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where ( a.id = 57 ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
$sql.= " and c.kind = 'C' ";
$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
$Income_Summary_List = SQL::fetch_all($sql);

$incsum_cr = 0;
$incsum_dr = 0;
$incsum_total = 0;

echo '<h2>Income Summary</h2>';
echo '<table class="table">';
echo '<thead>';
echo '<tr><th colspan="4">Close Income Summary to Owners Capital</th></tr>';
echo '<tr><th>Account</th><th>Close</th><th class="r">Debit</th><th class="r">Credit</th></tr>';
echo '</thead>';

if (count($Income_Summary_List)) {

	foreach ($Income_Summary_List as $line) {

		$data = array(
			'line' => $line,
			'date_alpha' => $this->date_alpha,
			'date_omega' => $this->date_omega
		);

		echo Radix::block('account-close-line', $data);

		$incsum_total += $line['balance'];

	}
	// Is Closed?
	// Closed is With Transactions and Zero Balance
	if ($incsum_total == 0) {
		echo '<tr><th colspan="3">Income Closed</th><th class="r">0.00</th></tr>';
	} else {
		// New Transaction Holder
		$at = [];
		$at['je'] = [];
		$at['je']['kind'] = 'C';
		$at['je']['date'] = $this->date_omega;
		$at['je']['note'] = 'Close Income Summary to Owners Capital';
		$at['le'] = [];

		// Close Income Summary (Dr) to Owners Capital (Cr)
		$le = [];
		$le['account_id'] = $this->Account['id'];
		$le['account_name'] = $this->Account['full_name'];
		$le['amount'] = ($incsum_total * -1);
		$at['le'][] = $le;

		// To Owners Capital
		// $a = new Account(6);
		$le = [];
		// $le['account_id'] = $a['id'];
		// $le['account_name'] = $a['full_name'];
		$le['amount'] = $incsum_total;
		$at['le'][] = $le;

		_close_account_button($at, $incsum_total, $incsum_total);

	}
} else {
	echo '<tr><th colspan="3">Pending</th><th class="r">' . number_format($incsum_total,2) . '</th></tr>';
}
echo '</table>';

// Credit Owners Capital and Debit Owners Drawing
$od_total = 0;
$od_total_c = 0;
$od_total_n = 0;
$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where ( a.kind = 'Equity: Owners Drawing' ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
// $sql.= " and c.kind = 'C' ";
$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
$Close_Drawing_List = SQL::fetch_all($sql);

echo '<h2>Drawings to Capital</h2>';
echo '<table class="table">';
echo '<tr><th colspan="4">Close Drawing to Capital</th></tr>';
if (count($Close_Drawing_List)) {

	foreach ($Close_Drawing_List as $line) {

		// print_r($item);
		// echo "<tr class='rero'>";
		// echo '<td><strong>' . $item['full_code'] . '</strong>&mdash;<a href="' . Radix::link('/account/ledger?id=' . $item->id) . '">' . $item->name . '</a></td>';
		// echo '<td class="c">' . $item['kind'] . '</td>';
		// echo '<td>&nbsp;</td>';
		// echo '<td class="r">' . number_format($item['balance'] * -1,2) . '</td>';
		// echo '</tr>';

		$data = array(
			'line' => $line,
			'date_alpha' => $this->date_alpha,
			'date_omega' => $this->date_omega
		);

		echo Radix::block('account-close-line', $data);

		if ($line['kind'] == 'C') {
			$od_total_c += $line['balance'];
		} else {
			$od_total_n += $line['balance'];
		}
	}
	$od_total = ($od_total_n + $od_total_c);
}
// $od_total will be a negative number
if ($od_total_c == 0) {
	// Has Not Been Properly Close
	// @todo try to find the closing transaction(s)

	// New Transaction Holder
	if (empty($s->AccountTransaction)) {

		$at = [];
		$at['je'] = [];
		$at['je']['kind'] = 'C';
		$at['je']['date'] = $this->date_omega;
		$at['je']['note'] = 'Close Owners Drawing to Owners Capital';
		$at['le'] = [];

		// Debit Owners Capital
		$a = new Account(6);
		$le = [];
		$le['account_id'] = $a['id'];
		$le['account_name'] = $a['full_name'];
		$le['amount'] = $od_total_n;
		$at['le'][] = $le;

		// Credit Owners Drawing
		$a = new Account(7);
		$le = [];
		$le['account_id'] = $a['id'];
		$le['account_name'] = $a['full_name'];
		$le['amount'] = $od_total_n * -1;
		$at['le'][] = $le;

		_close_account_button($at, $od_total_n, $od_total_n);

	}

} else {
	echo '<tr><th colspan="3">Income &amp; Capital Closed</th><th class="r">0.00</th></tr>';
}

echo '<tr>';
echo '<td><strong>Period Totals</strong></td>';
echo '<td>&nbsp;</td>';
echo '<td class="b r">' . number_format($ex_total_n *-1 ,2 ) . '</td>';
echo '<td class="b r">' . number_format($rv_total_n ,2 ) . '</td>';
echo '</tr>';

$pl_total = $rv_total_n + $ex_total_n;

if ($pl_total >= 0) {
	echo "<tr class='rero'>";
	echo "<td colspan='3'><strong>Period Profit</strong></td><td class='b r'>&curren;".number_format($pl_total,2)."</td></tr>";
} else {
	echo "<tr class='rero'>";
	echo "<td colspan='2'><strong>Period Loss</strong></td><td class='b r'>&curren;".number_format($pl_total,2)."</td></tr>";
}

echo '</table>';

if ( ($rv_total == 0) && ($ex_total == 0) && ($incsum_total == 0) && ($od_total == 0) ) {
// if ( ($ex_total + $rv_total + $incsum_total + $od_total) == 0) {
	echo '<p class="info">This Period is Closed</p>';
	// return(0);
} else {
	echo "<p>( ($rv_total == 0) && ($ex_total == 0) && ($incsum_total == 0) && ($od_total == 0) )</p>";
}

// Helper Notes
echo '<h2>Steps to Close the Books</h2>';

echo "<pre>\n";

// Revenues Debit Invoice Summary, Credit the Others
// @todo these logic statements actually generate the Transactions
//	   would be cool to have a link from here to Close using transactionAction

// Close Revenues to Income Summary
echo "DR Revenue accounts for balance and CR Income Summary for $rv_total_n\n";
// Close Expenses to Income Summary
echo "DR Income Summary for $ex_total_n and CR each of the Expense accounts for their balance\n";
// Close Income Summary to Owners Capital
if ($incsum_total > 0) {
  echo "DR Income Summary for ".number_format($pl_total*-1,2)." and CR Owners Capital for ".number_format($pl_total,2)."\n";
} else {
  echo "CR Income Summary for $incsum_total and DR Capital (Loss)\n";
}
if ($od_total != 0) {
  echo "DR Owners Capital ".number_format($od_total,2)." and CR Owners Drawing for ".number_format($od_total*-1,2)."\n";
}
echo '</pre>';


/**
 * Close Account Button Helper
 */
function _close_account_button($tt, $dr, $cr)
{
	if ( ! is_string($tt)) {
		$tt = sodium_bin2base64(json_encode($tt), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
	}

	$link = sprintf('%s?%s', Radix::link('/account/transaction'), http_build_query([
		'tt'=> $tt,
		'r' => Radix::link('/account/close'),
	]));

	echo '<tfoot>';
	echo '<tr>';
	echo '<td colspan="2"><a class="btn btn-primary" href="' . $link . '">Close these Accounts</a></td>';
	echo '<td class="r">' . number_format($dr,2) . '</td>';
	echo '<td class="r">' . number_format($cr,2) . '</td>';
	echo '</tr>';
	echo '</tfoot>';

}
