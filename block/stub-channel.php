<?php
/**
    Stub Channel
    Draws a Link and some pretty images to a channel

*/

if (empty($data)) {
    return;
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

$id = crc32(serialize($data));

// $buf = ContactChannel::$kind_list[$cc->kind];
$html = "\n";
if (strlen($data['name'])) {
    $html.= $data['name'] . ': ';
}

$edit_link = radix::link('/contact.channel/view?id=' . $data['id']);

switch ($data['kind']) {
case ContactChannel::PHONE:
case ContactChannel::FAX:

    $f = $data['data'];
    if (preg_match('/[a-z]+/',$data['data'])) {
        // Ignore
    } else {
        // No Letters
        $n = preg_replace('/[^\d]/',null,$data['data']);
        $ext = null;

        if (empty($n)) {
            return null;
        }

        // $image = img('/silk/1.3/telephone_go.png','Call');

        // if (strpos($number,'x')!==false) {
        //     list($number,$ext) = explode('x',$number);
        // }

        // US format
        if (strlen($n)==10) {
            $n = "1$n";
            $f = trim(substr($n,1,3) . '.' . substr($n,4,3) . '.' . substr($n,7));
        } else {
            $f = trim($data['data']);
        }

        if (strlen($n) == 0) $n = 'Empty';
        if (strlen($f) == 0) $f = $n;

        $f .= (strlen($ext) ? " x $ext" : null);
    }

    $html.= sprintf('<a href="tel://%s">%s</a>',preg_replace('/[^\d]/',null,$data['data']),$f);

    if (!empty($data->id)) {

        $i = img('/silk/1.3/telephone_edit.png','Edit Telephone Number');

        $html.= '<a class="fb" href="' . $edit_link . '">';
        $html.= $i;
        $html.= '</a>';
    }

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

    //$email_img = img('/silk/1.3/email_edit.png','Edit Email');
    //$image = img('/silk/1.3/email_go.png','Email');
    $html = '<a href="' . radix::link('/email/compose?to=' . $data['data']) .'">';
    $html.= htmlspecialchars($data['data']);
    $html.= '</a>';
    if (!empty($data['id'])) {
        $i = img('/silk/1.3/email_edit.png','Edit Email');
        $html.= '<a href="' . $edit_link . '">';
        $html.= $i;
        $html.= '</a>';
    }
    break;
default:
    // $i = img('/silk/1.3/email_edit.png','Edit Email');
    $html.= $data['data'];
    $html.= '[D]';
    $html.= '<a href="' . $edit_link . '">';
    $html.= '[Edit]';
    $html.= '</a>';
}

echo $html;
