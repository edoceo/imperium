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

$_ENV['title'] = array('Account','Reconcile');

if (empty($_ENV['mode'])) {
    $_ENV['mode'] = 'load';
}

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

    $cr = 0;
    $dr = 0;
    $je_i = 0;
    $le_i = 0;
    $date_alpha = $date_omega = null;
    foreach ($this->JournalEntryList as $je) {

        $je_i++;

        $offset_id = $_ENV['offset_account_id'];
        // Pattern Match to Find the Chosen Offseter?
        if (!empty($je->ledger)) {
        	//die("What?");
			$le_i = 0;
        	foreach ($je->ledger as $le) {

        		$le_i++;

				echo '<tr class="rero">';
				echo '<td class="r">' . $je_i . '</td>';
				echo '<td class="c"><input name="' . sprintf('je%ddate',$je_i) . '" size="10" type="text" value="' . $je->date . '"></td>';
				echo '<td><input name="' . sprintf('je%dnote',$je_i) . '" style="width:384px;" type="text" value="' . $je->note . '"></td>';
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
			echo '<tr class="rero">';
			echo '<td class="r">' . $je_i . '</td>';
			echo '<td class="c"><input id="' . sprintf('date-%d', $je_i) . '" name="' . sprintf('je%ddate',$je_i) . '" size="10" type="text" value="' . $je->date . '"></td>';
			echo '<td><input id="' . sprintf('note-%d', $je_i) . '" name="' . sprintf('je%dnote',$je_i) . '" type="text" value="' . $je->note . '"></td>';
			// echo $this->formText('note',$je->note,array('style'=>'width:25em'));
			// @todo Determine Side, which depends on the Kind of the Account for which side is which
			echo '<td>';
			echo Form::select(sprintf('je%daccount_id',$je_i), $offset_id, $this->AccountPairList);
			echo '</td>';

			if (!empty($je->dr)) {
				$dr += floatval($je->dr);
				echo '<td class="r">' . Form::text(sprintf('je%ddr',$je_i),number_format($je->dr,2),array('size'=>8)) . '</td>';
				echo '<td>&nbsp;</td>';
			} else {
				$cr += floatval($je->cr);
				echo '<td>&nbsp;</td>';
				echo '<td class="r">' . Form::text(sprintf('je%dcr',$je_i),number_format($je->cr,2),array('size'=>8)) . '</td>';
			}

			echo '<td>';
			if ($je->id) {
				echo Radix::link('/account/transaction?id=' . $je->id,$je->id);
				echo '<input name="' . sprintf('je%did',$je_i) . '" type="hidden" value="' . $je->id . '">';
			} else {
				echo '&mdash;';
			}
			echo '</td>';

			echo '<td>';
			echo '<button class="save-entry" data-id="' . $je_i . '" type="button"><i class="fa fa-save"></i></button>';
			echo '</td>';

			echo '</tr>';

		}

        if (empty($date_alpha)) $date_alpha = $je->date;
        $date_omega = $je->date;
    }
    echo '<tr>';
    echo '<td colspan="4">Summary: ' . $date_alpha . ' - ' . $date_omega . '</td>';
    echo '<td class="r">' . number_format($dr,2) . '</td>';
    echo '<td class="r">' . number_format($cr,2) . '</td>';
    echo '</table>';

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
    echo '<tr><td class="l" title="Default off-set account for the transactions, a pending queue for reconciliation">Offset:</td><td>' . Form::select('offset_id', $_ENV['account']['reconcile_offset_id'], $this->AccountPairList)  . '</td></tr>';
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
$(function() {
	$('.save-entry').on('click', function() {
		var je = $(this).data('id');

		var dts = $('#date-' + je).val();
		var txt = $('#note-' + je).val();
		var off = $('#je' + je + 'account_id').val();
		var dr =  parseFloat($('#je' + je + 'dr').val(), 10) || 0;
		var cr =  parseFloat($('#je' + je + 'cr').val(), 10) || 0;

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
				// Remove Row
				break;
			default:
				alert(res);
			}
		});

	});
});
</script>