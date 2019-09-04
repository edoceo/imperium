<?php
/**
	Review the Uploaded Transactions
*/

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

$_ENV['title'] = array('Account', 'Reconcile', $this->Account['full_name'], 'Preview');

$cr = 0;
$dr = 0;
$le_i = 0;
$date_alpha = $date_omega = null;

// View the Pending Transactions
?>

<table class="table table-sm table-hover">
<thead>
<tr>
	<th>#</th>
	<th>Date</th>
	<th>Note</th>
	<th>Account</th>
	<th>Debit</th>
	<th>Credit</th>
	<th>JE</th>
	<th>-</th>
</tr>

<tr>
	<td></td>
	<td><input class="form-control ar-date" id="filter-date" type="text"></td>
	<td><input class="form-control ar-note" id="filter-note" type="text"></td>
	<td><input class="form-control" id="update-account" type="text"></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
</thead>

<tbody>
<?php
foreach ($this->JournalEntryList as $je) {

	if (!empty($je['id'])) {
		continue;
	}

	$offset_id = $_ENV['offset_account_id'];

	foreach ($je['ledger_entry_list'] as $le) {

		$le_i++;

		echo '<tr class="reconcile-item reconcile-show journal-entry-index-' . $le_i . '" id="journal-entry-index-' . $le_i . '">';
		echo '<td class="ar-index">' . $le_i . '</td>';

		echo '<td><input class="form-control ar-date" id="' . sprintf('date-%d', $le_i) . '" name="' . sprintf('je%ddate', $le_i) . '" type="text" value="' . $je['date'] . '"></td>';
		echo '<td><input class="form-control reconcile-entry-note ar-note" id="' . sprintf('note-%d', $le_i) . '" name="' . sprintf('je%dnote', $le_i) . '" type="text" value="' . (!empty($le['note']) ? $le['note'] : $je['note']) . '"></td>';
		// echo $this->formText('note',$je->note,array('style'=>'width:25em'));
		// @todo Determine Side, which depends on the Kind of the Account for which side is which

		// @todo Autocomplete
		echo '<td>';
		// echo Form::select(sprintf('je%daccount_id',$le_i), $offset_id, $this->AccountPairList);
		echo '<input class="account-id" id="' . sprintf('account_id-%d', $le_i) . '" name="' . sprintf('account_id-%d', $le_i) . '" type="hidden" value="">';
		echo '<input class="form-control account-name ui-autocomplete-input ar-note" data-index="' . $le_i . '" id="' . sprintf('account_name-%d', $le_i) . '" name="' . sprintf('account_name-%d', $le_i) . '" type="text" value="" autocomplete="off">';
		echo '</td>';

		if (!empty($le['dr'])) {
			$dr += floatval($le['dr']);
			echo '<td class="r">' . Form::number(sprintf('je%ddr',$le_i), sprintf('%0.2f', $le['dr']), array('class' => 'form-control', 'step'=>0.01)) . '</td>';
			echo '<td>&nbsp;</td>';
		} elseif (!empty($le['cr'])) {
			$cr += floatval($le['cr']);
			echo '<td>&nbsp;</td>';
			echo '<td class="r">' . Form::number(sprintf('je%dcr',$le_i), sprintf('%0.2f', $le['cr']), array('class' => 'form-control', 'step'=>0.01)) . '</td>';
		} else {
			echo '<td class="r">' . Form::number(sprintf('je%ddr',$le_i), sprintf('%0.2f', $le['dr']), array('class' => 'form-control', 'step'=>0.01)) . '</td>';
			echo '<td class="r">' . Form::number(sprintf('je%dcr',$le_i), sprintf('%0.2f', $le['cr']), array('class' => 'form-control', 'step'=>0.01)) . '</td>';
		}

		// Lookup / Found?
		echo '<td>';
		if ($je['id']) {
			echo '<a href="' . Radix::link('/account/transaction?id=' . $je['id']) . '">' . $je['id'] . '</a>';
			echo '<input name="' . sprintf('je%did', $le_i) . '" type="hidden" value="' . $je['id'] . '">';
		} else {
			echo '&mdash;';
		}
		echo '</td>';

		echo '<td class="r">';
		echo '<button class="btn btn-sm btn-primary save-entry" data-index="' . $le_i . '" title="Save this Ledger Entry" type="button"><i class="fa fa-save"></i></button>';
		echo '<button class="btn btn-sm btn-warning join-entry" data-index="' . $le_i . '" title="Merge with another Ledger Entry for Journal" type="button"><i class="fa fa-compress"></i></button>';
		echo '<button class="btn btn-sm btn-danger drop-entry" data-index="' . $le_i . '" title="Drop" type="button"><i class="fa fa-times"></i></button>';
		echo '</td>';

		echo '</tr>';
	}

	if (empty($date_alpha)) {
		$date_alpha = $je['date'];
	}

	$date_omega = $je['date'];
}
?>
</tbody>

<tfoot>
<tr>
	<td colspan="4">Summary: <?= $date_alpha ?> - <?= $date_omega ?></td>
	<td class="r"><?= number_format($dr, 2) ?></td>
	<td class="r"><?= number_format($cr, 2) ?></td>
</tr>
</tfoot>
</table>

<?php

// Set Title with Known Count
$_ENV['title'][] = sprintf('<span id="reconcile-item-size">%d</span> Items', $le_i);

