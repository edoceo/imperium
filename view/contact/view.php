<?php
/**
    @file
    @brief Contact View Shows the Contact, Company or Vendors information
*/

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Filter;
use Edoceo\Radix\HTML\Form;

App::addMRU('/contact/view?c=' . $this->Contact['id'], '<i class="fa fa-user"></i> ' . html($this->Contact['name']));

if (!empty($this->Contact['id'])) {
       App::addMRU('/contact/view?c=' . $this->Contact['id'], '<i class="fa fa-user"></i> ' . html($this->Contact['name']));
}

?>

<style>
form div > label {
	display: block;
	font-weight: bold;
}
.fb {
	display: flex;
	flex-wrap: wrap;
}
.fb .fi {
	flex: 1 1 auto;
}
</style>

<form action="<?= Radix::link('/contact/save?c=' . $this->Contact['id']) ?>" autocomplete="off" method="post">

<div>
<?= Form::hidden('id',$this->Contact['id']); ?>
<?= Form::hidden('parent_id',$this->Contact['parent_id']); ?>
</div>

<div class="fb" style="margin:16px;">

<div class="fi">
	<label>Contact:</label>
	<?= Form::text('contact',$this->Contact['contact'])?>
</div>
<div class="fi">
	<label>Company:</label>
	<?= Form::text('company',$this->Contact['company'])?>
	<?= Form::hidden('parent_id', $this->Contact['parent_id']) ?>
<?php
if (!empty($this->Contact['parent_id'])) {
    echo ' <a href="' . Radix::link('/contact/view?c=' . $this->Contact['parent_id']) . '"><i class="fa fa-bolt"></i></a>';
}
?>
</div>

<div class="fi">
	<label>Phone:</label>
	<?= Form::text('phone', $this->Contact['phone'])?>
</div>

<div class="fi">
	<label><?=( strlen($this->Contact['email']) ? '<a href="mailto:' . $this->Contact['email'] . '">Email:</a>' : 'Email:' ) ?></label>
	<?= Form::text('email', $this->Contact['email'])?>
</div>

<div class="fi">
	<label><?=( strlen($this->Contact['url']) ? '<a href="' . $this->Contact['url'] . '" target="_blank">Web-Site</a>:' : 'Web-Site:' ) ?></label>
	<?= Form::text('url', $this->Contact['url']) ?>
</div>

<div class="fi">
	<label>Tags:</label>
	<?= Form::text('tags', $this->Contact['tags']) ?>
</div>

<div class="fi">
	<label>Kind</label>
	<?= Form::select('kind', $this->Contact['kind'], $this->KindList) ?>
</div>
<div class="fi">
	<label>Status:</label>
	<?= Form::select('status', $this->Contact['status'], $this->StatusList) ?>
</div>
</div>

<!-- Images? -->
<div>
<?php
$img = sprintf('/img/content/contact/%u/0.jpg', $this->Contact['id']);
$src = sprintf('%s/webroot/%s', APP_ROOT, $img);
if (is_file($src)) {
	echo '<img alt="Snap" src="' . Radix::link($img) . '">';
}
?>
</div>

<?php
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
    echo '<div style="margin:16px;"><label>Channels:</label>' . implode(', ', $list) . '</div>';
}

// Google Contact Detail
// echo '<tr><td class="l">Google:</td><td colspan="3"><div id="contact-google-area"><input id="contact-google-view" type="button" value="View" ></div></td></tr>';

echo '<div class="form-controls">';
echo '<button class="good" name="a" type="submit" value="save">Save</button>';
// echo '<button class="exec" id="exec-contact-ping" type="button" value="ping">Ping</button>';
// echo '<button class="exec" name="a" type="submit" value="capture">Photo</button>';
echo '<button class="fail" name="a" type="submit" value="delete">Delete</button>';
// echo '<a class="button" href="' . Radix::link('/contact/merge?' . http_build_query(array('c' => $this->Contact['id']))) . '">Merge</a>';
echo '</div>';
echo '</form>';

?>
<script>
$(function() {

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
			case 'comcast.net':
			case 'facebook.com':
			case 'gmail.com':
			case 'hotmail.com':
			case 'icloud.com':
			case 'inbox.com':
			case 'live.com':
			case 'mac.com':
			case 'mail.com':
			case 'mail.ru':
			case 'msn.com':
			case 'outlook.com':
			case 'pobox.com':
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

    // Ping the Contact
    $('#exec-contact-ping').on('click', function(e) {
		Imperium.modal('/contact/ping?c=<?= $this->Contact['id'] ?>');
		e.preventDefault();
		e.stopPropagation();
		return false;
    });

});
</script>
<?php

if ($this->Contact['id'] == 0) {
    return(0);
}

// Sub Addresses
echo '<h2 id="ContactAddressHead"><a href="' . Radix::link('/contact/address?' . http_build_query(array('a' => 'make', 'c' => $this->Contact['id']))) . '"><i class="fa fa-home"></i> Addresses</a></h2>';
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
            // echo '<td>' . Radix::block('stub-channel', array('data'=>$item['phone'])) . '</td>';
            // echo '<td>' . Radix::block('stub-channel', array('data'=>$item['email'])) . '</td>';
            echo '<td>' . html($item['phone']) . '</td>';
            echo '<td>' . html($item['email']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

// Event History
//$arg = array(
//    'list' => $this->ContactNoteList,
//    'page' => Radix::link('/note/edit?c=' . $this->Contact['id']),
//);
//echo Radix::block('note-list',$arg);

// Notes
$arg = array(
    'list' => $this->ContactNoteList,
    'page' => Radix::link('/note/edit?c=' . $this->Contact['id']),
);

echo Radix::block('note-list',$arg);

// Files
// Old way of Parameters
// $url = Radix::link('/file/create?c=' . $this->Contact['id']);
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

// Accounts
echo '<div>';
echo '<h2>Accounts: </h2>';
if (empty($this->Account['id'])) {
?>
	<form action="<?= Radix::link('/contact/save?c=' . $this->Contact['id']) ?>" autocomplete="off" method="post">
	<div>
	<p>Receiveable: <button class="exec" name="a" title="Create new Account for this Contact" style="margin:4px;" type="submit" value="create-account">Create</button>
	</div>
	</form>
<?php
} else {
	echo '<a href="' . Radix::link('/account/ledger?' . http_build_query(array('id' => $this->Account['id']))) . '">Account</a>:';
	echo '<span style="font-size: 22px; line-height: 32px;">';
	echo '$' . number_format($this->Account['balance'],2);
	// echo '<input id="account" style="width:20em;" type="text" value="' . $this->Account['full_name'] . '">';
	// echo '<input id="account_id" name="account_id" type="hidden" value="' . $this->Account['id'] . '">';
	echo '</span>';
}
echo '</div>';


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
