<?php
/**
    @file
    @brief Contact View Shows the Contact, Company or Vendors information
*/

namespace Edoceo\Imperium;

use Radix;


// Web Site & Email
$url = parse_url($this->Contact['url']);
if (empty($url['scheme'])) $url['scheme'] = 'http';
if (empty($url['host'])) {
    $url['host'] = $url['path'];
    $url['path'] = '/';
}
//print_r($url);
$this->Contact['url'] = sprintf('%s://%s%s', $url['scheme'], $url['host'], $url['path']);


echo '<form action="' . Radix::link('/contact/save?c=' . $this->Contact['id']) . '" method="post">';

echo '<div>';
echo \radix_html_form::hidden('id',$this->Contact['id']);
echo \radix_html_form::hidden('parent_id',$this->Contact['parent_id']);
echo '</div>';

?>

<style>
.l {
	font-size: 22px;
	font-weight: bold;
	line-height: 32px;
	position: relative;
	text-align:right;
	top: 50%;
	/*
	transform: translateY(-50%);
	-ms-transform: translateY(-50%);
	-webkit-transform: translateY(-50%);
	*/
}
</style>

<div class="pure-g" style="position:relative;">
<div class="pure-u-1-5"><div class="l">Contact:</div></div>
<div class="pure-u-4-5"><?= \radix_html_form::text('contact',$this->Contact['contact'])?></div>

<div class="pure-u-1-5"><div class="l"><?php
if (!empty($this->Contact['parent_id'])) {
    echo '<a href="' . Radix::link('/contact/view?c=' . $this->Contact['parent_id']) . '">Company:</a>';
} else {
    echo 'Company:';
}
?></div></div>
<div class="pure-u-4-5"><?= \radix_html_form::text('company',$this->Contact['company'])?></div>

<div class="pure-u-1-5"><div class="l">Phone:</div></div>
<div class="pure-u-4-5"><?= \radix_html_form::text('phone', $this->Contact['phone'])?></div>

<div class="pure-u-1-5"><div class="l"><?=( strlen($this->Contact['email']) ? '<a href="mailto:' . $this->Contact['email'] . '">Email:</a>' : 'Email:' ) ?></div></div>
<div class="pure-u-4-5"><?= \radix_html_form::text('email', $this->Contact['email'])?></div>

<div class="pure-u-1-5"><div class="l"><?=( strlen($this->Contact['url']) ? '<a href="' . $this->Contact['url'] . '" target="_blank">Web-Site</a>:' : 'Web-Site:' ) ?></div></div>
<div class="pure-u-4-5"><?= \radix_html_form::text('url', $this->Contact['url']) ?></div>

<div class="pure-u-1-5"><div class="l">Tags:</div></div>
<div class="pure-u-4-5"><?= \radix_html_form::text('tags', $this->Contact['tags']) ?></div>

</div>

<?php

echo '<table>';

// First & Last Name
// if ($this->Contact['kind'] == 'Person') {
//     echo '<tr>';
//     echo '<td class="l" style="width:6em;">First:</td><td>' . $this->formText('first_name',$this->Contact['first_name'],array('style'=>'width: 100%')) . '</td>';
//     echo '<td class="l" style="width:5em;">Last:</td><td>' . $this->formText('last_name',$this->Contact['last_name'],array('style'=>'width: 100%')) . '</td>';
//     echo '</tr>';
// }

// Kind & Status
echo '<tr>';
echo '<td class="l">Kind:</td><td>' . \radix_html_form::select('kind', $this->Contact['kind'], $this->KindList) . '</td>';
echo '<td class="l">Status:</td><td>' . \radix_html_form::select('status', $this->Contact['status'], $this->StatusList) . '</td>';
echo '</tr>';

