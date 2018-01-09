<?php
/**
    @brief View for reconciling/importing transactions
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

require_once(APP_ROOT . '/lib/Account/Reconcile.php');

$_ENV['title'] = array('Account', 'Reconcile');

if (empty($_ENV['mode'])) {
	$_ENV['mode'] = 'load';
}

// Build list of Accounts
$account_list_json = array();
foreach ($this->AccountList as $i=>$a) {
	$account_list_json[] = array(
		'id' => $a['id'],
		// 'label' => $a['full_name'],
		'value' => $a['full_name'],
	);
};
$account_list_json = json_encode($account_list_json);

?>

<style>
input[type="text"].ar-index {
	font-size: 14px;
	text-align: right;
}
input[type="text"].ar-date {
	width: 6em;
}
input[type="text"].ar-note {
	width: 100%;
}
</style>

<?php

switch ($_ENV['mode']) {
case 'save':
case 'view':
	require_once(__DIR__ . '/reconcile-review.php');
	break;

case 'load':
default:
?>
	<div class="container">
    <form enctype="multipart/form-data" method="post">
    <fieldset>
		<legend>Step 1 - Choose Account and Data File</legend>
    <table class="table">
    <tr>
		<td class="l" title="Transactions are being uploaded for this account">Account:</td>
		<td><?= Form::select('upload_id', $this->Account->id, $this->AccountPairList, array('class' => 'form-control')) ?></td>
	</tr>
	<!-- // echo '<tr><td class="l" title="Default off-set account for the transactions, a pending queue for reconciliation">Offset:</td><td>' . Form::select('offset_id', $_ENV['account']['reconcile_offset_id'], $this->AccountPairList)  . '</td></tr> -->
    <tr>
		<td class="l" title="Which data format is this in?">Format:</td>
		<td><?= Form::select('format',null,Account_Reconcile::$format_list, array('class' => 'form-control')) ?></td>
	</tr>
    <tr>
		<td class="l">File:</td>
		<td>
			<input name="file" type="file">
			<span class="s">(p:<?= ini_get('post_max_size') . '/u:' . ini_get('upload_max_filesize') ?>)</span>
		</td>
	</tr>
    </table>
    <div>
		<input class="btn btn-primary" name="a" type="submit" value="Upload" />
	</div>
    </fieldset>
    </form>

	</div>

<?php

	return(0);
}
