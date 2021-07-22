<?php
/**
    Stub Channel
    Draws a Link and some pretty images to a channel

    @todo Detect String?

*/

namespace Edoceo\Imperium;

use Edoceo\Radix;

if (empty($data)) {
    return;
}

if (is_array($data)) {
	// OK
} elseif (is_object($data)) {
	if (!($data instanceof ContactChannel)) {
		echo "Invalid Parameter to stub-channel (" . print_r($data) . ')';
		return(0);
	}
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
//print_r($data);

// $edit_link = Radix::link('/contact.channel/view?id=' . $data['id']);
$icon = null;
switch ($data['kind']) {
case ContactChannel::EMAIL:
	$icon = '<i class="far fa-envelope"></i>';
	break;
case ContactChannel::FAX:
	$icon = '<i class="fas fa-fax"></i>';
	break;
case ContactChannel::PHONE:
	$icon = '<i class="fas fa-phone-square"></i>';
	break;
default:
	$icon = '#' . $data['kind'];
}

$html = $icon;

// Link if given the Channel ID
if (!empty($data['id'])) {
	$html = '<a href="' . Radix::link('/contact/channel?id=' . $data['id']) . '">' . $icon. '</a>';
}

$html.= ' ';

// Name Formatting
// $buf = ContactChannel::$kind_list[$cc->kind];
//if (strlen($data['name'])) {
//    $html.= $data['name'] . ': ';
//}

switch ($data['kind']) {
case ContactChannel::PHONE:
case ContactChannel::FAX:

    if (preg_match('/[a-z]+/', $data['data'])) {
        // Ignore
    } else {
        // No Letters
        $href = preg_replace('/[^\d]/',null, $data['data']);
        $href = sprintf('tel://%s', $href);
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

    $html.= '<span class="grow-huge">';
    $html.= sprintf('<a href="%s">%s</a>', $href, html($data['data']));
    $html.= '</span>';

    break;

case ContactChannel::EMAIL:

    $html.= '<span class="grow-huge">';
    $html.= '<a href="' . Radix::link('/email/compose?to=' . $data['data']) .'">';
    $html.= html($data['data']);
    $html.= '</a>';
    $html.= '</span>';

    break;

default:

    $html.= '<a href="' . Radix::link('/contact/channel?id=' . $data['id']) . '">';
    $html.= html($data['data']);
    $html.= '</a>';

    break;
}

echo $html;
