<?php
/**
	Account Journal Entry View

	Draws the form necessary to input a multi-account journal entry
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\Radix;
use Edoceo\Radix\HTML\Form;

// @todo Set this in the controller
$this->FileYes = ini_get('file_uploads');
$this->FileMax = ImperiumView::niceSize( ini_get('upload_max_filesize') );

$css = null;

$cr_total = 0;
$dr_total = 0;

$account_list_json = array();
foreach ($this->AccountList as $i=>$a) {
	$account_list_json[] = array(
		'id' => $a['id'],
		// 'label' => $a['full_name'],
		'value' => $a['full_name'],
	);
};
$account_list_json = json_encode($account_list_json);

// @note duplicated on invoice/view.phtml
if (count($this->jump_list)) {
    echo '<div class="jump_list">';
    $list = array();
    foreach ($this->jump_list as $x) {
        if ($x['id'] == $this->AccountJournalEntry['id']) {
            $list[] = '<span class="hi">#' . $x['id'] . '</span>';
        } elseif ($x['id'] < $this->AccountJournalEntry['id'] ) {
            $list[] = '<a href="' . $this->link('/account/transaction?id=' . $x['id']) . '">&laquo; #' . $x['id'] . '</a>';
        } else { 
            $list[] = '<a href="' . $this->link('/account/transaction?id=' . $x['id']) . '">#' . $x['id'] . ' &raquo;</a>';
        }
    }
    echo implode(' | ',$list);
    echo '</div>';
}

echo '<form enctype="multipart/form-data" method="post">';

echo '<table>';
echo '<tr>';
// Date & Kind
echo '<td class="l">Date:</td><td><input id="account-transaction-date" name="date" type="date" value="' . html($this->AccountJournalEntry['date']) . '"></td>';
echo '<td class="l">Kind:</td><td>' . Form::select('kind', $this->AccountJournalEntry['kind'], array('N'=>'Normal','A'=>'Adjusting','C'=>'Closing')) . '</td>';
echo '</tr>';
// Note
echo '<tr><td class="l">Note:</td><td colspan="3">' . Form::text('note', $this->AccountJournalEntry['note'],array('autocomplete'=>'off', 'style'=>'width: 40em')) . "</td></tr>";
echo '</table>';

// Transaction Entry Lines
echo '<table id="JournalEntry">';
echo '<tr><th>Account</th><th>Link</th><th>Debit</th><th>Credit</th></tr>';

$AccountList = array();
foreach ($this->AccountList as $item) {
	$AccountList[$item->id] = $item->full_name;
}

foreach ($this->AccountLedgerEntryList as $i=>$item) {

	if ($item['amount'] < 0) {
		$item['debit_amount'] = abs($item['amount']);
		$item['credit_amount'] = null;
	} elseif ($item['amount'] > 0) {
		$item['debit_amount'] = null;
		$item['credit_amount'] = abs($item['amount']);
	} else {
		$item['debit_amount'] = null;
		$item['credit_amount'] = null;
	}

	$css = $css==' class="re"' ? ' class="ro"' : ' class="re"';

	echo "<tr$css>";
	echo '<td>';
	// Ledger Entry ID, Account ID and Account Name
	echo Form::hidden($i.'_id',$item['id']);
	echo Form::hidden($i.'_account_id',$item['account_id'],array('class'=>'account-id'));
	echo Form::text($i.'_account_name',$item['account_name'],array('class'=>'account-name'));
	echo '<small class="account-id-v" id="' . $i . '_account_id_v"></small>';

	echo '</td>';
	// Link to Object
	echo '<td>';
    echo Form::select($i.'_link_to', $item['link_to'], $this->LinkToList);
    echo Form::text($i.'_link_id', $item['link_id'], array('class' => 'link-to'));
	echo '</td>';

	// Display Both
	// Debit
	echo "<td class='r'>" . Form::number($i.'_dr', $item['debit_amount']) . "</td>";

	// Credit
	echo "<td class='r'>" . Form::number($i.'_cr', $item['credit_amount']) . "</td>";

	echo '</tr>';
}

echo '<tr><td class="b" colspan="2"><strong>Total:</strong></td>';
echo '<td class="r" id="drt">' . number_format(abs($dr_total),2) . '</td>';
echo '<td class="r" id="crt">' . number_format(abs($cr_total),2) . '</td>';
echo '</tr>';
echo '</table>';

// Attached Files
echo Radix::block('file-list', $this->FileList);

// Buttons & Hiddden
echo '<div class="bf">';
echo Form::hidden('id',$this->AccountJournalEntry['id']);
echo '<button accesskey="s" class="good" name="a" type="submit" value="save">Save</button>';
echo '<button class="good" name="a" type="submit" value="save-copy">Save & Copy</button>';
// echo '<input class="good" accesskey="s" name="a" type="submit" value="Save">';
// echo Form::submit('c','Apply');
// echo Form::button('a', 'Save');
echo '<button accesskey="n" class="info" onclick="addLedgerEntryLine();" type="button">Add Line</button>';
// Can Memorize New
if (empty($this->AccountJournalEntry['id'])) {
    echo Form::submit('a','Memorize');
}
if ($this->AccountJournalEntry['id']) {
	echo '<input class="fail" name="a" type="submit" value="Delete">';
}
echo '</div>';

// File
echo '<table>';
echo '<tr><td class="l">Attach:</td><td><input name="file" type="file">&nbsp;' . $this->FileMax . '</td></tr>';
echo '</table>';

// @todo Email Notify Field Here?

echo '</form>';

// History
$args = array(
//    'ajax' => true,
    'list' => $this->AccountJournalEntry->getHistory()
);
echo Radix::block('diff-list',$args);

?>

<p>
Input Standard Accounting Journal Entries here using the proper accounts
Choose the <i>Memorise</i> to remember this transaction as a template for later.
</p>

<script>
var updateMagic = true;

function acChangeSelect(event,ui)
{
    var c = parseInt(this.name);
	$('#' + c + '_account_id').val(ui.item.id);
	$('#' + c + '_account_id_v').html(ui.item.id);
	$('#' + c + '_account_name').val(ui.item.value);
}

function acInit()
{
    $('input[type=text]').on('click', function() { this.select(); });
    $('input[type=number]').on('click', function() { this.select(); });

    $("input[name$='_cr']").on('blur change', updateJournalEntryBalance );
    $("input[name$='_dr']").on('blur change', updateJournalEntryBalance );

    // $("input[name*='account_name']").autocomplete('destroy');
    $("input[name*='account_name']").autocomplete({
		delay:100,
        source:<?php echo $account_list_json; ?>,
        focus:acChangeSelect,
        select:acChangeSelect,
        change:acChangeSelect,
    });
}

function addLedgerEntryLine()
{
    // @todo   If only Debit or Credit shows there then only that will be copedit
    var t = document.getElementById('JournalEntry');
	var c = t.rows.length - 2; // number of existing accounting rows

    var html = '<tr>';

    // Account Cell
	var x = $(t.rows[c-1].cells[0]).clone(true);
	x.find('input[type=hidden]').attr({
		id: c + '_id',
		name: c + '_id',
	});
	x.find('.account-id').attr({
		id: c + '_account_id',
		name: c + '_account_id',
	});
	x.find('.account-name').attr({
		id: c + '_account_name',
		name: c + '_account_name',
	});
	x.find('.account-id-v').attr({
		id: c + '_account_id_v',
	}).html('');

	html += '<td>' + x.html() + '</td>';

    // Link Cell
    var x = $(t.rows[c-1].cells[1]).clone(true);
    x.find('select').attr('id', c + '_link_to').attr('name', c + '_link_to');
    x.find('input').attr('id', c + '_link_id').attr('name', c + '_link_id');
    html += '<td>' + x.html() + '</td>';

    // Debit Cell
    var x = $(t.rows[c-1].cells[2]).clone(true);
    x.find('input').attr('id', c + '_dr').attr('name', c + '_dr').val('0.00');
    html += '<td class="r">' + x.html() + '</td>';

    // Credit Cell
    var x = $(t.rows[c-1].cells[3]).clone(true);
    x.find('input').attr('id', c + '_cr').attr('name', c + '_cr').val('0.00');
    html += '<td class="r">' + x.html() + '</td>';
    html += '</tr>';

	$(t.rows[c]).after(html);

	acInit();

}

function updateJournalEntryBalance()
{
    var cr = 0;
    var dr = 0;

    $(':input').each(function(i) {
        var v = parseFloat(this.value.replace(/[^\d\.]+/g,'')) || 0;
        if (this.name.indexOf('_dr') > 0) {
          dr += v;
        } else if (this.name.indexOf('_cr') > 0) {
          cr += v;
        }
    });

    cr = cr.toFixed(2);
    dr = dr.toFixed(2);

    $('#crt').css('color','#000000');
    $('#crt').text(cr);

    $('#drt').css('color','#000000');
    $('#drt').text(dr);

    // Non-Zero & Equal
    if ( (parseFloat(cr)!=0) && (cr == dr) ) {
        $(':submit').removeAttr('disabled');
        return;
    }

    $(':submit').attr('disabled','disabled');
    if (cr != 0) {
		$('#crt').css('color','#ff0000');
    }
    if (dr != 0) {
		$('#drt').css('color','#ff0000');
    }

    // On First Update Clone Value from One Box To the Other
    // Maybe the workflow is to have this trigger only attached to dr0, and always do magic when dr0 is updated?
    if (updateMagic) {
		var dr0 = parseFloat($('#0_dr').val()) || 0;
		var cr0 = parseFloat($('#0_cr').val()) || 0;
		if ( (dr0 > 0) || (cr0 > 0) ) {
			var dr1 = parseFloat($('#1_dr').val()) || 0;
			var cr1 = parseFloat($('#1_cr').val()) || 0;
			if ( (dr1 == 0) && (cr1 == 0) ) {
				if (dr0) $('#1_cr').val(dr0);
				if (cr0) $('#1_dr').val(cr0);
				updateMagic = false;
			}
		}
	}
}

$(function() {

    $('#account-transaction-date').focus();

    updateJournalEntryBalance();
    acInit();

});
</script>

<?php
