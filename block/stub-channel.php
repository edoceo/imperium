<?php
/**
    Stub Channel
    Draws a Link and some pretty images to a channel

*/

namespace Edoceo\Imperium;

use Radix;

if (empty($data)) {
    return;
}
// Radix::dump($data);
if (!($data instanceof ContactChannel)) {
	echo "Invalid Parameter to stub-channel";
	return(0);
}


// Just a String? Promot to Object
// if (is_string($data)) {
//     $x = $data;
//     $data = new stdClass();
//     $data->id = null;
//     $data->name = null;
//     $data->data = $x;
//     if (preg_match('/[\w\-]+@[\w\-]+\.\w+/',$x)) {
//         $data->kind = ContactChannel::EMAIL;
//     } else {
//         $data->kind = ContactChannel::PHONE;
//     }
// }

// $edit_link = Radix::link('/contact.channel/view?id=' . $data['id']);

switch ($data['kind']) {
case ContactChannel::FAX:
	$icon = '<i class="fa fa-fax"></i>';
	break;
default:
	$icon = null;
}

// Name Formatting
// $buf = ContactChannel::$kind_list[$cc->kind];
if (strlen($data['name'])) {
    $html.= $data['name'] . ': ';
}

switch ($data['kind']) {
case ContactChannel::PHONE:
case ContactChannel::FAX:

	echo '<a href="' . Radix::link('/contact/channel?id=' . $data['id']) . '"><i class="fa fa-phone"></i></a> ';

    if (preg_match('/[a-z]+/', $data['data'])) {
        // Ignore
    } else {
        // No Letters
        $link = preg_replace('/[^\d]/',null, $link);
        // $ext = null;

        // if (strpos($number,'x')!==false) {
        //     list($number,$ext) = explode('x',$number);
        // }

        // US format
        // if (strlen($n)==10) {
        //     $n = "1$n";
        //     $f = trim(substr($n,1,3) . '.' . substr($n,4,3) . '.' . substr($n,7));
        // } else {
        //     $f = trim($data['data']);
        // }
        // 
        // if (strlen($n) == 0) $n = 'Empty';
        // if (strlen($f) == 0) $f = $n;
        // 
        // $f .= (strlen($ext) ? " x $ext" : null);
    }

    echo sprintf('<a href="tel://%s">%s</a>', $link, html($data['data']));

    // if (!empty($data->id)) {
    // 
    //     $i = img('/silk/1.3/telephone_edit.png','Edit Telephone Number');
    // 
    //     $html.= '<a class="fb" href="' . $edit_link . '">';
    //     $html.= $i;
    //     $html.= '</a>';
    // }

    // echo '&nbsp;<a href='sip:$n'>$image</a>';

    //echo "<script type=\"text/javascript\">\n";
    //echo "$(document).ready(function() {\n";
    //echo "$('#ch$id').fancybox({'height':'20%'});\n"; // ,'titleShow':false,'width':'60%'});\n";
    //echo "});</script>\n";
    //echo '<div id="' . sprintf('phone-%x',$id) . '" style="display:none;font-size:28pt;">';
    //echo $buf . '<br>' . $f;
    //echo '</div>';

    // $buf.= $this->link('/contact.channel/view?id='.$this->data->id,$phone_img);
    break;

case ContactChannel::EMAIL:

	echo '<a href="' . Radix::link('/contact/channel?id=' . $data['id']) . '"><i class="fa fa-envelope-o"></i></a> ';

    //$email_img = img('/silk/1.3/email_edit.png','Edit Email');
    //$image = img('/silk/1.3/email_go.png','Email');
    echo '<a href="' . Radix::link('/email/compose?to=' . $data['data']) .'">';
    echo html($data['data']);
    echo '</a>';
    // if (!empty($data['id'])) {
    //     $i = img('/silk/1.3/email_edit.png','Edit Email');
    //     $html.= '<a href="' . $edit_link . '">';
    //     $html.= $i;
    //     $html.= '</a>';
    // }

    break;
default:
    // $i = img('/silk/1.3/email_edit.png','Edit Email');
    echo '<a href="' . Radix::link('/contact/channel?id=' . $data['id']) . '">';
    echo html($data['data']);
    echo '</a>';
}

// echo trim("$icon $name $link");
// 
// echo $html;
