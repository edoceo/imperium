<?php
/**
    @file
    @brief View for reconciling/importing transactions
    @verison $Id$

*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
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
	width: 20em;
}
</style>


<?php

switch ($_ENV['mode']) {
case 'save':
case 'view':

    // View the Pending Transactions
    echo '<table>';
    echo '<tr>';
    echo '<th>#</th>';
    echo '<th>Date</th>';
    echo '<th>Note</th>';
    echo '<th>Account</th>';
    echo '<th>Debit</th>';
    echo '<th>Credit</th>';
    echo '<th>JE</th>';
    echo '<th>-</th>';
    echo '</tr>';

    echo '<tr class="r">';
    echo '<td><input style="width:1em;"></td>';
    echo '<td><input class="ar-date" id="filter-date" type="text"></td>';
    echo '<td><input class="ar-note" id="filter-note" type="text"></td>';
    echo '<td><input class="account-name ar-note" id="update-account" type="text"></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '</tr>';


    $cr = 0;
    $dr = 0;
    $je_i = 0;
    $le_i = 0;
    $date_alpha = $date_omega = null;
    foreach ($this->JournalEntryList as $je) {

    	if (!empty($je->id)) {
    		continue;
    	}
    	
    	// $je->note = str_shuffle($je->note);

        $je_i++;

        $offset_id = $_ENV['offset_account_id'];

		echo '<tr class="rero reconcile-item reconcile-show" id="journal-entry-index-' . $je_i . '">';
		echo '<td class="ar-index">' . $je_i . '</td>';

        // Pattern Match to Find the Chosen Offseter?
        if (!empty($je->ledger)) {
        	die("What?");
			$le_i = 0;
        	foreach ($je->ledger as $le) {

        		$le_i++;

				echo '<td class="c"><input class="ar-date" name="' . sprintf('je%ddate',$je_i) . '" type="text" value="' . $je->date . '"></td>';
				echo '<td><input class="ar-note" name="' . sprintf('je%dnote',$je_i) . '" type="text" value="' . $je->note . '"></td>';
				// echo $this->formText('note',$je->note,array('style'=>'width:25em'));
				// @todo Determine Side, which depends on the Kind of the Account for which side is which
				echo '<td>';
				echo Form::select(sprintf('je%d_le%d_account_id',$je_i, $le_i), $offset_id, $this->AccountPairList);
				echo '</td>';

				if (!empty($le['dr'])) {
					$dr += floatval($le['dr']);
					echo '<td class="r">' . Form::text(sprintf('je%d_le%d_dr',$je_i,$le_i),number_format($le['dr'],2),array('size'=>8)) . '</td>';
					echo '<td>&nbsp;</td>';
				} else {
					$cr += floatval($le['cr']);
					echo '<td>&nbsp;</td>';
					echo '<td class="r">' . Form::text(sprintf('je%d_le%d_cr',$je_i, $le_i),number_format($le['cr'],2),array('size'=>8)) . '</td>';
				}
        		echo '</tr>';
        	}

        } else {

        	$le_i++;

        	// Simplex Type
			echo '<td><input class="ar-date" id="' . sprintf('date-%d', $je_i) . '" name="' . sprintf('je%ddate',$je_i) . '" type="text" value="' . $je->date . '"></td>';
			echo '<td><input class="reconcile-entry-note ar-note" id="' . sprintf('note-%d', $je_i) . '" name="' . sprintf('je%dnote',$je_i) . '" type="text" value="' . $je->note . '"></td>';
			// echo $this->formText('note',$je->note,array('style'=>'width:25em'));
			// @todo Determine Side, which depends on the Kind of the Account for which side is which

			// @todo Autocomplete
			echo '<td>';
			// echo Form::select(sprintf('je%daccount_id',$je_i), $offset_id, $this->AccountPairList);
			echo '<input class="account-id" id="' . sprintf('account_id-%d', $je_i) . '" name="' . sprintf('account_id-%d', $je_i) . '" type="hidden" value="">';
			echo '<input class="account-name ui-autocomplete-input ar-note" data-index="' . $je_i . '" id="' . sprintf('account_name-%d', $je_i) . '" name="' . sprintf('account_name-%d', $je_i) . '" type="text" value="" autocomplete="off">';
			echo '</td>';

			if (!empty($je->dr)) {
				$dr += floatval($je->dr);
				echo '<td class="r">' . Form::number(sprintf('je%ddr',$je_i), sprintf('%0.2f', $je->dr), array('step'=>0.01)) . '</td>';
				echo '<td>&nbsp;</td>';
			} elseif (!empty($je->cr)) {
				$cr += floatval($je->cr);
				echo '<td>&nbsp;</td>';
				echo '<td class="r">' . Form::number(sprintf('je%dcr',$je_i), sprintf('%0.2f', $je->cr), array('step'=>0.01)) . '</td>';
			} else {
				die("Fail");
			}

			// Lookup / Found?
			echo '<td>';
			if ($je->id) {
				echo '<a href="' . Radix::link('/account/transaction?id=' . $je->id) . '">' . $je->id . '</a>';
				echo '<input name="' . sprintf('je%did', $je_i) . '" type="hidden" value="' . $je->id . '">';
			} else {
				echo '&mdash;';
			}
			echo '</td>';

			echo '<td>';
			echo '<button class="save-entry" data-index="' . $je_i . '" type="button"><i class="fa fa-save"></i></button>';
			echo '</td>';

			echo '</tr>';

		}

        if (empty($date_alpha)) $date_alpha = $je->date;
        $date_omega = $je->date;
    }

	// Footer
    echo '<tr>';
    echo '<td colspan="4">Summary: ' . $date_alpha . ' - ' . $date_omega . '</td>';
    echo '<td class="r">' . number_format($dr,2) . '</td>';
    echo '<td class="r">' . number_format($cr,2) . '</td>';
    echo '</table>';

    // Set Title with Known Count
	$_ENV['title'][] = sprintf('<span id="reconcile-item-size">%d</span> Items', $je_i);

    //$max = ($le_i * 4);
    //if ($max > intval(ini_get('max_input_vars'))) {
    //	Session::flash('warn', "There are too many elements for your system to handle, upload a smaller data set or increase <em>max_input_vars</em> above $max");
    //}

	break;

case 'load':
default:
    echo '<form enctype="multipart/form-data" method="post">';
    echo '<fieldset><legend>Step 1 - Choose Account and Data File</legend>';
    echo '<table>';
    echo '<tr><td class="l" title="Transactions are being uploaded for this account">Account:</td><td>' . Form::select('upload_id', $this->Account->id, $this->AccountPairList)  . '</td></tr>';
    // echo '<tr><td class="l" title="Default off-set account for the transactions, a pending queue for reconciliation">Offset:</td><td>' . Form::select('offset_id', $_ENV['account']['reconcile_offset_id'], $this->AccountPairList)  . '</td></tr>';
    echo '<tr><td class="l" title="Which data format is this in?">Format:</td><td>' . Form::select('format',null,Account_Reconcile::$format_list) . '</td></tr>';
    echo '<tr><td class="l">File:</td><td><input name="file" type="file">';
    echo ' <span class="s">(p:' . ini_get('post_max_size') . '/u:' . ini_get('upload_max_filesize') . ')</span>';
    echo '</td></tr>';
    echo '</table>';
    echo '<div><input name="a" type="submit" value="Upload" /></div>';
    echo '</fieldset>';
    echo '</form>';
    return(0);
}

?>

<script>
function acChange(e, ui)
{
    var c = parseInt($(e.target).data('index'));
	$('#account_id-' + c).val(ui.item.id);
	$('#account_name-' + c).val(ui.item.value);
}

function acFocus(e, ui)
{
    var c = parseInt($(e.target).data('index'));
	$('#account_id-' + c).val(ui.item.id);
	$('#account_name-' + c).val(ui.item.value);
}

function acSelect(e, ui)
{
    var c = parseInt($(e.target).data('index'));
	$('#account_id-' + c).val(ui.item.id);
	$('#account_name-' + c).val(ui.item.value);
}

function acChangeFocusSelect(e, ui)
{
    var c = parseInt($(e.target).data('index'));
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
    $('#update-account').on('blur change', function() {
    	var a = $(this).val();
		$('.reconcile-show .account-name').each(function(i, n) {
			$(n).val(a).blur();
		});
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
});
</script>