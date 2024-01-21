<?php
/**
 * Contact View Shows the Contact, Company or Vendors information
 *
 * SPDX-License-Identifier: GPL-3.0-only
 */

namespace Edoceo\Imperium;

use Edoceo\Radix;
use Edoceo\Radix\Layout;
use Edoceo\Radix\HTML\Form;

if (!empty($this->Contact['id'])) {
	App::addMRU('/contact/view?c=' . $this->Contact['id'], '<i class="far fa-user"></i> ' . html($this->Contact['name']));
}

?>

<form action="<?= Radix::link('/contact/save?c=' . $this->Contact['id']) ?>" autocomplete="off" method="post">

<div class="row">
<div class="col-md-6">
	<div class="input-group mb-2">
		<label class="input-group-text">Contact:</label>
		<?= Form::text('contact',$this->Contact['contact'], array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-6">
	<div class="input-group mb-2">
		<label class="input-group-text">Company:</label>
		<?= Form::text('company',$this->Contact['company'], array('class' => 'form-control'))?>
		<?php
		if (!empty($this->Contact['parent_id'])) {
			echo '<span class="input-group-addon">';
			echo '<a href="' . Radix::link('/contact/view?c=' . $this->Contact['parent_id']) . '"><i class="fas fa-bolt"></i></a>';
			echo '</span>';
		}
		?>
	</div>
</div>
</div>

<div class="row">
<div class="col-md-6">
	<div class="input-group mb-2">
		<label class="input-group-text">Phone:</label>
		<?= Form::text('phone', $this->Contact['phone'], array('class' => 'form-control'))?>
	</div>
</div>

<div class="col-md-6">
	<div class="input-group mb-2">
		<label class="input-group-text"><?=( strlen($this->Contact['email']) ? '<a href="mailto:' . $this->Contact['email'] . '">Email:</a>' : 'Email:' ) ?></label>
		<?= Form::text('email', $this->Contact['email'], array('class' => 'form-control'))?>
	</div>
</div>
</div>

<div class="row">
<div class="col-md-5">
	<div class="input-group mb-2">
		<label class="input-group-text"><?=( strlen($this->Contact['url']) ? '<a href="' . $this->Contact['url'] . '" target="_blank">Web-Site</a>:' : 'Web-Site:' ) ?></label>
		<?= Form::text('url', $this->Contact['url'], array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-3">
	<div class="input-group mb-2">
		<label class="input-group-text">Tags:</label>
		<?= Form::text('tags', $this->Contact['tags'], array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-2">
	<div class="input-group mb-2">
		<label class="input-group-text">Kind</label>
		<?= Form::select('kind', $this->Contact['kind'], $this->KindList, array('class' => 'form-control')) ?>
	</div>
</div>
<div class="col-md-2">
	<div class="input-group mb-2">
		<label class="input-group-text">Status:</label>
		<?= Form::select('status', $this->Contact['status'], $this->StatusList, array('class' => 'form-control')) ?>
	</div>
</div>
</div> <!-- /.row -->

<!-- Images? -->
<div class="row">
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
?>

<div class="form-actions">
	<?= Form::hidden('id',$this->Contact['id']) ?>
	<?= Form::hidden('parent_id', $this->Contact['parent_id']) ?>
	<button class="btn btn-primary" name="a" type="submit" value="save">Save</button>
	<button class="btn btn-danger" name="a" type="submit" value="delete">Delete</button>
	<!-- <button class="btn" id="exec-contact-ping" type="button" value="ping">Ping</button> -->
	<!-- <button class="btn" name="a" type="submit" value="capture">Photo</button>'; -->
	<button class="btn btn-danger" name="a" type="submit" value="delete">Delete</button>
	<!-- <a class="btn" href="' . Radix::link('/contact/merge?' . http_build_query(array('c' => $this->Contact['id']))) . '">Merge</a> -->
</div>

</form>

<?php
ob_start();
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
$code = ob_get_clean();
Layout::addScript($code);


if ($this->Contact['id'] == 0) {
    return(0);
}

// Sub Addresses
echo '<section>';

echo '<h2 id="ContactAddressHead"><a href="' . Radix::link('/contact/address?' . http_build_query(array('a' => 'make', 'c' => $this->Contact['id']))) . '"><i class="far fa-address-card"></i> Addresses</a></h2>';
if ($this->ContactAddressList) {
    echo "<div id='ContactAddressList'>";
    echo Radix::block('contact-address-list', array('list'=>$this->ContactAddressList));
    echo "</div>";
}
echo '</section>';

// Sub Contacts
require_once(__DIR__ . '/view-contact-family.php');

// Notes
echo '<section>';
echo Radix::block('note-list', [
    'list' => $this->ContactNoteList,
    'page' => Radix::link('/note/edit?c=' . $this->Contact['id']),
]);
echo '</section>';

// Files
// Old way of Parameters
// $url = Radix::link('/file/create?c=' . $this->Contact['id']);
// $arg = array(
//     'list' => $this->ContactFileList,
//     'page' => $url,
// );

echo '<section class="mb-2">';
echo Radix::block('file-list', $this->ContactFileList);
echo '</section>';

?>
<section class="mb-2">
<div class="d-flex justify-content-between">
	<div><h2><i class="far fa-clock"></i> Work Orders</h2></div>
	<div><a class="btn btn-secondary" href="<?= Radix::link('/workorder/new?c=' . $this->Contact['id']) ?>"><i class="far fa-plus-square"></i></a></div>
</div>
<?php
if ($this->WorkOrderList) {
    echo Radix::block('workorder-list',array('list'=>$this->WorkOrderList));
}
?>
</section>

<section class="mb-2">
<div class="d-flex justify-content-between">
	<div><h2><i class="fas fa-file-invoice-dollar"></i> Invoices</a></h2></div>
	<div><a class="btn btn-secondary" href="<?= Radix::link('/invoice/new?c=' . $this->Contact['id']) ?>"><i class="far fa-plus-square"></i></a></div>
</div>
<?php
if ($this->InvoiceList) {
    echo Radix::block('invoice-list', array('list'=>$this->InvoiceList));
}
echo '</section>';

require_once(__DIR__ . '/view-account.php');

// History
$x = array(
    'ajax' => true,
    'list' => $this->Contact->getHistory(),
);
echo Radix::block('diff-list',$x);
