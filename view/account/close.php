<?php
/**
	Account Close Period View
	Wizard for Closing a Period
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\HTML\Form;

$_ENV['h1'] = $_ENV['title'] = array('Accounting', 'Period', 'Close', $this->date_alpha . ' to ' . $this->date_omega);

$income_summary_account_id = 57;

$sql = 'SELECT * FROM account_period';
$res = SQL::fetch_all($sql);
//var_dump($res);


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
echo '<div style="margin:0.5em 0 0.5em 0">';
echo '<div style="display:flex;">';
echo '<div style="flex:1 1 auto;">' . Form::select('id', $this->Account['id'], $this->AccountList_Select) . '</div>';
echo '<div style="flex:1 1 auto;">' . Form::date('d0', $this->date_alpha, array('size'=>12)) . '</div>';
echo '<div style="flex:1 1 auto;">' . Form::date('d1', $this->date_omega, array('size'=>12)) . '</div>';
echo '<div style="flex:1 1 auto;"><input name="c" type="submit" value="View"></div>';
echo '<div style="flex:1 1 auto;"><input name="c" type="submit" value="Post"></div>';
echo '</div>';
echo '</div>';
echo '</form>';

// Has Revenue Been Closed (zero balance)?
echo '<h2>Closing Revenue</h2>';
echo '<table>';
echo '<tr><th>Account</th><th>Close</th><th>Debit</th><th>Credit</th></tr>';

$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance, ";
$sql.= " CASE c.kind WHEN 'N' THEN 1 WHEN 'A' THEN 2 WHEN 'C' then 3 end as kind_sort "; 
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where (substring(a.kind from 1 for 7) = 'Revenue' ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
// $sql.= " and c.kind = 'C' ";
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
	// echo '<tr><th colspan="4">Revenues not Closed</th></tr>';
	// Has Not

	// New Transaction Holder
	$at = new \stdClass();
	$at->AccountJournalEntry = new AccountJournalEntry();
	$at->AccountJournalEntry['kind'] = 'C';
	$at->AccountJournalEntry['date'] = $this->date_omega;
	$at->AccountJournalEntry['note'] = 'Closing Revenues to Income Summary';
	$at->AccountLedgerEntryList = array();

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
		$ale = new AccountLedgerEntry();
		$ale['account_id'] = $a['id'];
		$ale['account_name'] = $a['full_name'];
		$ale['amount'] = $a['balance'] * -1;
		$at->AccountLedgerEntryList[] = $ale;
	}

	// Close to Income Summary (Credit)
	$a = new Account(57);
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	$ale['amount'] = $rv_total_n;
	$at->AccountLedgerEntryList[] = $ale;

	$_SESSION['account-transaction'] = $at;
	$_SESSION['return-path'] = '/account/close'; // $s->ReturnGood
	//$s->ReturnTo = '/account/close';

	echo '<tr class="fail">';
	echo '<td colspan="2"><a href="' . Radix::link('/account/transaction') . '">Close these Accounts</a></td>';
	echo '<td class="r">' . number_format($rv_total_n,2) . '</td>';
	echo '<td class="r">' . number_format($rv_total_c,2) . '</td>';
	echo '</tr>';
	echo '</table>';

	return(0);
}
echo '</table>';

// Has Expense Been Closed?
echo '<h2>Expense Accounts</h2>';
echo '<table>';
echo '<tr><th class="l" colspan="4">Expense Accounts</th></tr>';

$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance, ";
$sql.= " CASE c.kind WHEN 'N' THEN 1 WHEN 'A' THEN 2 WHEN 'C' then 3 end as kind_sort ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where (substring(a.kind from 1 for 7) = 'Expense' ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
// $sql.= " and c.kind = 'C' ";
$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
$this->Close_Expense_List = SQL::fetch_all($sql);

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
	$at = new \stdClass();
	$at->AccountJournalEntry = new AccountJournalEntry();
	$at->AccountJournalEntry['kind'] = 'C';
	$at->AccountJournalEntry['date'] = $this->date_omega;
	$at->AccountJournalEntry['note'] = 'Closing Expenses to Income Summary';
	$at->AccountLedgerEntryList = array();

	// Close to Income Summary (Debit)
	$a = new Account(57);
	$ale = new AccountLedgerEntry();
	$ale['account_id'] = $a['id'];
	$ale['account_name'] = $a['full_name'];
	$ale['amount'] = $ex_total;
	$at->AccountLedgerEntryList[] = $ale;

	// Find List of Accounts to Close
	$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance ";
	$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
	$sql.= " where (substring(a.kind from 1 for 7) = 'Expense' ) ";
	$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
	$sql.= " and c.kind != 'C' ";
	$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
	$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
	$res = SQL::fetch_all($sql);
	foreach ($res as $a) {
		$ale = new AccountLedgerEntry();
		$ale['account_id'] = $a['id'];
		$ale['account_name'] = $a['full_name'];
		$ale['amount'] = $a['balance'] * -1;
		$at->AccountLedgerEntryList[] = $ale;
	}
	$_SESSION['account-transaction'] = $at;
	$_SESSION['return-path'] = '/account/close'; // $s->ReturnGood

	echo '<tr class="fail">';
	echo '<td colspan="2"><a href="' . $this->link('/account/transaction') . '">Close these Accounts</a></td>';
	echo '<td class="r">' . number_format($ex_total_n,2) . '</td>';
	echo '<td class="r">' . number_format($ex_total_c,2) . '</td>';
	echo '</tr>';
	echo '</table>';

	return(0);
}
echo '</table>';

echo '<h2>Income Summary</h2>';
echo '<table>';

// Debit Income Summary and Credit Owners Capital
echo '<tr><th class="l" colspan="4">Close Income Summary to Owners Capital</th></tr>';
$is_total = 0;
$sql = "select a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind,sum(b.amount) as balance, ";
$sql.= " CASE c.kind WHEN 'N' THEN 1 WHEN 'A' THEN 2 WHEN 'C' then 3 end as kind_sort ";
$sql.= "from account a join account_ledger b on a.id=b.account_id join account_journal c on b.account_journal_id=c.id ";
$sql.= " where ( a.id = 57 ) ";
$sql.= " and c.date >= '{$this->date_alpha}' and c.date <= '{$this->date_omega}' ";
// $sql.= " and c.kind = 'C' ";
$sql.= "group by a.id,a.code,a.kind,a.full_code,a.name,a.full_name,c.kind ";
$sql.= "order by a.full_code,a.code,a.full_name,c.kind desc ";
$Income_Summary_List = SQL::fetch_all($sql);
if (count($Income_Summary_List)) {

	foreach ($Income_Summary_List as $line) {

		$data = array(
			'line' => $line,
			'date_alpha' => $this->date_alpha,
			'date_omega' => $this->date_omega
		);

		echo Radix::block('account-close-line', $data);

		// print_r($item);
		// echo "<tr class='rero'>";
		// echo '<td><strong>' . $item->full_code . '</strong>&mdash;<a href="' . $this->link('/account/ledger/id=' . $item->id) . '">' . $item->name . '</a></td>';
		// echo '<td class="c">' . $item->kind . '</td>';
		// echo '<td>&nbsp;</td>';
		// echo '<td class="r">' . number_format($item->balance,2) . '</td>';
		// echo '</tr>';

		$is_total += $line['balance'];

	}
	// Is Closed?
	// Closed is With Transactions and Zero Balance
	if ($is_total == 0) {
		echo '<tr><th colspan="3">Income Closed</th><th class="r">0.00</th></tr>';
	} else {
		// New Transaction Holder
		if (empty($s->AccountTransaction)) {
			$at = new \stdClass();
			$at->AccountJournalEntry = new AccountJournalEntry();
			$at->AccountJournalEntry['kind'] = 'C';
			$at->AccountJournalEntry['date'] = $this->date_omega;
			$at->AccountJournalEntry['note'] = 'Close Income Summary to Owners Capital';
			$at->AccountLedgerEntryList = array();

			// Close Income Summary (Dr) to Owners Capital (Cr)
			$a = new Account(57);
			$ale = new AccountLedgerEntry();
			$ale['account_id'] = $a['id'];
			$ale['account_name'] = $a['full_name'];
			$ale['amount'] = ($is_total * -1);
			$at->AccountLedgerEntryList[] = $ale;
			// To Owners Capital
			$a = new Account(6);
			$ale = new AccountLedgerEntry();
			$ale['account_id'] = $a['id'];
			$ale['account_name'] = $a['full_name'];
			$ale['amount'] = $is_total;
			$at->AccountLedgerEntryList[] = $ale;

			$_SESSION['account-transaction'] = $at;
			$_SESSION['return-path'] = '/account/close';

			echo '<tr class="fail">';
			echo '<td colspan="2"><a href="' . $this->link('/account/transaction') . '">Close these Accounts</a></td>';
			echo '<td class="r">' . number_format($ex_total_n,2) . '</td>';
			echo '<td class="r">' . number_format($ex_total_c,2) . '</td>';
			echo '</tr>';
			echo '</table>';

			return(0);

		}
	}
} else {
	echo '<tr><th colspan="3">Pending</th><th class="r">' . number_format($is_total,2) . '</th></tr>';
}
echo '<tr><td colspan="4">&nbsp;</td></tr>';

// Credit Owners Capital and Debit Owners Drawing
echo '<tr><th colspan="4">Close Drawing to Capital</th></tr>';
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
		$at = new \stdClass();
		$at->AccountJournalEntry = new AccountJournalEntry();
		$at->AccountJournalEntry->kind = 'C';
		$at->AccountJournalEntry->date = $this->date_omega;
		$at->AccountJournalEntry->note = 'Close Owners Drawing to Owners Capital';
		$at->AccountLedgerEntryList = array();

		// Debit Owners Capital
		$a = new Account(6);
		$ale = new AccountLedgerEntry();
		$ale->account_id = $a->id;
		$ale->account_name = $a->full_name;
		$ale->amount = $od_total_n;
		$at->AccountLedgerEntryList[] = $ale;

		// Credit Owners Drawing
		$a = new Account(7);
		$ale = new AccountLedgerEntry();
		$ale->account_id = $a->id;
		$ale->account_name = $a->full_name;
		$ale->amount = $od_total_n * -1;
		$at->AccountLedgerEntryList[] = $ale;

		echo '<tr>';
		echo '<td class="fail" colspan="4"><a href="' . $this->link('/account/transaction') . '">Close these Accounts</a></td>';
		echo '</tr>';

		$s->AccountTransaction = $at;
		$s->ReturnGood = '/account/close';
		$s->ReturnTo = '/account/close';
	}
} else {
	echo '<tr><th colspan="3">Income &amp; Capital Closed</th><th class="r">0.00</th></tr>';
}

echo '<tr class="rero">';
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

if ( ($rv_total == 0) && ($ex_total == 0) && ($is_total == 0) && ($od_total == 0) ) {
// if ( ($ex_total + $rv_total + $is_total + $od_total) == 0) {
	echo '<p class="info">This Period is Closed</p>';
	return(0);
} else {
	echo "<p>( ($rv_total == 0) && ($ex_total == 0) && ($is_total == 0) && ($od_total == 0) )</p>";
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
if ($is_total>0) {
  echo "DR Income Summary for ".number_format($pl_total*-1,2)." and CR Owners Capital for ".number_format($pl_total,2)."\n";
} else {
  echo "CR Income Summary for $is_total and DR Capital (Loss)\n";
}
if ($od_total != 0) {
  echo "DR Owners Capital ".number_format($od_total,2)." and CR Owners Drawing for ".number_format($od_total*-1,2)."\n";
}
echo '</pre>';
