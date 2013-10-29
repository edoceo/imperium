<?php
/**
    @file
    @brief Contact View Shows the Contact, Company or Vendors information

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

// Autocomplete for Company, Kind and Status
$script_append = <<<EOF
EOF;

echo '<form action="' . $this->link('/contact/save') . '?c=' . $this->Contact->id . '" method="post">';

echo '<div>';
echo radix_html_form::hidden('id',$this->Contact->id);
echo radix_html_form::hidden('parent_id',$this->Contact->parent_id);
echo '</div>';

echo '<table>';

// Company & Phone Number
echo '<tr>';
echo '<td class="l">';
if (!empty($this->Contact->parent_id)) {
    echo '<a href="' . radix::link('/contact/view?c=' . $this->Contact->parent_id) . '">Company:</a>';
} else {
    echo 'Company:';
}
echo '</td><td>' . radix_html_form::text('company', $this->Contact->company) . '</td>';
echo '<td class="l">Phone:</td><td>' . radix_html_form::text('phone', $this->Contact->phone) . '</td>';
echo '</tr>';

// Contact and Email
echo '<tr>';
echo '<td class="l" style="width:6em;">Contact:</td><td>' . radix_html_form::text('contact',$this->Contact->contact) . '</td>';
echo '<td class="l">' . ( strlen($this->Contact->email) ? "<a href=\"mailto:" . $this->Contact->email ."\">Email:</a>" : 'Email:' ) . '</td>';
echo '<td>' . radix_html_form::text('email', $this->Contact->email) . '</td>';
echo '</tr>';

// First & Last Name
// if ($this->Contact->kind == 'Person') {
//     echo '<tr>';
//     echo '<td class="l" style="width:6em;">First:</td><td>' . $this->formText('first_name',$this->Contact->first_name,array('style'=>'width: 100%')) . '</td>';
//     echo '<td class="l" style="width:5em;">Last:</td><td>' . $this->formText('last_name',$this->Contact->last_name,array('style'=>'width: 100%')) . '</td>';
//     echo '</tr>';
// }

// Web Site & Email
$url = parse_url($this->Contact->url);
if (empty($url['scheme'])) $url['scheme'] = 'http';
if (empty($url['host'])) {
    $url['host'] = $url['path'];
    $url['path'] = '/';
}
//print_r($url);
$url = sprintf('%s://%s%s',$url['scheme'],$url['host'],$url['path']);

$x = strlen($this->Contact->url) ? '<a href="' . $url . '" target="_blank">Web-Site</a>:' : 'Web-Site:';
echo '<tr>';
echo '<td class="l">' . $x . '</td><td>' . radix_html_form::text('url',$this->Contact->url,array('style'=>'width: 100%')) . '</td>';
echo '</tr>';

//switch ($this->Contact->kind) {
//case 'Company':

    // Contact First & Last
//     echo '<tr>';
//     echo '<td class="l">Contact:</td><td>' . $this->formText('contact',$this->Contact->contact,array('style'=>'width: 100%')) . '</td>';
//     echo '<td class="l">Email:</td><td>' . $this->formText('email',$this->Contact->email,array('style'=>'width: 100%')) . '</td>';
//     echo '</tr>';

//    break;
//case 'Person':

//     echo '<tr>';
//     // Phone
//     echo '<td class="l">Phone:</td><td>' . $this->formText('phone',$this->Contact->phone,array('style'=>'width: 80%')) . '</td>';
//     echo '</tr>';

//    break;
//default:
//    die("Unhandled Contact Kind: {$this->Contact->kind}");
//}

// Company
// echo '<tr>';
// echo '<td class="l">';
// if (!empty($this->Contact->parent_id)) {
//     echo '<a href="' . $this->link('/contact/view?c=' . $this->Contact->parent_id) . '">Company:</a>';
// } else {
//     echo 'Company:';
// }
// echo '</td><td>' . $this->formText('company',$this->Contact->company,array('style'=>'width: 100%')) . '</td>';

// Title or Phone
// if ( (empty($this->Contact->kind)) || ($this->Contact->kind == 'Person') ) {
//     echo '<td class="l">Title:</td><td>' . $this->formText('title',$this->Contact->title,array('style'=>'width: 100%')) . '</td>';
// } else {
//     echo '<td class="l">Phone:</td><td>' . $this->formText('phone',$this->Contact->phone,array('style'=>'width: 80%')) . '</td>';
// }
// echo '</tr>';

// Kind & Status
echo '<tr>';
echo '<td class="l">Kind:</td><td>' . radix_html_form::select('kind',$this->Contact->kind,null,$this->KindList) . '</td>';
echo '<td class="l">Status:</td><td>' . radix_html_form::select('status',$this->Contact->status,null,$this->StatusList) . '</td>';
echo '</tr>';

// Channels
if (!empty($this->ContactChannelList)) {
    //$phone_img = img('/silk/1.3/telephone_edit.png','Edit Telephone Number');
    //$email_img = img('/silk/1.3/email_edit.png','Edit Email');
    $list = array();
    foreach ($this->ContactChannelList as $cc) {
        // $buf = ContactChannel::$kind_list[$cc->kind];
        //$buf = null;
        // if (strlen($cc->name)) {
        //     $buf.= $cc->name . ': ';
        // }
        //switch ($cc->kind) {
        //case ContactChannel::PHONE:
        //case ContactChannel::FAX:
            $buf = radix::block('stub-channel',array('data'=>$cc));
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
    echo '<tr><td class="l">Channels:</td><td colspan="5">' . implode(', ',$list) . '</td></tr><tr>';
}


// Tags
echo '<tr>';
echo '<td class="l">Tags:</td>';
echo '<td colspan="3">' . radix_html_form::text('tags',$this->Contact->tags,array('style'=>'width: 100%')) . '</td>';
echo '</tr>';

// Flags
// $flag_list = array();
// if ($this->Contact->kind == 'Person') {
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
if (empty($this->Account->id)) {
    echo 'Account';
} else {
    echo '<a href="' . $this->link('/account/ledger?' . http_build_query(array('id'=>$this->Account->id))) . '">Account</a>:';
}
echo '</td><td colspan="3">';
if (empty($this->Account->id)) {
    echo '<button class="s" name="c" title="Create new Account for this Contact" type="submit" value="create-account">Create</button>';
} else {
    echo '<input id="account" style="width:20em;" type="text" value="' . $this->Account->full_name . '">';
    echo '<input id="account_id" name="account_id" type="hidden" value="' . $this->Account->id . '">';
    echo ' $' . number_format($this->Account->balance,2);
}
echo '</td></tr>';

// Google Contact Detail
echo '<tr><td class="l">Google:</td><td colspan="3"><div id="contact-google-area"><input id="contact-google-view" type="button" value="View" ></div></td></tr>';


echo '</table>';

echo '<div class="bf">';
echo '<input name="a" type="submit" value="Save">';
//if ($this->Contact->kind == 'Person') {
//    echo '<input name="c" title="Mark as Billing Contact" type="submit" value="Bill">';
//    echo '<input name="c" title="Mark as Shipping Contact" type="submit" value="Ship">';
//}
echo '<input name="a" type="submit" value="Delete">';
echo '</div>';
echo '</form>';

?>
<script>
$(document).ready(function() {
    $("#company").autocomplete({
        source: "<?php echo $this->link('/contact/ajax?field=company'); ?>",
        change: function(event, ui) { if (ui.item) { $("#parent_id").val(ui.item.id); } }
    });
    $("#contact").autocomplete({
        source: "<?php echo $this->link('/contact/ajax?field=contact'); ?>",
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
    //     source: "<?php echo $this->link('/contact/ajax?field=kind'); ?>"
    // });
    // $("#status").autocomplete({
    //     source: "<?php echo $this->link('/contact/ajax?field=status'); ?>"
    // });
    $("#contact-google-view").click(function() {
        var u = "<?php echo radix::link('/contact/ajax?field=google'); ?>";
        $("#contact-google-area").load(u);
    });
});
</script>
<?php

if ($this->Contact->id == 0) {
    return(0);
}

// Sub Addresses
$i = img('/silk/1.3/report_add.png','Add Address');
echo '<h2 id="ContactAddressHead">Addresses';
echo '<span class="s">[ <a class="fb" href="' . radix::link('/contact/address?a=make') . '">' . $i . '</a> ]</span>';
echo '</h2>';
if ($this->ContactAddressList) {
    echo "<div id='ContactAddressList'>";
    echo radix::block('contact-address-list', array('list'=>$this->ContactAddressList));
    echo "</div>";
}

// Sub Contacts
if (empty($this->Contact->parent_id)) {

    $x = array(
        'controller' => 'contact',
        'action' => 'create',
        'parent' => $this->Contact->id,
    );
    $url = radix::link('/contact/create?parent=' . $this->Contact->id); // ($x,'default',true);

    echo '<h2 id="sub-contacts">Sub-Contacts';
    echo '<span class="s">[ <a href="' . $url . '">';
    echo img('/silk/1.3/user_add.png','Add Contact');
    echo '</a> ]</span>';
    echo '</h2>';

    if (count($this->ContactList)) {
        echo '<table>';
        foreach ($this->ContactList as $item) {
            echo '<tr class="rero">';
            echo '<td>' . radix::link('/contact/view?c='.$item->id,$item->name) . '</td>';
            echo '<td>' . radix::block('stub-channel', array('data'=>$item->phone)) . '</td>';
            echo '<td>' . radix::block('stub-channel', array('data'=>$item->email)) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

// Notes
$url = radix::link('/note/create?c=' . $this->Contact->id);
$arg = array(
    'list' => $this->ContactNoteList,
    'page' => $url,
);
echo radix::block('note-list',$arg);

// Files
$url = radix::link('/file/create?c=' . $this->Contact->id);
$arg = array(
    'list' => $this->ContactFileList,
    'page' => $url,
);
echo radix::block('file-list',$arg);

// Work Orders
echo '<h2>';
echo 'Work Orders';
echo '<span class="s">[ <a href="' . radix::link('/workorder/create?c=' . $this->Contact->id) . '">';
echo img('/silk/1.3/table_add.png','Create Work Order');
echo '</a> ]</span>';
echo '</h2>';
if ($this->WorkOrderList) {
    echo radix::block('workorder-list',array('list'=>$this->WorkOrderList));
}

// Invoices
echo '<h2>';
echo 'Invoices';
echo '<span class="s">[ <a href="' . radix::link('/invoice/create?c=' . $this->Contact->id) . '">';
echo img('/silk/1.3/layout_add.png','Create Invoice');
echo '</a> ]</span>';
echo '</h2>';
if ($this->InvoiceList) {
    echo radix::block('invoice-list', array('list'=>$this->InvoiceList));
}

// History
$x = array(
    'ajax' => true,
    'list' => $this->Contact->getHistory(),
);
echo radix::block('diff-list',$x);

?>

<script type='text/javascript'>
$('#account').autocomplete({
    source: "<?php echo radix::link('/account/ajax?a=account'); ?>",
    change: function(event, ui) { 
        if (ui.item) {
            $('#account').val(ui.item.label);
            $('#account_id').val(ui.item.id);
        }
    }
});
</script>
