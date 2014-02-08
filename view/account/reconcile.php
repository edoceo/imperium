<?php
/**
    @file
    @brief View for reconciling/importing transactions
    @verison $Id$

*/

$_ENV['title'] = array('Account','Reconcile');

if (empty($_ENV['mode'])) {
    $_ENV['mode'] = 'load';
}

switch ($_ENV['mode']) {
case 'save':
case 'view':

    // View the Pending Transactions
    echo '<form method="post">';
    echo '<table>';
    echo '<tr>';
    echo '<th>#</th>';
    echo '<th>Date</th>';
    echo '<th>Note</th>';
    echo '<th>Account</th>';
    echo '<th>Debit</th>';
    echo '<th>Credit</th>';
    echo '<th>JE</th>';
    echo '</tr>';

    $i = 0;
    $cr = 0;
    $dr = 0;
    $date_alpha = $date_omega = null;
    foreach ($this->JournalEntryList as $je) {

        $i++;
        $offset_id = $_ENV['offset_account_id'];
        // Pattern Match to Find the Chosen Offseter?

        echo '<tr class="rero">';
        echo '<td class="r">' . $i . '</td>';
        echo '<td class="c"><input name="' . sprintf('je%ddate',$i) . '" size="10" type="text" value="' . $je->date . '"></td>';
        echo '<td><input name="' . sprintf('je%dnote',$i) . '" style="width:384px;" type="text" value="' . $je->note . '"></td>';
        echo '<td>';
        echo radix_html_form::select(sprintf('je%daccount_id',$i), $offset_id, $this->AccountPairList);
        echo '</td>';
        // echo $this->formText('note',$je->note,array('style'=>'width:25em'));
        // @todo Determine Side, which depends on the Kind of the Account for which side is which
        if (!empty($je->dr)) {
            $dr += floatval($je->dr);
            echo '<td class="r">' . radix_html_form::text(sprintf('je%ddr',$i),number_format($je->dr,2),array('size'=>8)) . '</td>';
            echo '<td>&nbsp;</td>';
        } else {
            $cr += floatval($je->cr);
            echo '<td>&nbsp;</td>';
            echo '<td class="r">' . radix_html_form::text(sprintf('je%dcr',$i),number_format($je->cr,2),array('size'=>8)) . '</td>';
        }
        echo '<td>';
        if ($je->id) {
            echo radix::link('/account/transaction?id=' . $je->id,$je->id);
            echo '<input name="' . sprintf('je%did',$i) . '" type="hidden" value="' . $je->id . '">';
        } else {
            echo '&mdash;';
        }
        echo '</td>';
        echo '</tr>';
        
        if (empty($date_alpha)) $date_alpha = $je->date;
        $date_omega = $je->date;
    }
    echo '<tr>';
    echo '<td colspan="4">Summary: ' . $date_alpha . ' - ' . $date_omega . '</td>';
    echo '<td class="r">' . number_format($dr,2) . '</td>';
    echo '<td class="r">' . number_format($cr,2) . '</td>';
    echo '</table>';
    echo '<div>';
    echo '<input name="a" type="submit" value="Save">';
    echo '</div>';

    $max = ($i * 4);
    if ($max > intval(ini_get('max_input_vars'))) {
    	radix_session::flash('warn', "There are too many elements for your system to handle, upload a smaller data set or increase <em>max_input_vars</em> above $max");
    }

    echo '</form>';

    return;

case 'load':
default:
    echo '<form enctype="multipart/form-data" method="post">';
    echo '<fieldset><legend>Step 1 - Choose Account and Data File</legend>';
    echo '<table>';
    echo '<tr><td class="l" title="Account whos transactions are being uploaded">Account:</td><td>' . radix_html_form::select('upload_id', $this->Account->id, $this->AccountPairList)  . '</td></tr>';
    echo '<tr><td class="l" title="Default off-set account for the transactions, a pending queue for reconciliation">Offset:</td><td>' . radix_html_form::select('offset_id', $_ENV['account']['reconcile_offset_id'], $this->AccountPairList)  . '</td></tr>';
    echo '<tr><td class="l" title="Which data format is this in?">Format:</td><td>' . radix_html_form::select('format',null,Account_Reconcile::$format_list) . '</td></tr>';
    echo '<tr><td class="l">File:</td><td><input name="file" type="file">';
    echo ' <span class="s">(p:' . ini_get('post_max_size') . '/u:' . ini_get('upload_max_filesize') . ')</span>';
    echo '</td></tr>';
    echo '</table>';
    echo '<div><input name="a" type="submit" value="Upload" /></div>';
    echo '</fieldset>';
    echo '</form>';
    return(0);
}

