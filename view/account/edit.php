<?php
/**
    @todo Link this Account to A Customer
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\HTML\Form;

$this->Account = new Account($_GET['id']);

// $this->form('Account',array('action'=>$this->appurl.'/account/save','method'=>'post'));
echo '<form action="' . Radix::link('/account/save') . '" method="post">';
echo '<table>';

echo "<tr><td class='l'>Kind:</td><td colspan='2'>" . Form::select('kind',$this->Account['kind'], $this->AccountKindList) . "</td></tr>";
echo "<tr><td class='l'>Code &amp; Name:</td><td>" . Form::text('code', $this->Account['code'],array('size'=>6)) . '</td>';
echo '<td>' . Form::text('name', $this->Account['name']) . '</td>';
echo '</tr>';

// Note
// echo "<tr><td class='b r'>Note:</td><td colspan='2'>" . $this->formText('note',$this->Account->note,array('size'=>40)) . "</td></tr>";

// Parent Account
$list = array(0 => '- None -');
$list[0] = '- None -';
foreach ($this->AccountList as $item) {
    if (!empty($item['parent_id'])) continue;
	$list[$item['id']] = $item['full_name'];
}
echo "<tr><td class='b r'>Parent:</td><td colspan='2'>" . Form::select('parent_id', $this->Account['parent_id'], $list) . '</td></tr>';

/*
echo "<tr><td class='b r'>Type:</td><td><select name='flag_life'>";
foreach (array('Permanant'=>Account::PERMANENT,'Temporary'=>Account::TEMPORARY) as $k=>$v)
  echo "<option ".($a->flag&$v?"selected='selected' ":null)."value='$v'>$k</option>";
echo "</select><select name='flag_type'>";
foreach (array('Asset'=>Account::ASSET,'Liability'=>Account::LIABILITY,'Equity'=>Account::EQUITY,'Revenue'=>Account::REVENUE,'Expense'=>Account::EXPENSE) as $k=>$v)
  echo "<option ".($a->flag&$v?"selected='selected' ":null)."value='$v'>$k</option>";
echo "</select></td></tr>\n";

echo "<tr><td class='b r'>Class:</td><td><select name='flag_class'><option value='0'>None</option>";
foreach (array('Cash'=>Account::CASH,'Accounts Receiveable'=>Account::AR,'Accounts Payable'=>ACCOUNT::AP) as $k=>$v)
  echo "<option ".($a->flag&$v?"selected='selected' ":null)."value='$v'>$k</option>";
echo "</select><select name='flag_bank'><option value='0'>None</option>";
foreach (array('Checking'=>Account::CHECKING,'Savings'=>Account::SAVINGS,'Market'=>Account::MARKET) as $k=>$v)
  echo "<option ".($a->flag&$v?"selected='selected' ":null)."value='$v'>$k</option>";
echo "</select></td></tr>\n";
*/

$list = array(0 => '- None -');
$AccountTaxLineList = AccountTaxFormLine::listTaxLines();
foreach ($AccountTaxLineList as $x) {
	$list[ $x['id'] ] = $x['name'];
}
// Radix::dump($AccountTaxLineList);
// $list += $AccountTaxLineList;
echo "<tr><td class='b r'>Tax Line:</td><td colspan='3'>" . Form::select('account_tax_line_id', $this->Account->account_tax_line_id, $list) . "</td>";

/*
// Asset Details
echo "<tr><td class='b r'>Opening Balance:</td><td>" . $imperiumForm->input('Account.code',am($opts,array('class'=>'tb','size'=>8))) . "</td></tr>";
*/

// Kind Bank Account Details
echo "<tr><td class='b r'>Transit:</td><td colspan='2'>" . Form::text('bank_routing', $this->Account->bank_routing) . '</td></tr>';
echo "<tr><td class='b r'>Account:</td><td colspan='2'>" . Form::text('bank_account', $this->Account->bank_account) . '</td></tr>';

echo '</table>';

/*
echo $imperiumForm->checkbox('Income Statement');
echo $imperiumForm->checkbox('Equity Statement');
echo $imperiumForm->checkbox('Balance Sheet');
echo $imperiumForm->checkbox('Cash Flow');
*/

echo '<div class="cmd">';
echo Form::hidden('id', $this->Account['id']);
echo '<input name="a" type="submit" value="Save">';
echo '<input name="a" type="submit" value="Delete">';
echo '</div>';

echo '</form>';

// Show Transaction Count
$res = SQL::fetch_one('SELECT count(id) FROM account_ledger WHERE account_id = ?', array($this->Account['id']));
echo '<p>' . $res . ' total transactions in this account</p>';
