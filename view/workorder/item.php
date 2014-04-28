<?php
/**
    Work Order Items View

    Shows details about a Work Order Item

    @copyright  2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

if (empty($this->WorkOrderItem)) {
    echo '<p class="fail">Failed to load a Work Order Item</p>';
    return;
}

echo '<form action="'. radix::link('/workorder/item?' . http_build_query(array('id'=>$this->WorkOrderItem['id']))) . '" method="post">';
echo '<table>';

// Kind & Date
$time_base = mktime(0,0,0);
$time_list = array();
for ($m=0; $m<=86400; $m+=900) {
    $k = strftime('%H:%M',$time_base + $m);
    $v = strftime('%H:%M',$time_base + $m);
    $time_list[$k] = $v;
}

$d = radix_html_form::date('date',$this->WorkOrderItem['date'], array('id'=>'woi_date','size'=>12));
echo '<tr>';
echo '<td class="l">Kind:</td><td>' . radix_html_form::select('kind', $this->WorkOrderItem['kind'], WorkOrderItem::$kind_list) . '</td>';
echo '<td class="l">Date:</td><td>' . $d . '</td>';
echo '<td>' . radix_html_form::select('time_alpha',$this->WorkOrderItem['time_alpha'], $time_list) . '</td>';
echo '<td>' . radix_html_form::select('time_omega',$this->WorkOrderItem['time_omega'], $time_list) . '</td>';
echo '</tr>';

// Estimate: Quantity, Rate, Unit, Tax
$q = radix_html_form::number('e_quantity',$this->WorkOrderItem['e_quantity']);
$r = radix_html_form::number('e_rate',$this->WorkOrderItem['e_rate']);
$u = radix_html_form::select('e_unit', $this->WorkOrderItem['e_unit'], Base_Unit::getList());
$t = radix_html_form::number('e_tax_rate',tax_rate_format($this->WorkOrderItem['e_tax_rate']));
echo "<tr><td class='l'>Estimate:</td><td>$q</td><td><strong>@</strong>$r</td><td><strong>per</strong>&nbsp;$u<td class='b r'>Tax Rate:</td><td>$t&nbsp;%</td></tr>";

// Cost: Quantity, Rate, Unit, Tax
$q = radix_html_form::number('a_quantity',$this->WorkOrderItem['a_quantity']);
$r = radix_html_form::number('a_rate',$this->WorkOrderItem['a_rate']);
$u = radix_html_form::select('a_unit', $this->WorkOrderItem['a_unit'], Base_Unit::getList());
$t = radix_html_form::number('a_tax_rate',tax_rate_format($this->WorkOrderItem['a_tax_rate']));
echo "<tr><td class='l'>Actual:</td><td>$q</td><td><strong>@</strong>$r</td><td><strong>per</strong>&nbsp;$u<td class='b r'>Tax Rate:</td><td>$t&nbsp;%</td></tr>";

// Name
echo '<tr><td class="l">Name:</td><td colspan="5">'  . radix_html_form::text('name',$this->WorkOrderItem['name']) . '</td></tr>';

// Details
echo '<tr><td class="l">Note:</td><td colspan="5">' . radix_html_form::textarea('note',$this->WorkOrderItem['note']) . '</td></tr>';

// Notify
echo '<tr><td class="l">';
echo '<span title="Input an email address here and a notification email will be sent">Notify:</span></td>';
echo '<td colspan="5">' . radix_html_form::text('notify',$this->WorkOrderItem['notify']) . '</td>';
echo '</tr>';

echo "<tr>";
echo "<td class='l'><span title='The Status of this Item, Completed Items will be Billed when creating an Invoice'>Status:</span></td>";
echo '<td colspan="3">';
// echo '<input name="status" type="text" value="' . $this->WorkOrderItem['status'] . '">';
echo radix_html_form::select('status',$this->WorkOrderItem['status'], $this->ItemStatusList);
echo '</td>';
echo '</tr>';

echo "</table>";

echo '<div class="cmd">';
echo '<input name="workorder_id" type="hidden" value="' . $this->WorkOrder['id'] . '">';
// echo $this->formSubmit('c','Save');
echo '<button class="good" name="a" type="submit" value="save">Save</button>';
if (!empty($this->WorkOrderItem['id'])) {
    echo '<button class="good" name="a" type="submit" value="delete">Delete</button>';
}
echo '</div>';

echo '</form>';

// History
$args = array(
    'list' => $this->WorkOrderItem->getHistory()
);
echo radix::block('diff-list', $args);

?>

<script>
$(function() {

	$('#name').focus();
	$('#notify').autocomplete({
		source:'/imperium/contact/ajax',
		change:function(event, ui) { if (ui.item) {  $("#notify").val(ui.item.id); $("#notify").val(ui.item.contact); } }
	});

	$('#time_alpha, #time_omega').on('change',function() {

		var m = null;

		var alpha = $('#time_alpha').val();
		var omega = $('#time_omega').val();

		if (m = alpha.match(/^(\d+):(\d+)$/)) {
			h_alpha = parseInt(m[1]);
			m_alpha = parseInt(m[2]) / 60 * 100;
		}

		if (m = omega.match(/^(\d+):(\d+)$/)) {
			h_omega = parseInt(m[1]);
			m_omega = parseInt(m[2]) / 60 * 100;
		}

		if (omega < alpha) {
			h_omega += 24;
		}


		var h_delta = h_omega - h_alpha;
		var m_delta = Math.abs(m_omega - m_alpha);

		$('#a_quantity').val(h_delta + '.' + m_delta);
	});

	$('input[type=number]').on('blur', function() {
		toNumeric(this);
	});

});

</script>

<?php
// @todo should be at theme or webroot/index.php level
if (radix::isAJAX()) {
	exit(0);
}