//
ob_start();

?>

<script>
function acChange(e, ui)
{
	var c = parseInt($(e.target).data('index'), 10) || 0;
	if (ui.item) {
		$('#account_id-' + c).val(ui.item.id);
		$('#account_name-' + c).val(ui.item.value);
	} else {
		$('#account_id-' + c).val('');
		$('#account_name-' + c).val('');
	}
}

function acFocus(e, ui)
{
	var c = parseInt($(e.target).data('index'), 10) || 0;
	$('#account_id-' + c).val(ui.item.id);
	$('#account_name-' + c).val(ui.item.value);
}

function acSelect(e, ui)
{
	var c = parseInt($(e.target).data('index'), 10) || 0;
	$('#account_id-' + c).val(ui.item.id);
	$('#account_name-' + c).val(ui.item.value);
}

function acChangeFocusSelect(e, ui)
{
	var c = parseInt($(e.target).data('index'), 10) || 0;
	$('#account_id-' + c).val(ui.item.id);
	$('#account_name-' + c).val(ui.item.value);
}

$(function() {

	// $('input[type=text]').on('click', function() { this.select(); });
	// $('input[type=number]').on('click', function() { this.select(); });
	$('#filter-note').on('keyup', function() {

		var regx = new RegExp(this.value, 'i');
		var show = 0;
		$('.reconcile-item').each(function(i, n) {
			var note = $(n).find('.reconcile-entry-note').val();
			if (regx.test(note)) {
				$(n).show();
				$(n).addClass('reconcile-show');
				$(n).removeClass('reconcile-hide');
				show++;
			} else {
				$(n).hide();
				$(n).addClass('reconcile-hide');
				$(n).removeClass('reconcile-show');
			}
		});

		$('#reconcile-item-size').html(show);
	});

	// Set all Visible Accounts to this one
	$('#update-account').autocomplete({
		source: <?= $account_list_json; ?>,
		change: function(e, ui) {
			$('.account-name').each(function(i, n) {
				acChange({ target: n }, ui);
			});
		},
		response: function(e, ui) {
			if (1 == ui.content.length) {
				ui.item = ui.content[0];
				delete ui.content;
				acChangeFocusSelect(e, ui);
			}
		}
	});

	$('.account-name').autocomplete({
		delay: 100,
		source: <?= $account_list_json; ?>,
		focus: acFocus,
		select: acSelect,
		change: acChange,
		response: function(e, ui) {
			if (1 == ui.content.length) {
				ui.item = ui.content[0];
				delete ui.content;
				acChangeFocusSelect(e, ui);
			}
		}
	});

	$('.drop-entry').on('click', function(e) {
		var jei = $(this).data('index');
		$('#journal-entry-index-' + jei).remove();
	});

	$('.save-entry').on('click', function(e) {

		console.log('.save-entry:(' + e.type + ')');

		if ('keypress' === e.type) {
			if (13 !== e.keyCode) {
				return;
			}
		}

		var jei = $(this).data('index');

		var dts = $('#date-' + jei).val();
		var txt = $('#note-' + jei).val();
		var off = $('#account_id-' + jei).val();
		var dr =  $('#je' + jei + 'dr').val();
		var cr =  $('#je' + jei + 'cr').val();

		var arg = {
			a: 'save-one',
			date: dts,
			note: txt,
			offset_account_id: off,
			cr: cr,
			dr: dr
		};

		$.post('<?= Radix::link('/account/reconcile') ?>', arg, function(res, ret) {
			switch (ret) {
			case 'success':
				// Advance to Next Visible
				$('#journal-entry-index-' + jei).nextAll('.reconcile-show:first').find('.account-name').focus();
				// $('#account_name-' + (jei + 1)).focus();

				// Remove Row
				$('#journal-entry-index-' + jei).remove();

				// Decrement Counter
				var size = parseInt( $('#reconcile-item-size').html() ) || 0;
				if (size > 1) {
					size--;
					$('#reconcile-item-size').html(size)
				}

				break;
			default:
				alert(res);
			}
		});
	});

	var join_entry_list = [];

	$('.join-entry').on('click', function() {

		var lei = $(this).data('index');
		join_entry_list.push(lei);

		// Add this one
		if (join_entry_list.length >= 2) {

			// Merge to a Journal Entry somehow?

			var i0 = join_entry_list[0];
			var i1 = join_entry_list[1];

			var le0 = {
				date: $('#date-' + i0).val(),
				note: $('#note-' + i0).val(),
				account_id: $('#account_id-' + i0).val(),
				cr: $('#je' + i0 + 'cr').val(),
				dr: $('#je' + i0 + 'dr').val(),
			};

			var le1 = {
				date: $('#date-' + i1).val(),
				note: $('#note-' + i1).val(),
				account_id: $('#account_id-' + i1).val(),
				cr: $('#je' + i1 + 'cr').val(),
				dr: $('#je' + i1 + 'dr').val(),
			};

			var arg = {
				a: 'join-entry',
				le0: le0,
				le1: le1,
			}

			$.post('<?= Radix::link('/account/reconcile') ?>', arg, function(res, ret) {

			});

			join_entry_list = [];
		}

	});

});
</script>
<?php
$code = ob_get_clean();
Layout::addScript($code);
