<?php
/**
    @file
    @brief Views a Note using the note/edit element
*/

require_once('markdown.php');

echo '<div class="markdown" style="background:#e0e0e0;border:2px inset #666;padding:8px;">';
//if (substr($this->Note->data,0,1)=='#') {
    echo markdown($this->Note->data);
// } else {
//     echo '<pre style="overflow:auto;">';
//     echo html($this->Note->data);
//     echo '</pre>';
// }
echo '</div>';

echo '<form action="' . $this->link('/note/save') . '" id="note-edit-form" method="post">';
echo '<div class="cmd">';
echo $this->formHidden('id',$this->Note->id);
echo $this->formHidden('link',$this->Note->link);
echo '<input name="c" type="submit" value="Edit">';
echo '</div>';
echo '</form>';
