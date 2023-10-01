<?php
/**
    Email Compose View

    Compose a plain text message

    @copyright    2008 Edoceo, Inc
    @package    edoceo-imperium
    @link       http://imperium.edoceo.com
    @since      File available since Release 1013
*/

echo '<form action="' . radix::link('/email/send') . '" enctype="" method="post">';

echo '<table class="table">';
//if (!empty($this->EmailMessage->RecipientList)) {
//  echo '<tr><td class="b r">To:</td><td>' . $this->formSelect('to_s',$this->EmailMessage->to,array('style'=>'width: 50%;'),$this->EmailMessage->RecipientList) . '</td></tr>';
//  $this->EmailMessage->to = null;
//}
//echo '<tr><td class="b r">To:</td><td>' . $this->formText('to_t',$this->EmailMessage->to,array('style'=>'width: 50%;')) . '</td></tr>';

echo '<tr>';
echo '<td class="l">To:</td><td><input id="rcpt" name="rcpt" type="text" value="' . html($this->EmailMessage['rcpt']) . '" /></td>';
echo '</tr>';

echo '<tr><td class="b r">Subject:</td><td>' . radix_html_form::text('subj', $this->EmailMessage['subj']) . '</td></tr>';
echo '<tr><td class="b r">Message:</td><td>' . radix_html_form::textarea('body', $this->EmailMessage['body'],array('style'=>'height: 25em;','wrap'=>'off')) . '</td></tr>';
echo '</table>';

echo '<div class="cmd">';
echo '<button class="exec" name="a" value="send">Send</button>';
echo '</div>';

echo '</form>';

?>

<script type="text/javascript">
$(function() {
    $("#rcpt").autocomplete({
        focus: function(e,ui) {
            this.value = ui.item.email;
            return false;
        },
        select: function(e,ui) {
            this.value = ui.item.email;
            return false;
        },
        source: "<?php echo $this->link('/contact/ajax'); ?>"
    });
});
</script>