// Channels
if (!empty($this->ContactChannelList)) {
    $list = array();
    foreach ($this->ContactChannelList as $cc) {
    	$buf = Radix::block('stub-channel', $cc);
        // $buf = ContactChannel::$kind_list[$cc->kind];
        //$buf = null;
        // if (strlen($cc->name)) {
        //     $buf.= $cc->name . ': ';
        // }
        //switch ($cc->kind) {
        //case ContactChannel::PHONE:
        //case ContactChannel::FAX:
            
            //$buf.= $this->link('/contact.channel/view?id='.$cc->id,$phone_img);
        //    break;
        //case ContactChannel::EMAIL:
        //    $buf = $this->partial('../elements/stub-channel.phtml',array('data'=>$cc));
            //$buf.= $this->link('/contact.channel/view?id='.$cc->id,$email_img);
        //    break;
        //default:
        //    $buf = $cc->data;
        //}
        $list[] = $buf;
    }
    echo '<tr><td class="l">Channels:</td><td colspan="5">' . implode(', ', $list) . '</td></tr><tr>';
}


// Flags
// $flag_list = array();
// if ($this->Contact['kind'] == 'Person') {
//     $flag_list[] = '<label style="margin-right:16px;"><input ' . ($this->Contact->hasFlag(Contact::FLAG_BILL) ? 'checked="checked" ' : null) . 'name="flag_bill" type="checkbox" value="1" /> Billing Contact</label>';
//     $flag_list[] = '<label style="margin-right:16px;"><input ' . ($this->Contact->hasFlag(Contact::FLAG_SHIP) ? 'checked="checked" ' : null) . 'name="flag_ship" type="checkbox" value="1" /> Shipping Contact</label>';
// }
// if (count($flag_list)) {
//     echo '<tr><td class="l">Flags:</td><td colspan="3">' . implode(' ',$flag_list) . '</td></tr>';
// }

// Account?
// $list = Account::listAccounts();
// $AccountList = array(
//     null => '-None-',
// );
// foreach ($list as $x) {
//     $AccountList[$x->id] = $x->full_name;
// }

echo '<tr>';
echo '<td class="l">';
if (empty($this->Account['id'])) {
    echo 'Account';
} else {
    echo '<a href="' . Radix::link('/account/ledger?' . http_build_query(array('id'=>$this->Account->id))) . '">Account</a>:';
}
echo '</td><td colspan="3">';
if (empty($this->Account['id'])) {
    echo '<button class="s" name="c" title="Create new Account for this Contact" type="submit" value="create-account">Create</button>';
} else {
    echo '<input id="account" style="width:20em;" type="text" value="' . $this->Account['full_name'] . '">';
    echo '<input id="account_id" name="account_id" type="hidden" value="' . $this->Account['id'] . '">';
    echo ' $' . number_format($this->Account['balance'],2);
}
echo '</td></tr>';

// Google Contact Detail
echo '<tr><td class="l">Google:</td><td colspan="3"><div id="contact-google-area"><input id="contact-google-view" type="button" value="View" ></div></td></tr>';


echo '</table>';

echo '<div class="bf">';
echo '<input class="good" name="a" type="submit" value="Save">';
//if ($this->Contact['kind'] == 'Person') {
//    echo '<input name="c" title="Mark as Billing Contact" type="submit" value="Bill">';
//    echo '<input name="c" title="Mark as Shipping Contact" type="submit" value="Ship">';
//}
echo '<input class="fail" name="a" type="submit" value="Delete">';
echo '</div>';
echo '</form>';

?>
<script>
$(function() {
    $("#company").autocomplete({
        source: "<?= Radix::link('/contact/ajax?field=company'); ?>",
        change: function(event, ui) {
			if (!ui.item) return;
			$("#parent_id").val(ui.item.id);
			$("#company").val(ui.item.company);
		},
		focus:function(event, ui) {
			if (!ui.item) return;
			$("#company").val(ui.item.company);
		},
		select:function(event, ui) {
			if (!ui.item) return;
			$("#parent_id").val(ui.item.id);
			$("#company").val(ui.item.company);
		}
    });
    $("#contact").autocomplete({
        source: "<?= Radix::link('/contact/ajax?field=contact'); ?>",
        focus: function(event, ui) {
            if (ui.item) {
                $("#contact").val(ui.item.contact);
                event.preventDefault();
            }
        },
        select: function(event, ui) {
            if (!ui.item) return;
            $("#contact").val(ui.item.contact);
            event.preventDefault();
        }
    });
    // $("#kind").autocomplete({
    //     source: "<?= Radix::link('/contact/ajax?field=kind'); ?>"
    // });
    // $("#status").autocomplete({
    //     source: "<?php echo $this->link('/contact/ajax?field=status'); ?>"
    // });
    $("#contact-google-view").click(function() {
        var u = "<?= Radix::link('/contact/ajax?field=google'); ?>";
        $("#contact-google-area").load(u);
    });
    $('#email').on('change', function(e) {
		var t = $(this).val();
		var m = t.match(/^(.+)@(.+)$/);
    	if (m) {
    		var h = m[2].toLowerCase();
    		// @see http://en.wikipedia.org/wiki/Comparison_of_webmail_providers
    		switch (h) {
    		case 'aol.com':
    		case 'facebook.com':
    		case 'gmail.com':
    		case 'hotmail.com':
    		case 'inbox.com':
    		case 'mac.com':
    		case 'mail.com':
    		case 'mail.ru':
    		case 'outlook.com':
    		case 'sharklasers.com':
    		case 'yahoo.com':
    		case 'yandex.com':
    			return(0);
    			break;
    		}

    		var x = $('#url').val();
    		if (!x) {
    			$('#url').val(m[2]);
    		}
    	}
    });
});
</script>
<?php

