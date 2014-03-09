/**
    @file
    @brief Base JavaScript Library for Imperium
*/

var Imperium = {};


function toNumeric(e,l)
{
    if (!l) {
        l = 4;
    }
    var n = parseFloat(e.value ? e.value : 0);
    if (isNaN(n)) {
        n = 0;
    }
    e.value = parseFloat(n).toFixed(l);
}

// @todo this needs to become aware of the application base
function star_step()
{
    // alert(this.getAttribute('data'));
    var star_img = this;
    var star_cur = this.getAttribute('data');
    $.getJSON('/star?a=next&c=' + star_cur,function(res,ret,xhr) {
        star_img.setAttribute('data',res.name);
        star_img.src = res.src;
    });
}

var WorkOrder = {};
WorkOrder.initForm = function() {

	$('#contact_name').autocomplete({
		source: Imperium.base + '/contact/ajax',
		change: function(event, ui) { if (ui.item) { $('#contact_id').val(ui.item.id); } },
		select: function(event, ui) { if (ui.item) { $('#contact_id').val(ui.item.id); } }
	});
	$('#wo_date').datepicker();
	$('#requester').autocomplete({
		source: Imperium.base + '/contact/ajax',
		change: function(event, ui) {
			if (ui.item) {
				$('#account').val(ui.item.label);
				$('#account_id').val(ui.item.id);
			}
		}
	});
	$('#kind').autocomplete({ minLength:0, source:['Single','Project','Monthly','Quarterly','Yearly'] });
	// $('#add_contact_name').autocomplete({
	//     source:'<?php echo $this->link('/contact/ajax'); ?>',
	//     change:function(event, ui) { if (ui.item) {  $("#add_contact_id").val(ui.item.id); $("#add_contact_name").val(ui.item.contact); } }
	// });
}