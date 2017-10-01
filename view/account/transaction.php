<?php
/**
	Account Journal Entry View

	Draws the form necessary to input a multi-account journal entry
	
	https://en.wikipedia.org/wiki/Double-entry_bookkeeping_system#Customer_ledger_cards
	http://www.double-entry-bookkeeping.com/sales/revenue-received-in-advance/
	http://accounting-simplified.com/accrued-income.html
	http://accounting-simplified.com/prepaid-income.html
	http://taxation.lawyers.com/treatment-of-advance-income-under-the-accrual-method.html

	https://help.xero.com/us/Payments_PayInvoice
	http://accounting-simplified.com/accounting-for-sales.html - Credit Sale
	https://community.intuit.com/questions/836109-clearing-paid-invoices-without-double-counting-deposits
	https://support.waveapps.com/entries/108008253-How-to-record-a-payment-on-an-invoice

	https://www.freshbooks.com/support/journal-entries-an-overview-for-your-accountant#tocspecific
	
	https://www.freshbooks.com/support/how-does-freshbooks-calculate-accounts-receivable
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

// @todo Set this in the controller
$this->FileYes = ini_get('file_uploads');
$this->FileMax = ImperiumView::niceSize( ini_get('upload_max_filesize') );

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

?>

<form enctype="multipart/form-data" method="post">
<div class="row">
<div class="col-md-6">
	<label>Date:</label>
	<input class="form-control" id="account-transaction-date" name="date" type="date" value="<?= html($this->AccountJournalEntry['date']) ?>">
</div>
<div class="col-md-6">
	<label>Kind:</label>
	<?= Form::select('kind', $this->AccountJournalEntry['kind'], array('N'=>'Normal','A'=>'Adjusting','C'=>'Closing'), array('class' => 'form-control')) ?>
</div>
</div>

<div class="row">
<div class="col-md-10">
	<label>Note:</label>
	<?= Form::text('note', $this->AccountJournalEntry['note'],array('autocomplete'=>'off', 'class'=>'form-control')) ?>
</div>
<div class="col-md-2">
	<label>Flag:</label>
	<div>
	<label><input<?= (($this->AccountJournalEntry['flag'] & 1) ? ' checked' : null) ?> name="flag[]" style="vertical-align:middle;" type="checkbox" value="1">Audited</label>
	</div>
</div>
</div>

<table class="table" id="JournalEntry">
<thead>
	<tr><th>Account</th><th>Debit</th><th>Credit</th></tr>
</thead>
<tbody>
<?php

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

	echo '<tr>';
	echo '<td>';
	// Ledger Entry ID, Account ID and Account Name
	echo Form::hidden($i.'_id', $item['id']);
	echo Form::hidden($i.'_account_id', $item['account_id'],array('class'=>'account-id'));
	echo Form::text($i.'_account_name', $item['account_name'],array('class'=>'account-name'));

	echo ' <small class="account-id-v" id="' . $i . '_account_id_v"></small>';
	echo ' <small><a class="account-link" href="' . Radix::link('/account/ledger?id=' . $item['account_id']) . '" id="' . $i . '-account-link"><i class="fa fa-external-link"></a></small>';
	echo '</td>';

	// Link to Object
	//$to = strtok($item['link'], ':');
	//$id = strtok('');
	//echo '<td>';
	//echo Form::select($i.'_link_to', $to, $this->LinkToList);
	//echo Form::text($i.'_link_id', $id, array('class' => 'link-to'));
	//echo '</td>';

	// Display Both
	// Debit
	echo "<td class='r'>" . Form::number($i.'_dr', $item['debit_amount']) . "</td>";

	// Credit
	echo "<td class='r'>" . Form::number($i.'_cr', $item['credit_amount']) . "</td>";

	echo '</tr>';
}

echo '<tr><td class="b"><strong>Total:</strong></td>';
echo '<td class="r" id="drt">' . number_format(abs($dr_total),2) . '</td>';
echo '<td class="r" id="crt">' . number_format(abs($cr_total),2) . '</td>';
echo '</tr>';
echo '</tbody>';
echo '</table>';

// Attached Files
echo Radix::block('file-list', $this->FileList);

// Buttons & Hiddden
echo '<div class="bf">';
echo Form::hidden('id',$this->AccountJournalEntry['id']);
echo '<button class="btn btn-primary" accesskey="s" class="good" name="a" type="submit" value="save">Save</button>';
echo '<button class="btn btn-primary" name="a" type="submit" value="save-copy">Save & Copy</button>';
// echo '<input class="good" accesskey="s" name="a" type="submit" value="Save">';
// echo Form::submit('c','Apply');
// echo Form::button('a', 'Save');
echo '<button class="btn" accesskey="n" class="info" onclick="addLedgerEntryLine();" type="button">Add Line</button>';
// Can Memorize New
if (empty($this->AccountJournalEntry['id'])) {
    echo Form::submit('a','Memorize');
}
if ($this->AccountJournalEntry['id']) {
	echo '<input class="btn btn-danger" name="a" type="submit" value="Delete">';
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

// Radix::dump($this->AccountJournalEntry);

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
		value: '',
	});
	x.find('.account-id').attr({
		id: c + '_account_id',
		name: c + '_account_id',
		value: '',
	});
	x.find('.account-name').attr({
		id: c + '_account_name',
		name: c + '_account_name',
		value: '',
	});
	x.find('.account-id-v').attr({
		id: c + '_account_id_v',
	}).html('');

	x.find('.account-link').attr({
		id: c + '-account-link',
		href: '#',
	});

	html += '<td>' + x.html() + '</td>';

    // Link Cell
    //var x = $(t.rows[c-1].cells[1]).clone(true);
    //x.find('select').attr('id', c + '_link_to').attr('name', c + '_link_to');
    //x.find('input').attr('id', c + '_link_id').attr('name', c + '_link_id');
    //html += '<td>' + x.html() + '</td>';

    // Debit Cell
    var x = $(t.rows[c-1].cells[1]).clone(true);
    x.find('input').attr({
		id: c + '_cr',
		name: c + '_dr',
		value: ''
	});
    html += '<td class="r">' + x.html() + '</td>';

    // Credit Cell
    var x = $(t.rows[c-1].cells[2]).clone(true);
    x.find('input').attr({
		id: c + '_cr',
		name: c + '_cr',
		value: ''
	});
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

    //$(':submit').attr('disabled','disabled');
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
</script>

<?php

Layout::addScript(<<<EOS
$(function() {

    $('#account-transaction-date').focus();

    updateJournalEntryBalance();
    acInit();

});
EOS
);
