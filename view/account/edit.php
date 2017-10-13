<?php
/**
	Account Edit
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\DB\SQL;
use Edoceo\Radix\HTML\Form;

$this->Account = new Account($_GET['id']);

// Parent Account
$Account_Parent_list = array(0 => '- None -');
$Account_Parent_list[0] = '- None -';
foreach ($this->AccountList as $x) {
	if (!empty($x['parent_id'])) {
		continue;
	}
	$Account_Parent_list[$x['id']] = $x['full_name'];
}

$res = AccountTaxFormLine::listTaxLines();
$Account_TaxRec_list = array(0 => '- None -');
foreach ($res as $x) {
	$Account_TaxRec_list[ $x['id'] ] = $x['name'];
}

?>

<form action="<?= Radix::link('/account/save') ?>" method="post">
<div class="container">

<div class="row">
<div class="col-md-6">
	<div class="form-group">
		<label>Kind:</label>
		<?= Form::select('kind',$this->Account['kind'], $this->AccountKindList, array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-6">
	<div class="form-group">
		<label>Parent:</label>
		<?= Form::select('parent_id', $this->Account['parent_id'], $Account_Parent_list, array('class' => 'form-control')) ?>
	</div>
</div>
</div>

<div class="row">
<div class="col-md-4">
	<div class="form-group">
		<label>Code:</label>
		<?= Form::text('code', $this->Account['code'], array('class' => 'form-control', 'size'=> 6)) ?>
	</div>
</div>
<div class="col-md-8">
	<div class="form-group">
		<label>Name:</label>
		<?= Form::text('name', $this->Account['name'], array('class' => 'form-control')) ?>
	</div>
</div>
</div>

<div class="row">
<div class="col-md-4">
	<div class="form-group">
		<label>Account:</label>
		<?= Form::text('bank_account', $this->Account['bank_account'], array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-4">
	<div class="form-group">
		<label>Transit:</label>
		<?= Form::text('bank_routing', $this->Account['bank_routing'], array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-4">
	<div class="form-group">
		<label>Tax Line:</label>
		<?= Form::select('account_tax_line_id', $this->Account['account_tax_line_id'], $Account_TaxRec_list, array('class' => 'form-control')) ?>
	</div>
</div>
</div> <!-- /.row -->


<div class="cmd">
	<?= Form::hidden('id', $this->Account['id']) ?>
	<button class="btn btn-primary" name="a" type="submit" value="Save">Save</button>
	<button class="btn btn-danger" name="a" type="submit" value="Delete">Delete</button>
</div>

<?php
// Note
// echo "<tr><td class='b r'>Note:</td><td colspan='2'>" . $this->formText('note',$this->Account->note,array('size'=>40)) . "</td></tr>";

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

/*
echo $imperiumForm->checkbox('Income Statement');
echo $imperiumForm->checkbox('Equity Statement');
echo $imperiumForm->checkbox('Balance Sheet');
echo $imperiumForm->checkbox('Cash Flow');
*/

// Show Transaction Count
$res = SQL::fetch_one('SELECT count(id) FROM account_ledger WHERE account_id = ?', array($this->Account['id']));
echo '<p>' . $res . ' total transactions in this account</p>';

echo '</div>';
echo '</form>';