if ($this->Contact['id'] == 0) {
    return(0);
}

// Sub Addresses
echo '<h2 id="ContactAddressHead"><a href="' . Radix::link('/contact/address?a=make') . '"><i class="fa fa-home"></i> Addresses</a></h2>';
if ($this->ContactAddressList) {
    echo "<div id='ContactAddressList'>";
    echo Radix::block('contact-address-list', array('list'=>$this->ContactAddressList));
    echo "</div>";
}

// Sub Contacts
if (empty($this->Contact['parent_id'])) {

    $x = array(
        'controller' => 'contact',
        'action' => 'create',
        'parent' => $this->Contact['id'],
    );
    $url = Radix::link('/contact/new?parent=' . $this->Contact['id']); // ($x,'default',true);

    echo '<h2 id="sub-contacts"><a href="' . $url . '"><i class="fa fa-users"></i> Sub-Contacts</a>';
    // echo '<span class="s">[ <a href="' . $url . '">';
    // echo img('/silk/1.3/user_add.png','Add Contact');
    // echo '</a> ]</span>';
    echo '</h2>';

    if (count($this->ContactList)) {
        echo '<table>';
        foreach ($this->ContactList as $item) {
            echo '<tr class="rero">';
            echo '<td><a href="' . Radix::link('/contact/view?c=' . $item['id']) . '">' . html($item['name']) . '</a></td>';
            // echo '<td>' . radix::block('stub-channel', array('data'=>$item['phone'])) . '</td>';
            // echo '<td>' . radix::block('stub-channel', array('data'=>$item['email'])) . '</td>';
            echo '<td>' . html($item['phone']) . '</td>';
            echo '<td>' . html($item['email']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

// Notes
$arg = array(
    'list' => $this->ContactNoteList,
    'page' => Radix::link('/note/edit?c=' . $this->Contact['id']),
);

echo Radix::block('note-list',$arg);

// Files
// Old way of Parameters
// $url = radix::link('/file/create?c=' . $this->Contact['id']);
// $arg = array(
//     'list' => $this->ContactFileList,
//     'page' => $url,
// );
echo Radix::block('file-list', $this->ContactFileList);

// Work Orders
echo '<h2><a href="' . Radix::link('/workorder/new?c=' . $this->Contact['id']) . '"><i class="fa fa-clock-o"></i> Work Orders</a></h2>';
if ($this->WorkOrderList) {
    echo Radix::block('workorder-list',array('list'=>$this->WorkOrderList));
}

// Invoices
echo '<h2><a href="' . Radix::link('/invoice/new?c=' . $this->Contact['id']) . '"><i class="fa fa-list"></i> Invoices</a></h2>';
if ($this->InvoiceList) {
    echo Radix::block('invoice-list', array('list'=>$this->InvoiceList));
}

// History
$x = array(
    'ajax' => true,
    'list' => $this->Contact->getHistory(),
);
echo Radix::block('diff-list',$x);

?>

<script type='text/javascript'>
$('#account').autocomplete({
    source: "<?= Radix::link('/account/ajax?a=account'); ?>",
    change: function(event, ui) { 
        if (ui.item) {
            $('#account').val(ui.item.label);
            $('#account_id').val(ui.item.id);
        }
    }
});
</script>
